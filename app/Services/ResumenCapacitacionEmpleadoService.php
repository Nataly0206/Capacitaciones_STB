<?php

namespace App\Services;

use App\Models\EmpleadoCapacitacion;
use App\Models\HistorialCapacitacionEmpleado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ResumenCapacitacionEmpleadoService
{
    public function recalcular(EmpleadoCapacitacion $miCapacitacion): void
    {
        $estadoAnterior = $miCapacitacion->estado;
        $progresoAnterior = $miCapacitacion->progreso;
        $notaAnterior = $miCapacitacion->nota_final;

        $miCapacitacion->load([
            'capacitacion.capacitacionModulos' => function ($query) {
                $query->where('estado', 1)->orderBy('orden');
            },
            'capacitacion.capacitacionModulos.evaluaciones' => function ($query) {
                $query->where('activa', 1)->orderByDesc('id_evaluacion');
            },
            'capacitacion.capacitacionModulos.ejercicios' => function ($query) {
                $query->where('estado', 1)->orderBy('orden');
            },
            'modulosAvance',
            'intentosEvaluacion.evaluacion',
            'intentosEjercicio.ejercicio',
        ]);

        $modulosActivos = $miCapacitacion->capacitacion?->capacitacionModulos ?? collect();
        $totalModulos = $modulosActivos->count();

        foreach ($modulosActivos as $moduloActivo) {
            app(AvanceModuloContenidoService::class)->sincronizar($miCapacitacion, $moduloActivo);
        }

        $miCapacitacion->load('modulosAvance');

        $avancesPorModulo = $miCapacitacion->modulosAvance->keyBy('id_capacitacion_modulo');

        $sumaProgreso = 0;
        $sumaNotas = 0;
        $cantidadNotas = 0;
        $tieneModuloReprobado = false;
        $todosCompletados = $totalModulos > 0;
        $hayAvance = false;

        foreach ($modulosActivos as $modulo) {
            $avance = $avancesPorModulo->get($modulo->id_capacitacion_modulo);

            $intentosEvaluacionModulo = $miCapacitacion->intentosEvaluacion
                ->filter(function ($intento) use ($modulo) {
                    return optional($intento->evaluacion)->id_capacitacion_modulo === $modulo->id_capacitacion_modulo;
                })
                ->values();

            $idsEjerciciosModulo = ($modulo->ejercicios ?? collect())->pluck('id_ejercicio');

            $intentosEjercicioModulo = $miCapacitacion->intentosEjercicio
                ->whereIn('id_ejercicio', $idsEjerciciosModulo)
                ->values();

            $resumenModulo = $this->resolverResumenModulo(
                $modulo,
                $avance,
                $intentosEvaluacionModulo,
                $intentosEjercicioModulo
            );

            if ($avance) {
                $resumenModulo['progreso'] = round((float) ($avance->progreso ?? 0), 2);
            }

            $resumenModulo['progreso'] = min(100, max(0, (float) $resumenModulo['progreso']));

            $sumaProgreso += $resumenModulo['progreso'];

            if ($resumenModulo['hay_actividad']) {
                $hayAvance = true;
            }

            if (!is_null($resumenModulo['nota'])) {
                $sumaNotas += $resumenModulo['nota'];
                $cantidadNotas++;
            }

            if ($resumenModulo['estado'] === 'reprobado') {
                $tieneModuloReprobado = true;
            }

            if ($resumenModulo['estado'] !== 'completado') {
                $todosCompletados = false;
            }
        }

        $progresoCalculado = $totalModulos > 0
            ? round($sumaProgreso / $totalModulos, 2)
            : 0;

        $miCapacitacion->progreso = min(100, max(0, $progresoCalculado));

        $miCapacitacion->nota_final = $cantidadNotas > 0
            ? round($sumaNotas / $cantidadNotas, 2)
            : null;

        $fechaCierreReprobacion = $miCapacitacion->fecha_vencimiento
            ? Carbon::parse($miCapacitacion->fecha_vencimiento)->startOfDay()
            : (
                $miCapacitacion->fecha_limite
                    ? Carbon::parse($miCapacitacion->fecha_limite)->startOfDay()
                    : null
            );

        $fechaFinalizacionAnterior = $miCapacitacion->fecha_finalizacion
            ? Carbon::parse($miCapacitacion->fecha_finalizacion)
            : null;

        $finalizoAntesDelCierre = $fechaCierreReprobacion
            && $fechaFinalizacionAnterior
            && $fechaFinalizacionAnterior->lte($fechaCierreReprobacion->copy()->endOfDay())
            && in_array($estadoAnterior, ['aprobada', 'reprobada'], true);

        $reprobadaPorFechaLimite = $fechaCierreReprobacion
            && $fechaCierreReprobacion->lt(Carbon::today())
            && !$finalizoAntesDelCierre
            && !in_array($estadoAnterior, ['aprobada', 'cancelada'], true);

        if (!$miCapacitacion->fecha_inicio && $hayAvance) {
            $primerInicio = collect([
                $miCapacitacion->modulosAvance->filter(fn($avance) => !is_null($avance->fecha_inicio))->sortBy('fecha_inicio')->first()?->fecha_inicio,
                $miCapacitacion->intentosEvaluacion->filter(fn($intento) => !is_null($intento->fecha_inicio))->sortBy('fecha_inicio')->first()?->fecha_inicio,
                $miCapacitacion->intentosEjercicio->filter(fn($intento) => !is_null($intento->fecha_inicio))->sortBy('fecha_inicio')->first()?->fecha_inicio,
            ])->filter()->sort()->first();

            if ($primerInicio) {
                $miCapacitacion->fecha_inicio = $primerInicio;
            }
        }

        if ($reprobadaPorFechaLimite) {
            $miCapacitacion->estado = 'vencida';
            $miCapacitacion->aprobado = 0;
            $miCapacitacion->fecha_finalizacion = null;
        } elseif ($tieneModuloReprobado) {
            $miCapacitacion->estado = 'reprobada';
            $miCapacitacion->aprobado = 0;
            $miCapacitacion->fecha_finalizacion = now();
        } elseif ($todosCompletados && $totalModulos > 0) {
            $miCapacitacion->estado = 'aprobada';
            $miCapacitacion->aprobado = 1;
            $miCapacitacion->fecha_finalizacion = $estadoAnterior === 'aprobada'
                ? ($fechaFinalizacionAnterior ?? now())
                : now();
        } elseif ($hayAvance) {
            $miCapacitacion->estado = 'en_proceso';
            $miCapacitacion->aprobado = 0;
            $miCapacitacion->fecha_finalizacion = null;
        } else {
            $miCapacitacion->estado = 'pendiente';
            $miCapacitacion->aprobado = 0;
            $miCapacitacion->fecha_finalizacion = null;
        }

        $estadoNuevo = $miCapacitacion->estado;
        $progresoNuevo = $miCapacitacion->progreso;
        $notaNueva = $miCapacitacion->nota_final;

        $miCapacitacion->save();

        if ($estadoAnterior !== $estadoNuevo) {
            HistorialCapacitacionEmpleado::create([
                'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $estadoNuevo,
                'observacion' => 'Cambio automático por recálculo de avance. Progreso anterior: '
                    . number_format((float) ($progresoAnterior ?? 0), 2)
                    . '%. Progreso nuevo: '
                    . number_format((float) ($progresoNuevo ?? 0), 2)
                    . '%. Nota anterior: '
                    . (!is_null($notaAnterior) ? number_format((float) $notaAnterior, 2) . '%' : '-')
                    . '. Nota nueva: '
                    . (!is_null($notaNueva) ? number_format((float) $notaNueva, 2) . '%' : '-')
                    . '.',
                'fecha_movimiento' => now()->format('Ymd H:i:s'),
                'id_user' => Auth::check() ? Auth::id() : null,
            ]);
        }

        if ($estadoNuevo === 'aprobada') {
            \Illuminate\Support\Facades\DB::connection($miCapacitacion->getConnectionName())
                ->afterCommit(function () use ($miCapacitacion) {
                    app(SincronizarAsistenciaRrhhService::class)->registrarAprobacion($miCapacitacion);
                });
        }
    }

    private function resolverResumenModulo($modulo, $avance, $intentosEvaluacionModulo, $intentosEjercicioModulo): array
    {
        $ejerciciosActivos = ($modulo->ejercicios ?? collect())->where('estado', 1)->values();
        $totalEjercicios = $ejerciciosActivos->count();

        $intentosPorEjercicio = $intentosEjercicioModulo->groupBy('id_ejercicio');

        $idsEjerciciosCompletados = $ejerciciosActivos
            ->filter(function ($ejercicio) use ($intentosPorEjercicio) {
                $intentos = $intentosPorEjercicio->get($ejercicio->id_ejercicio, collect());

                return $this->ejercicioCuentaComoCompletado($ejercicio, $intentos);
            })
            ->pluck('id_ejercicio')
            ->unique();

        $ejerciciosCompletados = $idsEjerciciosCompletados->count();
        $pendientesRevisionEjercicio = $intentosEjercicioModulo->where('estado', 'pendiente_revision')->count();

        $evaluacion = ($modulo->evaluaciones ?? collect())->first();
        $intentosMaximos = $evaluacion?->intentos_maximos ? (int) $evaluacion->intentos_maximos : null;
        $evaluacionAprobada = $intentosEvaluacionModulo->where('aprobado', 1)->isNotEmpty();
        $evaluacionCerradaPorMax = $evaluacion
            && !is_null($intentosMaximos)
            && $intentosEvaluacionModulo->count() >= $intentosMaximos
            && !$evaluacionAprobada;

        $progresoAvanceModulo = (float) ($avance->progreso ?? 0);
        $estadoAvanceModulo = $avance->estado ?? 'pendiente';

        $hayActividad = $progresoAvanceModulo > 0
            || !in_array($estadoAvanceModulo, ['pendiente', null, ''], true)
            || $intentosEvaluacionModulo->isNotEmpty()
            || $intentosEjercicioModulo->isNotEmpty();

        $notaModulo = (!is_null($avance?->nota) && $intentosEvaluacionModulo->isNotEmpty())
            ? (float) $avance->nota
            : null;

        if ((int) $modulo->requiere_evaluacion === 1 && $evaluacion) {
            $parteEjercicios = $totalEjercicios > 0
                ? round(($ejerciciosCompletados / $totalEjercicios) * 50, 2)
                : 0;

            $parteEvaluacion = 0;

            if ($evaluacionAprobada || $evaluacionCerradaPorMax) {
                $parteEvaluacion = $totalEjercicios > 0 ? 50 : 100;
            }

            $progreso = round(min($parteEjercicios + $parteEvaluacion, 100), 2);

            if ($evaluacionAprobada) {
                $estado = 'completado';
            } elseif ($evaluacionCerradaPorMax) {
                $estado = 'reprobado';
            } elseif ($pendientesRevisionEjercicio > 0) {
                $estado = 'en_proceso';
            } elseif ($hayActividad) {
                $estado = 'en_proceso';
            } else {
                $estado = 'pendiente';
            }

            return [
                'progreso' => $progreso,
                'estado' => $estado,
                'nota' => $notaModulo,
                'hay_actividad' => $hayActividad,
            ];
        }

        if ($totalEjercicios > 0) {
            $progreso = round(($ejerciciosCompletados / $totalEjercicios) * 100, 2);

            if ($ejerciciosCompletados >= $totalEjercicios) {
                $estado = 'completado';
            } elseif ($pendientesRevisionEjercicio > 0) {
                $estado = 'en_proceso';
            } elseif ($hayActividad) {
                $estado = 'en_proceso';
            } else {
                $estado = 'pendiente';
            }

            return [
                'progreso' => $progreso,
                'estado' => $estado,
                'nota' => $notaModulo,
                'hay_actividad' => $hayActividad,
            ];
        }

        return [
            'progreso' => round((float) ($avance->progreso ?? 0), 2),
            'estado' => $avance->estado ?? 'pendiente',
            'nota' => $notaModulo,
            'hay_actividad' => $hayActividad,
        ];
    }

    private function ejercicioCuentaComoCompletado($ejercicio, $intentos): bool
    {
        $intentos = collect($intentos);

        if ($intentos->isEmpty()) {
            return false;
        }

        $tieneIntentoAprobado = $intentos->contains(function ($intento) {
            return in_array($intento->estado, ['finalizado', 'revisado'], true)
                && (int) ($intento->aprobado ?? 0) === 1;
        });

        if ($tieneIntentoAprobado) {
            return true;
        }

        $tienePendienteRevision = $intentos->contains(function ($intento) {
            return $intento->estado === 'pendiente_revision';
        });

        if ($tienePendienteRevision) {
            return false;
        }

        $intentosMaximos = $ejercicio->intentos_maximos
            ? (int) $ejercicio->intentos_maximos
            : null;

        if (is_null($intentosMaximos)) {
            return false;
        }

        return $intentos->count() >= $intentosMaximos;
    }

    private function intentoEjercicioCuentaComoCompletado($intento): bool
    {
        return in_array($intento->estado, ['finalizado', 'revisado'], true)
            && (int) ($intento->aprobado ?? 0) === 1;
    }
}