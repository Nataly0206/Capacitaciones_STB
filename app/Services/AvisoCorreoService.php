<?php

namespace App\Services;

use App\Mail\AvisoCapacitacionMail;
use App\Models\AvisoCorreo;
use App\Models\ConfiguracionAviso;
use App\Models\EmpleadoCapacitacion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AvisoCorreoService
{
    private const DIAS_ANTICIPACION_POR_VENCER = 2;

    private function fechaSql(): string
    {
        return now()->format('Ymd H:i:s');
    }

    private function asegurarConfiguracionesAutomaticas(): void
    {
        $configuraciones = [
            'asignada' => null,
            'por_vencer' => self::DIAS_ANTICIPACION_POR_VENCER,
            'vencida' => null,
            'terminada' => null,
        ];

        foreach ($configuraciones as $tipoAviso => $diasAnticipacion) {
            ConfiguracionAviso::firstOrCreate(
                ['tipo_aviso' => $tipoAviso],
                [
                    'dias_anticipacion' => $diasAnticipacion,
                    'enviar_a_empleado' => 1,
                    'enviar_a_admin' => 1,
                    'activo' => 1,
                ]
            );
        }
    }

    public function generarYEnviarAvisoAsignacion(EmpleadoCapacitacion $asignacion): array
    {
        $this->asegurarConfiguracionesAutomaticas();

        $resultado = [
            'creados' => 0,
            'enviados' => 0,
            'errores' => 0,
        ];

        $configuracion = ConfiguracionAviso::where('tipo_aviso', 'asignada')->first();

        if (!$configuracion || (int) $configuracion->activo !== 1) {
            return $resultado;
        }

        $asignacion->loadMissing([
            'empleado.empleadoUser.user',
            'capacitacion.instructor',
        ]);

        $usuario = $asignacion->empleado?->empleadoUser?->user;
        $destinatarios = $this->obtenerDestinatariosAviso($asignacion, $configuracion);

        foreach ($destinatarios as $destinatario) {
            $aviso = $this->crearAvisoAsignacionSiNoExiste(
                $asignacion,
                $configuracion,
                $destinatario['tipo'],
                $destinatario['email'],
                $usuario
            );

            if (!$aviso) {
                continue;
            }

            $resultado['creados']++;

            if ($this->enviarAvisoIndividual($aviso)) {
                $resultado['enviados']++;
            } else {
                $resultado['errores']++;
            }
        }

        return $resultado;
    }

    public function generarAvisos(): array
    {
        $this->asegurarConfiguracionesAutomaticas();

        $this->sincronizarAsignacionesAntesDeAvisos();

        $resultado = [
            'por_vencer' => 0,
            'vencida' => 0,
            'terminada' => 0,
            'total' => 0,
        ];

        Log::info('STB avisos: inicio de generación automática.', [
            'fecha' => now()->format('Y-m-d H:i:s'),
        ]);

        $configuraciones = ConfiguracionAviso::where('activo', 1)->get();

        foreach ($configuraciones as $configuracion) {
            if ($configuracion->tipo_aviso === 'por_vencer') {
                $cantidad = $this->generarAvisosPorVencer($configuracion);
                $resultado['por_vencer'] += $cantidad;
                $resultado['total'] += $cantidad;
            }

            if ($configuracion->tipo_aviso === 'vencida') {
                $cantidad = $this->generarAvisosVencidos($configuracion);
                $resultado['vencida'] += $cantidad;
                $resultado['total'] += $cantidad;
            }

            if ($configuracion->tipo_aviso === 'terminada') {
                $cantidad = $this->generarAvisosTerminados($configuracion);
                $resultado['terminada'] += $cantidad;
                $resultado['total'] += $cantidad;
            }
        }

        Log::info('STB avisos: generación automática finalizada.', $resultado);

        return $resultado;
    }

    private function sincronizarAsignacionesAntesDeAvisos(): void
    {
        EmpleadoCapacitacion::whereNotIn('estado', ['cancelada'])
            ->orderBy('id_empleado_capacitacion')
            ->chunkById(100, function ($asignaciones) {
                foreach ($asignaciones as $asignacion) {
                    try {
                        app(ResumenCapacitacionEmpleadoService::class)->recalcular($asignacion);
                    } catch (\Throwable $e) {
                        Log::error('STB avisos: error al recalcular asignación antes de generar avisos.', [
                            'id_empleado_capacitacion' => $asignacion->id_empleado_capacitacion ?? null,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }, 'id_empleado_capacitacion');
    }

    private function generarAvisosPorVencer(ConfiguracionAviso $configuracion): int
    {
        $hoy = Carbon::today();

        $diasAnticipacion = is_null($configuracion->dias_anticipacion)
            ? self::DIAS_ANTICIPACION_POR_VENCER
            : (int) $configuracion->dias_anticipacion;

        $asignaciones = EmpleadoCapacitacion::with(['empleado.empleadoUser.user', 'capacitacion.instructor'])
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->whereNull('fecha_finalizacion')
            ->whereNotNull('fecha_vencimiento')
            ->get()
            ->filter(function ($asignacion) use ($hoy, $diasAnticipacion) {
                $fechaVencimiento = $asignacion->fecha_vencimiento
                    ? Carbon::parse($asignacion->fecha_vencimiento)->startOfDay()
                    : null;

                if (!$fechaVencimiento) {
                    return false;
                }

                $fechaInicioAvisos = $asignacion->fecha_limite
                    ? Carbon::parse($asignacion->fecha_limite)->startOfDay()
                    : $fechaVencimiento->copy()->subDays($diasAnticipacion);

                return $hoy->between($fechaInicioAvisos, $fechaVencimiento, true);
            });

        return $this->crearAvisosParaAsignaciones($asignaciones, $configuracion);
    }

    private function generarAvisosVencidos(ConfiguracionAviso $configuracion): int
    {
        $hoy = Carbon::today();

        $asignaciones = EmpleadoCapacitacion::with(['empleado.empleadoUser.user', 'capacitacion.instructor'])
            ->whereIn('estado', ['pendiente', 'en_proceso', 'vencida'])
            ->whereNull('fecha_finalizacion')
            ->whereNotNull('fecha_limite')
            ->get()
            ->filter(function ($asignacion) use ($hoy) {
                $fechaLimite = $this->fechaReferenciaLimiteRetraso($asignacion);

                if (!$fechaLimite) {
                    return false;
                }

                return $fechaLimite->lt($hoy);
            });

        return $this->crearAvisosParaAsignaciones($asignaciones, $configuracion);
    }

    private function generarAvisosTerminados(ConfiguracionAviso $configuracion): int
    {
        $hoy = Carbon::today();

        $asignaciones = EmpleadoCapacitacion::with(['empleado.empleadoUser.user', 'capacitacion.instructor'])
            ->where(function ($query) {
                $query->whereNotNull('fecha_finalizacion')
                    ->orWhereNotNull('fecha_vencimiento')
                    ->orWhereNotNull('fecha_limite');
            })
            ->get()
            ->filter(function ($asignacion) use ($hoy) {
                if ($asignacion->fecha_finalizacion && Carbon::parse($asignacion->fecha_finalizacion)->lte(now())) {
                    return true;
                }

                $fecha = $this->fechaReferenciaVencimiento($asignacion);

                if (!$fecha) {
                    return false;
                }

                return $fecha->lt($hoy);
            });

        return $this->crearAvisosParaAsignaciones($asignaciones, $configuracion);
    }

    private function crearAvisosParaAsignaciones($asignaciones, ConfiguracionAviso $configuracion): int
    {
        $creados = 0;

        foreach ($asignaciones as $asignacion) {
            $asignacion->loadMissing([
                'empleado.empleadoUser.user',
                'capacitacion.instructor',
            ]);

            foreach ($this->obtenerDestinatariosAviso($asignacion, $configuracion) as $destinatario) {
                $creados += $this->crearAvisoSiNoExiste(
                    $asignacion,
                    $configuracion,
                    $destinatario['tipo'],
                    $destinatario['email']
                );
            }
        }

        return $creados;
    }

    private function crearAvisoSiNoExiste(
        EmpleadoCapacitacion $asignacion,
        ConfiguracionAviso $configuracion,
        string $destinatarioTipo,
        string $destinatarioEmail
    ): int {
        $consultaAvisoExistente = AvisoCorreo::where('id_empleado_capacitacion', $asignacion->id_empleado_capacitacion)
            ->where('id_configuracion_aviso', $configuracion->id_configuracion_aviso)
            ->where('tipo_aviso', $configuracion->tipo_aviso)
            ->where('destinatario_tipo', $destinatarioTipo)
            ->where('destinatario_email', $destinatarioEmail)
            ->whereIn('estado', ['pendiente', 'enviado', 'error']);

        if (in_array($configuracion->tipo_aviso, ['por_vencer', 'vencida'], true)) {
            $consultaAvisoExistente->whereDate('fecha_programada', Carbon::today());
        }

        $existe = $consultaAvisoExistente->exists();

        if ($existe) {
            return 0;
        }

        $aviso = AvisoCorreo::create([
            'id_empleado_capacitacion' => $asignacion->id_empleado_capacitacion,
            'id_configuracion_aviso' => $configuracion->id_configuracion_aviso,
            'tipo_aviso' => $configuracion->tipo_aviso,
            'destinatario_tipo' => $destinatarioTipo,
            'destinatario_email' => $destinatarioEmail,
            'asunto' => $this->generarAsunto($asignacion, $configuracion->tipo_aviso),
            'mensaje' => $this->generarMensaje($asignacion, $configuracion->tipo_aviso, $destinatarioTipo),
            'fecha_programada' => $this->fechaSql(),
            'fecha_enviada' => null,
            'estado' => 'pendiente',
            'intentos_envio' => 0,
            'error_envio' => null,
        ]);

        Log::info('STB avisos: aviso generado.', [
            'id_aviso_correo' => $aviso->id_aviso_correo,
            'id_empleado_capacitacion' => $asignacion->id_empleado_capacitacion,
            'tipo_aviso' => $configuracion->tipo_aviso,
            'destinatario_tipo' => $destinatarioTipo,
            'destinatario_email' => $destinatarioEmail,
            'estado_asignacion' => $asignacion->estado,
            'fecha_limite' => $asignacion->fecha_limite,
            'fecha_vencimiento' => $asignacion->fecha_vencimiento,
        ]);

        return 1;
    }

    private function crearAvisoAsignacionSiNoExiste(
        EmpleadoCapacitacion $asignacion,
        ConfiguracionAviso $configuracion,
        string $destinatarioTipo,
        string $destinatarioEmail,
        ?User $usuario
    ): ?AvisoCorreo {
        $existe = AvisoCorreo::where('id_empleado_capacitacion', $asignacion->id_empleado_capacitacion)
            ->where('tipo_aviso', 'asignada')
            ->where('destinatario_tipo', $destinatarioTipo)
            ->where('destinatario_email', $destinatarioEmail)
            ->whereIn('estado', ['pendiente', 'enviado', 'error'])
            ->exists();

        if ($existe) {
            return null;
        }

        return AvisoCorreo::create([
            'id_empleado_capacitacion' => $asignacion->id_empleado_capacitacion,
            'id_configuracion_aviso' => $configuracion->id_configuracion_aviso,
            'tipo_aviso' => 'asignada',
            'destinatario_tipo' => $destinatarioTipo,
            'destinatario_email' => $destinatarioEmail,
            'asunto' => $this->generarAsunto($asignacion, 'asignada'),
            'mensaje' => $this->generarMensajeAsignacion($asignacion, $usuario),
            'fecha_programada' => $this->fechaSql(),
            'fecha_enviada' => null,
            'estado' => 'pendiente',
            'intentos_envio' => 0,
            'error_envio' => null,
        ]);
    }

    private function enviarAvisoIndividual(AvisoCorreo $aviso): bool
    {
        if (!$this->avisoTieneDestinatarioActivo($aviso)) {
            $aviso->update([
                'estado' => 'cancelado',
                'error_envio' => 'El destinatario está inactivo o dejó de estar habilitado para recibir avisos.',
            ]);

            return false;
        }

        try {
            Mail::to($aviso->destinatario_email)->send(new AvisoCapacitacionMail($aviso));

            $aviso->update([
                'estado' => 'enviado',
                'fecha_enviada' => $this->fechaSql(),
                'intentos_envio' => ((int) $aviso->intentos_envio) + 1,
                'error_envio' => null,
            ]);

            return true;
        } catch (\Throwable $e) {
            $aviso->update([
                'estado' => 'error',
                'intentos_envio' => ((int) $aviso->intentos_envio) + 1,
                'error_envio' => mb_substr($e->getMessage(), 0, 1900),
            ]);

            return false;
        }
    }

    private function generarMensajeAsignacion(EmpleadoCapacitacion $asignacion, ?User $usuario): string
    {
        $empleado = $asignacion->empleado?->nombre_completo ?? 'Empleado';
        $capacitacion = $asignacion->capacitacion?->capacitacion ?? 'Capacitación';

        $fechaLimite = $asignacion->fecha_limite
            ? Carbon::parse($asignacion->fecha_limite)->format('d/m/Y')
            : 'No definida';

        $fechaVencimiento = $asignacion->fecha_vencimiento
            ? Carbon::parse($asignacion->fecha_vencimiento)->format('d/m/Y')
            : 'No definida';

        return "Hola {$empleado}.\n\n"
            . "Se te ha asignado una nueva capacitación en el sistema.\n\n"
            . "Capacitación: {$capacitacion}\n"
            . "Fecha límite: {$fechaLimite}\n"
            . "Fecha de vencimiento: {$fechaVencimiento}\n\n"
            . "Por favor ingresa al sistema de capacitaciones y completá la capacitación asignada dentro del plazo establecido.";
    }

    public function enviarPendientes(): array
    {
        $resultado = [
            'procesados' => 0,
            'enviados' => 0,
            'errores' => 0,
        ];

        $avisos = AvisoCorreo::with(['empleadoCapacitacion.empleado', 'empleadoCapacitacion.capacitacion'])
            ->where('estado', 'pendiente')
            ->where('fecha_programada', '<=', $this->fechaSql())
            ->orderBy('fecha_programada')
            ->get();

        foreach ($avisos as $aviso) {
            $resultado['procesados']++;

            if (!$this->avisoTieneDestinatarioActivo($aviso)) {
                $aviso->update([
                    'estado' => 'cancelado',
                    'error_envio' => 'El destinatario está inactivo o dejó de estar habilitado para recibir avisos.',
                ]);

                Log::info('STB avisos: envío cancelado por destinatario inactivo o no habilitado.', [
                    'id_aviso_correo' => $aviso->id_aviso_correo,
                    'destinatario_tipo' => $aviso->destinatario_tipo,
                    'destinatario_email' => $aviso->destinatario_email,
                ]);

                continue;
            }

            try {
                Mail::to($aviso->destinatario_email)->send(new AvisoCapacitacionMail($aviso));

                $aviso->update([
                    'estado' => 'enviado',
                    'fecha_enviada' => $this->fechaSql(),
                    'intentos_envio' => ((int) $aviso->intentos_envio) + 1,
                    'error_envio' => null,
                ]);

                Log::info('STB avisos: correo enviado correctamente.', [
                    'id_aviso_correo' => $aviso->id_aviso_correo,
                    'id_empleado_capacitacion' => $aviso->id_empleado_capacitacion,
                    'tipo_aviso' => $aviso->tipo_aviso,
                    'destinatario_tipo' => $aviso->destinatario_tipo,
                    'destinatario_email' => $aviso->destinatario_email,
                ]);

                $resultado['enviados']++;
            } catch (\Throwable $e) {
                $aviso->update([
                    'estado' => 'error',
                    'intentos_envio' => ((int) $aviso->intentos_envio) + 1,
                    'error_envio' => mb_substr($e->getMessage(), 0, 1900),
                ]);

                Log::error('STB avisos: error al enviar correo.', [
                    'id_aviso_correo' => $aviso->id_aviso_correo,
                    'id_empleado_capacitacion' => $aviso->id_empleado_capacitacion,
                    'tipo_aviso' => $aviso->tipo_aviso,
                    'destinatario_tipo' => $aviso->destinatario_tipo,
                    'destinatario_email' => $aviso->destinatario_email,
                    'error' => $e->getMessage(),
                ]);

                $resultado['errores']++;
            }
        }

        return $resultado;
    }

    private function fechaReferenciaVencimiento(EmpleadoCapacitacion $asignacion): ?Carbon
    {
        $fecha = $asignacion->fecha_vencimiento ?: $asignacion->fecha_limite;

        if (!$fecha) {
            return null;
        }

        return Carbon::parse($fecha)->startOfDay();
    }

    private function fechaReferenciaLimiteRetraso(EmpleadoCapacitacion $asignacion): ?Carbon
    {
        if (!$asignacion->fecha_limite) {
            return null;
        }

        return Carbon::parse($asignacion->fecha_limite)->startOfDay();
    }

    private function obtenerDestinatariosAviso(
        EmpleadoCapacitacion $asignacion,
        ConfiguracionAviso $configuracion
    ): array
    {
        $asignacion->loadMissing([
            'empleado.empleadoUser.user',
            'capacitacion.instructor',
        ]);

        $destinatarios = [];

        $empleado = $asignacion->empleado;
        $usuarioEmpleado = $empleado?->empleadoUser?->user;
        $empleadoActivo = (int) ($empleado?->estado ?? 0) === 1;
        $usuarioEmpleadoActivo = !$usuarioEmpleado || (int) $usuarioEmpleado->estado === 1;
        $correoEmpleado = $usuarioEmpleado?->email ?: $empleado?->correo;

        if ((int) $configuracion->enviar_a_empleado === 1
            && $empleadoActivo
            && $usuarioEmpleadoActivo
            && $correoEmpleado) {
            $destinatarios[] = [
                'tipo' => 'empleado',
                'email' => $correoEmpleado,
            ];
        }

        if ((int) $configuracion->enviar_a_admin === 1) {
            foreach ($this->obtenerDestinatariosAdministrativos() as $destinatarioAdministrativo) {
                $destinatarios[] = [
                    'tipo' => 'admin',
                    'email' => $destinatarioAdministrativo->email,
                ];
            }
        }

        return collect($destinatarios)
            ->filter(fn ($destinatario) => !empty($destinatario['email']))
            ->unique(fn ($destinatario) => mb_strtolower(trim($destinatario['email'])))
            ->values()
            ->all();
    }

    private function avisoTieneDestinatarioActivo(AvisoCorreo $aviso): bool
    {
        $correo = mb_strtolower(trim((string) $aviso->destinatario_email));

        if ($correo === '') {
            return false;
        }

        if ($aviso->destinatario_tipo === 'admin') {
            return User::where('estado', 1)
                ->where('recibe_avisos_capacitaciones', 1)
                ->whereRaw('LOWER(email) = ?', [$correo])
                ->exists();
        }

        $aviso->loadMissing('empleadoCapacitacion.empleado.empleadoUser.user');

        $empleado = $aviso->empleadoCapacitacion?->empleado;
        $usuario = $empleado?->empleadoUser?->user;

        if (!$empleado || (int) $empleado->estado !== 1) {
            return false;
        }

        return !$usuario || (int) $usuario->estado === 1;
    }

    private function obtenerDestinatariosAdministrativos()
    {
        return User::where('estado', 1)
            ->where('recibe_avisos_capacitaciones', 1)
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->get();
    }

    private function generarAsunto(EmpleadoCapacitacion $asignacion, string $tipoAviso): string
    {
        $capacitacion = $asignacion->capacitacion?->capacitacion ?? 'Capacitación';

        if ($tipoAviso === 'asignada') {
            return 'Nueva capacitación asignada - ' . $capacitacion;
        }

        if ($tipoAviso === 'por_vencer') {
            return 'Aviso: capacitación por vencer - ' . $capacitacion;
        }

        if ($tipoAviso === 'vencida') {
            return 'Aviso: capacitación reprobada por fecha límite - ' . $capacitacion;
        }

        if ($tipoAviso === 'terminada') {
            $estado = strtolower((string) $asignacion->estado);

            if ($estado === 'aprobada') {
                return 'Resultado de capacitación: aprobada - ' . $capacitacion;
            }

            if ($estado === 'reprobada') {
                return 'Resultado de capacitación: reprobada - ' . $capacitacion;
            }

            return 'Resultado de capacitación - ' . $capacitacion;
        }

        return 'Aviso de capacitación - ' . $capacitacion;
    }

    private function generarMensaje(EmpleadoCapacitacion $asignacion, string $tipoAviso, string $destinatarioTipo = 'empleado'): string
    {
        $empleado = $asignacion->empleado?->nombre_completo ?? 'Empleado';
        $capacitacion = $asignacion->capacitacion?->capacitacion ?? 'Capacitación';
        $fecha = $this->fechaReferenciaVencimiento($asignacion);
        $estado = strtolower((string) $asignacion->estado);
        $esAdmin = $destinatarioTipo === 'admin';

        if ($tipoAviso === 'por_vencer') {
            if ($esAdmin) {
                return 'Se informa que la capacitación "' . $capacitacion . '" asignada al empleado ' . $empleado . ' está próxima a vencer. Fecha límite: ' . ($fecha ? $fecha->format('d/m/Y') : 'No definida') . '.';
            }

            return 'La capacitación "' . $capacitacion . '" asignada a tu usuario está próxima a vencer. Fecha límite: ' . ($fecha ? $fecha->format('d/m/Y') : 'No definida') . '.';
        }

        if ($tipoAviso === 'vencida') {
            $fechaLimiteRetraso = $this->fechaReferenciaLimiteRetraso($asignacion);

            if ($esAdmin) {
                return 'Se informa que la capacitación "' . $capacitacion . '" asignada al empleado ' . $empleado . ' se encuentra REPROBADA POR FECHA LÍMITE. Fecha límite: ' . ($fechaLimiteRetraso ? $fechaLimiteRetraso->format('d/m/Y') : 'No definida') . '. Este aviso se generará diariamente mientras la capacitación no haya finalizado o no sea corregida por administración.';
            }

            return 'La capacitación "' . $capacitacion . '" asignada a tu usuario se encuentra REPROBADA POR FECHA LÍMITE. Fecha límite: ' . ($fechaLimiteRetraso ? $fechaLimiteRetraso->format('d/m/Y') : 'No definida') . '.';
        }

        if ($tipoAviso === 'terminada') {
            if ($estado === 'aprobada') {
                if ($esAdmin) {
                    return 'Se informa que el empleado ' . $empleado . ' finalizó la capacitación "' . $capacitacion . '" con resultado: APROBADA.';
                }

                return 'La capacitación "' . $capacitacion . '" ha finalizado. Resultado: APROBADA. Felicitaciones, aprobaste la capacitación. Diploma temporal: el diploma oficial será adjuntado cuando el formato esté disponible.';
            }

            if ($estado === 'reprobada') {
                if ($esAdmin) {
                    return 'Se informa que el empleado ' . $empleado . ' finalizó la capacitación "' . $capacitacion . '" con resultado: REPROBADA. El empleado no alcanzó la aprobación requerida para esta capacitación.';
                }

                return 'La capacitación "' . $capacitacion . '" ha finalizado. Resultado: REPROBADA. No alcanzaste la aprobación requerida para esta capacitación.';
            }

            if ($esAdmin) {
                return 'Se informa que el empleado ' . $empleado . ' finalizó la capacitación "' . $capacitacion . '". Estado actual: ' . strtoupper($estado) . '.';
            }

            return 'La capacitación "' . $capacitacion . '" ha finalizado.';
        }

        if ($esAdmin) {
            return 'Se informa un aviso relacionado con la capacitación "' . $capacitacion . '" asignada al empleado ' . $empleado . '.';
        }

        return 'Aviso relacionado con la capacitación "' . $capacitacion . '" asignada a tu usuario.';
    }
}
