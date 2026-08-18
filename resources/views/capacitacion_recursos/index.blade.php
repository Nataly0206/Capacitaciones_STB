<x-app-layout>

    @php
        $volverAlModuloDesdeRecursos = (int) request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) === 1;
        $idSeccionRetornoRecursos = request('id_capacitacion_modulo_seccion');

        $urlRegresoRecursos = $volverAlModuloDesdeRecursos
            ? route('capacitacion_modulos.edit', [
                'id' => $modulo->id_capacitacion_modulo,
                'origen' => 'builder',
            ]) . ($idSeccionRetornoRecursos ? '#seccion-modulo-' . $idSeccionRetornoRecursos : '')
            : route('capacitaciones.builder', $modulo->capacitacion?->id_capacitacion);
        $parametrosCrearRecurso = [
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
        ];

        if ($volverAlModuloDesdeRecursos && $idSeccionRetornoRecursos) {
            $parametrosCrearRecurso['volver_modulo'] = 1;
            $parametrosCrearRecurso['id_capacitacion_modulo_seccion'] = $idSeccionRetornoRecursos;
        }
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                    Recursos del módulo
                </p>

                <h2 class="esf-seguimiento-title">
                    Recursos
                </h2>

                <p class="esf-seguimiento-subtitle">
                    Módulo: {{ $modulo->titulo }}
                </p>
            </div>

        </div>
    </x-slot>

    <div class="esf-admin-form-page">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="mb-5 rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded border border-red-300 bg-red-100 px-4 py-3 text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="esf-admin-sheet-card">
                <div class="flex flex-col gap-4 p-6 sm:p-8 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                            Recursos registrados
                        </p>

                        <h3 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">
                            Recursos del módulo
                        </h3>

                        <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Administra los recursos de este módulo: imágenes, videos, audios, documentos, archivos y enlaces.
                        </p>

                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                            <strong>Capacitación:</strong> {{ $modulo->capacitacion?->capacitacion }}
                        </p>
                    </div>

                    <div class="flex flex-col md:flex-row gap-2">
                        <input type="text"
                            id="buscarRecurso"
                            placeholder="Buscar recurso..."
                            class="min-w-[220px] rounded-full border border-slate-200 bg-white/90 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">

                        <button type="button"
                                @click="abrirModal('modalCrearRecurso')"
                                class="esf-btn esf-btn-primary">
                            Crear recurso
                        </button>

                        <a href="{{ $urlRegresoRecursos }}"
                            class="esf-btn esf-btn-soft">
                            Volver
                        </a>
                    </div>
                </div>
            </div>

            <div id="modalCrearRecurso"
                class="fixed inset-0 z-50 hidden items-start justify-center overflow-y-auto bg-black/55 px-4 py-10">

                <div class="esf-admin-modal-card w-full max-w-4xl p-6 sm:p-8 my-8">
                    <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">
                        Crear recurso
                    </h3>

                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        Completa los datos del recurso. Después se mostrará dentro del módulo.
                    </p>

                    <form method="POST"
                        action="{{ route('capacitacion_modulos.recursos.store', $modulo->id_capacitacion_modulo) }}"
                        enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="volver_modulo" value="{{ request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) }}">
                        <input type="hidden" name="id_capacitacion_modulo_seccion" value="{{ request('id_capacitacion_modulo_seccion') }}">

                        <div class="esf-admin-modal-grid">
                            <div>
                                <label>Tipo de recurso</label>
                                <select name="tipo_recurso" required>
                                    <option value="imagen">Imagen</option>
                                    <option value="pdf">PDF</option>
                                    <option value="word">Word</option>
                                    <option value="powerpoint">PowerPoint</option>
                                    <option value="excel">Excel</option>
                                    <option value="video">Video</option>
                                    <option value="audio">Audio</option>
                                    <option value="enlace">Enlace</option>
                                    <option value="comprimido">Archivo comprimido</option>
                                </select>
                            </div>

                            <div>
                                <label>Ubicar en sección/subsección</label>
                                <select name="id_capacitacion_modulo_seccion">
                                    <option value="">Contenido general del módulo</option>

                                    @foreach($modulo->secciones->where('estado', 1) as $seccion)
                                        <option value="{{ $seccion->id_capacitacion_modulo_seccion }}"
                                                {{ (string) request('id_capacitacion_modulo_seccion') === (string) $seccion->id_capacitacion_modulo_seccion ? 'selected' : '' }}>
                                            {{ $seccion->nivel == 2 ? '— ' : '' }}{{ $seccion->titulo }}
                                        </option>
                                    @endforeach
                                </select>

                                <p class="esf-help-text">
                                    Aquí decides en qué parte del módulo aparecerá este recurso.
                                </p>
                            </div>

                            <div>
                                <label>Orden</label>
                                <input type="number"
                                    name="orden"
                                    value="{{ old('orden', $siguienteOrden ?? 1) }}"
                                    min="1"
                                    required>
                            </div>

                            <div class="esf-admin-modal-full">
                                <label>Título <span class="text-xs font-semibold text-slate-400">(opcional)</span></label>
                                <input type="text" name="titulo">
                            </div>

                            <div class="esf-admin-modal-full">
                                <label>Descripción</label>
                                <textarea name="descripcion" rows="4"></textarea>
                            </div>

                            <div>
                                <label>URL externa</label>
                                <input type="url" name="url_recurso" placeholder="https://...">
                            </div>

                            <div>
                                <label>Archivo del recurso</label>
                                <input type="file" name="archivo_recurso" class="esf-admin-file-input">

                                <p class="esf-help-text">
                                    Puedes subir PDF, Word, PowerPoint, Excel, imagen, video, audio o archivo comprimido.
                                </p>
                            </div>

                            <div>
                                <label>Obligatorio</label>
                                <select name="obligatorio">
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <div>
                                <label>Permite descarga</label>
                                <select name="permite_descarga">
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <div>
                                <label>Estado</label>
                                <select name="estado">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <div class="esf-admin-actions-footer">
                            <button type="submit" class="esf-btn esf-btn-primary">
                                Guardar recurso
                            </button>

                            <button type="button"
                                    @click="cerrarModal('modalCrearRecurso')"
                                    class="esf-btn esf-btn-soft">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="contenedorRecursos" class="space-y-4">
                @forelse($recursos as $recurso)
                    @php
                        $nombreVisibleRecurso = $recurso->titulo ?: 'Recurso sin título';

                        $extension = $recurso->ruta_archivo
                            ? strtolower(pathinfo($recurso->ruta_archivo, PATHINFO_EXTENSION))
                            : null;

                        $urlArchivo = $recurso->ruta_archivo
                            ? asset('storage/' . $recurso->ruta_archivo)
                            : null;

                        $esImagenRecurso = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);
                        $esPdfRecurso = $extension === 'pdf';
                        $esVideoRecurso = in_array($extension, ['mp4', 'webm', 'mov', 'm4v', 'avi'], true);
                        $esAudioRecurso = in_array($extension, ['mp3', 'wav', 'ogg', 'm4a'], true);
                        $esOfficeRecurso = in_array($extension, ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'], true);

                        $urlEsLocalRecurso = $urlArchivo
                            ? (str_contains($urlArchivo, '127.0.0.1') || str_contains($urlArchivo, 'localhost'))
                            : true;

                        $urlOfficeViewerRecurso = $urlArchivo
                            ? 'https://view.officeapps.live.com/op/embed.aspx?src=' . urlencode($urlArchivo)
                            : null;
                    @endphp

                    <details id="recurso-{{ $recurso->id_capacitacion_recurso }}"
                        class="recurso-card esf-learning-admin-card transition hover:-translate-y-1 hover:shadow-xl">
                        <summary class="esf-learning-admin-summary">
                            <div class="inline-flex w-full flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <p class="recurso-titulo font-bold text-gray-900 dark:text-gray-100">
                                        {{ $recurso->orden }}. {{ $nombreVisibleRecurso }}
                                    </p>

                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Tipo: {{ $recurso->tipo_recurso }}
                                        · {{ (int) $recurso->obligatorio === 1 ? 'Obligatorio' : 'Opcional' }}
                                        · {{ (int) $recurso->permite_descarga === 1 ? 'Permite descarga' : 'Solo visualización' }}
                                    </p>
                                </div>

                                <span class="px-3 py-1 text-xs rounded-full {{ (int) $recurso->estado === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ (int) $recurso->estado === 1 ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </summary>

                        <div class="esf-learning-admin-body space-y-4">
                            <div class="esf-learning-info-panel">
                                <div class="esf-learning-info-layout">
                                    <div>
                                        <h4 class="text-lg font-black text-slate-900 dark:text-slate-100">
                                            Información del recurso
                                        </h4>

                                        <div class="esf-learning-info-grid mt-4">
                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Tipo:</span>
                                                {{ $recurso->tipo_recurso }}
                                            </p>

                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Orden:</span>
                                                {{ $recurso->orden }}
                                            </p>

                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Ubicación:</span>
                                                {{ $recurso->seccion?->titulo ?? 'Contenido general del módulo' }}
                                            </p>

                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Obligatorio:</span>
                                                {{ (int) $recurso->obligatorio === 1 ? 'Sí' : 'No' }}
                                            </p>

                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Permite descarga:</span>
                                                {{ (int) $recurso->permite_descarga === 1 ? 'Sí' : 'No' }}
                                            </p>

                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300 md:col-span-2">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Descripción:</span>
                                                {{ $recurso->descripcion ?: 'Sin descripción registrada.' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="esf-learning-inline-actions">
                                        <button type="button"
                                                @click="abrirModal('modalEditarRecurso{{ $recurso->id_capacitacion_recurso }}')"
                                                class="esf-action-btn esf-action-edit justify-center text-center">
                                            Editar recurso
                                        </button>

                                        <form method="POST"
                                            action="{{ route('capacitacion_recursos.toggleEstado', $recurso->id_capacitacion_recurso) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="esf-action-btn esf-action-status w-full justify-center text-center">
                                                {{ (int) $recurso->estado === 1 ? 'Inactivar recurso' : 'Activar recurso' }}
                                            </button>
                                        </form>

                                        <form method="POST"
                                            action="{{ route('capacitacion_recursos.destroy', $recurso->id_capacitacion_recurso) }}"
                                            @submit="if (!confirm('¿Eliminar este recurso?')) $event.preventDefault()">
                                            @csrf
                                            @method('DELETE')

                                            <input type="hidden" name="volver_modulo" value="{{ request('volver_modulo', $recurso->id_capacitacion_modulo_seccion ? 1 : 0) }}">
                                            <input type="hidden" name="id_capacitacion_modulo_seccion" value="{{ request('id_capacitacion_modulo_seccion', $recurso->id_capacitacion_modulo_seccion) }}">
                                            <button type="submit"
                                                    class="esf-action-btn esf-action-delete w-full justify-center text-center">
                                                Eliminar recurso
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="esf-learning-question-panel">
                                <h4 class="text-lg font-black text-slate-900 dark:text-slate-100">
                                    Vista previa del recurso
                                </h4>

                                <div class="mt-4 text-sm font-semibold text-slate-600 dark:text-slate-300">
                                    @if($urlArchivo)
                                        <div class="esf-resource-preview">
                                            @if($esImagenRecurso)
                                                <img src="{{ $urlArchivo }}" alt="{{ $nombreVisibleRecurso }}">
                                            @elseif($esPdfRecurso)
                                                <iframe src="{{ $urlArchivo }}"></iframe>
                                            @elseif($esVideoRecurso)
                                                <video controls>
                                                    <source src="{{ $urlArchivo }}">
                                                </video>
                                            @elseif($esAudioRecurso)
                                                <div class="p-5">
                                                    <audio controls class="w-full">
                                                        <source src="{{ $urlArchivo }}">
                                                    </audio>
                                                </div>
                                            @elseif($esOfficeRecurso && !$urlEsLocalRecurso)
                                                <iframe src="{{ $urlOfficeViewerRecurso }}"></iframe>
                                            @else
                                                <div class="p-5">
                                                    <p class="font-black text-slate-900 dark:text-slate-100">
                                                        Archivo disponible para visualización
                                                    </p>

                                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                                        En local, algunos documentos como Word, PowerPoint o Excel se abren desde el botón. Cuando el sistema esté publicado con una URL accesible, el visor externo podrá intentar mostrarlos aquí.
                                                    </p>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <a href="{{ $urlArchivo }}"
                                            target="_blank"
                                            class="esf-btn esf-btn-soft inline-flex">
                                                Abrir archivo
                                            </a>
                                        </div>
                                    @elseif($recurso->url_recurso)
                                        <div class="esf-resource-preview">
                                            <iframe src="{{ $recurso->url_recurso }}"></iframe>
                                        </div>

                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <a href="{{ $recurso->url_recurso }}"
                                            target="_blank"
                                            class="esf-btn esf-btn-soft inline-flex">
                                                Abrir enlace
                                            </a>
                                        </div>
                                    @else
                                        <p class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-5 text-sm font-semibold text-slate-500 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300">
                                            Este recurso todavía no tiene contenido visible, archivo o enlace registrado.
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </details>

                    <div id="modalEditarRecurso{{ $recurso->id_capacitacion_recurso }}"
                        class="fixed inset-0 z-50 hidden items-start justify-center overflow-y-auto bg-black/55 px-4 py-10">

                        <div class="esf-admin-modal-card w-full max-w-4xl p-6 sm:p-8 my-8">
                            <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">
                                Editar recurso
                            </h3>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                Modifica los datos del recurso seleccionado.
                            </p>

                            <form method="POST"
                                action="{{ route('capacitacion_recursos.update', $recurso->id_capacitacion_recurso) }}"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <input type="hidden" name="volver_modulo" value="{{ request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) }}">
                                <input type="hidden" name="ruta_archivo" value="{{ old('ruta_archivo', $recurso->ruta_archivo) }}">
                                <input type="hidden" name="contenido_texto" value="">

                                <div class="esf-admin-modal-grid">
                                    <div>
                                        <label>Tipo de recurso</label>
                                        <select name="tipo_recurso" required>
                                            <option value="imagen" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'imagen' ? 'selected' : '' }}>Imagen</option>
                                            <option value="pdf" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'pdf' ? 'selected' : '' }}>PDF</option>
                                            <option value="word" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'word' ? 'selected' : '' }}>Word</option>
                                            <option value="powerpoint" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'powerpoint' ? 'selected' : '' }}>PowerPoint</option>
                                            <option value="excel" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'excel' ? 'selected' : '' }}>Excel</option>
                                            <option value="video" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'video' ? 'selected' : '' }}>Video</option>
                                            <option value="audio" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'audio' ? 'selected' : '' }}>Audio</option>
                                            <option value="enlace" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'enlace' ? 'selected' : '' }}>Enlace</option>
                                            <option value="comprimido" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'comprimido' ? 'selected' : '' }}>Archivo comprimido</option>
                                            <option value="documento" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'documento' ? 'selected' : '' }}>Documento</option>
                                            <option value="archivo" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'archivo' ? 'selected' : '' }}>Archivo</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label>Ubicar en sección/subsección</label>
                                        <select name="id_capacitacion_modulo_seccion">
                                            <option value="">Contenido general del módulo</option>

                                            @foreach($modulo->secciones->where('estado', 1) as $seccion)
                                                <option value="{{ $seccion->id_capacitacion_modulo_seccion }}"
                                                        {{ (string) old('id_capacitacion_modulo_seccion', $recurso->id_capacitacion_modulo_seccion) === (string) $seccion->id_capacitacion_modulo_seccion ? 'selected' : '' }}>
                                                    {{ $seccion->nivel == 2 ? '— ' : '' }}{{ $seccion->titulo }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <p class="esf-help-text">
                                            Aquí decides en qué parte del módulo aparecerá este recurso.
                                        </p>
                                    </div>

                                    <div>
                                        <label>Orden</label>
                                        <input type="number"
                                            name="orden"
                                            value="{{ old('orden', $recurso->orden) }}"
                                            min="1"
                                            required>
                                    </div>

                                    <div class="esf-admin-modal-full">
                                        <label>Título <span class="text-xs font-semibold text-slate-400">(opcional)</span></label>
                                        <input type="text"
                                            name="titulo"
                                            value="{{ old('titulo', $recurso->titulo) }}">
                                    </div>

                                    <div class="esf-admin-modal-full">
                                        <label>Descripción</label>
                                        <textarea name="descripcion" rows="4">{{ old('descripcion', $recurso->descripcion) }}</textarea>
                                    </div>

                                    <div>
                                        <label>URL externa</label>
                                        <input type="url"
                                            name="url_recurso"
                                            value="{{ old('url_recurso', $recurso->url_recurso) }}"
                                            placeholder="https://...">
                                    </div>

                                    <div>
                                        <label>Reemplazar archivo</label>
                                        <input type="file" name="archivo_recurso" class="esf-admin-file-input">

                                        <p class="esf-help-text">
                                            @if($recurso->ruta_archivo)
                                                Archivo actual: {{ basename($recurso->ruta_archivo) }}
                                            @else
                                                Este recurso aún no tiene archivo cargado.
                                            @endif
                                        </p>
                                    </div>

                                    <div>
                                        <label>Obligatorio</label>
                                        <select name="obligatorio" required>
                                            <option value="1" {{ old('obligatorio', $recurso->obligatorio) == 1 ? 'selected' : '' }}>Sí</option>
                                            <option value="0" {{ old('obligatorio', $recurso->obligatorio) == 0 ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label>Permite descarga</label>
                                        <select name="permite_descarga" required>
                                            <option value="1" {{ old('permite_descarga', $recurso->permite_descarga) == 1 ? 'selected' : '' }}>Sí</option>
                                            <option value="0" {{ old('permite_descarga', $recurso->permite_descarga) == 0 ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label>Estado</label>
                                        <select name="estado" required>
                                            <option value="1" {{ old('estado', $recurso->estado) == 1 ? 'selected' : '' }}>Activo</option>
                                            <option value="0" {{ old('estado', $recurso->estado) == 0 ? 'selected' : '' }}>Inactivo</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="esf-admin-actions-footer">
                                    <button type="submit" class="esf-btn esf-btn-primary">
                                        Guardar cambios
                                    </button>

                                    <button type="button"
                                            @click="cerrarModal('modalEditarRecurso{{ $recurso->id_capacitacion_recurso }}')"
                                            class="esf-btn esf-btn-soft">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="esf-admin-sheet-card p-8 text-center">
                        <p class="text-lg font-black text-slate-900 dark:text-slate-100">
                            Este módulo todavía no tiene recursos registrados.
                        </p>

                        <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Cuando crees recursos, aparecerán aquí ordenados para administrarlos.
                        </p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
                const detalleAbierto = new URLSearchParams(window.location.search).get('open');

                if (detalleAbierto) {
                    const detalle = document.getElementById(detalleAbierto);

                    if (detalle && detalle.tagName.toLowerCase() === 'details') {
                        detalle.open = true;
                        detalle.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            const input = document.getElementById('buscarRecurso');
            const tarjetas = document.querySelectorAll('.recurso-card');

            if (!input) {
                return;
            }

            input.addEventListener('input', function () {
                const texto = this.value.toLowerCase().trim();

                tarjetas.forEach(function (tarjeta) {
                    const titulo = tarjeta.querySelector('.recurso-titulo')?.textContent.toLowerCase() || '';
                    tarjeta.style.display = titulo.includes(texto) ? '' : 'none';
                });
            });
        });
    </script>

    <script>
    function abrirModal(id) {
        const modal = document.getElementById(id);

        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function cerrarModal(id) {
        const modal = document.getElementById(id);

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('DOMContentLoaded', function () {
        @if((int) request('crear') === 1)
            abrirModal('modalCrearRecurso');
        @endif
    });
</script>
</x-app-layout>