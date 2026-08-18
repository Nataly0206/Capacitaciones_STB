<?php

namespace App\Http\Controllers;

use App\Models\Capacitacion;
use App\Models\CapacitacionModulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\CapacitacionModuloSeccion;
use App\Services\EliminacionCapacitacionService;


class CapacitacionModuloController extends Controller
{
    public function index($id_capacitacion)
    {
        $capacitacion = Capacitacion::findOrFail($id_capacitacion);

        $modulos = CapacitacionModulo::where('id_capacitacion', $id_capacitacion)
            ->orderBy('orden')
            ->orderBy('id_capacitacion_modulo')
            ->get();

        return view('capacitacion_modulos.index', compact('capacitacion', 'modulos'));
    }

    public function create($id_capacitacion)
    {
        $capacitacion = Capacitacion::findOrFail($id_capacitacion);

        return view('capacitacion_modulos.create', compact('capacitacion'));
    }

    public function store(Request $request, $id_capacitacion)
    {
        $capacitacion = Capacitacion::findOrFail($id_capacitacion);

        $request->merge([
            'titulo' => trim((string) $request->titulo),
            'descripcion' => $request->descripcion !== null ? trim((string) $request->descripcion) : null,
            'objetivo' => $request->objetivo !== null ? trim((string) $request->objetivo) : null,
        ]);

        $request->validate([
            'titulo' => ['required', 'string', 'min:3', 'max:250'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'objetivo' => ['nullable', 'string', 'max:1000'],
            'orden' => ['required', 'integer', 'min:1'],
            'duracion_horas' => ['nullable', 'numeric', 'min:0'],
            'requiere_evaluacion' => ['required', 'in:0,1'],
            'porcentaje_aprobacion' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'estado' => ['required', 'in:0,1'],
            'secciones_titulo' => ['nullable', 'array'],
            'secciones_titulo.*' => ['nullable', 'string', 'max:250'],
            'secciones_contenido' => ['nullable', 'array'],
            'secciones_contenido.*' => ['nullable', 'string', 'max:100000'],
            'secciones_contenido_tocado' => ['nullable', 'array'],
            'secciones_contenido_tocado.*' => ['nullable', 'in:0,1'],
            'secciones_nivel' => ['nullable', 'array'],
            'secciones_nivel.*' => ['nullable', 'in:1,2'],
            'secciones_padre' => ['nullable', 'array'],
            'secciones_padre.*' => ['nullable', 'integer'],
        ]);

        $ordenRepetido = CapacitacionModulo::where('id_capacitacion', $capacitacion->id_capacitacion)
            ->where('orden', $request->orden)
            ->exists();

        if ($ordenRepetido) {
            return back()
                ->withErrors(['orden' => 'Ya existe otro módulo con ese orden en esta capacitación.'])
                ->withInput();
        }

        [$modulo, $seccionesCreadasPorIndice] = DB::transaction(function () use ($request, $capacitacion) {
            $modulo = CapacitacionModulo::create([
                'id_capacitacion' => $capacitacion->id_capacitacion,
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion ?: null,
                'objetivo' => $request->objetivo ?: null,
                'orden' => $request->orden,
                'duracion_horas' => $request->duracion_horas ?: null,
                'requiere_evaluacion' => $request->requiere_evaluacion,
                'porcentaje_aprobacion' => $request->porcentaje_aprobacion ?: null,
                'estado' => $request->estado,
            ]);

            $seccionesCreadasPorIndice = $this->guardarSeccionesModulo($request, $modulo);

            return [$modulo, $seccionesCreadasPorIndice];
        });

        $accionDespuesCrearModulo = $request->input('accion_despues_crear_modulo');
        $indiceSeccionDespuesCrearModulo = $request->input('indice_seccion_despues_crear_modulo');

        $seccionDestino = null;

        if ($indiceSeccionDespuesCrearModulo !== null && $indiceSeccionDespuesCrearModulo !== '') {
            $seccionDestino = $seccionesCreadasPorIndice[(int) $indiceSeccionDespuesCrearModulo] ?? null;
        }

        if ($seccionDestino && $accionDespuesCrearModulo === 'recurso') {
            return redirect()->route('capacitacion_modulos.recursos.index', [
                'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                'crear' => 1,
                'volver_modulo' => 1,
                'id_capacitacion_modulo_seccion' => $seccionDestino->id_capacitacion_modulo_seccion,
            ]);
        }

        if ($seccionDestino && $accionDespuesCrearModulo === 'ejercicio') {
            return redirect()->route('capacitacion_modulos.ejercicios.index', [
                'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                'crear' => 1,
                'volver_modulo' => 1,
                'id_capacitacion_modulo_seccion' => $seccionDestino->id_capacitacion_modulo_seccion,
            ]);
        }

        if ($seccionDestino && $accionDespuesCrearModulo === 'evaluacion') {
            return redirect()->route('capacitacion_modulos.evaluaciones.index', [
                'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                'crear' => 1,
                'volver_modulo' => 1,
                'id_capacitacion_modulo_seccion' => $seccionDestino->id_capacitacion_modulo_seccion,
            ]);
        }

        return redirect()
            ->route('capacitacion_modulos.edit', [
                'id' => $modulo->id_capacitacion_modulo,
                'origen' => 'builder',
            ])
            ->with('success', 'El módulo fue creado correctamente. Ahora puedes agregar recursos, ejercicios y evaluaciones a sus secciones o subsecciones.');

    }

    public function edit($id)
    {
        $modulo = CapacitacionModulo::with([
            'secciones' => function ($query) {
                $query->reorder()
                    ->orderBy('orden')
                    ->orderBy('id_capacitacion_modulo_seccion');
            },
            'recursos' => function ($query) {
                $query->reorder()
                    ->orderBy('orden')
                    ->orderBy('id_capacitacion_recurso');
            },
            'ejercicios' => function ($query) {
                $query->reorder()
                    ->orderBy('orden')
                    ->orderBy('id_ejercicio');
            },
            'ejercicios.preguntas' => function ($query) {
                $query->reorder()
                    ->orderBy('orden')
                    ->orderBy('id_ejercicio_pregunta');
            },
            'evaluaciones' => function ($query) {
                $query->reorder()
                    ->orderBy('orden')
                    ->orderBy('id_evaluacion');
            },
            'evaluaciones.preguntas' => function ($query) {
                $query->reorder()
                    ->orderBy('orden')
                    ->orderBy('id_evaluacion_pregunta');
            },
        ])->findOrFail($id);

        $capacitacion = $modulo->capacitacion;

        return view('capacitacion_modulos.edit', compact('modulo', 'capacitacion'));
    }

    public function guardarSeccionRapida(Request $request, $id)
    {
        $modulo = CapacitacionModulo::findOrFail($id);

        $data = $request->validate([
            'id_seccion' => ['nullable', 'integer'],
            'titulo' => ['required', 'string', 'min:1', 'max:250'],
            'contenido' => ['nullable', 'string'],
            'nivel' => ['required', 'in:1,2'],
            'id_seccion_padre' => ['nullable', 'integer'],
        ]);

        $idSeccion = $data['id_seccion'] ?? null;
        $nivel = (int) $data['nivel'];
        $titulo = trim((string) $data['titulo']);

        $idPadre = null;

        if ($nivel === 2 && !empty($data['id_seccion_padre'])) {
            $padreExiste = CapacitacionModuloSeccion::where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
                ->where('id_capacitacion_modulo_seccion', $data['id_seccion_padre'])
                ->where('nivel', 1)
                ->exists();

            if ($padreExiste) {
                $idPadre = (int) $data['id_seccion_padre'];
            }
        }

        $contenido = $this->limpiarHtmlTeoria($data['contenido'] ?? null);

        if ($idSeccion) {
            $seccion = CapacitacionModuloSeccion::where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
                ->where('id_capacitacion_modulo_seccion', $idSeccion)
                ->firstOrFail();

            $contenidoAnterior = $seccion->contenido;

            $seccion->update([
                'titulo' => $titulo,
                'contenido' => $contenido,
                'nivel' => $nivel,
                'id_seccion_padre' => $nivel === 2 ? $idPadre : null,
                'estado' => 1,
            ]);

        } else {
            $siguienteOrden = ((int) CapacitacionModuloSeccion::where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
                ->max('orden')) + 1;

            $seccion = CapacitacionModuloSeccion::create([
                'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                'titulo' => $titulo,
                'contenido' => $contenido,
                'nivel' => $nivel,
                'id_seccion_padre' => $nivel === 2 ? $idPadre : null,
                'orden' => $siguienteOrden,
                'estado' => 1,
            ]);
        }

        return response()->json([
            'ok' => true,
            'id_seccion' => $seccion->id_capacitacion_modulo_seccion,
            'titulo' => $seccion->titulo,
            'urls' => [
                'recurso' => route('capacitacion_modulos.recursos.index', [
                    'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                    'crear' => 1,
                    'volver_modulo' => 1,
                    'id_capacitacion_modulo_seccion' => $seccion->id_capacitacion_modulo_seccion,
                ]),
                'ejercicio' => route('capacitacion_modulos.ejercicios.index', [
                    'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                    'crear' => 1,
                    'volver_modulo' => 1,
                    'id_capacitacion_modulo_seccion' => $seccion->id_capacitacion_modulo_seccion,
                ]),
                'evaluacion' => route('capacitacion_modulos.evaluaciones.index', [
                    'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                    'crear' => 1,
                    'volver_modulo' => 1,
                    'id_capacitacion_modulo_seccion' => $seccion->id_capacitacion_modulo_seccion,
                ]),
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $modulo = CapacitacionModulo::findOrFail($id);
        $capacitacion = $modulo->capacitacion;

        $request->merge([
            'titulo' => trim((string) $request->titulo),
            'descripcion' => $request->descripcion !== null ? trim((string) $request->descripcion) : null,
            'objetivo' => $request->objetivo !== null ? trim((string) $request->objetivo) : null,
        ]);

        $request->validate([
            'titulo' => ['required', 'string', 'min:3', 'max:250'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'objetivo' => ['nullable', 'string', 'max:1000'],
            'orden' => ['required', 'integer', 'min:1'],
            'duracion_horas' => ['nullable', 'numeric', 'min:0'],
            'requiere_evaluacion' => ['required', 'in:0,1'],
            'porcentaje_aprobacion' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'estado' => ['required', 'in:0,1'],
            'secciones_titulo' => ['nullable', 'array'],
            'secciones_titulo.*' => ['nullable', 'string', 'max:250'],
            'secciones_contenido' => ['nullable', 'array'],
            'secciones_contenido.*' => ['nullable', 'string', 'max:100000'],
            'secciones_nivel' => ['nullable', 'array'],
            'secciones_nivel.*' => ['nullable', 'in:1,2'],
            'secciones_padre' => ['nullable', 'array'],
            'secciones_padre.*' => ['nullable', 'integer'],
        ]);

        $ordenRepetido = CapacitacionModulo::where('id_capacitacion', $capacitacion->id_capacitacion)
            ->where('orden', $request->orden)
            ->where('id_capacitacion_modulo', '!=', $modulo->id_capacitacion_modulo)
            ->exists();

        if ($ordenRepetido) {
            return back()
                ->withErrors(['orden' => 'Ya existe otro módulo con ese orden en esta capacitación.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $modulo) {
            $modulo->update([
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion ?: null,
                'objetivo' => $request->objetivo ?: null,
                'orden' => $request->orden,
                'duracion_horas' => $request->duracion_horas ?: null,
                'requiere_evaluacion' => $request->requiere_evaluacion,
                'porcentaje_aprobacion' => $request->porcentaje_aprobacion ?: null,
                'estado' => $request->estado,
            ]);

            $this->guardarSeccionesModulo($request, $modulo);
        });

        $this->guardarSeccionesModulo($request, $modulo);

        return redirect()
            ->route('capacitaciones.builder', $modulo->id_capacitacion)
            ->with('success', 'El módulo fue actualizado correctamente.');
    }

    public function toggleEstado($id)
    {
        $modulo = CapacitacionModulo::findOrFail($id);
        $capacitacion = $modulo->capacitacion;

        $modulo->estado = (int) $modulo->estado === 1 ? 0 : 1;
        $modulo->save();

        $mensaje = (int) $modulo->estado === 1
            ? 'El módulo fue activado correctamente.'
            : 'El módulo fue inactivado correctamente.';

        return redirect()
            ->route('capacitaciones.builder', $capacitacion->id_capacitacion)
            ->with('success', $mensaje);
    }

    public function destroy(Request $request, $id)
    {
        $modulo = CapacitacionModulo::with('capacitacion')->findOrFail($id);
        $capacitacion = $modulo->capacitacion;

        app(EliminacionCapacitacionService::class)
            ->eliminarModulo((int) $id);

        return redirect()
            ->route('capacitaciones.builder', $capacitacion->id_capacitacion)
            ->with('success', 'El módulo, sus recursos, ejercicios, evaluaciones, intentos y avances asociados fueron eliminados correctamente.');
    }

    private function guardarSeccionesModulo(Request $request, CapacitacionModulo $modulo): array
    {
        $ids = $request->input('secciones_id', []);
        $titulos = $request->input('secciones_titulo', []);
        $contenidos = $request->input('secciones_contenido', []);
        $contenidosTocados = $request->input('secciones_contenido_tocado', []);
        $niveles = $request->input('secciones_nivel', []);
        $padres = $request->input('secciones_padre', []);

        $seccionesGuardadasPorIndice = [];

        $seccionesExistentes = $modulo->secciones()
            ->get()
            ->keyBy('id_capacitacion_modulo_seccion');

        $idsRecibidos = collect($ids)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        $idsEliminarBase = $seccionesExistentes
            ->keys()
            ->diff($idsRecibidos)
            ->values()
            ->all();

        $idsEliminar = collect($this->obtenerIdsSeccionesConSubsecciones($modulo, $idsEliminarBase))
            ->unique()
            ->values();

        if ($idsEliminar->isNotEmpty()) {
            app(EliminacionCapacitacionService::class)
                ->eliminarSeccionesModulo($idsEliminar);

            $seccionesExistentes = $seccionesExistentes->except($idsEliminar->all());
        }

        $ordenTemporal = 100000;

        foreach ($seccionesExistentes as $seccionTemporal) {
            $seccionTemporal->update([
                'orden' => $ordenTemporal,
            ]);

            $ordenTemporal++;
        }

        $seccionesExistentes = $modulo->secciones()
            ->get()
            ->keyBy('id_capacitacion_modulo_seccion');

        $ultimaSeccionPrincipal = null;
        $orden = 1;

        foreach ($titulos as $index => $titulo) {
            $tituloLimpio = trim((string) ($titulo ?? ''));
            $nivel = (int) ($niveles[$index] ?? 1);
            $idActual = isset($ids[$index]) && $ids[$index] !== '' ? (int) $ids[$index] : null;
            $idPadreSeleccionado = isset($padres[$index]) && $padres[$index] !== '' ? (int) $padres[$index] : null;

            $contenidoRecibido = $contenidos[$index] ?? null;
            $contenidoFueTocado = (string) ($contenidosTocados[$index] ?? '0') === '1';

            $contenidoAnterior = null;

            if ($idActual && $seccionesExistentes->has($idActual)) {
                $contenidoAnterior = $seccionesExistentes->get($idActual)->contenido;
            }

            $contenidoLimpio = $contenidoRecibido;

            if (!$contenidoFueTocado && blank($contenidoRecibido) && filled($contenidoAnterior)) {
                $contenidoLimpio = $contenidoAnterior;
            }

            if ($idActual && $idsEliminar->contains($idActual)) {
                continue;
            }

            if ($tituloLimpio === '' && blank($contenidoLimpio)) {
                continue;
            }

            $idPadreFinal = null;

            if ($nivel === 2) {
                if (
                    $idPadreSeleccionado &&
                    $seccionesExistentes->has($idPadreSeleccionado) &&
                    $idPadreSeleccionado !== $idActual
                ) {
                    $idPadreFinal = $idPadreSeleccionado;
                } elseif ($ultimaSeccionPrincipal) {
                    $idPadreFinal = $ultimaSeccionPrincipal->id_capacitacion_modulo_seccion;
                }
            }

            $datosSeccion = [
                'id_seccion_padre' => $nivel === 2 ? $idPadreFinal : null,
                'titulo' => $tituloLimpio !== '' ? $tituloLimpio : 'Página ' . $orden,
                'contenido' => $contenidoLimpio,
                'orden' => $orden,
                'nivel' => $nivel,
                'estado' => 1,
            ];

            if ($idActual && $seccionesExistentes->has($idActual)) {
                $seccionGuardada = $seccionesExistentes->get($idActual);
                $seccionGuardada->update($datosSeccion);
            } else {
                $seccionGuardada = $modulo->secciones()->create($datosSeccion);
            }

            if ($nivel === 1) {
                $ultimaSeccionPrincipal = $seccionGuardada;
            }

            $seccionesGuardadasPorIndice[$index] = $seccionGuardada;

            $orden++;
        }
        return $seccionesGuardadasPorIndice;
    }

    private function obtenerIdsSeccionesConSubsecciones(CapacitacionModulo $modulo, $idsBase): array
    {
        $idsEliminar = collect($idsBase)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($idsEliminar->isEmpty()) {
            return [];
        }

        do {
            $cantidadAntes = $idsEliminar->count();

            $idsHijas = CapacitacionModuloSeccion::where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
                ->whereIn('id_seccion_padre', $idsEliminar)
                ->pluck('id_capacitacion_modulo_seccion')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->values();

            $idsEliminar = $idsEliminar
                ->merge($idsHijas)
                ->unique()
                ->values();
        } while ($idsEliminar->count() > $cantidadAntes);

        return $idsEliminar->all();
    }

    private function limpiarHtmlTeoria(?string $contenido): ?string
    {
        return app(\App\Services\ContenidoHtmlSeguro::class)->limpiar($contenido);
    }

    public function subirImagenTeoria(Request $request)
    {
        $request->validate([
            'imagen' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:4096'],
        ]);

        $ruta = $request->file('imagen')->store('teoria_modulos', 'public');

        return response()->json([
            'url' => asset('storage/' . $ruta),
        ]);
    }
}
