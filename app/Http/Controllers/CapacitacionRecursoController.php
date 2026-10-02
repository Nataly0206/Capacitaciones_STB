<?php

namespace App\Http\Controllers;

use App\Models\CapacitacionModulo;
use App\Models\CapacitacionRecurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\EliminacionCapacitacionService;


class CapacitacionRecursoController extends Controller
{
    public function index($id_capacitacion_modulo)
    {
        $modulo = CapacitacionModulo::with([
            'capacitacion',
            'secciones' => function ($query) {
                $query->where('estado', 1)
                    ->orderBy('id_capacitacion_modulo_seccion');
            },
        ])->findOrFail($id_capacitacion_modulo);

        $secciones = $modulo->secciones;

        $recursos = CapacitacionRecurso::with('seccion')
            ->where('id_capacitacion_modulo', $id_capacitacion_modulo)
            ->orderBy('orden')
            ->orderBy('id_capacitacion_recurso')
            ->get();

        $siguienteOrden = ((int) CapacitacionRecurso::where('id_capacitacion_modulo', $id_capacitacion_modulo)
            ->max('orden')) + 1;

        return view('capacitacion_recursos.index', compact('modulo', 'recursos', 'secciones', 'siguienteOrden'));
    }

    public function create($id_capacitacion_modulo)
    {
        $modulo = CapacitacionModulo::with([
            'capacitacion',
            'secciones' => function ($query) {
                $query->where('estado', 1)
                    ->orderBy('id_capacitacion_modulo_seccion');
            },
        ])->findOrFail($id_capacitacion_modulo);

        $secciones = $modulo->secciones;

        $siguienteOrden = ((int) CapacitacionRecurso::where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
            ->max('orden')) + 1;

        return view('capacitacion_recursos.create', compact('modulo', 'siguienteOrden', 'secciones'));
    }

    public function store(Request $request, $id_capacitacion_modulo)
    {
        $modulo = CapacitacionModulo::with('capacitacion')->findOrFail($id_capacitacion_modulo);

        $request->merge([
            'titulo' => $request->filled('titulo') ? trim((string) $request->titulo) : null,
            'descripcion' => $request->descripcion !== null ? trim((string) $request->descripcion) : null,
            'url_recurso' => $request->url_recurso !== null ? trim((string) $request->url_recurso) : null,
        ]);

        $request->validate([
            'tipo_recurso' => ['required', 'in:imagen,pdf,word,powerpoint,excel,video,audio,enlace,comprimido,documento,archivo'],
            'id_capacitacion_modulo_seccion' => ['nullable', 'integer', 'exists:capacitacion_modulo_seccion,id_capacitacion_modulo_seccion'],
            'titulo' => ['nullable', 'string', 'max:250'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'url_recurso' => ['nullable', 'url:http,https', 'max:1000'],
            'archivo_recurso' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,gif,webp,bmp,mp4,mov,avi,webm,m4v,mp3,wav,ogg,m4a,zip,rar',
                'max:204800',
            ],
            'obligatorio' => ['required', 'in:0,1'],
            'orden' => ['required', 'integer', 'min:1'],
            'estado' => ['required', 'in:0,1'],
            'contenido_texto' => ['nullable', 'string'],
            'permite_descarga' => ['required', 'in:0,1'],
        ], [
            'archivo_recurso.uploaded' => 'El servidor no pudo recibir el archivo. Puede deberse al límite de carga de PHP o a una carga interrumpida. Selecciona el archivo nuevamente; si el problema persiste, contacta al administrador.',
            'archivo_recurso.file' => 'El recurso debe ser un archivo válido.',
            'archivo_recurso.max' => 'El archivo no puede superar los 200 MB.',
            'archivo_recurso.mimes' => 'El archivo debe ser un documento, imagen, video, audio o archivo comprimido válido.',
        ]);

        $idSeccionSeleccionada = $request->filled('id_capacitacion_modulo_seccion')
            ? (int) $request->id_capacitacion_modulo_seccion
            : null;

        $secciones = $modulo->secciones;

        if ($idSeccionSeleccionada && !$secciones->contains('id_capacitacion_modulo_seccion', $idSeccionSeleccionada)) {
            return back()
                ->withErrors(['id_capacitacion_modulo_seccion' => 'La sección seleccionada no pertenece a este módulo.'])
                ->withInput();
        }

        $ordenRepetido = CapacitacionRecurso::where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
            ->where('orden', (int) $request->orden)
            ->exists();

        if ($ordenRepetido) {
            return back()
                ->withErrors(['orden' => 'Ya existe otro recurso con ese orden en este módulo. Cambia el orden o usa el siguiente número disponible.'])
                ->withInput();
        }

        $rutaArchivo = null;

        if ($request->hasFile('archivo_recurso')) {
            $rutaArchivo = $request->file('archivo_recurso')->store('capacitaciones/recursos', 'public');
        }

        $recurso = CapacitacionRecurso::create([
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
            'id_capacitacion_modulo_seccion' => $idSeccionSeleccionada,
            'tipo_recurso' => $request->tipo_recurso,
            'titulo' => $request->titulo ?: null,
            'descripcion' => $request->descripcion ?: null,
            'url_recurso' => $request->url_recurso ?: null,
            'ruta_archivo' => $rutaArchivo,
            'obligatorio' => $request->obligatorio,
            'orden' => $request->orden,
            'estado' => $request->estado,
            'contenido_texto' => null,
            'permite_descarga' => $request->permite_descarga,
        ]);

        $parametrosRedireccion = [
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
            'open' => 'recurso-' . $recurso->id_capacitacion_recurso,
        ];

        if ((int) $request->input('volver_modulo', $idSeccionSeleccionada ? 1 : 0) === 1 && $idSeccionSeleccionada) {
            $parametrosRedireccion['volver_modulo'] = 1;
            $parametrosRedireccion['id_capacitacion_modulo_seccion'] = $idSeccionSeleccionada;
        }

        return redirect()
            ->route('capacitacion_modulos.recursos.index', $parametrosRedireccion)
            ->with('success', 'El recurso fue creado correctamente.');
    }

    public function edit($id)
    {
        $recurso = CapacitacionRecurso::with('capacitacionModulo.capacitacion')->findOrFail($id);
        $modulo = $recurso->capacitacionModulo;
        $modulo->load([
            'secciones' => function ($query) {
                $query->where('estado', 1)
                    ->orderBy('id_capacitacion_modulo_seccion');
            },
        ]);

        $secciones = $modulo->secciones;

        return view('capacitacion_recursos.edit', compact('recurso', 'modulo', 'secciones'));
    }

    public function update(Request $request, $id)
    {
        $recurso = CapacitacionRecurso::with('capacitacionModulo.capacitacion')->findOrFail($id);
        $modulo = $recurso->capacitacionModulo;

        $request->merge([
            'titulo' => $request->filled('titulo') ? trim((string) $request->titulo) : null,            'descripcion' => $request->descripcion !== null ? trim((string) $request->descripcion) : null,
            'url_recurso' => $request->url_recurso !== null ? trim((string) $request->url_recurso) : null,
        ]);

        $request->validate([
            'tipo_recurso' => ['required', 'in:imagen,pdf,word,powerpoint,excel,video,audio,enlace,comprimido,documento,archivo'],
            'id_capacitacion_modulo_seccion' => ['nullable', 'integer', 'exists:capacitacion_modulo_seccion,id_capacitacion_modulo_seccion'],
            'titulo' => ['nullable', 'string', 'max:250'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'url_recurso' => ['nullable', 'url:http,https', 'max:1000'],
            'archivo_recurso' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,gif,webp,bmp,mp4,mov,avi,webm,m4v,mp3,wav,ogg,m4a,zip,rar',
                'max:204800',
            ],
            'obligatorio' => ['required', 'in:0,1'],
            'orden' => ['required', 'integer', 'min:1'],
            'estado' => ['required', 'in:0,1'],
            'contenido_texto' => ['nullable', 'string'],
            'permite_descarga' => ['required', 'in:0,1'],
        ], [
            'archivo_recurso.uploaded' => 'El servidor no pudo recibir el archivo. Puede deberse al límite de carga de PHP o a una carga interrumpida. Selecciona el archivo nuevamente; si el problema persiste, contacta al administrador.',
            'archivo_recurso.file' => 'El recurso debe ser un archivo válido.',
            'archivo_recurso.max' => 'El archivo no puede superar los 200 MB.',
            'archivo_recurso.mimes' => 'El archivo debe ser un documento, imagen, video, audio o archivo comprimido válido.',
        ]);

        $idSeccionSeleccionada = $request->filled('id_capacitacion_modulo_seccion')
            ? (int) $request->id_capacitacion_modulo_seccion
            : null;

        $modulo->loadMissing([
            'secciones' => function ($query) {
                $query->where('estado', 1)
                    ->orderBy('id_capacitacion_modulo_seccion');
            },
        ]);

        $secciones = $modulo->secciones;

        if ($idSeccionSeleccionada && !$secciones->contains('id_capacitacion_modulo_seccion', $idSeccionSeleccionada)) {
            return back()
                ->withErrors(['id_capacitacion_modulo_seccion' => 'La sección seleccionada no pertenece a este módulo.'])
                ->withInput();
        }

        $ordenRepetido = CapacitacionRecurso::where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
            ->where('orden', (int) $request->orden)
            ->where('id_capacitacion_recurso', '!=', $recurso->id_capacitacion_recurso)
            ->exists();

        if ($ordenRepetido) {
            return back()
                ->withErrors(['orden' => 'Ya existe otro recurso con ese orden en este módulo. Cambia el orden o usa otro número disponible.'])
                ->withInput();
        }

        $rutaArchivo = $recurso->ruta_archivo;

        if ($request->hasFile('archivo_recurso')) {
            $rutaArchivo = $request->file('archivo_recurso')->store('capacitaciones/recursos', 'public');
        }

        $recurso->update([
            'id_capacitacion_modulo_seccion' => $idSeccionSeleccionada,
            'tipo_recurso' => $request->tipo_recurso,
            'titulo' => $request->titulo ?: null,
            'descripcion' => $request->descripcion ?: null,
            'url_recurso' => $request->url_recurso ?: null,
            'ruta_archivo' => $rutaArchivo,
            'obligatorio' => $request->obligatorio,
            'orden' => $request->orden,
            'estado' => $request->estado,
            'contenido_texto' => null,
            'permite_descarga' => $request->permite_descarga,
        ]);

        $parametrosRedireccion = [
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
            'open' => 'recurso-' . $recurso->id_capacitacion_recurso,
        ];

        if ((int) $request->input('volver_modulo', $idSeccionSeleccionada ? 1 : 0) === 1 && $idSeccionSeleccionada) {
            $parametrosRedireccion['volver_modulo'] = 1;
            $parametrosRedireccion['id_capacitacion_modulo_seccion'] = $idSeccionSeleccionada;
        }

        return redirect()
            ->route('capacitacion_modulos.recursos.index', $parametrosRedireccion)
            ->with('success', 'El recurso fue actualizado correctamente.');
    }

    public function toggleEstado($id)
    {
        $recurso = CapacitacionRecurso::with('capacitacionModulo.capacitacion')->findOrFail($id);
        $modulo = $recurso->capacitacionModulo;

        $recurso->estado = (int) $recurso->estado === 1 ? 0 : 1;
        $recurso->save();

        $mensaje = (int) $recurso->estado === 1
            ? 'El recurso fue activado correctamente.'
            : 'El recurso fue inactivado correctamente.';

        return redirect()
            ->route('capacitacion_modulos.recursos.index', [
                'id_capacitacion_modulo' => $recurso->id_capacitacion_modulo,
                'open' => 'recurso-' . $recurso->id_capacitacion_recurso,
            ])
            ->with('success', 'El recurso fue actualizado correctamente.');
    }

    public function destroy(Request $request, int $id)
    {
        $recurso = CapacitacionRecurso::findOrFail($id);
        $idModulo = $recurso->id_capacitacion_modulo;
        $idSeccionRetorno = $request->input('id_capacitacion_modulo_seccion') ?: $recurso->id_capacitacion_modulo_seccion;

        app(EliminacionCapacitacionService::class)
            ->eliminarRecurso((int) $id);

        $parametrosRedireccion = [
            'id_capacitacion_modulo' => $idModulo,
        ];

        if ((int) $request->input('volver_modulo', $idSeccionRetorno ? 1 : 0) === 1 && $idSeccionRetorno) {
            $parametrosRedireccion['volver_modulo'] = 1;
            $parametrosRedireccion['id_capacitacion_modulo_seccion'] = $idSeccionRetorno;
        }

        return redirect()
            ->route('capacitacion_modulos.recursos.index', $parametrosRedireccion)
            ->with('success', 'El recurso, su archivo cargado y su avance asociado fueron eliminados correctamente.');
    }
}
