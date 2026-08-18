<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                Módulo de capacitación
            </p>

            <h2 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100 leading-tight">
                Editar módulo: {{ $modulo->titulo }}
            </h2>

            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Organiza la teoría, secciones, recursos, ejercicios y evaluaciones del módulo.
            </p>
        </div>
    </x-slot>

    <div class="py-8 esf-module-editor-page">
        <div class="w-full max-w-[1150px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="esf-page-card esf-module-editor-card overflow-hidden">
                <div class="p-6 sm:p-8 text-slate-900 dark:text-slate-100">

                    @if (session('success'))
                        <div class="mb-6 esf-alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 esf-alert-error">
                            <strong>Revisa los siguientes campos:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('capacitacion_modulos.update', $modulo->id_capacitacion_modulo) }}">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="origen" value="builder">
                        <div class="mb-4">
                            <label class="block mb-1">Título del módulo</label>
                            <input type="text" name="titulo" value="{{ old('titulo', $modulo->titulo) }}"
                                class="w-full border rounded px-3 py-2 text-black @error('titulo') border-red-500 @enderror">
                            @error('titulo') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-500 mt-1">Mínimo 3 caracteres.</p>
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Descripción</label>
                            <textarea name="descripcion" rows="4"
                                class="w-full border rounded px-3 py-2 text-black @error('descripcion') border-red-500 @enderror">{{ old('descripcion', $modulo->descripcion) }}</textarea>
                            @error('descripcion') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Objetivo</label>
                            <textarea name="objetivo" rows="3"
                                class="w-full border rounded px-3 py-2 text-black @error('objetivo') border-red-500 @enderror">{{ old('objetivo', $modulo->objetivo) }}</textarea>
                            @error('objetivo') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        @php
                            $seccionesPadreModulo = $modulo->secciones
                                ->where('nivel', 1)
                                ->where('estado', 1)
                                ->values();

                            $urlCrearRecursoModulo = route('capacitacion_modulos.recursos.index', [
                                'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                                'crear' => 1,
                                'volver_modulo' => 1,
                            ]);

                            $urlEjerciciosModulo = route('capacitacion_modulos.ejercicios.index', [
                                'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                                'crear' => 1,
                                'volver_modulo' => 1,
                            ]);

                            $urlEvaluacionesModulo = route('capacitacion_modulos.evaluaciones.index', [
                                'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                                'crear' => 1,
                                'volver_modulo' => 1,
                            ]);
                        @endphp

                        <div class="mb-6 esf-module-soft-panel">
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 dark:text-slate-100">
                                        Teoría del módulo
                                    </h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        Edita las páginas o secciones del módulo. Desde cada sección puedes agregar recursos, ejercicios o evaluaciones.
                                    </p>
                                </div>

                                <button type="button"
                                        @click="agregarSeccionModulo('contenedorSeccionesModulo')"
                                        class="esf-btn esf-btn-primary text-sm">
                                    + Agregar página
                                </button>
                            </div>

                            <div id="contenedorSeccionesModulo" class="space-y-4">
                                @forelse($modulo->secciones as $seccion)
                                    <div id="seccion-modulo-{{ $seccion->id_capacitacion_modulo_seccion }}"
                                        class="border border-gray-300 bg-white p-3 rounded mb-3 seccion-modulo-item"
                                        data-bloque-pagina-seccion>
                                        <input type="hidden"
                                            name="secciones_id[]"
                                            value="{{ $seccion->id_capacitacion_modulo_seccion }}">

                                        <div class="flex justify-between items-center mb-2 gap-2">
                                            <strong>Página / sección {{ $loop->iteration }}</strong>

                                            <div class="flex flex-wrap gap-2 justify-end">
                                                <button type="button"
                                                        @click="agregarSubseccionASeccion($el)"
                                                        class="px-2 py-1 bg-emerald-600 text-white rounded text-xs boton-agregar-subseccion {{ (int) old('secciones_nivel.' . $loop->index, $seccion->nivel ?? 1) === 1 ? '' : 'hidden' }}">
                                                    + Subsección
                                                </button>

                                                <button type="button"
                                                        @click="eliminarSeccionModulo($el)"
                                                        class="px-2 py-1 bg-red-600 text-white rounded text-xs">
                                                    Quitar
                                                </button>
                                            </div>
                                        </div>

                                        <label class="block text-sm font-medium">Título de la página</label>
                                        <input type="text"
                                               name="secciones_titulo[]"
                                               value="{{ old('secciones_titulo.' . $loop->index, $seccion->titulo) }}"
                                               class="w-full border rounded px-3 py-2 text-black mb-3">

                                        <label class="block text-sm font-medium">Tipo de sección</label>
                                        <select name="secciones_nivel[]"
                                                class="w-full border rounded px-3 py-2 text-black mb-3 tipo-seccion-modulo"
                                                @change="actualizarSelectorPadreSeccion($el)">
                                            <option value="1" {{ old('secciones_nivel.' . $loop->index, $seccion->nivel ?? 1) == 1 ? 'selected' : '' }}>
                                                Sección principal
                                            </option>
                                            <option value="2" {{ old('secciones_nivel.' . $loop->index, $seccion->nivel ?? 1) == 2 ? 'selected' : '' }}>
                                                Subsección
                                            </option>
                                        </select>


                                        @php
                                            $indiceSeccionFormulario = $loop->index;

                                            $nivelActualSeccion = (int) old(
                                                'secciones_nivel.' . $indiceSeccionFormulario,
                                                $seccion->nivel ?? 1
                                            );

                                            $idSeccionActualModulo = $seccion->id_capacitacion_modulo_seccion;

                                            $recursosDeEstaSeccion = ($modulo->recursos ?? collect())
                                                ->where('id_capacitacion_modulo_seccion', $idSeccionActualModulo)
                                                ->values();

                                            $ejerciciosDeEstaSeccion = ($modulo->ejercicios ?? collect())
                                                ->where('id_capacitacion_modulo_seccion', $idSeccionActualModulo)
                                                ->values();

                                            $evaluacionesDeEstaSeccion = ($modulo->evaluaciones ?? collect())
                                                ->where('id_capacitacion_modulo_seccion', $idSeccionActualModulo)
                                                ->values();
                                        @endphp

                                        <div class="mt-2 campo-padre-seccion {{ $nivelActualSeccion === 2 ? '' : 'hidden' }}">
                                            <label class="block text-sm font-medium">Subsección de</label>

                                            <select name="secciones_padre[]"
                                                    class="w-full border rounded px-3 py-2 text-black mb-3">
                                                <option value="">Seleccionar sección principal</option>

                                                @foreach($seccionesPadreModulo as $seccionPadre)
                                                    @if($seccionPadre->id_capacitacion_modulo_seccion !== $seccion->id_capacitacion_modulo_seccion)
                                                        <option value="{{ $seccionPadre->id_capacitacion_modulo_seccion }}"
                                                                {{ old('secciones_padre.' . $indiceSeccionFormulario, $seccion->id_seccion_padre) == $seccionPadre->id_capacitacion_modulo_seccion ? 'selected' : '' }}>
                                                            {{ $seccionPadre->titulo }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>

                                            <p class="text-xs text-gray-500 mt-1">
                                                Selecciona debajo de qué sección principal aparecerá esta subsección.
                                            </p>
                                        </div>

                                        <div class="acciones-contenido-subseccion mt-3 rounded border border-emerald-300 bg-emerald-50 p-3"
                                            data-url-recurso-base="{{ $urlCrearRecursoModulo }}"
                                            data-url-ejercicios-base="{{ $urlEjerciciosModulo }}"
                                            data-url-evaluaciones-base="{{ $urlEvaluacionesModulo }}">

                                            <p class="text-sm font-semibold text-emerald-900">
                                                Contenido de esta sección
                                            </p>

                                            <p class="text-xs text-emerald-800 mt-1">
                                                Esta sección puede tener teoría escrita, recursos/archivos, ejercicios o evaluaciones. También puede ser una sección principal o una subsección.
                                            </p>

                                            <div class="aviso-subseccion-sin-guardar {{ $idSeccionActualModulo ? 'hidden' : '' }} mt-3 rounded border border-yellow-300 bg-yellow-100 px-3 py-2 text-xs text-yellow-800">
                                                Escribí el título de la sección. Al elegir una acción, el sistema guardará esta sección automáticamente.
                                            </div>

                                            <div class="botones-subseccion-contenido {{ $idSeccionActualModulo ? '' : 'hidden' }} mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">

                                                <div class="space-y-2">
                                                    <a data-accion-subseccion="recurso"
                                                        href="{{ route('capacitacion_modulos.recursos.index', [
                                                                'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                                                                'crear' => 1,
                                                                'volver_modulo' => 1,
                                                                'id_capacitacion_modulo_seccion' => $idSeccionActualModulo,
                                                        ]) }}"
                                                        class="block text-center px-3 py-2 bg-blue-600 text-white rounded text-xs">
                                                            Seleccionar archivo
                                                    </a>

                                                    @foreach($recursosDeEstaSeccion as $recursoSubseccion)
                                                        <a href="{{ route('capacitacion_modulos.recursos.index', [
                                                                'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                                                                'open' => 'recurso-' . $recursoSubseccion->id_capacitacion_recurso,
                                                                'volver_modulo' => 1,
                                                                'id_capacitacion_modulo_seccion' => $idSeccionActualModulo,
                                                            ]) }}"
                                                        class="block rounded border border-blue-200 bg-white px-3 py-2 text-xs text-blue-900 hover:bg-blue-50">
                                                            <span class="block font-bold">Recurso</span>
                                                            <span class="block truncate">{{ $recursoSubseccion->titulo }}</span>
                                                        </a>
                                                    @endforeach
                                                </div>

                                                <div class="space-y-2">
                                                    <a data-accion-subseccion="ejercicio"
                                                    href="{{ route('capacitacion_modulos.ejercicios.index', [
                                                            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                                                            'crear' => 1,
                                                            'volver_modulo' => 1,
                                                            'id_capacitacion_modulo_seccion' => $idSeccionActualModulo,
                                                    ]) }}"
                                                    class="block text-center px-3 py-2 bg-emerald-600 text-white rounded text-xs">
                                                        Agregar ejercicio
                                                    </a>

                                                    @foreach($ejerciciosDeEstaSeccion as $ejercicioSubseccion)
                                                        <a href="{{ route('capacitacion_modulos.ejercicios.index', [
                                                                'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                                                                'open' => 'ejercicio-' . $ejercicioSubseccion->id_ejercicio,
                                                                'volver_modulo' => 1,
                                                                'id_capacitacion_modulo_seccion' => $idSeccionActualModulo,
                                                            ]) }}"
                                                        class="block rounded border border-emerald-200 bg-white px-3 py-2 text-xs text-emerald-900 hover:bg-emerald-50">
                                                            <span class="block font-bold">Ejercicio</span>
                                                            <span class="block truncate">{{ $ejercicioSubseccion->titulo }}</span>
                                                            <span class="block text-[11px] text-gray-500">
                                                                {{ $ejercicioSubseccion->preguntas->count() }} pregunta(s)
                                                            </span>
                                                        </a>
                                                    @endforeach
                                                </div>

                                                <div class="space-y-2">
                                                    <a data-accion-subseccion="evaluacion"
                                                        href="{{ route('capacitacion_modulos.evaluaciones.index', [
                                                                'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                                                                'crear' => 1,
                                                                'volver_modulo' => 1,
                                                                'id_capacitacion_modulo_seccion' => $idSeccionActualModulo,
                                                        ]) }}"
                                                        class="block text-center px-3 py-2 bg-purple-600 text-white rounded text-xs">
                                                            Agregar evaluación
                                                        </a>

                                                    @foreach($evaluacionesDeEstaSeccion as $evaluacionSubseccion)
                                                        <a href="{{ route('capacitacion_modulos.evaluaciones.index', [
                                                                'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                                                                'open' => 'evaluacion-' . $evaluacionSubseccion->id_evaluacion,
                                                                'volver_modulo' => 1,
                                                                'id_capacitacion_modulo_seccion' => $idSeccionActualModulo,
                                                            ]) }}"
                                                        class="block rounded border border-purple-200 bg-white px-3 py-2 text-xs text-purple-900 hover:bg-purple-50">
                                                            <span class="block font-bold">Evaluación</span>
                                                            <span class="block truncate">{{ $evaluacionSubseccion->titulo }}</span>
                                                            <span class="block text-[11px] text-gray-500">
                                                                {{ $evaluacionSubseccion->preguntas->count() }} pregunta(s)
                                                            </span>
                                                        </a>
                                                    @endforeach
                                                </div>

                                            </div>
                                        </div>


                                        <label class="block text-sm font-medium mb-1">Contenido escrito</label>

                                        <input type="hidden"
                                            name="secciones_contenido[]"
                                            class="input-contenido-seccion-modulo"
                                            value="{{ old('secciones_contenido.' . $loop->index, $seccion->contenido) }}">

                                        <div class="editor-contenido-seccion-modulo bg-white text-black rounded border border-gray-300"
                                            style="min-height: 260px;">
                                            {!! old('secciones_contenido.' . $loop->index, $seccion->contenido) !!}
                                        </div>
                                    </div>
                                @empty
                                    <div class="seccion-modulo-item rounded border border-gray-300 bg-white p-4"
                                        data-bloque-pagina-seccion>
                                        <input type="hidden"
                                            name="secciones_id[]"
                                            value="">

                                        <div class="flex items-center justify-between mb-3 gap-2">
                                            <strong>Página / sección</strong>

                                            <div class="flex flex-wrap gap-2 justify-end">
                                                <button type="button"
                                                        @click="agregarSubseccionASeccion($el)"
                                                        class="px-2 py-1 bg-emerald-600 text-white rounded text-xs boton-agregar-subseccion">
                                                    + Subsección
                                                </button>

                                                <button type="button"
                                                        @click="eliminarSeccionModulo($el)"
                                                        class="px-2 py-1 bg-red-600 text-white rounded text-xs">
                                                    Quitar
                                                </button>
                                            </div>
                                        </div>

                                        <label class="block text-sm font-medium">Título de la página</label>
                                        <input type="text"
                                            name="secciones_titulo[]"
                                            placeholder="Ejemplo: Introducción"
                                            class="w-full border rounded px-3 py-2 text-black mb-3">

                                        <label class="block text-sm font-medium">Tipo de sección</label>
                                        <select name="secciones_nivel[]"
                                                class="w-full border rounded px-3 py-2 text-black mb-3 tipo-seccion-modulo"
                                                @change="actualizarSelectorPadreSeccion($el)">
                                            <option value="1">Sección principal</option>
                                            <option value="2">Subsección</option>
                                        </select>

                                        <div class="mt-2 campo-padre-seccion hidden">
                                            <label class="block text-sm font-medium">Subsección de</label>

                                            <select name="secciones_padre[]"
                                                    class="w-full border rounded px-3 py-2 text-black mb-3">
                                                <option value="">Seleccionar sección principal</option>

                                                @foreach($seccionesPadreModulo as $seccionPadre)
                                                    <option value="{{ $seccionPadre->id_capacitacion_modulo_seccion }}">
                                                        {{ $seccionPadre->titulo }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <p class="text-xs text-gray-500 mt-1">
                                                Selecciona debajo de qué sección principal aparecerá esta subsección.
                                            </p>
                                        </div>

                                        <div class="acciones-contenido-subseccion mt-3 rounded border border-emerald-300 bg-emerald-50 p-3"
                                            data-url-recurso-base="{{ $urlCrearRecursoModulo }}"
                                            data-url-ejercicios-base="{{ $urlEjerciciosModulo }}"
                                            data-url-evaluaciones-base="{{ $urlEvaluacionesModulo }}">

                                            <p class="text-sm font-semibold text-emerald-900">
                                                Contenido de esta sección
                                            </p>

                                            <p class="text-xs text-emerald-800 mt-1">
                                                Escribí el título de la sección y luego puedes agregar recursos, ejercicios o evaluaciones sin presionar Actualizar.
                                            </p>

                                            <div class="aviso-subseccion-sin-guardar mt-3 rounded border border-yellow-300 bg-yellow-100 px-3 py-2 text-xs text-yellow-800">
                                                Esta sección todavía no está guardada. Al seleccionar una acción, se guardará automáticamente.
                                            </div>

                                            <div class="botones-subseccion-contenido mt-3 flex flex-wrap gap-2">
                                                <a data-accion-subseccion="recurso"
                                                href="#"
                                                class="px-3 py-2 bg-blue-600 text-white rounded text-xs">
                                                    Seleccionar archivo
                                                </a>

                                                <a data-accion-subseccion="ejercicio"
                                                href="#"
                                                class="px-3 py-2 bg-emerald-600 text-white rounded text-xs">
                                                    Agregar ejercicio
                                                </a>

                                                <a data-accion-subseccion="evaluacion"
                                                href="#"
                                                class="px-3 py-2 bg-purple-600 text-white rounded text-xs">
                                                    Agregar evaluación
                                                </a>
                                            </div>
                                        </div>

                                        <label class="block text-sm font-medium mb-1">Contenido escrito</label>

                                        <input type="hidden"
                                            name="secciones_contenido[]"
                                            class="input-contenido-seccion-modulo">

                                        <div class="editor-contenido-seccion-modulo bg-white text-black rounded border border-gray-300"
                                            style="min-height: 260px;"></div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="block mb-1">Orden</label>
                                <input type="number" name="orden" value="{{ old('orden', $modulo->orden) }}"
                                    class="w-full border rounded px-3 py-2 text-black @error('orden') border-red-500 @enderror">
                                @error('orden') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1">Duración en horas</label>
                                <input type="number" step="0.01" name="duracion_horas" value="{{ old('duracion_horas', $modulo->duracion_horas) }}"
                                    class="w-full border rounded px-3 py-2 text-black @error('duracion_horas') border-red-500 @enderror">
                                @error('duracion_horas') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1">Requiere evaluación</label>
                                <select name="requiere_evaluacion"
                                    class="w-full border rounded px-3 py-2 text-black @error('requiere_evaluacion') border-red-500 @enderror">
                                    <option value="1" {{ old('requiere_evaluacion', $modulo->requiere_evaluacion) == '1' ? 'selected' : '' }}>Sí</option>
                                    <option value="0" {{ old('requiere_evaluacion', $modulo->requiere_evaluacion) == '0' ? 'selected' : '' }}>No</option>
                                </select>
                                @error('requiere_evaluacion') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1">Porcentaje de aprobación</label>
                                <input type="number" step="0.01" name="porcentaje_aprobacion" value="{{ old('porcentaje_aprobacion', $modulo->porcentaje_aprobacion) }}"
                                    class="w-full border rounded px-3 py-2 text-black @error('porcentaje_aprobacion') border-red-500 @enderror">
                                @error('porcentaje_aprobacion') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-6">
                                <label class="block mb-1">Estado</label>
                                <select name="estado"
                                    class="w-full border rounded px-3 py-2 text-black @error('estado') border-red-500 @enderror">
                                    <option value="1" {{ old('estado', $modulo->estado) == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('estado', $modulo->estado) == '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                @error('estado') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-200/80 dark:border-slate-700/80 pt-5">
                            <p class="text-xs text-slate-400 dark:text-slate-500">
                                Guarda los cambios para actualizar la estructura del módulo.
                            </p>

                            <div class="flex flex-col sm:flex-row gap-3">
                                <a href="{{ route('capacitaciones.builder', $modulo->id_capacitacion) }}"
                                class="esf-btn esf-btn-soft">
                                    Cancelar
                                </a>

                                <button type="submit" class="esf-btn esf-btn-primary">
                                    Actualizar módulo
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">

    <script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

    <style>
        .editor-contenido-seccion-modulo .ql-editor {
            min-height: 220px;
            font-size: 16px;
            line-height: 1.75;
        }

        .editor-contenido-seccion-modulo .ql-editor img {
            max-width: 100%;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 0 !important;
            margin: 12px 0;
        }

        .editor-contenido-seccion-modulo .ql-toolbar {
            border-radius: 0.375rem 0.375rem 0 0;
            background: #ffffff;
        }

        .editor-contenido-seccion-modulo .ql-container {
            border-radius: 0 0 0.375rem 0.375rem;
            background: #ffffff;
        }

        .editor-contenido-seccion-modulo .ql-editor [style*="column-count"] {
            column-gap: 24px;
        }

        .editor-contenido-seccion-modulo .ql-editor img {
            height: auto;
            cursor: pointer;
        }

        .ql-toolbar button.ql-columnas::before {
            content: "Cols";
            font-size: 10px;
            font-weight: 700;
        }

        .ql-toolbar button.ql-imageLeft::before {
            content: "Img←";
            font-size: 9px;
            font-weight: 700;
        }

        .ql-toolbar button.ql-imageInline::before {
            content: "Img↔";
            font-size: 9px;
            font-weight: 700;
        }

        .ql-toolbar button.ql-imageRight::before {
            content: "Img→";
            font-size: 9px;
            font-weight: 700;
        }

        .panel-ajuste-imagen-teoria {
            display: none;
            position: fixed;
            z-index: 99999;
            width: min(520px, calc(100vw - 2rem));
            padding: 0.85rem;
            border: 1px solid rgba(147, 197, 253, 0.95);
            border-radius: 18px;
            background: rgba(239, 246, 255, 0.98);
            box-shadow: 0 22px 55px rgba(15, 23, 42, 0.18);
            backdrop-filter: blur(14px);
        }

        .panel-ajuste-imagen-teoria.activo {
            display: block;
        }

        .panel-ajuste-imagen-teoria::before {
            content: "";
            position: absolute;
            top: -8px;
            left: 28px;
            width: 14px;
            height: 14px;
            transform: rotate(45deg);
            border-left: 1px solid rgba(147, 197, 253, 0.95);
            border-top: 1px solid rgba(147, 197, 253, 0.95);
            background: rgba(239, 246, 255, 0.98);
        }

        .panel-ajuste-imagen-teoria-titulo {
            font-size: 0.78rem;
            font-weight: 900;
            color: #1e3a8a;
            margin-bottom: 0.65rem;
        }

        .panel-ajuste-imagen-teoria-controles {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
        }

        .panel-ajuste-imagen-teoria button {
            border: 1px solid rgba(191, 219, 254, 0.95);
            background: #ffffff;
            color: #1e3a8a;
            border-radius: 999px;
            padding: 0.42rem 0.68rem;
            font-size: 0.72rem;
            font-weight: 900;
            cursor: pointer;
            transition: background 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
        }

        .panel-ajuste-imagen-teoria button:hover {
            background: #dbeafe;
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.12);
        }

        .panel-ajuste-imagen-teoria-ayuda {
            margin-top: 0.65rem;
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
        }

        @media (max-width: 640px) {
            .panel-ajuste-imagen-teoria {
                left: 1rem !important;
                right: 1rem !important;
                top: auto !important;
                bottom: 1rem !important;
                width: auto;
                max-height: 45vh;
                overflow-y: auto;
            }

            .panel-ajuste-imagen-teoria::before {
                display: none;
            }

            .panel-ajuste-imagen-teoria button {
                flex: 1 1 auto;
            }
        }

        .ql-font-arial {
            font-family: Arial, sans-serif;
        }

        .ql-font-times-new-roman {
            font-family: "Times New Roman", serif;
        }

        .ql-font-georgia {
            font-family: Georgia, serif;
        }

        .ql-font-verdana {
            font-family: Verdana, sans-serif;
        }

        .ql-font-tahoma {
            font-family: Tahoma, sans-serif;
        }

        .ql-font-trebuchet-ms {
            font-family: "Trebuchet MS", sans-serif;
        }

        .ql-font-courier-new {
            font-family: "Courier New", monospace;
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="arial"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="arial"]::before {
            content: "Arial";
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="times-new-roman"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="times-new-roman"]::before {
            content: "Times";
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="georgia"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="georgia"]::before {
            content: "Georgia";
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="verdana"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="verdana"]::before {
            content: "Verdana";
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="tahoma"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="tahoma"]::before {
            content: "Tahoma";
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="trebuchet-ms"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="trebuchet-ms"]::before {
            content: "Trebuchet";
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="courier-new"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="courier-new"]::before {
            content: "Courier";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="12px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="12px"]::before {
            content: "12px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="14px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="14px"]::before {
            content: "14px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="16px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="16px"]::before {
            content: "16px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="18px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="18px"]::before {
            content: "18px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="20px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="20px"]::before {
            content: "20px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="24px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="24px"]::before {
            content: "24px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="28px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="28px"]::before {
            content: "28px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="32px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="32px"]::before {
            content: "32px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="36px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="36px"]::before {
            content: "36px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="48px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="48px"]::before {
            content: "48px";
        }
    </style>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        const urlSubidaImagenTeoriaModulo = "{{ route('capacitacion_modulos.teoria.imagen') }}";
        const tokenCsrfTeoriaModulo = "{{ csrf_token() }}";

        function mostrarAvisoLocalModulo(elemento, mensaje) {
            if (!elemento) {
                return;
            }

            const contenedor = elemento.closest('.seccion-modulo-item, .esf-admin-modal-card, form') || elemento.parentElement;

            if (!contenedor) {
                return;
            }

            const avisoAnterior = contenedor.querySelector('.esf-inline-field-alert');

            if (avisoAnterior) {
                avisoAnterior.remove();
            }

            const aviso = document.createElement('div');
            aviso.className = 'esf-inline-field-alert';
            aviso.textContent = mensaje;

            if (elemento.parentElement) {
                elemento.parentElement.insertBefore(aviso, elemento.nextSibling);
            } else {
                contenedor.prepend(aviso);
            }
        }

        function mostrarAvisoEditorModulo(quill, mensaje) {
            const editor = quill && quill.container ? quill.container : null;
            mostrarAvisoLocalModulo(editor, mensaje);
        }

        const urlGuardarSeccionRapida = "{{ route('capacitacion_modulos.secciones.guardar_rapida', $modulo->id_capacitacion_modulo) }}";

        const urlCrearRecursoModulo = "{{ $urlCrearRecursoModulo }}";
        const urlEjerciciosModulo = "{{ $urlEjerciciosModulo }}";
        const urlEvaluacionesModulo = "{{ $urlEvaluacionesModulo }}";

        const FontTeoriaModulo = Quill.import('formats/font');
        FontTeoriaModulo.whitelist = [
            'arial',
            'times-new-roman',
            'georgia',
            'verdana',
            'tahoma',
            'trebuchet-ms',
            'courier-new'
        ];
        Quill.register(FontTeoriaModulo, true);

        const SizeTeoriaModulo = Quill.import('attributors/style/size');
        SizeTeoriaModulo.whitelist = [
            '12px',
            '14px',
            '16px',
            '18px',
            '20px',
            '24px',
            '28px',
            '32px',
            '36px',
            '48px'
        ];
        Quill.register(SizeTeoriaModulo, true);

        const ImagenBaseTeoriaModulo = Quill.import('formats/image');

        class ImagenTeoriaModulo extends ImagenBaseTeoriaModulo {
            static formats(domNode) {
                const formatos = super.formats ? (super.formats(domNode) || {}) : {};

                ['style', 'class', 'data-align', 'data-width'].forEach(function (atributo) {
                    if (domNode.hasAttribute(atributo)) {
                        formatos[atributo] = domNode.getAttribute(atributo);
                    }
                });

                return formatos;
            }

            format(nombre, valor) {
                if (['style', 'class', 'data-align', 'data-width'].includes(nombre)) {
                    if (valor) {
                        this.domNode.setAttribute(nombre, valor);
                    } else {
                        this.domNode.removeAttribute(nombre);
                    }

                    return;
                }

                super.format(nombre, valor);
            }
        }

        ImagenTeoriaModulo.blotName = 'image';
        ImagenTeoriaModulo.tagName = 'IMG';

        Quill.register(ImagenTeoriaModulo, true);

        const ParchmentTeoriaModulo = Quill.import('parchment');

        const ColumnasTeoriaModulo = new ParchmentTeoriaModulo.Attributor.Style(
            'columns',
            'column-count',
            {
                scope: ParchmentTeoriaModulo.Scope.BLOCK,
                whitelist: ['2', '3']
            }
        );

        const SeparacionColumnasTeoriaModulo = new ParchmentTeoriaModulo.Attributor.Style(
            'columnGap',
            'column-gap',
            {
                scope: ParchmentTeoriaModulo.Scope.BLOCK,
                whitelist: ['24px']
            }
        );

        Quill.register(ColumnasTeoriaModulo, true);
        Quill.register(SeparacionColumnasTeoriaModulo, true);

        const toolbarTeoriaModulo = [
            [{ 'font': [] }],
            [{ 'size': ['small', false, 'large', 'huge'] }],
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

            ['bold', 'italic', 'underline', 'strike'],
            [{ 'script': 'sub' }, { 'script': 'super' }],

            [{ 'color': [] }, { 'background': [] }],

            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            [{ 'indent': '-1' }, { 'indent': '+1' }],

            [{ 'align': [] }],
            ['blockquote', 'code-block'],

            ['link', 'image', 'columnas'],
            ['imageLeft', 'imageInline', 'imageRight'],
            ['clean']
        ];

        function obtenerModulosQuillTeoria() {
            return {
                toolbar: toolbarTeoriaModulo,
                history: {
                    delay: 1000,
                    maxStack: 100,
                    userOnly: true
                }
            };
        }

        function aplicarColumnasTeoriaModulo(quill) {
            const rango = quill.getSelection(true);

            if (!rango) {
                return;
            }

            const valor = prompt('Escribí 2 o 3 para poner columnas. Escribí 0 para quitar columnas.', '2');

            if (valor === null) {
                return;
            }

            const columnas = valor.trim();

            if (columnas === '0' || columnas === '') {
                quill.formatLine(rango.index, rango.length || 1, 'columns', false);
                quill.formatLine(rango.index, rango.length || 1, 'columnGap', false);
                return;
            }

            if (!['2', '3'].includes(columnas)) {
                mostrarAvisoEditorModulo(quill, 'Solo se permite usar 2 o 3 columnas.');
                return;
            }

            quill.formatLine(rango.index, rango.length || 1, 'columns', columnas);
            quill.formatLine(rango.index, rango.length || 1, 'columnGap', '24px');
        }

        function obtenerImagenSeleccionadaTeoriaModulo(quill, silencioso = false) {
            const editor = quill && quill.container ? quill.container : null;

            if (editor && editor.__imagenSeleccionadaTeoriaModulo) {
                return editor.__imagenSeleccionadaTeoriaModulo;
            }

            const rango = quill.getSelection(true);

            if (!rango) {
                if (!silencioso) {
                    mostrarAvisoEditorModulo(quill, 'Primero haz clic sobre una imagen dentro del editor.');
                }

                return null;
            }

            const leaf = quill.getLeaf(rango.index);

            if (!leaf || !leaf[0] || !leaf[0].domNode) {
                if (!silencioso) {
                    mostrarAvisoEditorModulo(quill, 'Primero haz clic sobre una imagen dentro del editor.');
                }

                return null;
            }

            const nodo = leaf[0].domNode;

            if (nodo.tagName === 'IMG') {
                return nodo;
            }

            if (nodo.previousSibling && nodo.previousSibling.tagName === 'IMG') {
                return nodo.previousSibling;
            }

            if (!silencioso) {
                mostrarAvisoEditorModulo(quill, 'Primero haz clic sobre una imagen dentro del editor.');
            }

            return null;
        }

        function actualizarAtributosImagenTeoriaModulo(imagen, posicion, ancho) {
            if (!imagen) {
                return;
            }

            imagen.classList.add('imagen-teoria-ajustada');
            imagen.setAttribute('data-align', posicion);
            imagen.setAttribute('data-width', ancho);

            imagen.style.maxWidth = '100%';
            imagen.style.height = 'auto';
            imagen.style.cursor = 'pointer';

            if (ancho === 'auto') {
                imagen.style.width = '';
            } else {
                imagen.style.width = ancho;
            }

            if (posicion === 'left') {
                imagen.style.float = 'left';
                imagen.style.display = 'inline-block';
                imagen.style.margin = '0 18px 12px 0';
                imagen.style.verticalAlign = 'middle';
                return;
            }

            if (posicion === 'right') {
                imagen.style.float = 'right';
                imagen.style.display = 'inline-block';
                imagen.style.margin = '0 0 12px 18px';
                imagen.style.verticalAlign = 'middle';
                return;
            }

            if (posicion === 'center') {
                imagen.style.float = '';
                imagen.style.display = 'block';
                imagen.style.margin = '14px auto';
                imagen.style.verticalAlign = '';
                return;
            }

            imagen.style.float = '';
            imagen.style.display = 'block';
            imagen.style.margin = '14px 0';
            imagen.style.verticalAlign = '';
        }

        function aplicarAjusteImagenTeoriaModulo(editor, quill, posicion = null, ancho = null) {
            const imagen = obtenerImagenSeleccionadaTeoriaModulo(quill);

            if (!imagen) {
                return;
            }

            const posicionActual = posicion || imagen.getAttribute('data-align') || 'normal';
            const anchoActual = ancho || imagen.getAttribute('data-width') || imagen.style.width || 'auto';

            actualizarAtributosImagenTeoriaModulo(imagen, posicionActual, anchoActual);

            marcarContenidoSeccionModuloTocado(editor);
            quill.update('silent');
            sincronizarEditorSeccionModulo(editor);
        }

        function limpiarAjusteImagenTeoriaModulo(editor, quill) {
            const imagen = obtenerImagenSeleccionadaTeoriaModulo(quill);

            if (!imagen) {
                return;
            }

            imagen.classList.remove('imagen-teoria-ajustada');
            imagen.removeAttribute('data-align');
            imagen.removeAttribute('data-width');
            imagen.removeAttribute('style');

            imagen.style.maxWidth = '100%';
            imagen.style.height = 'auto';
            imagen.style.margin = '12px 0';
            imagen.style.cursor = 'pointer';

            marcarContenidoSeccionModuloTocado(editor);
            quill.update('silent');
            sincronizarEditorSeccionModulo(editor);
        }

        function aplicarPosicionImagenTeoriaModulo(quill, posicion) {
            const editor = quill && quill.container ? quill.container : null;

            if (!editor) {
                return;
            }

            aplicarAjusteImagenTeoriaModulo(editor, quill, posicion, null);
        }

        function subirImagenTeoriaModulo(archivo, quill) {
            const formData = new FormData();
            formData.append('imagen', archivo);

            fetch(urlSubidaImagenTeoriaModulo, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': tokenCsrfTeoriaModulo,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.url) {
                    mostrarAvisoEditorModulo(quill, 'No se pudo subir la imagen.');
                    return;
                }

                const rango = quill.getSelection(true);
                quill.insertEmbed(rango.index, 'image', data.url);
                quill.setSelection(rango.index + 1);
            })
            .catch(() => {
                mostrarAvisoEditorModulo(quill, 'Ocurrió un error al subir la imagen.');
            });
        }

        // El botón de imagen de la barra de herramientas ya sube el archivo al
        // servidor (subirImagenTeoriaModulo). Pero al pegar una captura de
        // pantalla o arrastrar un archivo de imagen dentro del editor, Quill
        // por defecto la incrusta como base64 directamente en el contenido, lo
        // que infla el HTML a decenas de miles de caracteres y revienta el
        // límite de 100000 caracteres de secciones_contenido. Estas dos
        // funciones interceptan esos dos caminos y las suben igual que el botón.
        function subirBlobImagenTeoriaModulo(blob, quill, indice) {
            const formData = new FormData();
            formData.append('imagen', blob, 'imagen.png');

            fetch(urlSubidaImagenTeoriaModulo, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': tokenCsrfTeoriaModulo,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.url) {
                    mostrarAvisoEditorModulo(quill, 'No se pudo subir la imagen.');
                    return;
                }

                quill.insertEmbed(indice, 'image', data.url, 'user');
                quill.setSelection(indice + 1);
            })
            .catch(() => {
                mostrarAvisoEditorModulo(quill, 'Ocurrió un error al subir la imagen.');
            });
        }

        // La política de seguridad del sitio (connect-src 'self') bloquea que
        // fetch() cargue URLs data:, así que la conversión de base64 a Blob se
        // hace a mano con atob() en vez de fetch(src).blob() — eso no depende
        // de red y no choca con esa política.
        function dataUrlABlobTeoriaModulo(dataUrl) {
            const partes = dataUrl.split(',');
            const encabezado = partes[0] || '';
            const coincidenciaMime = /:(.*?);/.exec(encabezado);
            const mime = coincidenciaMime ? coincidenciaMime[1] : 'image/png';
            const binario = atob(partes[1] || '');
            const bytes = new Uint8Array(binario.length);

            for (let i = 0; i < binario.length; i++) {
                bytes[i] = binario.charCodeAt(i);
            }

            return new Blob([bytes], { type: mime });
        }

        function interceptarImagenBase64TeoriaModulo(node, quill) {
            const src = node && node.getAttribute ? node.getAttribute('src') : null;

            if (!src || src.indexOf('data:') !== 0) {
                return null;
            }

            const rango = quill.getSelection(true);
            const indice = rango ? rango.index : quill.getLength();

            try {
                const blob = dataUrlABlobTeoriaModulo(src);
                subirBlobImagenTeoriaModulo(blob, quill, indice);
            } catch (error) {
                mostrarAvisoEditorModulo(quill, 'Ocurrió un error al procesar la imagen pegada.');
            }

            const Delta = Quill.import('delta');
            return new Delta();
        }

        function seleccionarImagenTeoriaModulo(quill) {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = function () {
                const archivo = input.files[0];

                if (archivo) {
                    subirImagenTeoriaModulo(archivo, quill);
                }
            };
        }

        function obtenerCampoContenidoSeccionModulo(item) {
            if (!item) {
                return null;
            }

            return item.querySelector('.input-contenido-seccion-modulo');
        }

        function obtenerCampoContenidoTocadoSeccionModulo(item) {
            if (!item) {
                return null;
            }

            let campo = item.querySelector('.input-contenido-seccion-modulo-tocado');

            if (!campo) {
                campo = document.createElement('input');
                campo.type = 'hidden';
                campo.name = 'secciones_contenido_tocado[]';
                campo.value = '0';
                campo.className = 'input-contenido-seccion-modulo-tocado';
                item.appendChild(campo);
            }

            return campo;
        }

        function decodificarContenidoInicialSeccionModulo(valor) {
            if (!valor) {
                return '';
            }

            try {
                const textoBinario = atob(valor);
                const bytes = Uint8Array.from(textoBinario, function (caracter) {
                    return caracter.charCodeAt(0);
                });

                return new TextDecoder('utf-8').decode(bytes);
            } catch (error) {
                try {
                    return atob(valor);
                } catch (errorInterno) {
                    return '';
                }
            }
        }

        function normalizarHtmlContenidoSeccionModulo(html) {
            const contenido = String(html ?? '').trim();

            if (
                contenido === '' ||
                contenido === '<p><br></p>' ||
                contenido === '<p><br></p><p><br></p>'
            ) {
                return '';
            }

            return contenido;
        }

        function escapeHtmlSeccionModulo(valor) {
            const div = document.createElement('div');
            div.textContent = String(valor ?? '');
            return div.innerHTML;
        }

        function prepararHtmlInicialParaEditorSeccionModulo(contenido) {
            const contenidoLimpio = String(contenido ?? '').trim();

            if (contenidoLimpio === '') {
                return '';
            }

            const pareceHtml = /<\/?[a-z][\s\S]*>/i.test(contenidoLimpio);

            if (pareceHtml) {
                return contenidoLimpio;
            }

            return contenidoLimpio
                .split(/\r?\n/)
                .map(function (linea) {
                    return linea.trim();
                })
                .filter(function (linea) {
                    return linea !== '';
                })
                .map(function (linea) {
                    return '<p>' + escapeHtmlSeccionModulo(linea) + '</p>';
                })
                .join('');
        }

        function obtenerContenidoJsonSeccionModulo(item) {
            if (!item) {
                return '';
            }

            const scriptContenido = item.querySelector('.json-contenido-seccion-modulo');

            if (!scriptContenido) {
                return '';
            }

            try {
                return JSON.parse(scriptContenido.textContent || '""') || '';
            } catch (error) {
                return scriptContenido.textContent || '';
            }
        }

        function obtenerContenidoInicialSeccionModulo(editor, campoContenido) {
            const item = editor ? editor.closest('.seccion-modulo-item') : null;

            let contenidoInicial = obtenerContenidoJsonSeccionModulo(item);

            if (String(contenidoInicial).trim() === '' && campoContenido) {
                contenidoInicial = campoContenido.value || '';
            }

            if (String(contenidoInicial).trim() === '' && editor && editor.dataset && editor.dataset.contenidoInicial) {
                contenidoInicial = decodificarContenidoInicialSeccionModulo(editor.dataset.contenidoInicial);
            }

            if (campoContenido) {
                campoContenido.value = contenidoInicial || '';
            }

            return prepararHtmlInicialParaEditorSeccionModulo(contenidoInicial);
        }

        function marcarContenidoSeccionModuloTocado(editor) {
            const item = editor ? editor.closest('.seccion-modulo-item') : null;
            const campoTocado = obtenerCampoContenidoTocadoSeccionModulo(item);

            if (campoTocado) {
                campoTocado.value = '1';
            }
        }

        function obtenerQuillDeEditorSeccionModulo(editor) {
            if (!editor || !window.Quill) {
                return null;
            }

            if (editor.__quill) {
                return editor.__quill;
            }

            try {
                const quillEncontrado = Quill.find(editor);

                if (quillEncontrado && quillEncontrado.root) {
                    editor.__quill = quillEncontrado;
                    return quillEncontrado;
                }
            } catch (error) {
                return null;
            }

            return null;
        }

        function sincronizarEditorSeccionModulo(editor) {
            if (!editor) {
                return;
            }

            const item = editor.closest('.seccion-modulo-item');
            const campoContenido = obtenerCampoContenidoSeccionModulo(item);

            if (!campoContenido) {
                return;
            }

            const quill = obtenerQuillDeEditorSeccionModulo(editor);

            if (quill && quill.root) {
                campoContenido.value = normalizarHtmlContenidoSeccionModulo(quill.root.innerHTML);
                return;
            }

            const editorInterno = editor.querySelector('.ql-editor');

            if (editorInterno) {
                campoContenido.value = normalizarHtmlContenidoSeccionModulo(editorInterno.innerHTML);
            }
        }

        function cerrarPanelesAjusteImagenTeoriaModulo(panelPermitido = null) {
            document.querySelectorAll('.panel-ajuste-imagen-teoria.activo').forEach(function (panelAbierto) {
                if (panelPermitido && panelAbierto === panelPermitido) {
                    return;
                }

                panelAbierto.classList.remove('activo');
            });
        }

        function posicionarPanelAjusteImagenTeoriaModulo(panel, imagen) {
            if (!panel || !imagen) {
                return;
            }

            const rectImagen = imagen.getBoundingClientRect();

            panel.classList.add('activo');

            const anchoPanel = panel.offsetWidth || 520;
            const altoPanel = panel.offsetHeight || 120;
            const margen = 12;

            let top = rectImagen.bottom + margen;
            let left = rectImagen.left;

            if ((left + anchoPanel) > (window.innerWidth - margen)) {
                left = window.innerWidth - anchoPanel - margen;
            }

            if (left < margen) {
                left = margen;
            }

            if ((top + altoPanel) > (window.innerHeight - margen)) {
                top = rectImagen.top - altoPanel - margen;
            }

            if (top < margen) {
                top = margen;
            }

            panel.style.top = `${top}px`;
            panel.style.left = `${left}px`;
        }

        function mostrarPanelAjusteImagenTeoriaModulo(editor, quill, imagen) {
            if (!editor || !quill || !imagen) {
                return;
            }

            const panel = editor.__panelAjusteImagenTeoriaModulo;

            if (!panel) {
                return;
            }

            editor.__imagenSeleccionadaTeoriaModulo = imagen;

            cerrarPanelesAjusteImagenTeoriaModulo(panel);
            posicionarPanelAjusteImagenTeoriaModulo(panel, imagen);
        }

        function crearPanelAjusteImagenTeoriaModulo(editor, quill) {
            if (!editor || !quill) {
                return null;
            }

            if (editor.__panelAjusteImagenTeoriaModulo) {
                return editor.__panelAjusteImagenTeoriaModulo;
            }

            const panel = document.createElement('div');
            panel.className = 'panel-ajuste-imagen-teoria';
            panel.innerHTML = `
                <div class="panel-ajuste-imagen-teoria-titulo">
                    Ajustes de imagen
                </div>

                <div class="panel-ajuste-imagen-teoria-controles">
                    <button type="button" data-img-width="25%">Pequeña</button>
                    <button type="button" data-img-width="50%">Mediana</button>
                    <button type="button" data-img-width="75%">Grande</button>
                    <button type="button" data-img-width="100%">Completa</button>

                    <button type="button" data-img-align="left">Izquierda + texto</button>
                    <button type="button" data-img-align="center">Centrada</button>
                    <button type="button" data-img-align="right">Derecha + texto</button>
                    <button type="button" data-img-align="normal">Normal</button>

                    <button type="button" data-img-clear="1">Quitar ajustes</button>
                </div>

                <p class="panel-ajuste-imagen-teoria-ayuda">
                    Selecciona una imagen del contenido escrito para ajustar tamaño y posición.
                </p>
            `;

            document.body.appendChild(panel);
            editor.__panelAjusteImagenTeoriaModulo = panel;

            panel.addEventListener('mousedown', function (event) {
                event.preventDefault();
            });

            panel.querySelectorAll('[data-img-width]').forEach(function (boton) {
                boton.addEventListener('click', function () {
                    aplicarAjusteImagenTeoriaModulo(editor, quill, null, boton.dataset.imgWidth);

                    const imagen = obtenerImagenSeleccionadaTeoriaModulo(quill, true);

                    if (imagen) {
                        posicionarPanelAjusteImagenTeoriaModulo(panel, imagen);
                    }
                });
            });

            panel.querySelectorAll('[data-img-align]').forEach(function (boton) {
                boton.addEventListener('click', function () {
                    aplicarAjusteImagenTeoriaModulo(editor, quill, boton.dataset.imgAlign, null);

                    const imagen = obtenerImagenSeleccionadaTeoriaModulo(quill, true);

                    if (imagen) {
                        posicionarPanelAjusteImagenTeoriaModulo(panel, imagen);
                    }
                });
            });

            const botonLimpiar = panel.querySelector('[data-img-clear]');

            if (botonLimpiar) {
                botonLimpiar.addEventListener('click', function () {
                    limpiarAjusteImagenTeoriaModulo(editor, quill);

                    const imagen = obtenerImagenSeleccionadaTeoriaModulo(quill, true);

                    if (imagen) {
                        posicionarPanelAjusteImagenTeoriaModulo(panel, imagen);
                    }
                });
            }

            return panel;
        }

        function configurarEventosEditorSeccionModulo(editor, quill) {
            if (!editor || !quill || editor.dataset.eventosQuillSeccion === '1') {
                return;
            }

            editor.dataset.eventosQuillSeccion = '1';

            const panelAjusteImagen = crearPanelAjusteImagenTeoriaModulo(editor, quill);

            quill.root.addEventListener('mouseover', function (event) {
                if (event.target && event.target.tagName === 'IMG') {
                    mostrarPanelAjusteImagenTeoriaModulo(editor, quill, event.target);
                }
            });

            quill.root.addEventListener('click', function (event) {
                if (event.target && event.target.tagName === 'IMG') {
                    mostrarPanelAjusteImagenTeoriaModulo(editor, quill, event.target);
                    return;
                }

                if (panelAjusteImagen && !panelAjusteImagen.contains(event.target)) {
                    panelAjusteImagen.classList.remove('activo');
                }
            });

            document.addEventListener('mousedown', function (event) {
                if (!panelAjusteImagen || !panelAjusteImagen.classList.contains('activo')) {
                    return;
                }

                if (panelAjusteImagen.contains(event.target)) {
                    return;
                }

                if (quill.root.contains(event.target) && event.target.tagName === 'IMG') {
                    return;
                }

                panelAjusteImagen.classList.remove('activo');
            });

            window.addEventListener('resize', function () {
                const imagen = obtenerImagenSeleccionadaTeoriaModulo(quill, true);

                if (panelAjusteImagen && panelAjusteImagen.classList.contains('activo') && imagen) {
                    posicionarPanelAjusteImagenTeoriaModulo(panelAjusteImagen, imagen);
                }
            });

            quill.on('text-change', function (delta, oldDelta, source) {
                if (source === 'user') {
                    marcarContenidoSeccionModuloTocado(editor);
                }

                sincronizarEditorSeccionModulo(editor);
            });

            quill.on('selection-change', function () {
                sincronizarEditorSeccionModulo(editor);
            });

            quill.root.addEventListener('keydown', function () {
                marcarContenidoSeccionModuloTocado(editor);
            });

            quill.root.addEventListener('input', function () {
                marcarContenidoSeccionModuloTocado(editor);
                sincronizarEditorSeccionModulo(editor);
            });

            quill.root.addEventListener('cut', function () {
                marcarContenidoSeccionModuloTocado(editor);

                setTimeout(function () {
                    sincronizarEditorSeccionModulo(editor);
                }, 100);
            });

            quill.root.addEventListener('paste', function () {
                marcarContenidoSeccionModuloTocado(editor);

                setTimeout(function () {
                    sincronizarEditorSeccionModulo(editor);
                }, 100);
            });

            quill.clipboard.addMatcher('img', function (node, delta) {
                const deltaSinBase64 = interceptarImagenBase64TeoriaModulo(node, quill);
                return deltaSinBase64 || delta;
            });

            quill.root.addEventListener('drop', function (event) {
                if (!event.dataTransfer || !event.dataTransfer.files || !event.dataTransfer.files.length) {
                    return;
                }

                const archivo = Array.from(event.dataTransfer.files).find(function (item) {
                    return item.type && item.type.indexOf('image/') === 0;
                });

                if (!archivo) {
                    return;
                }

                event.preventDefault();
                marcarContenidoSeccionModuloTocado(editor);

                const rango = quill.getSelection(true);
                const indice = rango ? rango.index : quill.getLength();

                subirBlobImagenTeoriaModulo(archivo, quill, indice);
            });

            const toolbar = quill.getModule('toolbar');

            if (!toolbar) {
                return;
            }

            toolbar.addHandler('image', function () {
                marcarContenidoSeccionModuloTocado(editor);
                seleccionarImagenTeoriaModulo(quill);

                setTimeout(function () {
                    sincronizarEditorSeccionModulo(editor);
                }, 300);
            });

            toolbar.addHandler('imageLeft', function () {
                aplicarAjusteImagenTeoriaModulo(editor, quill, 'left', null);
            });

            toolbar.addHandler('imageInline', function () {
                aplicarAjusteImagenTeoriaModulo(editor, quill, 'center', null);
            });

            toolbar.addHandler('imageRight', function () {
                aplicarAjusteImagenTeoriaModulo(editor, quill, 'right', null);
            });
        }

        function inicializarUnEditorSeccionModulo(editor) {
            if (!editor) {
                return;
            }

            const quillExistente = obtenerQuillDeEditorSeccionModulo(editor);

            if (quillExistente) {
                editor.dataset.quillInicializado = '1';
                quillExistente.enable(true);
                configurarEventosEditorSeccionModulo(editor, quillExistente);
                sincronizarEditorSeccionModulo(editor);
                return;
            }

            if (editor.dataset.quillInicializado === '1') {
                sincronizarEditorSeccionModulo(editor);
                return;
            }

            const item = editor.closest('.seccion-modulo-item');
            const campoContenido = obtenerCampoContenidoSeccionModulo(item);
            const campoTocado = obtenerCampoContenidoTocadoSeccionModulo(item);
            const contenidoInicial = obtenerContenidoInicialSeccionModulo(editor, campoContenido);

            editor.innerHTML = '';

            const quill = new Quill(editor, {
                theme: 'snow',
                modules: obtenerModulosQuillTeoria()
            });

            editor.__quill = quill;
            editor.dataset.quillInicializado = '1';

            quill.enable(true);
            configurarEventosEditorSeccionModulo(editor, quill);

            if (contenidoInicial !== '') {
                try {
                    quill.clipboard.dangerouslyPasteHTML(0, contenidoInicial, 'silent');
                } catch (error) {
                    quill.root.innerHTML = contenidoInicial;
                }

                if (normalizarHtmlContenidoSeccionModulo(quill.root.innerHTML) === '' || quill.getText().trim() === '') {
                    quill.root.innerHTML = contenidoInicial;
                }

                quill.update('silent');

                setTimeout(function () {
                    const htmlActual = normalizarHtmlContenidoSeccionModulo(quill.root.innerHTML);

                    if ((htmlActual === '' || quill.getText().trim() === '') && contenidoInicial !== '') {
                        quill.root.innerHTML = contenidoInicial;
                        quill.update('silent');
                    }

                    sincronizarEditorSeccionModulo(editor);

                    if (campoTocado) {
                        campoTocado.value = '0';
                    }
                }, 50);
            }

            sincronizarEditorSeccionModulo(editor);

            if (campoTocado) {
                campoTocado.value = '0';
            }
        }

        function inicializarEditoresSeccionesModulo() {
            document.querySelectorAll('.editor-contenido-seccion-modulo').forEach(function (editor) {
                try {
                    inicializarUnEditorSeccionModulo(editor);
                } catch (error) {
                    console.error('No se pudo inicializar una caja de contenido escrito:', error, editor);
                }
            });
        }

        function sincronizarEditoresSeccionesModulo() {
            document.querySelectorAll('.editor-contenido-seccion-modulo').forEach(function (editor) {
                sincronizarEditorSeccionModulo(editor);
            });

            return true;
        }

        function obtenerOpcionesPadreDesdePantalla(idPadreSeleccionado = '') {
            let opciones = '<option value="">Seleccionar sección principal</option>';

            document.querySelectorAll('.seccion-modulo-item').forEach(function (item) {
                const selectNivel = item.querySelector('select[name="secciones_nivel[]"]');
                const inputTitulo = item.querySelector('input[name="secciones_titulo[]"]');
                const inputId = item.querySelector('input[name="secciones_id[]"]');

                if (!selectNivel || !inputTitulo || !inputId) {
                    return;
                }

                if (selectNivel.value !== '1') {
                    return;
                }

                const titulo = inputTitulo.value.trim();

                if (titulo === '') {
                    return;
                }

                const id = inputId.value.trim();

                if (id !== '') {
                    const selected = id === String(idPadreSeleccionado) ? ' selected' : '';
                    opciones += '<option value="' + escapeHtmlSeccionModulo(id) + '"' + selected + '>' + escapeHtmlSeccionModulo(titulo) + '</option>';
                }
            });

            return opciones;
        }

        function agregarSeccionModulo(contenedorId) {
            const contenedor = document.getElementById(contenedorId);

            if (!contenedor) {
                return;
            }

            const html = `
                <div class="seccion-modulo-item rounded border border-gray-300 bg-white p-4"
                    data-bloque-pagina-seccion>
                    <input type="hidden"
                        name="secciones_id[]"
                        value="">

                    <div class="flex items-center justify-between mb-3 gap-2">
                        <strong>Página / sección</strong>

                        <div class="flex flex-wrap gap-2 justify-end">
                            <button type="button"
                                    @click="agregarSubseccionASeccion($el)"
                                    class="px-2 py-1 bg-emerald-600 text-white rounded text-xs boton-agregar-subseccion">
                                + Subsección
                            </button>

                            <button type="button"
                                    @click="eliminarSeccionModulo($el)"
                                    class="px-2 py-1 bg-red-600 text-white rounded text-xs">
                                Quitar
                            </button>
                        </div>
                    </div>

                    <label class="block text-sm font-medium">Título de la página</label>
                    <input type="text"
                        name="secciones_titulo[]"
                        placeholder="Ejemplo: Conceptos básicos"
                        class="w-full border rounded px-3 py-2 text-black mb-3">

                    <label class="block text-sm font-medium">Tipo de sección</label>
                    <select name="secciones_nivel[]"
                            class="w-full border rounded px-3 py-2 text-black mb-3 tipo-seccion-modulo"
                            @change="actualizarSelectorPadreSeccion($el)">
                        <option value="1">Sección principal</option>
                        <option value="2">Subsección</option>
                    </select>

                    <div class="mt-2 campo-padre-seccion hidden">
                        <label class="block text-sm font-medium">Subsección de</label>

                        <select name="secciones_padre[]"
                                class="w-full border rounded px-3 py-2 text-black mb-3">
                            ${obtenerOpcionesPadreDesdePantalla()}
                        </select>

                        <p class="text-xs text-gray-500 mt-1">
                            Selecciona debajo de qué sección principal aparecerá esta subsección.
                        </p>
                    </div>

                    <div class="acciones-contenido-subseccion mt-3 rounded border border-emerald-300 bg-emerald-50 p-3"
                        data-url-recurso-base="${urlCrearRecursoModulo}"
                        data-url-ejercicios-base="${urlEjerciciosModulo}"
                        data-url-evaluaciones-base="${urlEvaluacionesModulo}">

                        <p class="text-sm font-semibold text-emerald-900">
                            Contenido de esta sección
                        </p>

                        <p class="text-xs text-emerald-800 mt-1">
                            Escribí el título y luego agregá recursos, ejercicios o evaluaciones. Si todavía no está guardada, el sistema la guardará automáticamente.
                        </p>

                        <div class="aviso-subseccion-sin-guardar mt-3 rounded border border-yellow-300 bg-yellow-100 px-3 py-2 text-xs text-yellow-800">
                            Esta sección todavía no está guardada. Al seleccionar una acción, se guardará automáticamente.
                        </div>

                        <div class="botones-subseccion-contenido mt-3 flex flex-wrap gap-2">
                            <a data-accion-subseccion="recurso"
                            href="#"
                            class="px-3 py-2 bg-blue-600 text-white rounded text-xs">
                                Seleccionar archivo
                            </a>

                            <a data-accion-subseccion="ejercicio"
                            href="#"
                            class="px-3 py-2 bg-emerald-600 text-white rounded text-xs">
                                Agregar ejercicio
                            </a>

                            <a data-accion-subseccion="evaluacion"
                            href="#"
                            class="px-3 py-2 bg-purple-600 text-white rounded text-xs">
                                Agregar evaluación
                            </a>
                        </div>
                    </div>

                    <label class="block text-sm font-medium mb-1 mt-3">Contenido escrito</label>

                    <input type="hidden"
                        name="secciones_contenido[]"
                        class="input-contenido-seccion-modulo">

                    <div class="editor-contenido-seccion-modulo bg-white text-black rounded border border-gray-300"
                        style="min-height: 260px;"></div>
                </div>
            `;

            contenedor.insertAdjacentHTML('beforeend', html);
            inicializarEditoresSeccionesModulo();

            const nuevaSeccion = contenedor.lastElementChild;
            const selectorTipo = nuevaSeccion ? nuevaSeccion.querySelector('.tipo-seccion-modulo') : null;

            if (selectorTipo) {
                actualizarSelectorPadreSeccion(selectorTipo);
            }
        }

                function obtenerHijosDirectosSeccionesModulo() {
            const contenedor = document.getElementById('contenedorSeccionesModulo');

            if (!contenedor) {
                return [];
            }

            return Array.from(contenedor.children).filter(function (elemento) {
                return elemento.classList.contains('seccion-modulo-item');
            });
        }

        function obtenerReferenciaInsercionSubseccion(idPadre, bloquePadre) {
            const bloques = obtenerHijosDirectosSeccionesModulo();
            const indicePadre = bloques.indexOf(bloquePadre);

            if (indicePadre === -1) {
                return null;
            }

            for (let i = indicePadre + 1; i < bloques.length; i++) {
                const bloque = bloques[i];
                const selectNivel = bloque.querySelector(':scope > select[name="secciones_nivel[]"], select[name="secciones_nivel[]"]');
                const selectPadre = bloque.querySelector(':scope select[name="secciones_padre[]"]');

                if (!selectNivel) {
                    continue;
                }

                if (selectNivel.value === '1') {
                    return bloque;
                }

                if (selectPadre && selectPadre.value !== String(idPadre)) {
                    return bloque;
                }
            }

            return null;
        }

        function insertarSubseccionDespuesDeSuSeccionPadre(idPadre, bloquePadre, html) {
            const contenedor = document.getElementById('contenedorSeccionesModulo');

            if (!contenedor) {
                return null;
            }

            const template = document.createElement('template');
            template.innerHTML = html.trim();

            const nuevaSubseccion = template.content.firstElementChild;

            if (!nuevaSubseccion) {
                return null;
            }

            const referencia = obtenerReferenciaInsercionSubseccion(idPadre, bloquePadre);

            contenedor.insertBefore(nuevaSubseccion, referencia);

            return nuevaSubseccion;
        }

        function obtenerBloquesDirectosSeccionesModulo() {
            const contenedor = document.getElementById('contenedorSeccionesModulo');

            if (!contenedor) {
                return [];
            }

            return Array.from(contenedor.children).filter(function (elemento) {
                return elemento.classList.contains('seccion-modulo-item');
            });
        }

        function obtenerNivelDirectoSeccionModulo(bloque) {
            const selectNivel = bloque.querySelector('select[name="secciones_nivel[]"]');

            if (!selectNivel) {
                return '1';
            }

            return selectNivel.value;
        }

        function obtenerReferenciaParaNuevaSubseccion(bloquePadre) {
            const bloques = obtenerBloquesDirectosSeccionesModulo();
            const indicePadre = bloques.indexOf(bloquePadre);

            if (indicePadre === -1) {
                return null;
            }

            for (let i = indicePadre + 1; i < bloques.length; i++) {
                const bloqueActual = bloques[i];

                if (obtenerNivelDirectoSeccionModulo(bloqueActual) === '1') {
                    return bloqueActual;
                }
            }

            return null;
        }

        function insertarSubseccionEnLugarCorrectoModulo(bloquePadre, html) {
            const contenedor = document.getElementById('contenedorSeccionesModulo');

            if (!contenedor) {
                return null;
            }

            const template = document.createElement('template');
            template.innerHTML = html.trim();

            const nuevaSubseccion = template.content.firstElementChild;

            if (!nuevaSubseccion) {
                return null;
            }

            const referencia = obtenerReferenciaParaNuevaSubseccion(bloquePadre);

            contenedor.insertBefore(nuevaSubseccion, referencia);

            return nuevaSubseccion;
        }

        async function agregarSubseccionASeccion(boton) {
            const bloquePadre = boton.closest('.seccion-modulo-item');

            if (!bloquePadre) {
                return;
            }

            const inputIdPadre = bloquePadre.querySelector('input[name="secciones_id[]"]');
            const inputTituloPadre = bloquePadre.querySelector('input[name="secciones_titulo[]"]');

            if (!inputIdPadre || inputIdPadre.value.trim() === '') {
                const dataPadre = await guardarSeccionRapidaDesdeBloque(bloquePadre);

                if (!dataPadre || !dataPadre.id_seccion) {
                    return;
                }
            }

            const idPadre = inputIdPadre.value.trim();

            const tituloPadre = inputTituloPadre && inputTituloPadre.value.trim() !== ''
                ? inputTituloPadre.value.trim()
                : 'Sección principal';

            const html = `
                <div class="seccion-modulo-item rounded border border-emerald-300 bg-emerald-50 p-4 ml-6"
                    data-bloque-pagina-seccion>
                    <input type="hidden"
                        name="secciones_id[]"
                        value="">

                    <div class="flex items-center justify-between mb-3 gap-2">
                        <strong>Subsección de: ${escapeHtmlSeccionModulo(tituloPadre)}</strong>

                        <div class="flex flex-wrap gap-2 justify-end">
                            <button type="button"
                                    @click="agregarSubseccionASeccion($el)"
                                    class="px-2 py-1 bg-emerald-600 text-white rounded text-xs boton-agregar-subseccion hidden">
                                + Subsección
                            </button>

                            <button type="button"
                                    @click="eliminarSeccionModulo($el)"
                                    class="px-2 py-1 bg-red-600 text-white rounded text-xs">
                                Quitar
                            </button>
                        </div>
                    </div>

                    <label class="block text-sm font-medium">Título de la subsección</label>
                    <input type="text"
                        name="secciones_titulo[]"
                        placeholder="Ejemplo: Partes de un montacargas"
                        class="w-full border rounded px-3 py-2 text-black mb-3">

                    <label class="block text-sm font-medium">Tipo de sección</label>
                    <select name="secciones_nivel[]"
                            class="w-full border rounded px-3 py-2 text-black mb-3 tipo-seccion-modulo"
                            @change="actualizarSelectorPadreSeccion($el)">
                        <option value="1">Sección principal</option>
                        <option value="2" selected>Subsección</option>
                    </select>

                    <div class="mt-2 campo-padre-seccion">
                        <label class="block text-sm font-medium">Subsección de</label>

                        <select name="secciones_padre[]"
                                class="w-full border rounded px-3 py-2 text-black mb-3">
                            ${obtenerOpcionesPadreDesdePantalla(idPadre)}
                        </select>

                        <p class="text-xs text-gray-500 mt-1">
                            Esta nueva subsección quedará debajo de la sección principal seleccionada.
                        </p>
                    </div>

                    <div class="acciones-contenido-subseccion mt-3 rounded border border-emerald-300 bg-emerald-50 p-3"
                        data-url-recurso-base="${urlCrearRecursoModulo}"
                        data-url-ejercicios-base="${urlEjerciciosModulo}"
                        data-url-evaluaciones-base="${urlEvaluacionesModulo}">

                        <p class="text-sm font-semibold text-emerald-900">
                            Contenido de esta sección
                        </p>

                        <p class="text-xs text-emerald-800 mt-1">
                            Escribí el título y luego agregá recursos, ejercicios o evaluaciones. Si todavía no está guardada, el sistema la guardará automáticamente.
                        </p>

                        <div class="aviso-subseccion-sin-guardar mt-3 rounded border border-yellow-300 bg-yellow-100 px-3 py-2 text-xs text-yellow-800">
                            Esta subsección todavía no está guardada. Al seleccionar una acción, se guardará automáticamente.
                        </div>

                        <div class="botones-subseccion-contenido mt-3 flex flex-wrap gap-2">
                            <a data-accion-subseccion="recurso"
                            href="#"
                            class="px-3 py-2 bg-blue-600 text-white rounded text-xs">
                                Seleccionar archivo
                            </a>

                            <a data-accion-subseccion="ejercicio"
                            href="#"
                            class="px-3 py-2 bg-emerald-600 text-white rounded text-xs">
                                Agregar ejercicio
                            </a>

                            <a data-accion-subseccion="evaluacion"
                            href="#"
                            class="px-3 py-2 bg-purple-600 text-white rounded text-xs">
                                Agregar evaluación
                            </a>
                        </div>
                    </div>

                    <label class="block text-sm font-medium mb-1 mt-3">Contenido escrito</label>

                    <input type="hidden"
                        name="secciones_contenido[]"
                        class="input-contenido-seccion-modulo">

                    <div class="editor-contenido-seccion-modulo bg-white text-black rounded border border-gray-300"
                        style="min-height: 260px;"></div>
                </div>
            `;

            const nuevaSubseccion = insertarSubseccionDespuesDeSuSeccionPadre(idPadre, bloquePadre, html);

            if (!nuevaSubseccion) {
                mostrarAvisoLocalModulo(bloquePadre, 'No se pudo agregar la subsección.');
                return;
            }

            inicializarEditoresSeccionesModulo();

            const selectorTipo = nuevaSubseccion.querySelector('.tipo-seccion-modulo');

            if (selectorTipo) {
                actualizarSelectorPadreSeccion(selectorTipo);
            }
        }
                function obtenerIdSeccionDesdeBloque(bloque) {
            const inputId = bloque.querySelector('input[name="secciones_id[]"]');
            return inputId ? inputId.value.trim() : '';
        }

        function obtenerNivelSeccionDesdeBloque(bloque) {
            const selectNivel = bloque.querySelector('select[name="secciones_nivel[]"]');
            return selectNivel ? selectNivel.value : '1';
        }

        function obtenerPadreSeccionDesdeBloque(bloque) {
            const selectPadre = bloque.querySelector('select[name="secciones_padre[]"]');
            return selectPadre ? selectPadre.value.trim() : '';
        }

        function obtenerBloquesHijosSeccion(bloquePadre) {
            const idPadre = obtenerIdSeccionDesdeBloque(bloquePadre);
            const hijos = [];

            if (idPadre !== '') {
                document.querySelectorAll('#contenedorSeccionesModulo .seccion-modulo-item').forEach(function (bloque) {
                    if (bloque === bloquePadre) {
                        return;
                    }

                    if (obtenerPadreSeccionDesdeBloque(bloque) === idPadre) {
                        hijos.push(bloque);
                    }
                });

                return hijos;
            }

            let siguiente = bloquePadre.nextElementSibling;

            while (siguiente && siguiente.classList.contains('seccion-modulo-item')) {
                if (obtenerNivelSeccionDesdeBloque(siguiente) === '1') {
                    break;
                }

                hijos.push(siguiente);
                siguiente = siguiente.nextElementSibling;
            }

            return hijos;
        }

        function eliminarSeccionModulo(boton) {
            const item = boton.closest('.seccion-modulo-item');

            if (!item) {
                return;
            }

            const hijos = obtenerBloquesHijosSeccion(item);
            const cantidadTotal = hijos.length + 1;

            const confirmar = confirm(
                cantidadTotal > 1
                    ? 'Vas a quitar esta sección y sus subsecciones. Al actualizar el módulo también se eliminarán sus recursos, ejercicios y evaluaciones. ¿Deseas continuar?'
                    : 'Vas a quitar esta sección. Al actualizar el módulo también se eliminarán sus recursos, ejercicios y evaluaciones. ¿Deseas continuar?'
            );

            if (!confirmar) {
                return;
            }

            hijos.forEach(function (hijo) {
                hijo.remove();
            });

            item.remove();
        }

        function obtenerDatosSeccionParaGuardado(bloque) {
            sincronizarEditoresSeccionesModulo();

            const inputId = bloque.querySelector('input[name="secciones_id[]"]');
            const inputTitulo = bloque.querySelector('input[name="secciones_titulo[]"]');
            const inputContenido = bloque.querySelector('.input-contenido-seccion-modulo');
            const selectNivel = bloque.querySelector('select[name="secciones_nivel[]"]');
            const selectPadre = bloque.querySelector('select[name="secciones_padre[]"]');

            const titulo = inputTitulo ? inputTitulo.value.trim() : '';

            if (titulo === '') {
                mostrarAvisoLocalModulo(inputTitulo, 'Primero escribe el título de la sección.');
                if (inputTitulo) {
                    inputTitulo.focus();
                }

                return null;
            }

            return {
                id_seccion: inputId && inputId.value.trim() !== '' ? inputId.value.trim() : null,
                titulo: titulo,
                contenido: inputContenido ? inputContenido.value : '',
                nivel: selectNivel ? selectNivel.value : '1',
                id_seccion_padre: selectPadre && selectPadre.value.trim() !== '' ? selectPadre.value.trim() : null,
            };
        }

        async function guardarSeccionRapidaDesdeBloque(bloque) {
            const datos = obtenerDatosSeccionParaGuardado(bloque);

            if (!datos) {
                return null;
            }

            const respuesta = await fetch(urlGuardarSeccionRapida, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': tokenCsrfTeoriaModulo,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(datos),
            });

            if (!respuesta.ok) {
                mostrarAvisoLocalModulo(bloque, 'No se pudo guardar la sección. Revisa el título o intentá nuevamente.');
                return null;
            }

            const data = await respuesta.json();

            const inputId = bloque.querySelector('input[name="secciones_id[]"]');

            if (inputId && data.id_seccion) {
                inputId.value = data.id_seccion;
            }

            actualizarPanelContenidoConSeccionGuardada(bloque, data);

            return data;
        }

        function actualizarPanelContenidoConSeccionGuardada(bloque, data) {
            const panel = bloque.querySelector('.acciones-contenido-subseccion');

            if (!panel || !data || !data.id_seccion) {
                return;
            }

            const aviso = panel.querySelector('.aviso-subseccion-sin-guardar');

            if (aviso) {
                aviso.classList.add('hidden');
            }

            const botones = panel.querySelector('.botones-subseccion-contenido');

            if (botones) {
                botones.classList.remove('hidden');
            }

            const enlaceRecurso = panel.querySelector('[data-accion-subseccion="recurso"]');
            const enlaceEjercicio = panel.querySelector('[data-accion-subseccion="ejercicio"]');
            const enlaceEvaluacion = panel.querySelector('[data-accion-subseccion="evaluacion"]');

            if (enlaceRecurso && data.urls?.recurso) {
                enlaceRecurso.href = data.urls.recurso;
            }

            if (enlaceEjercicio && data.urls?.ejercicio) {
                enlaceEjercicio.href = data.urls.ejercicio;
            }

            if (enlaceEvaluacion && data.urls?.evaluacion) {
                enlaceEvaluacion.href = data.urls.evaluacion;
            }
        }

        async function manejarAccionContenidoSeccion(event, enlace) {
            event.preventDefault();

            const bloque = enlace.closest('.seccion-modulo-item');

            if (!bloque) {
                return;
            }

            const tipoAccion = enlace.dataset.accionSubseccion;

            const data = await guardarSeccionRapidaDesdeBloque(bloque);

            if (!data || !data.urls || !data.urls[tipoAccion]) {
                return;
            }

            window.location.href = data.urls[tipoAccion];
        }

        function construirUrlConSubseccion(urlBase, idSubseccion) {
            const separador = urlBase.includes('?') ? '&' : '?';

            return urlBase + separador + 'id_capacitacion_modulo_seccion=' + encodeURIComponent(idSubseccion);
        }

        function actualizarAccionesContenidoSubseccion(bloque, nivelSeleccionado) {
            const panel = bloque.querySelector('.acciones-contenido-subseccion');

            if (!panel) {
                return;
            }

            panel.classList.remove('hidden');

            const inputIdSeccion = bloque.querySelector('input[name="secciones_id[]"]');
            const idSeccion = inputIdSeccion ? inputIdSeccion.value.trim() : '';

            const avisoSinGuardar = panel.querySelector('.aviso-subseccion-sin-guardar');
            const botones = panel.querySelector('.botones-subseccion-contenido');

            if (avisoSinGuardar) {
                avisoSinGuardar.classList.toggle('hidden', idSeccion !== '');
            }

            if (botones) {
                botones.classList.remove('hidden');
            }

            const enlaceRecurso = panel.querySelector('[data-accion-subseccion="recurso"]');
            const enlaceEjercicio = panel.querySelector('[data-accion-subseccion="ejercicio"]');
            const enlaceEvaluacion = panel.querySelector('[data-accion-subseccion="evaluacion"]');

            if (idSeccion !== '') {
                if (enlaceRecurso) {
                    enlaceRecurso.href = construirUrlConSubseccion(panel.dataset.urlRecursoBase, idSeccion);
                }

                if (enlaceEjercicio) {
                    enlaceEjercicio.href = construirUrlConSubseccion(panel.dataset.urlEjerciciosBase, idSeccion);
                }

                if (enlaceEvaluacion) {
                    enlaceEvaluacion.href = construirUrlConSubseccion(panel.dataset.urlEvaluacionesBase, idSeccion);
                }
            } else {
                if (enlaceRecurso) {
                    enlaceRecurso.href = '#';
                }

                if (enlaceEjercicio) {
                    enlaceEjercicio.href = '#';
                }

                if (enlaceEvaluacion) {
                    enlaceEvaluacion.href = '#';
                }
            }
        }

        function actualizarSelectorPadreSeccion(select) {
            const bloque = select.closest('[data-bloque-pagina-seccion]');

            if (!bloque) {
                return;
            }

            const campoPadre = bloque.querySelector('.campo-padre-seccion');

            if (!campoPadre) {
                return;
            }

            const botonSubseccion = bloque.querySelector('.boton-agregar-subseccion');

            if (botonSubseccion) {
                botonSubseccion.classList.toggle('hidden', select.value !== '1');
            }

            if (select.value === '2') {
                campoPadre.classList.remove('hidden');
            } else {
                campoPadre.classList.add('hidden');

                const selectorPadre = campoPadre.querySelector('select');

                if (selectorPadre) {
                    selectorPadre.value = '';
                }
            }

            actualizarAccionesContenidoSubseccion(bloque, select.value);
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.addEventListener('submit', function () {
                sincronizarEditoresSeccionesModulo();
            }, true);

            try {
                inicializarEditoresSeccionesModulo();
            } catch (error) {
                console.error('Error al inicializar los editores de teoría del módulo:', error);
            }

            document.querySelectorAll('.tipo-seccion-modulo').forEach(function (select) {
                actualizarSelectorPadreSeccion(select);
            });

            document.addEventListener('click', function (event) {
                const enlace = event.target.closest('[data-accion-subseccion]');

                if (!enlace) {
                    return;
                }

                manejarAccionContenidoSeccion(event, enlace);
            });
        });
    </script>
</x-app-layout>