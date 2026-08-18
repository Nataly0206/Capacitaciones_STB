<x-app-layout>

    @php
        $totalModulos = $capacitacion->modulos->count();
        $totalRecursos = $capacitacion->modulos->sum(fn($modulo) => $modulo->recursos->count());
        $totalEvaluaciones = $capacitacion->modulos->sum(fn($modulo) => $modulo->evaluaciones->count());
        $totalPreguntas = $capacitacion->modulos->sum(fn($modulo) => $modulo->evaluaciones->sum(fn($evaluacion) => $evaluacion->preguntas->count()));
        $totalEjercicios = $capacitacion->modulos->sum(fn($modulo) => $modulo->ejercicios->count());
        $totalPreguntasEjercicio = $capacitacion->modulos->sum(fn($modulo) => $modulo->ejercicios->sum(fn($ejercicio) => $ejercicio->preguntas->count()));
    @endphp

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="rounded border border-green-300 bg-green-100 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded border border-red-300 bg-red-100 px-4 py-3 text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="esf-page-card overflow-hidden">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                                Capacitación
                            </p>

                            <h3 class="mt-1 text-3xl font-black text-slate-900 dark:text-slate-100 leading-tight">
                                {{ $capacitacion->capacitacion }}
                            </h3>

                            <p class="mt-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
                                Código:
                                <span class="font-black text-slate-800 dark:text-slate-100">
                                    {{ $capacitacion->codigo ?: 'Sin código' }}
                                </span>
                            </p>

                            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400 max-w-3xl">
                                {{ $capacitacion->descripcion ?: 'Sin descripción registrada.' }}
                            </p>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="esf-badge esf-badge-blue">
                                    Aprobación: {{ $capacitacion->porcentaje_aprobacion }}%
                                </span>

                                <span class="esf-badge esf-badge-purple">
                                    Vigencia: {{ $capacitacion->dias_vigencia ?? 0 }} días
                                </span>

                                @if((int) $capacitacion->estado === 1)
                                    <span class="esf-badge esf-badge-green">
                                        Activa
                                    </span>
                                @else
                                    <span class="esf-badge esf-badge-red">
                                        Inactiva
                                    </span>
                                @endif
                            </div>

                            <div class="mt-5 flex flex-wrap gap-2">
                                <a href="{{ route('capacitaciones.edit', $capacitacion->id_capacitacion) }}?origen=builder"
                                class="esf-action-btn esf-action-edit">
                                    Editar datos
                                </a>

                                <a href="{{ route('capacitaciones.index') }}"
                                class="esf-action-btn esf-action-status">
                                    Volver
                                </a>
                            </div>
                        </div>

                        <div class="esf-kpi-card esf-kpi-blue min-w-full sm:min-w-[120px] lg:min-w-[120px]">
                            <p class="text-xs uppercase tracking-[0.16em] font-black text-blue-700 dark:text-blue-300">
                                Módulos
                            </p>

                            <p class="mt-3 text-5xl font-black text-slate-900 dark:text-slate-100">
                                {{ $totalModulos }}
                            </p>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                Total de módulos creados
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="esf-page-card overflow-hidden">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                                Organización del contenido
                            </p>

                            <h3 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">
                                Módulos de la capacitación
                            </h3>

                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-2xl">
                                Busca módulos, abre o cierra el listado y administra el contenido de cada módulo.
                            </p>
                        </div>

                        <div class="flex flex-col md:flex-row gap-2">
                            <input type="text"
                                   id="buscarModulo"
                                   placeholder="Buscar módulo..."
                                   class="esf-form-input md:w-64">

                            <button type="button"
                                    @click="abrirTodos()"
                                    class="esf-action-btn esf-action-status">
                                Abrir todo
                            </button>

                            <button type="button"
                                    @click="cerrarTodos()"
                                    class="esf-action-btn esf-action-status">
                                Cerrar todo
                            </button>

                            <button type="button"
                                    @click="abrirModal('modalCrearModulo')"
                                    class="esf-btn esf-btn-primary text-center">
                                + Nuevo módulo
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="contenedorModulos" class="space-y-4">
                @forelse($capacitacion->modulos as $modulo)
                    <details class="modulo-card esf-page-card overflow-hidden transition hover:-translate-y-1 hover:shadow-xl" open>
                        <summary class="cursor-pointer px-6 py-5 bg-blue-50/70 dark:bg-slate-900/80 border-b border-slate-200/80 dark:border-slate-700/80">
                            <div class="inline-flex w-full flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.16em] font-black text-slate-400 dark:text-slate-500">
                                        Módulo {{ $modulo->orden }}
                                    </p>

                                    <p class="modulo-titulo mt-1 text-xl font-black text-slate-900 dark:text-slate-100">
                                        {{ $modulo->titulo }}
                                    </p>

                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        {{ $modulo->recursos->count() }} recurso(s) ·
                                        {{ $modulo->ejercicios->count() }} ejercicio(s) ·
                                        {{ $modulo->evaluaciones->count() }} evaluación(es)
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    @if((int) $modulo->estado === 1)
                                        <span class="esf-badge esf-badge-green">
                                            Activo
                                        </span>
                                    @else
                                        <span class="esf-badge esf-badge-red">
                                            Inactivo
                                        </span>
                                    @endif

                                    @if((int) $modulo->requiere_evaluacion === 1)
                                        <span class="esf-badge esf-badge-purple">
                                            Requiere evaluación
                                        </span>
                                    @else
                                        <span class="esf-badge esf-badge-slate">
                                            Sin evaluación obligatoria
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </summary>

                        <div class="p-6 space-y-5">
                            <div class="rounded-3xl border border-slate-200/90 dark:border-slate-700/90 bg-white/75 dark:bg-slate-900/55 p-5">
                                <div class="esf-module-description-layout">
                                    <div>
                                        @php
                                            $descripcionModuloPreview = trim((string) ($modulo->descripcion ?? ''));
                                            $objetivoModuloPreview = trim((string) ($modulo->objetivo ?? ''));
                                        @endphp

                                        @if($descripcionModuloPreview !== '' || $objetivoModuloPreview !== '')
                                            <h4 class="text-lg font-black text-slate-900 dark:text-slate-100">
                                                Descripción del módulo
                                            </h4>

                                            @if($descripcionModuloPreview !== '')
                                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                                    {{ $descripcionModuloPreview }}
                                                </p>
                                            @endif

                                            @if($objetivoModuloPreview !== '')
                                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                                                    <strong>Objetivo:</strong>
                                                    {{ $objetivoModuloPreview }}
                                                </p>
                                            @endif
                                        @endif

                                        <p class="text-sm text-gray-600 dark:text-gray-300 {{ ($descripcionModuloPreview !== '' || $objetivoModuloPreview !== '') ? 'mt-2' : 'mt-0' }}">
                                            <strong>Páginas de teoría:</strong>
                                            {{ $modulo->secciones->count() }} página(s)
                                        </p>

                                        <div class="esf-module-actions-bar">
                                            <a href="{{ route('capacitacion_modulos.edit', $modulo->id_capacitacion_modulo) }}?origen=builder"
                                            class="esf-action-btn esf-action-edit">
                                                Editar módulo
                                            </a>

                                            <form method="POST"
                                                action="{{ route('capacitacion_modulos.destroy', $modulo->id_capacitacion_modulo) }}"
                                                @submit="if (!confirm('¿Eliminar este módulo y todo su contenido interno? Esta acción no se puede deshacer.')) $event.preventDefault()">
                                                @csrf
                                                @method('DELETE')

                                                <input type="hidden" name="origen" value="builder">

                                                <button type="submit"
                                                        class="esf-action-btn esf-action-delete">
                                                    Eliminar módulo
                                                </button>
                                            </form>
                                        </div>

                                        @if($modulo->secciones->count() > 0 || $modulo->recursos->count() > 0 || $modulo->ejercicios->count() > 0 || $modulo->evaluaciones->count() > 0)
                                            @php
                                                $seccionesBasePreview = $modulo->secciones
                                                    ->where('estado', 1)
                                                    ->values();

                                                $seccionesPrincipalesPreview = $seccionesBasePreview
                                                    ->filter(function ($seccion) {
                                                        return (int) ($seccion->nivel ?? 1) === 1;
                                                    })
                                                    ->sortBy(function ($seccion) {
                                                        return sprintf(
                                                            '%04d-%010d',
                                                            (int) ($seccion->orden ?? 0),
                                                            (int) $seccion->id_capacitacion_modulo_seccion
                                                        );
                                                    })
                                                    ->values();

                                                $subseccionesAgrupadasPreview = $seccionesBasePreview
                                                    ->filter(function ($seccion) {
                                                        return (int) ($seccion->nivel ?? 1) === 2;
                                                    })
                                                    ->sortBy(function ($seccion) {
                                                        return sprintf(
                                                            '%010d-%04d-%010d',
                                                            (int) ($seccion->id_seccion_padre ?? 0),
                                                            (int) ($seccion->orden ?? 0),
                                                            (int) $seccion->id_capacitacion_modulo_seccion
                                                        );
                                                    })
                                                    ->groupBy('id_seccion_padre');

                                                $seccionesPreviewModulo = collect();
                                                $numerosSeccionesBuilder = [];
                                                $idsSeccionesPreviewIncluidas = collect();

                                                $contadorPrincipalBuilder = 0;

                                                foreach ($seccionesPrincipalesPreview as $seccionPrincipalPreview) {
                                                    $contadorPrincipalBuilder++;
                                                    $contadorSubBuilder = 0;

                                                    $seccionesPreviewModulo->push($seccionPrincipalPreview);
                                                    $idsSeccionesPreviewIncluidas->push((int) $seccionPrincipalPreview->id_capacitacion_modulo_seccion);

                                                    $numerosSeccionesBuilder[$seccionPrincipalPreview->id_capacitacion_modulo_seccion] = (string) $contadorPrincipalBuilder;

                                                    $subseccionesPreview = $subseccionesAgrupadasPreview
                                                        ->get($seccionPrincipalPreview->id_capacitacion_modulo_seccion, collect())
                                                        ->values();

                                                    foreach ($subseccionesPreview as $subseccionPreview) {
                                                        $contadorSubBuilder++;

                                                        $seccionesPreviewModulo->push($subseccionPreview);
                                                        $idsSeccionesPreviewIncluidas->push((int) $subseccionPreview->id_capacitacion_modulo_seccion);

                                                        $numerosSeccionesBuilder[$subseccionPreview->id_capacitacion_modulo_seccion] = $contadorPrincipalBuilder . '.' . $contadorSubBuilder;
                                                    }
                                                }

                                                $seccionesHuerfanasPreview = $seccionesBasePreview
                                                    ->reject(function ($seccion) use ($idsSeccionesPreviewIncluidas) {
                                                        return $idsSeccionesPreviewIncluidas->contains((int) $seccion->id_capacitacion_modulo_seccion);
                                                    })
                                                    ->sortBy(function ($seccion) {
                                                        return sprintf(
                                                            '%04d-%010d',
                                                            (int) ($seccion->orden ?? 0),
                                                            (int) $seccion->id_capacitacion_modulo_seccion
                                                        );
                                                    })
                                                    ->values();

                                                foreach ($seccionesHuerfanasPreview as $seccionHuerfanaPreview) {
                                                    $contadorPrincipalBuilder++;

                                                    $seccionesPreviewModulo->push($seccionHuerfanaPreview);
                                                    $numerosSeccionesBuilder[$seccionHuerfanaPreview->id_capacitacion_modulo_seccion] = (string) $contadorPrincipalBuilder;
                                                }

                                                $seccionesPreviewModulo = $seccionesPreviewModulo->values();

                                                $evaluacionesFinalModuloPreview = $modulo->evaluaciones
                                                    ->where('activa', 1)
                                                    ->sortBy(function ($evaluacion) {
                                                        return sprintf(
                                                            '%04d-%010d',
                                                            (int) ($evaluacion->orden ?? 0),
                                                            (int) $evaluacion->id_evaluacion
                                                        );
                                                    })
                                                    ->values();

                                                $modulosMenuPreview = $capacitacion->modulos
                                                    ->sortBy(function ($moduloMenuPreview) {
                                                        return sprintf(
                                                            '%04d-%010d',
                                                            (int) ($moduloMenuPreview->orden ?? 0),
                                                            (int) $moduloMenuPreview->id_capacitacion_modulo
                                                        );
                                                    })
                                                    ->values();

                                                $recursosSinSeccionPreview = $modulo->recursos
                                                    ->where('estado', 1)
                                                    ->whereNull('id_capacitacion_modulo_seccion')
                                                    ->values();

                                                $ejerciciosSinSeccionPreview = $modulo->ejercicios
                                                    ->where('estado', 1)
                                                    ->whereNull('id_capacitacion_modulo_seccion')
                                                    ->values();

                                                $evaluacionesSinSeccionPreview = $modulo->evaluaciones
                                                    ->where('activa', 1)
                                                    ->whereNull('id_capacitacion_modulo_seccion')
                                                    ->values();
                                            @endphp

                                            <div class="esf-builder-user-preview mt-5">
                                                <div class="esf-learning-shell curso-capacitate-shell">
                                                    <div x-data="{ sidebarPreviewAbierto: true }"
                                                        class="esf-learning-layout curso-capacitate-layout grid grid-cols-1 lg:grid-cols-12 relative">

                                                        <button type="button"
                                                                @click="sidebarPreviewAbierto = !sidebarPreviewAbierto"
                                                                class="curso-capacitate-sidebar-toggle"
                                                                :class="sidebarPreviewAbierto ? 'is-open' : 'is-closed'"
                                                                :aria-label="sidebarPreviewAbierto ? 'Ocultar plan del módulo' : 'Mostrar plan del módulo'">
                                                            <span class="curso-capacitate-sidebar-toggle-icon" aria-hidden="true">
                                                                <span></span>
                                                                <span></span>
                                                                <span></span>
                                                            </span>
                                                        </button>

                                                        <aside x-show="sidebarPreviewAbierto"
                                                            x-transition
                                                            class="esf-learning-sidebar curso-capacitate-sidebar lg:col-span-4 xl:col-span-3">
                                                            <div>
                                                                <div class="curso-capacitate-sidebar-head">
                                                                    <p class="curso-capacitate-sidebar-label">
                                                                        Plan de capacitación
                                                                    </p>

                                                                    <h3>
                                                                        Sigue el orden del curso
                                                                    </h3>

                                                                    <div class="curso-capacitate-sidebar-progress">
                                                                        <span style="width: 0%"></span>
                                                                    </div>

                                                                    <p>
                                                                        Previsualización del módulo actual
                                                                    </p>
                                                                </div>

                                                                <nav class="p-4 space-y-4">

                                                                    @foreach($modulosMenuPreview as $moduloMenuPreview)
                                                                        @php
                                                                            $moduloActualPreview = (int) $moduloMenuPreview->id_capacitacion_modulo === (int) $modulo->id_capacitacion_modulo;

                                                                            $seccionesModuloMenuPreview = $moduloMenuPreview->secciones
                                                                                ->where('estado', 1)
                                                                                ->sortBy(function ($seccionMenuPreview) {
                                                                                    return sprintf(
                                                                                        '%02d-%010d-%04d-%010d',
                                                                                        (int) ($seccionMenuPreview->nivel ?? 1),
                                                                                        (int) ($seccionMenuPreview->id_seccion_padre ?? 0),
                                                                                        (int) ($seccionMenuPreview->orden ?? 0),
                                                                                        (int) $seccionMenuPreview->id_capacitacion_modulo_seccion
                                                                                    );
                                                                                })
                                                                                ->values();

                                                                            $seccionesPrincipalesPreviewSidebar = $seccionesModuloMenuPreview
                                                                                ->where('nivel', 1)
                                                                                ->values();

                                                                            $subseccionesPreviewSidebar = $seccionesModuloMenuPreview
                                                                                ->where('nivel', 2)
                                                                                ->groupBy('id_seccion_padre');
                                                                        @endphp

                                                                        <details {{ $moduloActualPreview ? 'open' : '' }} class="rounded-2xl bg-white/5 border border-white/10 overflow-hidden">
                                                                            <summary class="list-none cursor-pointer select-none px-4 py-3">
                                                                                <div class="flex items-center justify-between gap-3">
                                                                                    <div>
                                                                                        <p class="text-[11px] uppercase tracking-[0.16em] font-black text-slate-400">
                                                                                            Módulo {{ $moduloMenuPreview->orden }}
                                                                                        </p>

                                                                                        <p class="mt-1 text-sm font-black {{ $moduloActualPreview ? 'text-white' : 'text-slate-300' }}">
                                                                                            {{ $moduloMenuPreview->titulo }}
                                                                                        </p>
                                                                                    </div>

                                                                                    <span class="text-slate-300">⌄</span>
                                                                                </div>
                                                                            </summary>

                                                                            <div class="px-3 pb-3 space-y-1">
                                                                                @if(!$moduloActualPreview)
                                                                                    <a href="#preview-builder-modulo-{{ $moduloMenuPreview->id_capacitacion_modulo }}"
                                                                                    class="esf-learning-nav-link">
                                                                                        <span class="esf-learning-dot"></span>
                                                                                        <span>Abrir este módulo</span>
                                                                                    </a>
                                                                                @endif

                                                                                @foreach($seccionesPrincipalesPreviewSidebar as $seccionSidebarPreview)
                                                                                    @php
                                                                                        $subseccionesDeSidebarPreview = $subseccionesPreviewSidebar
                                                                                            ->get($seccionSidebarPreview->id_capacitacion_modulo_seccion, collect())
                                                                                            ->values();

                                                                                        $hrefSeccionSidebarPreview = $moduloActualPreview
                                                                                            ? '#preview-builder-seccion-' . $seccionSidebarPreview->id_capacitacion_modulo_seccion
                                                                                            : '#preview-builder-modulo-' . $moduloMenuPreview->id_capacitacion_modulo;

                                                                                        $recursosSidebarPreview = $moduloMenuPreview->recursos
                                                                                            ->where('estado', 1)
                                                                                            ->where('id_capacitacion_modulo_seccion', $seccionSidebarPreview->id_capacitacion_modulo_seccion)
                                                                                            ->sortBy('orden');

                                                                                        $ejerciciosSidebarPreview = $moduloMenuPreview->ejercicios
                                                                                            ->where('estado', 1)
                                                                                            ->where('id_capacitacion_modulo_seccion', $seccionSidebarPreview->id_capacitacion_modulo_seccion)
                                                                                            ->sortBy('orden');
                                                                                    @endphp

                                                                                    <a href="{{ $hrefSeccionSidebarPreview }}"
                                                                                    class="esf-learning-nav-link esf-learning-nav-section">
                                                                                        <span class="esf-learning-dot {{ $moduloActualPreview ? 'esf-learning-dot-active' : '' }}"></span>

                                                                                        <span class="block font-bold">
                                                                                            {{ $seccionSidebarPreview->titulo }}
                                                                                        </span>
                                                                                    </a>

                                                                                    <div class="esf-learning-branch">
                                                                                        @foreach($subseccionesDeSidebarPreview as $subSidebarPreview)
                                                                                            @php
                                                                                                $recursosSubSidebarPreview = $moduloMenuPreview->recursos
                                                                                                    ->where('estado', 1)
                                                                                                    ->where('id_capacitacion_modulo_seccion', $subSidebarPreview->id_capacitacion_modulo_seccion)
                                                                                                    ->sortBy('orden');

                                                                                                $ejerciciosSubSidebarPreview = $moduloMenuPreview->ejercicios
                                                                                                    ->where('estado', 1)
                                                                                                    ->where('id_capacitacion_modulo_seccion', $subSidebarPreview->id_capacitacion_modulo_seccion)
                                                                                                    ->sortBy('orden');
                                                                                            @endphp

                                                                                            <a href="{{ $moduloActualPreview ? '#preview-builder-seccion-' . $subSidebarPreview->id_capacitacion_modulo_seccion : '#preview-builder-modulo-' . $moduloMenuPreview->id_capacitacion_modulo }}"
                                                                                            class="esf-learning-nav-link esf-learning-nav-subsection">
                                                                                                <span class="esf-learning-dot {{ $moduloActualPreview ? 'esf-learning-dot-active' : '' }}"></span>

                                                                                                <span class="block font-semibold">
                                                                                                    {{ $subSidebarPreview->titulo }}
                                                                                                </span>
                                                                                            </a>

                                                                                            @foreach($recursosSubSidebarPreview as $recursoSubSidebarPreview)
                                                                                                <a href="{{ $moduloActualPreview ? '#preview-builder-recurso-' . $recursoSubSidebarPreview->id_capacitacion_recurso : '#preview-builder-modulo-' . $moduloMenuPreview->id_capacitacion_modulo }}"
                                                                                                class="esf-learning-nav-link esf-learning-nav-subitem">
                                                                                                    <span class="esf-learning-dot"></span>
                                                                                                    <span>Recurso</span>
                                                                                                </a>
                                                                                            @endforeach

                                                                                            @foreach($ejerciciosSubSidebarPreview as $ejercicioSubSidebarPreview)
                                                                                                <a href="{{ $moduloActualPreview ? '#preview-builder-ejercicio-' . $ejercicioSubSidebarPreview->id_ejercicio : '#preview-builder-modulo-' . $moduloMenuPreview->id_capacitacion_modulo }}"
                                                                                                class="esf-learning-nav-link esf-learning-nav-subitem">
                                                                                                    <span class="esf-learning-dot"></span>
                                                                                                    <span>Ejercicio</span>
                                                                                                </a>
                                                                                            @endforeach
                                                                                        @endforeach

                                                                                        @foreach($recursosSidebarPreview as $recursoSidebarPreview)
                                                                                            <a href="{{ $moduloActualPreview ? '#preview-builder-recurso-' . $recursoSidebarPreview->id_capacitacion_recurso : '#preview-builder-modulo-' . $moduloMenuPreview->id_capacitacion_modulo }}"
                                                                                            class="esf-learning-nav-link esf-learning-nav-section-item">
                                                                                                <span class="esf-learning-dot"></span>
                                                                                                <span>Recurso</span>
                                                                                            </a>
                                                                                        @endforeach

                                                                                        @foreach($ejerciciosSidebarPreview as $ejercicioSidebarPreview)
                                                                                            <a href="{{ $moduloActualPreview ? '#preview-builder-ejercicio-' . $ejercicioSidebarPreview->id_ejercicio : '#preview-builder-modulo-' . $moduloMenuPreview->id_capacitacion_modulo }}"
                                                                                            class="esf-learning-nav-link esf-learning-nav-section-item">
                                                                                                <span class="esf-learning-dot"></span>
                                                                                                <span>Ejercicio</span>
                                                                                            </a>
                                                                                        @endforeach
                                                                                    </div>
                                                                                @endforeach

                                                                                @if($moduloMenuPreview->evaluaciones->where('activa', 1)->count() > 0)
                                                                                    <a href="{{ $moduloActualPreview ? '#preview-builder-examen-' . $modulo->id_capacitacion_modulo : '#preview-builder-modulo-' . $moduloMenuPreview->id_capacitacion_modulo }}"
                                                                                    class="esf-learning-nav-link">
                                                                                        <span class="esf-learning-dot"></span>
                                                                                        <span>
                                                                                            <span class="block text-xs text-slate-400">Final</span>
                                                                                            <span class="block">Examen general</span>
                                                                                        </span>
                                                                                    </a>
                                                                                @endif
                                                                            </div>
                                                                        </details>
                                                                    @endforeach
                                                                </nav>
                                                            </div>
                                                        </aside>

                                                        <section class="esf-learning-main curso-capacitate-main"
                                                            :class="sidebarPreviewAbierto ? 'lg:col-span-8 xl:col-span-9' : 'lg:col-span-12 xl:col-span-12 curso-capacitate-main-full'">
                                                            <div class="curso-capacitate-content space-y-8">

                                                                <div id="preview-builder-modulo-{{ $modulo->id_capacitacion_modulo }}"
                                                                    class="esf-learning-hero curso-capacitate-module-intro">
                                                                    <h1 class="mt-2 text-3xl font-black text-slate-900 dark:text-slate-100">
                                                                        {{ $modulo->titulo }}
                                                                    </h1>

                                                                    @if($descripcionModuloPreview !== '' || $objetivoModuloPreview !== '')
                                                                        <div class="mt-3 space-y-2">
                                                                            @if($descripcionModuloPreview !== '')
                                                                                <p class="text-base text-slate-600 dark:text-slate-300">
                                                                                    {{ $descripcionModuloPreview }}
                                                                                </p>
                                                                            @endif

                                                                            @if($objetivoModuloPreview !== '')
                                                                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                                                                    <span class="font-black text-slate-800 dark:text-slate-100">Objetivo:</span>
                                                                                    {{ $objetivoModuloPreview }}
                                                                                </p>
                                                                            @endif
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                                @forelse($seccionesPreviewModulo as $seccionPreview)
                                                                    @php
                                                                        $recursosPreviewSeccion = $modulo->recursos
                                                                            ->where('estado', 1)
                                                                            ->where('id_capacitacion_modulo_seccion', $seccionPreview->id_capacitacion_modulo_seccion)
                                                                            ->sortBy('orden')
                                                                            ->values();

                                                                        $ejerciciosPreviewSeccion = $modulo->ejercicios
                                                                            ->where('estado', 1)
                                                                            ->where('id_capacitacion_modulo_seccion', $seccionPreview->id_capacitacion_modulo_seccion)
                                                                            ->sortBy('orden')
                                                                            ->values();

                                                                        $itemsPreviewSeccion = collect();

                                                                        foreach ($recursosPreviewSeccion as $recursoPreview) {
                                                                            $itemsPreviewSeccion->push([
                                                                                'tipo' => 'recurso',
                                                                                'orden' => $recursoPreview->orden,
                                                                                'data' => $recursoPreview,
                                                                            ]);
                                                                        }

                                                                        foreach ($ejerciciosPreviewSeccion as $ejercicioPreview) {
                                                                            $itemsPreviewSeccion->push([
                                                                                'tipo' => 'ejercicio',
                                                                                'orden' => $ejercicioPreview->orden,
                                                                                'data' => $ejercicioPreview,
                                                                            ]);
                                                                        }

                                                                        $itemsPreviewSeccion = $itemsPreviewSeccion
                                                                            ->sortBy('orden')
                                                                            ->values();

                                                                        $contenidoPreviewHtml = trim((string) ($seccionPreview->contenido ?? ''));
                                                                        $contenidoPreviewHtml = preg_replace('/^(<p>(\s|&nbsp;|<br\s*\/?>)*<\/p>\s*)+/i', '', $contenidoPreviewHtml);
                                                                        $contenidoPreviewHtml = preg_replace('/(\s*<p>(\s|&nbsp;|<br\s*\/?>)*<\/p>)+$/i', '', $contenidoPreviewHtml);
                                                                        $contenidoPreviewHtml = trim($contenidoPreviewHtml);

                                                                        $seccionPreviewTieneContenido = $contenidoPreviewHtml !== '' && $contenidoPreviewHtml !== '<p><br></p>';
                                                                        $seccionPreviewTieneItems = $itemsPreviewSeccion->count() > 0;
                                                                        $margenHeaderPreview = $seccionPreviewTieneContenido || $seccionPreviewTieneItems ? 'mb-1' : 'mb-0';
                                                                    @endphp

                                                                    <article id="preview-builder-seccion-{{ $seccionPreview->id_capacitacion_modulo_seccion }}"
                                                                            class="esf-learning-section curso-capacitate-page">
                                                                        <div class="curso-capacitate-section-title {{ $margenHeaderPreview }}">
                                                                            <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100">
                                                                                {{ $seccionPreview->titulo }}
                                                                            </h2>
                                                                        </div>

                                                                        @if($seccionPreviewTieneContenido)
                                                                            <div class="ql-snow">
                                                                                <div class="contenido-teoria-render ql-editor max-w-none text-slate-800 dark:text-slate-100 leading-relaxed bg-transparent p-0">
                                                                                    {!! $contenidoPreviewHtml !!}
                                                                                </div>
                                                                            </div>
                                                                        @endif

                                                                        @if($itemsPreviewSeccion->count() > 0)
                                                                            <div class="{{ $seccionPreviewTieneContenido ? 'mt-5' : 'mt-3' }} space-y-5">
                                                                                @foreach($itemsPreviewSeccion as $itemPreview)
                                                                                    @php
                                                                                        $tipoPreview = $itemPreview['tipo'];
                                                                                        $dataPreview = $itemPreview['data'];
                                                                                    @endphp

                                                                                    @if($tipoPreview === 'recurso')
                                                                                        @php
                                                                                            $rutaArchivoPreview = $dataPreview->ruta_archivo
                                                                                                ? asset('storage/' . $dataPreview->ruta_archivo)
                                                                                                : null;

                                                                                            $extensionPreview = strtolower(pathinfo($dataPreview->ruta_archivo ?? '', PATHINFO_EXTENSION));

                                                                                            $esImagenPreview = in_array($extensionPreview, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);
                                                                                            $esPdfPreview = $extensionPreview === 'pdf';
                                                                                            $esVideoPreview = in_array($extensionPreview, ['mp4', 'webm', 'mov', 'm4v'], true);
                                                                                            $esAudioPreview = in_array($extensionPreview, ['mp3', 'wav', 'ogg', 'm4a'], true);
                                                                                            $esOfficePreview = in_array($extensionPreview, ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'], true);

                                                                                            $urlEsLocalPreview = !\App\Support\VistaPreviaRecurso::esUrlAccesibleDesdeInternet($rutaArchivoPreview);

                                                                                            $urlOfficePreview = $rutaArchivoPreview
                                                                                                ? 'https://view.officeapps.live.com/op/embed.aspx?src=' . urlencode($rutaArchivoPreview)
                                                                                                : null;

                                                                                            $extensionUrlPreview = $dataPreview->url_recurso
                                                                                                ? strtolower(pathinfo(parse_url($dataPreview->url_recurso, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION))
                                                                                                : null;

                                                                                            $esImagenUrlPreview = in_array($extensionUrlPreview, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);
                                                                                        @endphp

                                                                                        <div id="preview-builder-recurso-{{ $dataPreview->id_capacitacion_recurso }}"
                                                                                            class="esf-learning-content-card curso-capacitate-activity-card">
                                                                                            <div class="flex items-start gap-3">
                                                                                                <span class="esf-learning-dot"></span>

                                                                                                <div class="flex-1">
                                                                                                    <p class="text-xs uppercase tracking-[0.16em] font-black text-blue-600 dark:text-blue-300">
                                                                                                        Recurso de aprendizaje
                                                                                                    </p>

                                                                                                    <h3 class="mt-1 text-xl font-black text-slate-900 dark:text-slate-100">
                                                                                                        {{ $dataPreview->titulo }}
                                                                                                    </h3>

                                                                                                    @if($dataPreview->descripcion)
                                                                                                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                                                                                            {{ $dataPreview->descripcion }}
                                                                                                        </p>
                                                                                                    @endif

                                                                                                    @if($rutaArchivoPreview)
                                                                                                        <div class="esf-resource-preview">
                                                                                                            @if($esImagenPreview)
                                                                                                                <img src="{{ $rutaArchivoPreview }}"
                                                                                                                    alt="{{ $dataPreview->titulo }}">
                                                                                                            @elseif($esPdfPreview)
                                                                                                                <iframe src="{{ $rutaArchivoPreview }}"></iframe>
                                                                                                            @elseif($esVideoPreview)
                                                                                                                <video controls>
                                                                                                                    <source src="{{ $rutaArchivoPreview }}">
                                                                                                                </video>
                                                                                                            @elseif($esAudioPreview)
                                                                                                                <div class="p-5">
                                                                                                                    <audio controls class="w-full">
                                                                                                                        <source src="{{ $rutaArchivoPreview }}">
                                                                                                                    </audio>
                                                                                                                </div>
                                                                                                            @elseif($esOfficePreview && !$urlEsLocalPreview)
                                                                                                                <iframe src="{{ $urlOfficePreview }}"></iframe>
                                                                                                            @else
                                                                                                                <div class="p-5">
                                                                                                                    <p class="font-black text-slate-900 dark:text-slate-100">
                                                                                                                        Archivo disponible
                                                                                                                    </p>

                                                                                                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                                                                                                        Este tipo de archivo se podrá previsualizar cuando el sistema esté publicado con una URL accesible. En local, abrilo desde el botón.
                                                                                                                    </p>

                                                                                                                    <a href="{{ $rutaArchivoPreview }}"
                                                                                                                    target="_blank"
                                                                                                                    class="mt-4 esf-learning-big-action esf-learning-action-blue">
                                                                                                                        Abrir archivo
                                                                                                                    </a>
                                                                                                                </div>
                                                                                                            @endif
                                                                                                        </div>
                                                                                                    @endif

                                                                                                    @if($dataPreview->url_recurso && $esImagenUrlPreview)
                                                                                                        <div class="esf-resource-preview">
                                                                                                            <img src="{{ $dataPreview->url_recurso }}"
                                                                                                                alt="{{ $dataPreview->titulo }}">
                                                                                                        </div>
                                                                                                    @endif

                                                                                                    @if($dataPreview->url_recurso)
                                                                                                        <a href="{{ $dataPreview->url_recurso }}"
                                                                                                        target="_blank"
                                                                                                        class="mt-4 esf-learning-big-action esf-learning-action-blue">
                                                                                                            {{ $esImagenUrlPreview ? 'Abrir imagen' : 'Abrir enlace' }}
                                                                                                        </a>
                                                                                                    @endif
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    @elseif($tipoPreview === 'ejercicio')
                                                                                        <div id="preview-builder-ejercicio-{{ $dataPreview->id_ejercicio }}"
                                                                                            class="esf-learning-content-card curso-capacitate-activity-card">
                                                                                            <div class="flex items-start gap-3">
                                                                                                <span class="esf-learning-dot"></span>

                                                                                                <div class="flex-1">
                                                                                                    <p class="text-xs uppercase tracking-[0.16em] font-black text-emerald-600 dark:text-emerald-300">
                                                                                                        Ejercicio
                                                                                                    </p>

                                                                                                    <h3 class="mt-1 text-xl font-black text-slate-900 dark:text-slate-100">
                                                                                                        {{ $dataPreview->titulo }}
                                                                                                    </h3>

                                                                                                    @if($dataPreview->descripcion)
                                                                                                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                                                                                            {{ $dataPreview->descripcion }}
                                                                                                        </p>
                                                                                                    @endif

                                                                                                    <div class="mt-4 flex flex-wrap gap-2">
                                                                                                        <span class="esf-badge esf-badge-blue">
                                                                                                            Intentos realizados: 0
                                                                                                        </span>

                                                                                                        <span class="esf-badge esf-badge-slate">
                                                                                                            Máximo: {{ $dataPreview->intentos_maximos ?: 'Ilimitado' }}
                                                                                                        </span>
                                                                                                    </div>

                                                                                                    <div class="mt-5 esf-learning-big-action esf-learning-action-green pointer-events-none select-none">
                                                                                                        Comenzar ejercicio
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    @endif
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    </article>
                                                                @empty
                                                                    <div class="esf-learning-section p-8 text-center">
                                                                        <p class="text-lg font-black text-slate-900 dark:text-slate-100">
                                                                            Este módulo todavía no tiene contenido configurado.
                                                                        </p>

                                                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                                                            Cuando el administrador agregue secciones, aparecerán aquí.
                                                                        </p>
                                                                    </div>
                                                                @endforelse

                                                                @if($evaluacionesFinalModuloPreview->count() > 0)
                                                                    <section id="preview-builder-examen-{{ $modulo->id_capacitacion_modulo }}"
                                                                        class="esf-learning-section curso-capacitate-page curso-capacitate-exam">
                                                                        <div class="border-b border-slate-200 dark:border-slate-700 pb-3 mb-1">
                                                                            <p class="text-xs uppercase tracking-[0.18em] font-black text-purple-600 dark:text-purple-300">
                                                                                Examen general
                                                                            </p>

                                                                            <h2 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">
                                                                                Evaluación final del módulo
                                                                            </h2>

                                                                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                                                                Cuando termines de leer el contenido y realizar los ejercicios, presiona el botón "Comenzar examen" de la evaluación para comenzar.
                                                                            </p>
                                                                        </div>

                                                                        <div class="space-y-5">
                                                                            @foreach($evaluacionesFinalModuloPreview as $evaluacionPreview)
                                                                                <div class="esf-learning-content-card curso-capacitate-activity-card">
                                                                                    <p class="text-xs uppercase tracking-[0.16em] font-black text-purple-600 dark:text-purple-300">
                                                                                        Evaluación
                                                                                    </p>

                                                                                    <h3 class="mt-1 text-xl font-black text-slate-900 dark:text-slate-100">
                                                                                        {{ $evaluacionPreview->titulo }}
                                                                                    </h3>

                                                                                    @if($evaluacionPreview->descripcion)
                                                                                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                                                                            {{ $evaluacionPreview->descripcion }}
                                                                                        </p>
                                                                                    @endif

                                                                                    <div class="mt-4 flex flex-wrap gap-2">
                                                                                        <span class="esf-badge esf-badge-blue">
                                                                                            Intentos realizados: 0
                                                                                        </span>

                                                                                        <span class="esf-badge esf-badge-purple">
                                                                                            Disponible
                                                                                        </span>
                                                                                    </div>

                                                                                    <div class="mt-5 esf-learning-big-action esf-learning-action-purple pointer-events-none select-none">
                                                                                        Comenzar examen
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </section>
                                                                @endif

                                                                @if($recursosSinSeccionPreview->count() || $ejerciciosSinSeccionPreview->count() || $evaluacionesSinSeccionPreview->count())
                                                                    <div class="esf-learning-section p-6">
                                                                        <p class="font-black text-orange-700 dark:text-orange-300">
                                                                            Contenido pendiente de ubicar
                                                                        </p>

                                                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                                                            Estos elementos todavía no han sido organizados por el administrador en una sección.
                                                                        </p>
                                                                    </div>
                                                                @endif

                                                            </div>
                                                        </section>

                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                            </div>


                        </div>
                    </details>
                @empty
                    <div class="esf-page-card p-8 text-center">
                        <p class="text-lg font-black text-slate-900 dark:text-slate-100">
                            Esta capacitación todavía no tiene módulos.
                        </p>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Crea el primer módulo para empezar a organizar el contenido del curso.
                        </p>

                        <button type="button"
                                @click="abrirModal('modalCrearModulo')"
                                class="mt-5 esf-btn esf-btn-primary">
                            Crear primer módulo
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div id="modalCrearModulo"
        class="modal-builder hidden fixed inset-0 z-50 bg-black bg-opacity-50 items-start justify-center overflow-y-auto p-4">

        <div class="esf-module-editor-page w-full max-w-[1150px] mx-auto my-8">
            <div class="esf-page-card esf-module-editor-card overflow-hidden">
                <div class="p-6 sm:p-8 text-slate-900 dark:text-slate-100">

                    <div class="mb-6">
                        <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                            Módulo de capacitación
                        </p>

                        <h3 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100 leading-tight">
                            Nuevo módulo
                        </h3>

                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Crea el módulo, organiza su teoría y prepara las secciones donde irán recursos, ejercicios o evaluaciones.
                        </p>
                    </div>

                    <form method="POST"
                        action="{{ route('capacitaciones.modulos.store', $capacitacion->id_capacitacion) }}"
                        class="space-y-6">
                        @csrf

                        <input type="hidden" name="accion_despues_crear_modulo" id="accionDespuesCrearModulo" value="">
                        <input type="hidden" name="indice_seccion_despues_crear_modulo" id="indiceSeccionDespuesCrearModulo" value="">
                        <input type="hidden" name="origen" value="builder">

                        <div class="space-y-4">
                            <div>
                                <label class="block mb-1">
                                    Título del módulo
                                </label>

                                <input type="text"
                                    name="titulo"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                    required>

                                <p class="text-xs text-slate-400 mt-1">
                                    Mínimo 3 caracteres.
                                </p>
                            </div>

                            <div>
                                <label class="block mb-1">
                                    Descripción
                                </label>

                                <textarea name="descripcion"
                                        rows="4"
                                        class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"></textarea>
                            </div>

                            <div>
                                <label class="block mb-1">
                                    Objetivo
                                </label>

                                <textarea name="objetivo"
                                        rows="3"
                                        class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"></textarea>
                            </div>
                        </div>

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
                                        @click="agregarSeccionModulo('contenedorSeccionesNuevoModulo')"
                                        class="esf-btn esf-btn-primary text-sm">
                                    + Agregar página
                                </button>
                            </div>

                            <div id="contenedorSeccionesNuevoModulo" class="space-y-4">
                                <div class="seccion-modulo-item rounded border border-gray-300 bg-white dark:bg-gray-800 p-4"
                                    data-bloque-pagina-seccion>
                                    <input type="hidden" name="secciones_id[]" value="">
                                    <input type="hidden" name="secciones_padre[]" value="">

                                    <div class="flex justify-between items-center mb-2 gap-2">
                                        <strong>Página / sección 1</strong>

                                        <div class="flex flex-wrap gap-2 justify-end">
                                            <button type="button"
                                                    @click="agregarSubseccionModuloNuevo($el)"
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

                                    <label class="block text-sm font-medium" data-label-titulo-seccion-nuevo-modulo>
                                        Título de la página
                                    </label>

                                    <input type="text"
                                        name="secciones_titulo[]"
                                        placeholder="Ejemplo: Introducción"
                                        class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100 mb-3">

                                    <label class="block text-sm font-medium">
                                        Tipo de sección
                                    </label>

                                    <select name="secciones_nivel[]"
                                            @change="actualizarSelectorNivelSeccionNuevoModulo($el)"
                                            class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100 mb-3">
                                        <option value="1" selected>Sección principal</option>
                                        <option value="2">Subsección</option>
                                    </select>

                                    <div class="acciones-contenido-subseccion mt-3 rounded border border-emerald-300 bg-emerald-50 p-3">
                                        <p class="text-sm font-semibold text-emerald-900">
                                            Contenido de esta sección
                                        </p>

                                        <p class="text-xs text-emerald-800 mt-1">
                                            Ya puedes agregar recursos, ejercicios o evaluaciones. El sistema guardará automáticamente el módulo y esta sección.
                                        </p>

                                        <div class="botones-subseccion-contenido mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
                                            <button type="button"
                                                    data-accion-crear-modulo="recurso"
                                                    class="block text-center px-3 py-2 bg-blue-600 text-white rounded text-xs">
                                                Seleccionar archivo
                                            </button>

                                            <button type="button"
                                                    data-accion-crear-modulo="ejercicio"
                                                    class="block text-center px-3 py-2 bg-emerald-600 text-white rounded text-xs">
                                                Agregar ejercicio
                                            </button>

                                            <button type="button"
                                                    data-accion-crear-modulo="evaluacion"
                                                    class="block text-center px-3 py-2 bg-purple-600 text-white rounded text-xs">
                                                Agregar evaluación
                                            </button>
                                        </div>
                                    </div>

                                    <label class="block text-sm font-medium mb-1 mt-3">
                                        Contenido escrito
                                    </label>

                                    <input type="hidden"
                                        name="secciones_contenido[]"
                                        class="input-contenido-seccion-modulo">

                                    <div class="editor-contenido-seccion-modulo bg-white text-black rounded border border-gray-300"
                                        style="min-height: 260px;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="block mb-1">
                                    Orden
                                </label>

                                <input type="number"
                                    name="orden"
                                    value="{{ $totalModulos + 1 }}"
                                    min="1"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                    required>
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1">
                                    Duración en horas
                                </label>

                                <input type="number"
                                    step="0.01"
                                    name="duracion_horas"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1">
                                    Requiere evaluación
                                </label>

                                <select name="requiere_evaluacion"
                                        class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                        required>
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1">
                                    Porcentaje de aprobación
                                </label>

                                <input type="number"
                                    name="porcentaje_aprobacion"
                                    min="1"
                                    max="100"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                            </div>

                            <div class="mb-6">
                                <label class="block mb-1">
                                    Estado
                                </label>

                                <select name="estado"
                                        class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                        required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-200/80 dark:border-slate-700/80 pt-5">
                            <p class="text-xs text-slate-400 dark:text-slate-500">
                                Guarda el módulo o selecciona una acción dentro de una sección para continuar agregando contenido.
                            </p>

                            <div class="flex flex-col sm:flex-row gap-3">
                                <button type="button"
                                        @click="cerrarModal('modalCrearModulo')"
                                        class="esf-btn esf-btn-soft">
                                    Cancelar
                                </button>

                                <button type="submit"
                                        class="esf-btn esf-btn-primary">
                                    Guardar módulo
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@foreach($capacitacion->modulos as $modulo)

    <div id="modalCrearEvaluacion{{ $modulo->id_capacitacion_modulo }}" class="modal-builder hidden fixed inset-0 z-50 bg-black bg-opacity-50 items-start justify-center overflow-y-auto p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6 my-8">
            <h3 class="text-xl font-bold mb-4">Nueva evaluación: {{ $modulo->titulo }}</h3>

            <form method="POST" action="{{ route('capacitacion_modulos.evaluaciones.store', $modulo->id_capacitacion_modulo) }}">
                @csrf
                <input type="hidden" name="origen" value="builder">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium">Título</label>
                        <input type="text" name="titulo" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium">Descripción</label>
                        <textarea name="descripcion" rows="3" class="w-full rounded border-gray-300 dark:bg-gray-900"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium">Instrucciones</label>
                        <textarea name="instrucciones" rows="3" class="w-full rounded border-gray-300 dark:bg-gray-900"></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            Indicaciones que verá el usuario antes de presentar la evaluación.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Intentos máximos</label>
                        <input type="number" name="intentos_maximos" min="1" value="3" class="w-full rounded border-gray-300 dark:bg-gray-900">
                        <p class="text-xs text-gray-500 mt-1">Si lo dejas vacío, los intentos serán ilimitados.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Tiempo límite en minutos</label>
                        <input type="number" name="tiempo_limite_minutos" min="1" class="w-full rounded border-gray-300 dark:bg-gray-900">
                        <p class="text-xs text-gray-500 mt-1">Si lo dejas vacío, no tendrá temporizador.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">% aprobación</label>
                        <input type="number" name="porcentaje_aprobacion" min="1" max="100" step="0.01" value="{{ $modulo->porcentaje_aprobacion ?? 70 }}" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Orden</label>
                        <input type="number" name="orden" min="1" value="{{ $modulo->evaluaciones->count() + 1 }}" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Obligatorio</label>
                        <select name="obligatorio" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Estado</label>
                        <select name="activa" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                            <option value="1">Activa</option>
                            <option value="0">Inactiva</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Mostrar resultado inmediato</label>
                        <select name="mostrar_resultado_inmediato" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>

<input type="hidden" name="requiere_revision_manual" value="0">
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="cerrarModal('modalCrearEvaluacion{{ $modulo->id_capacitacion_modulo }}')" class="px-4 py-2 bg-gray-500 text-white rounded">
                        Cancelar
                    </button>

                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded">
                        Guardar evaluación
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach

   @foreach($capacitacion->modulos as $modulo)
    @foreach($modulo->evaluaciones as $evaluacion)
        <div id="modalCrearPregunta{{ $evaluacion->id_evaluacion }}" class="modal-builder hidden fixed inset-0 z-50 bg-black bg-opacity-50 items-start justify-center overflow-y-auto p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6 my-8">
                <h3 class="text-xl font-bold mb-4">Nueva pregunta: {{ $evaluacion->titulo }}</h3>

                <form method="POST" action="{{ route('evaluaciones.preguntas.store', $evaluacion->id_evaluacion) }}">
                    @csrf
                    <input type="hidden" name="origen" value="builder">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="campo-pregunta-normal-evaluacion md:col-span-2" data-scope="crear-pregunta-evaluacion-{{ $evaluacion->id_evaluacion }}">
                            <label class="block text-sm font-medium">Pregunta</label>
                            <textarea name="pregunta" rows="3" class="w-full rounded border-gray-300 dark:bg-gray-900" required></textarea>
                        </div>

                        <div class="campo-completar-evaluacion md:col-span-2 hidden" data-scope="crear-pregunta-evaluacion-{{ $evaluacion->id_evaluacion }}">
                            <label class="block text-sm font-medium mb-1">Texto antes del espacio en blanco</label>
                            <input type="text" name="completar_texto_antes" class="w-full rounded border-gray-300 dark:bg-gray-900">

                            <label class="block text-sm font-medium mb-1 mt-3">Respuesta correcta</label>
                            <input type="text" name="respuesta_correcta_texto" class="w-full rounded border-gray-300 dark:bg-gray-900">

                            <label class="block text-sm font-medium mb-1 mt-3">Texto después del espacio en blanco</label>
                            <input type="text" name="completar_texto_despues" class="w-full rounded border-gray-300 dark:bg-gray-900">

                            <p class="text-xs text-gray-500 mt-2">
                                El sistema construirá automáticamente la frase usando el espacio en blanco.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Tipo de pregunta</label>
                            <select name="tipo_pregunta"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 tipo-pregunta-evaluacion"
                                    data-scope="crear-pregunta-evaluacion-{{ $evaluacion->id_evaluacion }}"
                                    required>
                                <option value="opcion_unica">Opción única</option>
                                <option value="checklist_guiado">Opción múltiple</option>
                                <option value="verdadero_falso">Verdadero / Falso</option>
                                <option value="completar">Completar en frase</option>
                                <option value="respuesta_corta">Respuesta breve</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Puntaje</label>
                            <input type="number" name="puntaje" min="0.01" step="0.01" value="1" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                        </div>

                        <div class="md:col-span-2 rounded border border-blue-300 bg-blue-100 px-4 py-3 text-blue-800 text-sm ayuda-tipo-evaluacion"
                            data-scope="crear-pregunta-evaluacion-{{ $evaluacion->id_evaluacion }}">
                            Pregunta de selección: la corrección se define desde las opciones.
                        </div>

                        <div class="campo-respuesta-breve-evaluacion md:col-span-2 hidden" data-scope="crear-pregunta-evaluacion-{{ $evaluacion->id_evaluacion }}">
                            <label class="block text-sm font-medium mb-1">Placeholder para el usuario</label>
                            <input type="text"
                                name="respuesta_breve_placeholder"
                                class="w-full rounded border-gray-300 dark:bg-gray-900"
                                placeholder="Escribe aquí tu respuesta breve...">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Mínimo de caracteres</label>
                                    <input type="number" name="respuesta_breve_min" min="0" class="w-full rounded border-gray-300 dark:bg-gray-900">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Máximo de caracteres</label>
                                    <input type="number" name="respuesta_breve_max" min="1" class="w-full rounded border-gray-300 dark:bg-gray-900">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Orden</label>
                            <input type="number" name="orden" min="1" value="{{ $evaluacion->preguntas->count() + 1 }}" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Estado</label>
                            <select name="activa" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                                <option value="1">Activa</option>
                                <option value="0">Inactiva</option>
                            </select>
                        </div>

<input type="hidden" name="requiere_revision_manual" value="0">
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="cerrarModal('modalCrearPregunta{{ $evaluacion->id_evaluacion }}')" class="px-4 py-2 bg-gray-500 text-white rounded">
                            Cancelar
                        </button>

                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                            Guardar pregunta
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @foreach($evaluacion->preguntas as $pregunta)
            <div id="modalCrearOpcion{{ $pregunta->id_evaluacion_pregunta }}" class="modal-builder hidden fixed inset-0 z-50 bg-black bg-opacity-50 items-start justify-center overflow-y-auto p-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 my-8">
                    <h3 class="text-xl font-bold mb-4">Nueva opción</h3>

                    <form method="POST" action="{{ route('evaluacion_preguntas.opciones.store', $pregunta->id_evaluacion_pregunta) }}">
                        @csrf
                        <input type="hidden" name="origen" value="builder">

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium">Texto de la opción</label>
                                <textarea name="opcion" rows="2" class="w-full rounded border-gray-300 dark:bg-gray-900" required></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Orden</label>
                                <input type="number" name="orden" min="1" value="{{ $pregunta->opciones->count() + 1 }}" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium">¿Es correcta?</label>
                                <select name="es_correcta" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" @click="cerrarModal('modalCrearOpcion{{ $pregunta->id_evaluacion_pregunta }}')" class="px-4 py-2 bg-gray-500 text-white rounded">
                                Cancelar
                            </button>

                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                                Guardar opción
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endforeach
@endforeach

    @foreach($capacitacion->modulos as $modulo)
    <div id="modalCrearEjercicio{{ $modulo->id_capacitacion_modulo }}" class="modal-builder hidden fixed inset-0 z-50 bg-black bg-opacity-50 items-start justify-center overflow-y-auto p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6 my-8">
            <h3 class="text-xl font-bold mb-4">Nuevo ejercicio: {{ $modulo->titulo }}</h3>

            <form method="POST" action="{{ route('capacitacion_modulos.ejercicios.store', $modulo->id_capacitacion_modulo) }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium">Título</label>
                        <input type="text" name="titulo" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium">Descripción</label>
                        <textarea name="descripcion" rows="3" class="w-full rounded border-gray-300 dark:bg-gray-900"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium">Instrucciones</label>
                        <textarea name="instrucciones" rows="4" class="w-full rounded border-gray-300 dark:bg-gray-900"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Intentos máximos</label>
                        <input type="number" name="intentos_maximos" min="1" class="w-full rounded border-gray-300 dark:bg-gray-900">
                        <p class="text-xs text-gray-500 mt-1">Si lo dejas vacío, los intentos serán ilimitados y no se mostrarán respuestas correctas al usuario.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Tiempo límite en minutos</label>
                        <input type="number" name="tiempo_limite_minutos" min="1" class="w-full rounded border-gray-300 dark:bg-gray-900">
                        <p class="text-xs text-gray-500 mt-1">Si lo dejas vacío, el ejercicio no tendrá temporizador.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Porcentaje para aprobar</label>
                        <input type="number" name="porcentaje_aprobacion" min="1" max="100" step="0.01" value="70" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Orden</label>
                        <input type="number" name="orden" min="1" value="{{ $modulo->ejercicios->count() + 1 }}" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Obligatorio</label>
                        <select name="obligatorio" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Estado</label>
                        <select name="estado" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Mostrar resultado inmediato</label>
                        <select name="mostrar_resultado_inmediato" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>

<input type="hidden" name="requiere_revision_manual" value="0">
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="cerrarModal('modalCrearEjercicio{{ $modulo->id_capacitacion_modulo }}')" class="px-4 py-2 bg-gray-500 text-white rounded">
                        Cancelar
                    </button>

                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded">
                        Guardar ejercicio
                    </button>
                </div>
            </form>
        </div>
    </div>

    @foreach($modulo->ejercicios as $ejercicioItem)
        <div id="modalEditarEjercicio{{ $ejercicioItem->id_ejercicio }}" class="modal-builder hidden fixed inset-0 z-50 bg-black bg-opacity-50 items-start justify-center overflow-y-auto p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6 my-8">
                <h3 class="text-xl font-bold mb-4">Editar ejercicio: {{ $ejercicioItem->titulo }}</h3>

                <form method="POST" action="{{ route('ejercicios.update', $ejercicioItem->id_ejercicio) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium">Título</label>
                            <input type="text" name="titulo" value="{{ old('titulo', $ejercicioItem->titulo) }}" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium">Descripción</label>
                            <textarea name="descripcion" rows="3" class="w-full rounded border-gray-300 dark:bg-gray-900">{{ old('descripcion', $ejercicioItem->descripcion) }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium">Instrucciones</label>
                            <textarea name="instrucciones" rows="4" class="w-full rounded border-gray-300 dark:bg-gray-900">{{ old('instrucciones', $ejercicioItem->instrucciones) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Intentos máximos</label>
                            <input type="number" name="intentos_maximos" min="1" value="{{ old('intentos_maximos', $ejercicioItem->intentos_maximos) }}" class="w-full rounded border-gray-300 dark:bg-gray-900">
                            <p class="text-xs text-gray-500 mt-1">Si lo dejas vacío, los intentos serán ilimitados y no se mostrarán respuestas correctas al usuario.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Tiempo límite en minutos</label>
                            <input type="number" name="tiempo_limite_minutos" min="1" value="{{ old('tiempo_limite_minutos', $ejercicioItem->tiempo_limite_minutos) }}" class="w-full rounded border-gray-300 dark:bg-gray-900">
                            <p class="text-xs text-gray-500 mt-1">Si lo dejas vacío, el ejercicio no tendrá temporizador.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Porcentaje para aprobar</label>
                            <input type="number" name="porcentaje_aprobacion" min="1" max="100" step="0.01" value="{{ old('porcentaje_aprobacion', $ejercicioItem->porcentaje_aprobacion ?? 70) }}" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Orden</label>
                            <input type="number" name="orden" min="1" value="{{ old('orden', $ejercicioItem->orden) }}" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Obligatorio</label>
                            <select name="obligatorio" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                                <option value="1" {{ old('obligatorio', $ejercicioItem->obligatorio) == '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ old('obligatorio', $ejercicioItem->obligatorio) == '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Estado</label>
                            <select name="estado" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                                <option value="1" {{ old('estado', $ejercicioItem->estado) == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('estado', $ejercicioItem->estado) == '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Mostrar resultado inmediato</label>
                            <select name="mostrar_resultado_inmediato" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                                <option value="1" {{ old('mostrar_resultado_inmediato', $ejercicioItem->mostrar_resultado_inmediato) == '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ old('mostrar_resultado_inmediato', $ejercicioItem->mostrar_resultado_inmediato) == '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

<input type="hidden" name="requiere_revision_manual" value="0">
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="cerrarModal('modalEditarEjercicio{{ $ejercicioItem->id_ejercicio }}')" class="px-4 py-2 bg-gray-500 text-white rounded">
                            Cancelar
                        </button>

                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="modalCrearPreguntaEjercicio{{ $ejercicioItem->id_ejercicio }}" class="modal-builder hidden fixed inset-0 z-50 bg-black bg-opacity-50 items-start justify-center overflow-y-auto p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6 my-8">
                <h3 class="text-xl font-bold mb-4">Nueva pregunta de ejercicio: {{ $ejercicioItem->titulo }}</h3>

                <form method="POST" action="{{ route('ejercicios.preguntas.store', $ejercicioItem->id_ejercicio) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="campo-enunciado-normal-ejercicio" data-scope="crear-pregunta-ejercicio-{{ $ejercicioItem->id_ejercicio }}">
                            <label class="block text-sm font-medium mb-1">Enunciado</label>
                            <textarea name="enunciado" rows="3" class="w-full border rounded px-3 py-2 text-black">{{ old('enunciado') }}</textarea>
                        </div>

                        <div class="campo-completar-amigable-ejercicio hidden" data-scope="crear-pregunta-ejercicio-{{ $ejercicioItem->id_ejercicio }}">
                            <label class="block text-sm font-medium mb-1">Texto antes del espacio en blanco</label>
                            <input
                                type="text"
                                name="completar_texto_antes"
                                value="{{ old('completar_texto_antes') }}"
                                class="w-full border rounded px-3 py-2 text-black"
                            >

                            <label class="block text-sm font-medium mb-1 mt-3">Respuesta correcta</label>
                            <input
                                type="text"
                                name="respuesta_correcta_texto"
                                value="{{ old('respuesta_correcta_texto') }}"
                                class="w-full border rounded px-3 py-2 text-black"
                            >

                            <label class="block text-sm font-medium mb-1 mt-3">Texto después del espacio en blanco</label>
                            <input
                                type="text"
                                name="completar_texto_despues"
                                value="{{ old('completar_texto_despues') }}"
                                class="w-full border rounded px-3 py-2 text-black"
                            >

                            <p class="text-xs text-slate-500 mt-2">
                                El sistema construirá automáticamente la frase con el espacio en blanco.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Tipo de pregunta</label>
                            <select name="tipo_pregunta"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 tipo-pregunta-ejercicio"
                                    data-scope="crear-pregunta-ejercicio-{{ $ejercicioItem->id_ejercicio }}"
                                    required>
                                <option value="opcion_unica">Opción única</option>
                                <option value="checklist_guiado">Opción múltiple</option>
                                <option value="verdadero_falso">Verdadero / Falso</option>
                                <option value="relacionar">Relacionar</option>
                                <option value="completar">Completar en frase</option>
                                <option value="respuesta_corta">Respuesta breve</option>
                                <option value="caso_practico">Caso de estudio</option>
                                <option value="actividad_visual_identificacion">Actividad visual de identificación</option>
                            </select>
                        </div>

                        <div class="md:col-span-2 rounded border border-blue-300 bg-blue-100 px-4 py-3 text-blue-800 text-sm ayuda-tipo-ejercicio"
                            data-scope="crear-pregunta-ejercicio-{{ $ejercicioItem->id_ejercicio }}">
                            Selecciona el tipo de pregunta. El formulario ajustará automáticamente qué campos son importantes para ese tipo.
                        </div>

                        <div class="md:col-span-2 campo-respuesta-breve-ejercicio hidden" data-scope="crear-pregunta-ejercicio-{{ $ejercicioItem->id_ejercicio }}">
                            <label class="block text-sm font-medium mb-1">Placeholder para el usuario</label>
                            <input
                                type="text"
                                name="respuesta_breve_placeholder"
                                value="{{ old('respuesta_breve_placeholder') }}"
                                class="w-full border rounded px-3 py-2 text-black"
                                placeholder="Escribe aquí la indicación breve..."
                            >

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Mínimo de caracteres</label>
                                    <input
                                        type="number"
                                        name="respuesta_breve_min"
                                        value="{{ old('respuesta_breve_min') }}"
                                        class="w-full border rounded px-3 py-2 text-black"
                                        min="0"
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Máximo de caracteres</label>
                                    <input
                                        type="number"
                                        name="respuesta_breve_max"
                                        value="{{ old('respuesta_breve_max') }}"
                                        class="w-full border rounded px-3 py-2 text-black"
                                        min="1"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2 campo-caso-estudio-ejercicio hidden" data-scope="crear-pregunta-ejercicio-{{ $ejercicioItem->id_ejercicio }}">
                            <label class="block text-sm font-medium mb-1">Placeholder para el usuario</label>
                            <input
                                type="text"
                                name="caso_placeholder"
                                value="{{ old('caso_placeholder') }}"
                                class="w-full border rounded px-3 py-2 text-black"
                                placeholder="Describe tu análisis del caso..."
                            >

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Mínimo de caracteres</label>
                                    <input
                                        type="number"
                                        name="caso_min"
                                        value="{{ old('caso_min') }}"
                                        class="w-full border rounded px-3 py-2 text-black"
                                        min="0"
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Máximo de caracteres</label>
                                    <input
                                        type="number"
                                        name="caso_max"
                                        value="{{ old('caso_max') }}"
                                        class="w-full border rounded px-3 py-2 text-black"
                                        min="1"
                                    >
                                </div>
                            </div>

                            <label class="block text-sm font-medium mb-1 mt-3">Criterios de revisión para el administrador</label>
                            <textarea
                                name="caso_criterios_revision"
                                rows="3"
                                class="w-full border rounded px-3 py-2 text-black"
                            >{{ old('caso_criterios_revision') }}</textarea>
                        </div>

                        <div class="md:col-span-2 campo-visual-identificacion-ejercicio hidden" data-scope="crear-pregunta-ejercicio-{{ $ejercicioItem->id_ejercicio }}">
                            <label class="block text-sm font-medium mb-1">Imagen de referencia</label>
                            <input
                                type="file"
                                name="visual_imagen"
                                accept="image/*"
                                class="w-full border rounded px-3 py-2 text-black bg-white"
                            >

                            <label class="block text-sm font-medium mb-1 mt-3">Texto de apoyo (opcional)</label>
                            <input
                                type="text"
                                name="visual_texto_apoyo"
                                value="{{ old('visual_texto_apoyo') }}"
                                class="w-full border rounded px-3 py-2 text-black"
                            >

                            <p class="text-xs text-slate-500 mt-2">
                                El usuario responderá escribiendo su identificación o análisis sobre la imagen.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Puntaje</label>
                            <input type="number" name="puntaje" min="0.01" step="0.01" value="1" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Orden</label>
                            <input type="number" name="orden" min="1" value="{{ $ejercicioItem->preguntas->count() + 1 }}" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Activa</label>
                            <select name="activa" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div class="md:col-span-2 campo-respuesta-correcta-ejercicio"
                            data-scope="crear-pregunta-ejercicio-{{ $ejercicioItem->id_ejercicio }}">
                            <label class="block text-sm font-medium">Respuesta correcta texto</label>
                            <textarea name="respuesta_correcta_texto" rows="2" class="w-full rounded border-gray-300 dark:bg-gray-900"></textarea>
                            <p class="text-xs text-gray-500 mt-1">
                                Úsalo sobre todo en preguntas tipo completar.
                            </p>
                        </div>

                        <div class="md:col-span-2 campo-config-json-ejercicio"
                            data-scope="crear-pregunta-ejercicio-{{ $ejercicioItem->id_ejercicio }}">
                            <label class="block text-sm font-medium">Configuración JSON</label>
                            <textarea name="configuracion_json" rows="3" class="w-full rounded border-gray-300 dark:bg-gray-900" placeholder='Ejemplo: {"modo":"libre"}'></textarea>
                            <p class="text-xs text-gray-500 mt-1">
                                Déjalo vacío por ahora, salvo que necesites configuración especial más adelante.
                            </p>
                        </div>

<input type="hidden" name="requiere_revision_manual" value="0">
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="cerrarModal('modalCrearPreguntaEjercicio{{ $ejercicioItem->id_ejercicio }}')" class="px-4 py-2 bg-gray-500 text-white rounded">
                            Cancelar
                        </button>

                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                            Guardar pregunta
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @foreach($ejercicioItem->preguntas as $preguntaEjercicio)
            <div id="modalEditarPreguntaEjercicio{{ $preguntaEjercicio->id_ejercicio_pregunta }}" class="modal-builder hidden fixed inset-0 z-50 bg-black bg-opacity-50 items-start justify-center overflow-y-auto p-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6 my-8">
                    <h3 class="text-xl font-bold mb-4">Editar pregunta de ejercicio</h3>

                    <form method="POST" action="{{ route('ejercicio_preguntas.update', $preguntaEjercicio->id_ejercicio_pregunta) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @php
                                $partesCompletarEditar = explode('[[blank]]', $preguntaEjercicio->enunciado ?? '');
                                $textoAntesEditar = $partesCompletarEditar[0] ?? '';
                                $textoDespuesEditar = $partesCompletarEditar[1] ?? '';
                            @endphp

                            <div class="campo-enunciado-normal-ejercicio" data-scope="editar-pregunta-ejercicio-{{ $preguntaEjercicio->id_ejercicio_pregunta }}">
                                <label class="block text-sm font-medium mb-1">Enunciado</label>
                                <textarea name="enunciado" rows="3" class="w-full border rounded px-3 py-2 text-black">{{ old('enunciado', $preguntaEjercicio->enunciado) }}</textarea>
                            </div>

                            <div class="campo-completar-amigable-ejercicio hidden" data-scope="editar-pregunta-ejercicio-{{ $preguntaEjercicio->id_ejercicio_pregunta }}">
                                <label class="block text-sm font-medium mb-1">Texto antes del espacio en blanco</label>
                                <input
                                    type="text"
                                    name="completar_texto_antes"
                                    value="{{ old('completar_texto_antes', $textoAntesEditar) }}"
                                    class="w-full border rounded px-3 py-2 text-black"
                                >

                                <label class="block text-sm font-medium mb-1 mt-3">Respuesta correcta</label>
                                <input
                                    type="text"
                                    name="respuesta_correcta_texto"
                                    value="{{ old('respuesta_correcta_texto', $preguntaEjercicio->respuesta_correcta_texto) }}"
                                    class="w-full border rounded px-3 py-2 text-black"
                                >

                                <label class="block text-sm font-medium mb-1 mt-3">Texto después del espacio en blanco</label>
                                <input
                                    type="text"
                                    name="completar_texto_despues"
                                    value="{{ old('completar_texto_despues', $textoDespuesEditar) }}"
                                    class="w-full border rounded px-3 py-2 text-black"
                                >

                                <p class="text-xs text-slate-500 mt-2">
                                    El sistema construirá automáticamente la frase con el espacio en blanco.
                                </p>
                            </div>

                            @php
                                $configRespuestaBreve = json_decode($preguntaEjercicio->configuracion_json ?? '{}', true);
                            @endphp

                            <div class="campo-respuesta-breve-ejercicio hidden" data-scope="editar-pregunta-ejercicio-{{ $preguntaEjercicio->id_ejercicio_pregunta }}">
                                <label class="block text-sm font-medium mb-1">Placeholder para el usuario</label>
                                <input
                                    type="text"
                                    name="respuesta_breve_placeholder"
                                    value="{{ old('respuesta_breve_placeholder', $configRespuestaBreve['placeholder'] ?? '') }}"
                                    class="w-full border rounded px-3 py-2 text-black"
                                >

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Mínimo de caracteres</label>
                                        <input
                                            type="number"
                                            name="respuesta_breve_min"
                                            value="{{ old('respuesta_breve_min', $configRespuestaBreve['min_caracteres'] ?? '') }}"
                                            class="w-full border rounded px-3 py-2 text-black"
                                            min="0"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Máximo de caracteres</label>
                                        <input
                                            type="number"
                                            name="respuesta_breve_max"
                                            value="{{ old('respuesta_breve_max', $configRespuestaBreve['max_caracteres'] ?? '') }}"
                                            class="w-full border rounded px-3 py-2 text-black"
                                            min="1"
                                        >
                                    </div>
                                </div>
                            </div>

                            @php
                                $configCaso = json_decode($preguntaEjercicio->configuracion_json ?? '{}', true);
                            @endphp

                            <div class="campo-caso-estudio-ejercicio hidden" data-scope="editar-pregunta-ejercicio-{{ $preguntaEjercicio->id_ejercicio_pregunta }}">
                                <label class="block text-sm font-medium mb-1">Placeholder para el usuario</label>
                                <input
                                    type="text"
                                    name="caso_placeholder"
                                    value="{{ old('caso_placeholder', $configCaso['placeholder'] ?? '') }}"
                                    class="w-full border rounded px-3 py-2 text-black"
                                >

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Mínimo de caracteres</label>
                                        <input
                                            type="number"
                                            name="caso_min"
                                            value="{{ old('caso_min', $configCaso['min_caracteres'] ?? '') }}"
                                            class="w-full border rounded px-3 py-2 text-black"
                                            min="0"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Máximo de caracteres</label>
                                        <input
                                            type="number"
                                            name="caso_max"
                                            value="{{ old('caso_max', $configCaso['max_caracteres'] ?? '') }}"
                                            class="w-full border rounded px-3 py-2 text-black"
                                            min="1"
                                        >
                                    </div>
                                </div>

                                <label class="block text-sm font-medium mb-1 mt-3">Criterios de revisión para el administrador</label>
                                <textarea
                                    name="caso_criterios_revision"
                                    rows="3"
                                    class="w-full border rounded px-3 py-2 text-black"
                                >{{ old('caso_criterios_revision', $configCaso['criterios_revision'] ?? '') }}</textarea>
                            </div>

                            @php
                                $configVisual = json_decode($preguntaEjercicio->configuracion_json ?? '{}', true);
                            @endphp

                            <div class="campo-visual-identificacion-ejercicio hidden" data-scope="editar-pregunta-ejercicio-{{ $preguntaEjercicio->id_ejercicio_pregunta }}">
                                <label class="block text-sm font-medium mb-1">Reemplazar imagen de referencia</label>
                                <input
                                    type="file"
                                    name="visual_imagen"
                                    accept="image/*"
                                    class="w-full border rounded px-3 py-2 text-black bg-white"
                                >

                                @if(!empty($configVisual['imagen']))
                                    <div class="mt-3">
                                        <p class="text-sm font-medium mb-2">Imagen actual</p>
                                        <img src="{{ asset('storage/' . $configVisual['imagen']) }}" class="max-h-48 rounded border">
                                    </div>
                                @endif

                                <label class="block text-sm font-medium mb-1 mt-3">Texto de apoyo (opcional)</label>
                                <input
                                    type="text"
                                    name="visual_texto_apoyo"
                                    value="{{ old('visual_texto_apoyo', $configVisual['texto_apoyo'] ?? '') }}"
                                    class="w-full border rounded px-3 py-2 text-black"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Tipo de pregunta</label>
                                <select name="tipo_pregunta"
                                        class="w-full rounded border-gray-300 dark:bg-gray-900 tipo-pregunta-ejercicio"
                                        data-scope="editar-pregunta-ejercicio-{{ $preguntaEjercicio->id_ejercicio_pregunta }}"
                                        required>
                                    <option value="opcion_unica" {{ old('tipo_pregunta', $preguntaEjercicio->tipo_pregunta) == 'opcion_unica' ? 'selected' : '' }}>Opción única</option>
                                    <option value="checklist_guiado" {{ in_array(old('tipo_pregunta', $preguntaEjercicio->tipo_pregunta), ['checklist_guiado', 'opcion_multiple', 'multiple'], true) ? 'selected' : '' }}>Opción múltiple</option>
                                    <option value="verdadero_falso" {{ old('tipo_pregunta', $preguntaEjercicio->tipo_pregunta) == 'verdadero_falso' ? 'selected' : '' }}>Verdadero/Falso</option>
                                    <option value="relacionar" {{ old('tipo_pregunta', $preguntaEjercicio->tipo_pregunta) == 'relacionar' ? 'selected' : '' }}>Relacionar</option>
                                    <option value="completar" {{ old('tipo_pregunta', $preguntaEjercicio->tipo_pregunta) == 'completar' ? 'selected' : '' }}>Completar en frase</option>
                                    <option value="respuesta_corta" {{ old('tipo_pregunta', $preguntaEjercicio->tipo_pregunta) == 'respuesta_corta' ? 'selected' : '' }}>Respuesta breve</option>
                                    <option value="caso_practico" {{ old('tipo_pregunta', $preguntaEjercicio->tipo_pregunta) == 'caso_practico' ? 'selected' : '' }}>Caso de estudio</option>
                                    <option value="actividad_visual_identificacion" {{ old('tipo_pregunta', $preguntaEjercicio->tipo_pregunta) == 'actividad_visual_identificacion' ? 'selected' : '' }}>Actividad visual de identificación</option>
                                </select>
                            </div>

                            <div class="md:col-span-2 rounded border border-blue-300 bg-blue-100 px-4 py-3 text-blue-800 text-sm ayuda-tipo-ejercicio"
                                data-scope="editar-pregunta-ejercicio-{{ $preguntaEjercicio->id_ejercicio_pregunta }}">
                                El formulario ajustará automáticamente los campos más relevantes según el tipo de pregunta.
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Puntaje</label>
                                <input type="number" name="puntaje" min="0.01" step="0.01" value="{{ old('puntaje', $preguntaEjercicio->puntaje) }}" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Orden</label>
                                <input type="number" name="orden" min="1" value="{{ old('orden', $preguntaEjercicio->orden) }}" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Activa</label>
                                <select name="activa" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                                    <option value="1" {{ old('activa', $preguntaEjercicio->activa) == '1' ? 'selected' : '' }}>Sí</option>
                                    <option value="0" {{ old('activa', $preguntaEjercicio->activa) == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>

                            <div class="md:col-span-2 campo-respuesta-correcta-ejercicio"
                                data-scope="editar-pregunta-ejercicio-{{ $preguntaEjercicio->id_ejercicio_pregunta }}">
                                <label class="block text-sm font-medium">Respuesta correcta texto</label>
                                <textarea name="respuesta_correcta_texto" rows="2" class="w-full rounded border-gray-300 dark:bg-gray-900">{{ old('respuesta_correcta_texto', $preguntaEjercicio->respuesta_correcta_texto) }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">
                                    Úsalo sobre todo en preguntas tipo completar.
                                </p>
                            </div>

                            <div class="md:col-span-2 campo-config-json-ejercicio"
                                data-scope="editar-pregunta-ejercicio-{{ $preguntaEjercicio->id_ejercicio_pregunta }}">
                                <label class="block text-sm font-medium">Configuración JSON</label>
                                <textarea name="configuracion_json" rows="3" class="w-full rounded border-gray-300 dark:bg-gray-900">{{ old('configuracion_json', $preguntaEjercicio->configuracion_json) }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">
                                    Déjalo vacío por ahora, salvo que necesites configuración especial más adelante.
                                </p>
                            </div>

<input type="hidden" name="requiere_revision_manual" value="0">
                        </div>

                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" @click="cerrarModal('modalEditarPreguntaEjercicio{{ $preguntaEjercicio->id_ejercicio_pregunta }}')" class="px-4 py-2 bg-gray-500 text-white rounded">
                                Cancelar
                            </button>

                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                                Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>



            <div id="modalCrearOpcionEjercicio{{ $preguntaEjercicio->id_ejercicio_pregunta }}" class="modal-builder hidden fixed inset-0 z-50 bg-black bg-opacity-50 items-start justify-center overflow-y-auto p-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 my-8">
                    <h3 class="text-xl font-bold mb-4">Nueva opción de ejercicio</h3>

                    <p class="mb-4 text-sm text-slate-600 dark:text-slate-300">
                        Usa este formulario según el tipo de pregunta:
                        <br>• <strong>Opción única / múltiple / verdadero-falso:</strong> usa “¿Es correcta?”
                        <br>• <strong>Relacionar:</strong> usa “Lado” y “Clave relación”
                        <br>• <strong>Ordenar:</strong> solo importa el texto y el orden
                    </p>

                    <form method="POST" action="{{ route('ejercicio_preguntas.opciones.store', $preguntaEjercicio->id_ejercicio_pregunta) }}">
                        @csrf

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium">Opción</label>
                                <textarea name="opcion" rows="2" class="w-full rounded border-gray-300 dark:bg-gray-900" required></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Lado</label>
                                <select name="lado" class="w-full rounded border-gray-300 dark:bg-gray-900">
                                    <option value="">Sin lado</option>
                                    <option value="izquierda">Izquierda</option>
                                    <option value="derecha">Derecha</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Clave relación</label>
                                <input type="text" name="clave_relacion" class="w-full rounded border-gray-300 dark:bg-gray-900">
                            </div>

                            <div>
                                <label class="block text-sm font-medium">¿Es correcta?</label>
                                <select name="es_correcta" class="w-full rounded border-gray-300 dark:bg-gray-900">
                                    <option value="">No aplica</option>
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Orden</label>
                                <input type="number" name="orden" min="1" value="{{ $preguntaEjercicio->opciones->count() + 1 }}" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" @click="cerrarModal('modalCrearOpcionEjercicio{{ $preguntaEjercicio->id_ejercicio_pregunta }}')" class="px-4 py-2 bg-gray-500 text-white rounded">
                                Cancelar
                            </button>

                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">
                                Guardar opción
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @foreach($preguntaEjercicio->opciones as $opcionEjercicio)
                <div id="modalEditarOpcionEjercicio{{ $opcionEjercicio->id_ejercicio_opcion }}" class="modal-builder hidden fixed inset-0 z-50 bg-black bg-opacity-50 items-start justify-center overflow-y-auto p-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 my-8">
                        <h3 class="text-xl font-bold mb-4">Editar opción de ejercicio</h3>

                        <p class="mb-4 text-sm text-slate-600 dark:text-slate-300">
                            Revisa este registro según el tipo de pregunta:
                            <br>• <strong>Opción única / múltiple / verdadero-falso:</strong> usa “¿Es correcta?”
                            <br>• <strong>Relacionar:</strong> usa “Lado” y “Clave relación”
                            <br>• <strong>Ordenar:</strong> solo importa el texto y el orden
                        </p>

                        <form method="POST" action="{{ route('ejercicio_opciones.update', $opcionEjercicio->id_ejercicio_opcion) }}">
                            @csrf
                            @method('PUT')

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium">Opción</label>
                                    <textarea name="opcion" rows="2" class="w-full rounded border-gray-300 dark:bg-gray-900" required>{{ old('opcion', $opcionEjercicio->opcion) }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium">Lado</label>
                                    <select name="lado" class="w-full rounded border-gray-300 dark:bg-gray-900">
                                        <option value="" {{ old('lado', $opcionEjercicio->lado) === null || old('lado', $opcionEjercicio->lado) === '' ? 'selected' : '' }}>Sin lado</option>
                                        <option value="izquierda" {{ old('lado', $opcionEjercicio->lado) == 'izquierda' ? 'selected' : '' }}>Izquierda</option>
                                        <option value="derecha" {{ old('lado', $opcionEjercicio->lado) == 'derecha' ? 'selected' : '' }}>Derecha</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium">Clave relación</label>
                                    <input type="text" name="clave_relacion" value="{{ old('clave_relacion', $opcionEjercicio->clave_relacion) }}" class="w-full rounded border-gray-300 dark:bg-gray-900">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium">¿Es correcta?</label>
                                    <select name="es_correcta" class="w-full rounded border-gray-300 dark:bg-gray-900">
                                        <option value="" {{ old('es_correcta', $opcionEjercicio->es_correcta) === null || old('es_correcta', $opcionEjercicio->es_correcta) === '' ? 'selected' : '' }}>No aplica</option>
                                        <option value="0" {{ old('es_correcta', $opcionEjercicio->es_correcta) == '0' ? 'selected' : '' }}>No</option>
                                        <option value="1" {{ old('es_correcta', $opcionEjercicio->es_correcta) == '1' ? 'selected' : '' }}>Sí</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium">Orden</label>
                                    <input type="number" name="orden" min="1" value="{{ old('orden', $opcionEjercicio->orden) }}" class="w-full rounded border-gray-300 dark:bg-gray-900" required>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-2">
                                <button type="button" @click="cerrarModal('modalEditarOpcionEjercicio{{ $opcionEjercicio->id_ejercicio_opcion }}')" class="px-4 py-2 bg-gray-500 text-white rounded">
                                    Cancelar
                                </button>

                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">
                                    Guardar cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        @endforeach
    @endforeach
@endforeach

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">

        function abrirModal(id) {
            const modal = document.getElementById(id);

            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function cerrarModal(id) {
            const modal = document.getElementById(id);

            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal-builder').forEach(function (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                });
            }
        });

        function abrirTodos() {
            document.querySelectorAll('.modulo-card').forEach(function (detalle) {
                detalle.open = true;
            });
        }

        function cerrarTodos() {
            document.querySelectorAll('.modulo-card').forEach(function (detalle) {
                detalle.open = false;
            });
        }

        document.getElementById('buscarModulo')?.addEventListener('input', function () {
            let texto = this.value.toLowerCase();

            document.querySelectorAll('.modulo-card').forEach(function (card) {
                let titulo = card.querySelector('.modulo-titulo')?.innerText.toLowerCase() || '';

                if (titulo.includes(texto)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        function actualizarFormularioPreguntaEjercicio(select) {
            const scope = select.dataset.scope;
            const tipo = select.value;

            const ayuda = document.querySelector('.ayuda-tipo-ejercicio[data-scope="' + scope + '"]');
            const campoRespuesta = document.querySelector('.campo-respuesta-correcta-ejercicio[data-scope="' + scope + '"]');
            const campoJson = document.querySelector('.campo-config-json-ejercicio[data-scope="' + scope + '"]');
            const campoRevision = document.querySelector('.campo-revision-manual-ejercicio[data-scope="' + scope + '"]');

            const campoEnunciadoNormal = document.querySelector('.campo-enunciado-normal-ejercicio[data-scope="' + scope + '"]');
            const campoCompletarAmigable = document.querySelector('.campo-completar-amigable-ejercicio[data-scope="' + scope + '"]');

            const campoRespuestaBreve = document.querySelector('.campo-respuesta-breve-ejercicio[data-scope="' + scope + '"]');
            const campoCasoEstudio = document.querySelector('.campo-caso-estudio-ejercicio[data-scope="' + scope + '"]');
            const campoVisualIdentificacion = document.querySelector('.campo-visual-identificacion-ejercicio[data-scope="' + scope + '"]');

            if (campoRespuesta) campoRespuesta.style.display = '';
            if (campoJson) campoJson.style.display = '';
            if (campoRevision) campoRevision.style.display = '';

            if (campoEnunciadoNormal) campoEnunciadoNormal.classList.remove('hidden');
            if (campoCompletarAmigable) campoCompletarAmigable.classList.add('hidden');
            if (campoRespuestaBreve) campoRespuestaBreve.classList.add('hidden');
            if (campoCasoEstudio) campoCasoEstudio.classList.add('hidden');
            if (campoVisualIdentificacion) campoVisualIdentificacion.classList.add('hidden');

            if (tipo === 'completar') {
                if (ayuda) {
                    ayuda.innerHTML = 'Completar en frase: usa los campos amigables para texto antes, respuesta correcta y texto después.';
                }

                if (campoJson) campoJson.style.display = 'none';
                if (campoEnunciadoNormal) campoEnunciadoNormal.classList.add('hidden');
                if (campoCompletarAmigable) campoCompletarAmigable.classList.remove('hidden');

            } else if (tipo === 'respuesta_corta') {
                if (ayuda) {
                    ayuda.innerHTML = 'Respuesta breve: usa placeholder y límites de caracteres para orientar al usuario.';
                }

                if (campoRespuesta) campoRespuesta.style.display = 'none';
                if (campoJson) campoJson.style.display = 'none';
                if (campoEnunciadoNormal) campoEnunciadoNormal.classList.remove('hidden');
                if (campoCompletarAmigable) campoCompletarAmigable.classList.add('hidden');
                if (campoRespuestaBreve) campoRespuestaBreve.classList.remove('hidden');
                if (campoCasoEstudio) campoCasoEstudio.classList.add('hidden');
                if (campoVisualIdentificacion) campoVisualIdentificacion.classList.add('hidden');

            } else if (tipo === 'caso_practico') {
                if (ayuda) {
                    ayuda.innerHTML = 'Caso de estudio: permite una respuesta amplia y requiere revisión manual del administrador.';
                }

                if (campoRespuesta) campoRespuesta.style.display = 'none';
                if (campoJson) campoJson.style.display = 'none';
                if (campoEnunciadoNormal) campoEnunciadoNormal.classList.remove('hidden');
                if (campoCompletarAmigable) campoCompletarAmigable.classList.add('hidden');
                if (campoRespuestaBreve) campoRespuestaBreve.classList.add('hidden');
                if (campoCasoEstudio) campoCasoEstudio.classList.remove('hidden');
                if (campoVisualIdentificacion) campoVisualIdentificacion.classList.add('hidden');

            } else if (tipo === 'checklist_guiado') {
                if (ayuda) {
                    ayuda.innerHTML = 'Opción múltiple: agrega varias opciones y marca todas las respuestas correctas.';
                }

                if (campoRespuesta) campoRespuesta.style.display = 'none';
                if (campoJson) campoJson.style.display = 'none';
                if (campoEnunciadoNormal) campoEnunciadoNormal.classList.remove('hidden');
                if (campoCompletarAmigable) campoCompletarAmigable.classList.add('hidden');
                if (campoRespuestaBreve) campoRespuestaBreve.classList.add('hidden');
                if (campoCasoEstudio) campoCasoEstudio.classList.add('hidden');
                if (campoVisualIdentificacion) campoVisualIdentificacion.classList.add('hidden');

            } else if (tipo === 'actividad_visual_identificacion') {
                if (ayuda) {
                    ayuda.innerHTML = 'Actividad visual de identificación: sube una imagen y un texto de apoyo. El usuario responderá escribiendo su identificación o análisis en una caja de texto.';
                }

                if (campoRespuesta) campoRespuesta.style.display = 'none';
                if (campoJson) campoJson.style.display = 'none';
                if (campoEnunciadoNormal) campoEnunciadoNormal.classList.remove('hidden');
                if (campoCompletarAmigable) campoCompletarAmigable.classList.add('hidden');
                if (campoRespuestaBreve) campoRespuestaBreve.classList.add('hidden');
                if (campoCasoEstudio) campoCasoEstudio.classList.add('hidden');
                if (campoVisualIdentificacion) campoVisualIdentificacion.classList.remove('hidden');

            } else if (tipo === 'relacionar') {
                if (ayuda) {
                    ayuda.innerHTML = 'Relacionar: configura las parejas desde las <strong>opciones</strong>. El sistema mezclará automáticamente el lado derecho cuando lo vea el usuario.';
                }

                if (campoRespuesta) campoRespuesta.style.display = 'none';
                if (campoJson) campoJson.style.display = 'none';
                if (campoRespuestaBreve) campoRespuestaBreve.classList.add('hidden');
                if (campoCasoEstudio) campoCasoEstudio.classList.add('hidden');
                if (campoVisualIdentificacion) campoVisualIdentificacion.classList.add('hidden');

            } else {
                if (ayuda) {
                    ayuda.innerHTML = 'Pregunta de selección: la corrección se define principalmente desde las <strong>opciones</strong>.';
                }

                if (campoRespuesta) campoRespuesta.style.display = 'none';
                if (campoJson) campoJson.style.display = 'none';
                if (campoRespuestaBreve) campoRespuestaBreve.classList.add('hidden');
                if (campoCasoEstudio) campoCasoEstudio.classList.add('hidden');
                if (campoVisualIdentificacion) campoVisualIdentificacion.classList.add('hidden');
            }
        }

        document.querySelectorAll('.tipo-pregunta-ejercicio').forEach(function (select) {
            select.addEventListener('change', function () {
                actualizarFormularioPreguntaEjercicio(this);
            });

            actualizarFormularioPreguntaEjercicio(select);
        });

        function actualizarFormularioPreguntaEvaluacion(select) {
        const scope = select.dataset.scope;
        const tipo = select.value;

        const ayuda = document.querySelector('.ayuda-tipo-evaluacion[data-scope="' + scope + '"]');
        const campoPreguntaNormal = document.querySelector('.campo-pregunta-normal-evaluacion[data-scope="' + scope + '"]');
        const campoCompletar = document.querySelector('.campo-completar-evaluacion[data-scope="' + scope + '"]');
        const campoRespuestaBreve = document.querySelector('.campo-respuesta-breve-evaluacion[data-scope="' + scope + '"]');
        const campoRevision = document.querySelector('.campo-revision-manual-evaluacion[data-scope="' + scope + '"]');

        if (campoPreguntaNormal) campoPreguntaNormal.classList.remove('hidden');
        if (campoCompletar) campoCompletar.classList.add('hidden');
        if (campoRespuestaBreve) campoRespuestaBreve.classList.add('hidden');
        if (campoRevision) campoRevision.classList.remove('hidden');

        if (tipo === 'completar') {
            if (ayuda) {
                ayuda.innerHTML = 'Completar en frase: usa texto antes, respuesta correcta y texto después. El sistema construirá la frase automáticamente.';
            }

            if (campoPreguntaNormal) campoPreguntaNormal.classList.add('hidden');
            if (campoCompletar) campoCompletar.classList.remove('hidden');
            if (campoRespuestaBreve) campoRespuestaBreve.classList.add('hidden');

        } else if (tipo === 'respuesta_corta') {
            if (ayuda) {
                ayuda.innerHTML = 'Respuesta breve: el usuario escribirá una respuesta corta. Requiere revisión manual para asignar puntaje.';
            }

            if (campoPreguntaNormal) campoPreguntaNormal.classList.remove('hidden');
            if (campoCompletar) campoCompletar.classList.add('hidden');
            if (campoRespuestaBreve) campoRespuestaBreve.classList.remove('hidden');

        } else if (tipo === 'checklist_guiado') {
            if (ayuda) {
                ayuda.innerHTML = 'Opción múltiple: agrega varias opciones y marca todas las respuestas correctas.';
            }

        } else {
            if (ayuda) {
                ayuda.innerHTML = 'Pregunta de selección: la corrección se define desde las opciones.';
            }
        }
    }

    document.querySelectorAll('.tipo-pregunta-evaluacion').forEach(function (select) {
        select.addEventListener('change', function () {
            actualizarFormularioPreguntaEvaluacion(this);
        });

        actualizarFormularioPreguntaEvaluacion(select);
    });


    </script>

    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">

    <script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

    <style>
        .editor-contenido-seccion-modulo .ql-editor {
            min-height: 220px;
            font-size: 15px;
            line-height: 1.7;
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
        }

        .editor-contenido-seccion-modulo .ql-container {
            border-radius: 0 0 0.375rem 0.375rem;
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
        }

        .panel-ajuste-imagen-teoria button:hover {
            background: #dbeafe;
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

        .contenido-teoria-render {
            padding: 0 !important;
            margin: 0 !important;
            min-height: 0 !important;
            line-height: 1.55 !important;
        }

        .contenido-teoria-render > :first-child {
            margin-top: 0 !important;
        }

        .contenido-teoria-render > :last-child {
            margin-bottom: 0 !important;
        }

        .contenido-teoria-render p {
            margin: 0 0 0.35rem 0 !important;
        }

        .contenido-teoria-render p:empty {
            display: none !important;
        }

        .contenido-teoria-render h1,
        .contenido-teoria-render h2,
        .contenido-teoria-render h3,
        .contenido-teoria-render h4,
        .contenido-teoria-render h5,
        .contenido-teoria-render h6 {
            margin: 0.75rem 0 0.35rem 0 !important;
            line-height: 1.2 !important;
        }

        .contenido-teoria-render ul,
        .contenido-teoria-render ol {
            margin: 0.35rem 0 0.5rem 1.25rem !important;
            padding-left: 1.25rem !important;
        }

        .contenido-teoria-render img {
            max-width: 100%;
            height: auto;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 0 !important;
        }

        .esf-builder-user-preview .curso-capacitate-content-ajustado {
            display: flex !important;
            flex-direction: column !important;
            gap: 0 !important;
        }

        .esf-builder-user-preview .curso-capacitate-content-ajustado > * {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        .esf-builder-user-preview .curso-capacitate-content-ajustado > * + * {
            margin-top: 0.85rem !important;
        }

        .esf-builder-user-preview .curso-capacitate-page {
            clear: none !important;
            overflow: visible !important;
            padding: 0 !important;
            margin: 0 !important;
            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            min-height: 0 !important;
        }

        .esf-builder-user-preview .curso-capacitate-page > div:first-child,
        .esf-builder-user-preview .curso-capacitate-page > .curso-capacitate-section-title {
            clear: none !important;
        }

        .esf-builder-user-preview .curso-capacitate-section-title {
            border-top: 2px solid rgba(14, 116, 144, 0.32) !important;
            border-bottom: 2px solid rgba(14, 116, 144, 0.32) !important;
            padding: 0.48rem 0 !important;
            margin: 0 0 0.82rem 0 !important;
        }

        .esf-builder-user-preview .curso-capacitate-section-title h2 {
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.08 !important;
        }

        .esf-builder-user-preview .contenido-teoria-render {
            display: block !important;
            clear: none !important;
            overflow: visible !important;
            margin: 0 !important;
            padding: 0 !important;
            min-height: 0 !important;
            line-height: 1.62 !important;
        }

        .esf-builder-user-preview .contenido-teoria-render::after {
            content: none !important;
            display: none !important;
            clear: none !important;
        }

        .esf-builder-user-preview .contenido-teoria-render > :first-child {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .esf-builder-user-preview .contenido-teoria-render > :last-child {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }

        .esf-builder-user-preview .contenido-teoria-render p {
            margin-top: 0 !important;
            margin-bottom: 0.42rem !important;
        }

        .esf-builder-user-preview .contenido-teoria-render p:empty {
            display: none !important;
            margin: 0 !important;
            padding: 0 !important;
            height: 0 !important;
            line-height: 0 !important;
        }

        .esf-builder-user-preview .curso-capacitate-activity-card {
            clear: none !important;
        }

        .esf-builder-user-preview .contenido-teoria-render img[style*="float: left"],
        .esf-builder-user-preview .contenido-teoria-render img[style*="float:left"],
        .esf-builder-user-preview .contenido-teoria-render img[style*="float: right"],
        .esf-builder-user-preview .contenido-teoria-render img[style*="float:right"] {
            margin-bottom: 0.45rem !important;
        }

        .esf-builder-user-preview .contenido-teoria-render img[style*="float: left"],
        .esf-builder-user-preview .contenido-teoria-render img[style*="float:left"],
        .esf-builder-user-preview .contenido-teoria-render img[style*="float: right"],
        .esf-builder-user-preview .contenido-teoria-render img[style*="float:right"] {
            margin-bottom: 0.45rem !important;
        }
    </style>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        const urlSubidaImagenTeoriaModulo = "{{ route('capacitacion_modulos.teoria.imagen') }}";
        const tokenCsrfTeoriaModulo = "{{ csrf_token() }}";

        function mostrarAvisoLocalModulo(elemento, mensaje) {
            if (!elemento) {
                return;
            }

            const contenedor = elemento.closest('[data-bloque-pagina-seccion], .seccion-modulo-item, .esf-admin-modal-card, form') || elemento.parentElement;

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

        function avisarImagenEditorTeoriaModulo(quill, mensaje) {
    if (typeof mostrarAvisoEditorModulo === 'function') {
        mostrarAvisoEditorModulo(quill, mensaje);
        return;
    }

    alert(mensaje);
}

function obtenerImagenSeleccionadaTeoriaModulo(quill, silencioso = false) {
    const editor = quill && quill.container ? quill.container : null;

    if (editor && editor.__imagenSeleccionadaTeoriaModulo) {
        return editor.__imagenSeleccionadaTeoriaModulo;
    }

    const rango = quill.getSelection(true);

    if (!rango) {
        if (!silencioso) {
            avisarImagenEditorTeoriaModulo(quill, 'Primero haz clic sobre una imagen dentro del editor.');
        }

        return null;
    }

    const leaf = quill.getLeaf(rango.index);

    if (!leaf || !leaf[0] || !leaf[0].domNode) {
        if (!silencioso) {
            avisarImagenEditorTeoriaModulo(quill, 'Primero haz clic sobre una imagen dentro del editor.');
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
        avisarImagenEditorTeoriaModulo(quill, 'Primero haz clic sobre una imagen dentro del editor.');
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

            return item.querySelector('.input-contenido-seccion-modulo-tocado');
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

        function normalizarHtmlContenidoSeccionModulo(html) {
            const contenido = String(html ?? '').trim();

            if (contenido === '' || contenido === '<p><br></p>') {
                return '';
            }

            return contenido;
        }

        function sincronizarEditorSeccionModulo(editor, forzarLecturaHtml = false) {
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
                return;
            }

            if (forzarLecturaHtml) {
                const htmlDirecto = normalizarHtmlContenidoSeccionModulo(editor.innerHTML);

                if (htmlDirecto !== '') {
                    campoContenido.value = htmlDirecto;
                }
            }
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
                sincronizarEditorSeccionModulo(editor);

                if (source === 'user') {
                    marcarContenidoSeccionModuloTocado(editor);
                }
            });

            quill.on('selection-change', function () {
                sincronizarEditorSeccionModulo(editor);
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

            toolbar.addHandler('columnas', function () {
                marcarContenidoSeccionModuloTocado(editor);
                aplicarColumnasTeoriaModulo(quill);
                sincronizarEditorSeccionModulo(editor);
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
            const contenidoInicial = campoContenido ? campoContenido.value : '';

            editor.innerHTML = '';

            const quill = new Quill(editor, {
                theme: 'snow',
                modules: obtenerModulosQuillTeoria()
            });

            editor.__quill = quill;
            editor.dataset.quillInicializado = '1';
            quill.enable(true);
            configurarEventosEditorSeccionModulo(editor, quill);

            if (contenidoInicial.trim() !== '') {
                quill.clipboard.dangerouslyPasteHTML(0, contenidoInicial);
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
                sincronizarEditorSeccionModulo(editor, true);
            });
        }

        function escapeHtmlModuloBuilder(texto) {
            return String(texto ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function obtenerNivelSeccionNuevoModuloDesdeBloque(bloque) {
            const selectNivel = bloque.querySelector('select[name="secciones_nivel[]"]');
            const inputNivel = bloque.querySelector('input[name="secciones_nivel[]"]');

            if (selectNivel) {
                return selectNivel.value;
            }

            return inputNivel ? inputNivel.value : '1';
        }

        function obtenerTituloSeccionPrincipalAnteriorNuevoModulo(bloque) {
            let anterior = bloque.previousElementSibling;

            while (anterior && anterior.classList.contains('seccion-modulo-item')) {
                if (obtenerNivelSeccionNuevoModuloDesdeBloque(anterior) === '1') {
                    const inputTitulo = anterior.querySelector('input[name="secciones_titulo[]"]');

                    return inputTitulo && inputTitulo.value.trim() !== ''
                        ? inputTitulo.value.trim()
                        : 'Sección principal';
                }

                anterior = anterior.previousElementSibling;
            }

            return 'Sección principal';
        }

        function actualizarSelectorNivelSeccionNuevoModulo(select) {
            const bloque = select.closest('[data-bloque-pagina-seccion]');

            if (!bloque) {
                return;
            }

            const nivelSeleccionado = select.value === '2' ? '2' : '1';

            const botonSubseccion = bloque.querySelector('.boton-agregar-subseccion');

            if (botonSubseccion) {
                botonSubseccion.classList.toggle('hidden', nivelSeleccionado !== '1');
            }

            const tituloEncabezado = bloque.querySelector(':scope > div.flex strong');

            if (tituloEncabezado) {
                if (nivelSeleccionado === '2') {
                    tituloEncabezado.textContent = 'Subsección de: ' + obtenerTituloSeccionPrincipalAnteriorNuevoModulo(bloque);
                } else {
                    tituloEncabezado.textContent = 'Página / sección';
                }
            }

            const labelTitulo = bloque.querySelector('[data-label-titulo-seccion-nuevo-modulo]');

            if (labelTitulo) {
                labelTitulo.textContent = nivelSeleccionado === '2'
                    ? 'Título de la subsección'
                    : 'Título de la página';
            }

            bloque.classList.toggle('ml-6', nivelSeleccionado === '2');

            bloque.classList.toggle('border-emerald-300', nivelSeleccionado === '2');
            bloque.classList.toggle('bg-emerald-50', nivelSeleccionado === '2');

            bloque.classList.toggle('border-gray-300', nivelSeleccionado !== '2');
            bloque.classList.toggle('bg-white', nivelSeleccionado !== '2');
            bloque.classList.toggle('dark:bg-gray-800', nivelSeleccionado !== '2');
        }

        function agregarSeccionModulo(contenedorId) {
            const contenedor = document.getElementById(contenedorId);

            if (!contenedor) {
                return;
            }

            const numeroSeccion = contenedor.querySelectorAll('.seccion-modulo-item').length + 1;

            const html = `
                <div class="seccion-modulo-item rounded border border-gray-300 bg-white dark:bg-gray-800 p-4"
                    data-bloque-pagina-seccion>
                    <input type="hidden" name="secciones_id[]" value="">
                    <input type="hidden" name="secciones_padre[]" value="">

                    <div class="flex justify-between items-center mb-2 gap-2">
                        <strong>Página / sección ${numeroSeccion}</strong>

                        <div class="flex flex-wrap gap-2 justify-end">
                            <button type="button"
                                    @click="agregarSubseccionModuloNuevo($el)"
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

                    <label class="block text-sm font-medium" data-label-titulo-seccion-nuevo-modulo>
                        Título de la página
                    </label>

                    <input type="text"
                        name="secciones_titulo[]"
                        placeholder="Ejemplo: Conceptos básicos"
                        class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100 mb-3">

                    <label class="block text-sm font-medium">
                        Tipo de sección
                    </label>

                    <select name="secciones_nivel[]"
                            @change="actualizarSelectorNivelSeccionNuevoModulo($el)"
                            class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100 mb-3">
                        <option value="1" selected>Sección principal</option>
                        <option value="2">Subsección</option>
                    </select>

                    <div class="acciones-contenido-subseccion mt-3 rounded border border-emerald-300 bg-emerald-50 p-3">
                        <p class="text-sm font-semibold text-emerald-900">
                            Contenido de esta sección
                        </p>

                        <p class="text-xs text-emerald-800 mt-1">
                            Ya puedes agregar recursos, ejercicios o evaluaciones. El sistema guardará automáticamente el módulo y esta sección.
                        </p>

                        <div class="botones-subseccion-contenido mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
                            <button type="button"
                                    data-accion-crear-modulo="recurso"
                                    class="block text-center px-3 py-2 bg-blue-600 text-white rounded text-xs">
                                Seleccionar archivo
                            </button>

                            <button type="button"
                                    data-accion-crear-modulo="ejercicio"
                                    class="block text-center px-3 py-2 bg-emerald-600 text-white rounded text-xs">
                                Agregar ejercicio
                            </button>

                            <button type="button"
                                    data-accion-crear-modulo="evaluacion"
                                    class="block text-center px-3 py-2 bg-purple-600 text-white rounded text-xs">
                                Agregar evaluación
                            </button>
                        </div>
                    </div>

                    <label class="block text-sm font-medium mb-1 mt-3">
                        Contenido escrito
                    </label>

                    <input type="hidden"
                        name="secciones_contenido[]"
                        class="input-contenido-seccion-modulo">

                    <div class="editor-contenido-seccion-modulo bg-white text-black rounded border border-gray-300"
                        style="min-height: 260px;"></div>
                </div>
            `;

            contenedor.insertAdjacentHTML('beforeend', html);
            inicializarEditoresSeccionesModulo();
        }

    function agregarSubseccionModuloNuevo(boton) {
        const bloquePadre = boton.closest('.seccion-modulo-item');

        if (!bloquePadre) {
            return;
        }

        const inputTituloPadre = bloquePadre.querySelector('input[name="secciones_titulo[]"]');

        const tituloPadre = inputTituloPadre && inputTituloPadre.value.trim() !== ''
            ? inputTituloPadre.value.trim()
            : 'Sección principal';

        const html = `
            <div class="seccion-modulo-item rounded border border-gray-300 bg-white dark:bg-gray-800 p-4 ml-6"
                data-bloque-pagina-seccion>
                <input type="hidden" name="secciones_id[]" value="">
                <input type="hidden" name="secciones_padre[]" value="">

                <div class="flex justify-between items-center mb-2 gap-2">
                    <strong>Subsección de: ${escapeHtmlModuloBuilder(tituloPadre)}</strong>

                    <div class="flex flex-wrap gap-2 justify-end">
                        <button type="button"
                                @click="agregarSubseccionModuloNuevo($el)"
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

                <label class="block text-sm font-medium" data-label-titulo-seccion-nuevo-modulo>
                    Título de la subsección
                </label>

                <input type="text"
                    name="secciones_titulo[]"
                    placeholder="Ejemplo: Partes de un montacargas"
                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100 mb-3">

                <label class="block text-sm font-medium">
                    Tipo de sección
                </label>

                <select name="secciones_nivel[]"
                        @change="actualizarSelectorNivelSeccionNuevoModulo($el)"
                        class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100 mb-3">
                    <option value="1">Sección principal</option>
                    <option value="2" selected>Subsección</option>
                </select>

                <div class="acciones-contenido-subseccion mt-3 rounded border border-emerald-300 bg-emerald-50 p-3">
                    <p class="text-sm font-semibold text-emerald-900">
                        Contenido de esta sección
                    </p>

                    <p class="text-xs text-emerald-800 mt-1">
                        Ya puedes agregar recursos, ejercicios o evaluaciones. El sistema guardará automáticamente el módulo y esta subsección.
                    </p>

                    <div class="botones-subseccion-contenido mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
                        <button type="button"
                                data-accion-crear-modulo="recurso"
                                class="block text-center px-3 py-2 bg-blue-600 text-white rounded text-xs">
                            Seleccionar archivo
                        </button>

                        <button type="button"
                                data-accion-crear-modulo="ejercicio"
                                class="block text-center px-3 py-2 bg-emerald-600 text-white rounded text-xs">
                            Agregar ejercicio
                        </button>

                        <button type="button"
                                data-accion-crear-modulo="evaluacion"
                                class="block text-center px-3 py-2 bg-purple-600 text-white rounded text-xs">
                            Agregar evaluación
                        </button>
                    </div>
                </div>

                <label class="block text-sm font-medium mb-1 mt-3">
                    Contenido escrito
                </label>

                <input type="hidden"
                    name="secciones_contenido[]"
                    class="input-contenido-seccion-modulo">

                <div class="editor-contenido-seccion-modulo bg-white text-black rounded border border-gray-300"
                    style="min-height: 260px;"></div>
            </div>
        `;

        let referenciaInsercion = bloquePadre.nextElementSibling;

        while (referenciaInsercion && referenciaInsercion.classList.contains('seccion-modulo-item')) {
            const nivelReferencia = obtenerNivelSeccionNuevoModuloDesdeBloque(referenciaInsercion);

            if (nivelReferencia === '1') {
                break;
            }

            referenciaInsercion = referenciaInsercion.nextElementSibling;
        }

        if (referenciaInsercion) {
            referenciaInsercion.insertAdjacentHTML('beforebegin', html);
        } else if (bloquePadre.parentElement) {
            bloquePadre.parentElement.insertAdjacentHTML('beforeend', html);
        }

        inicializarEditoresSeccionesModulo();
    }

    function eliminarSeccionModulo(boton) {
        const item = boton.closest('.seccion-modulo-item');

        if (item) {
            item.remove();
        }
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
    });

    function obtenerBloquesSeccionesModalCrearModulo() {
        const modal = document.getElementById('modalCrearModulo');

        if (!modal) {
            return [];
        }

        return Array.from(modal.querySelectorAll('[data-bloque-pagina-seccion]'));
    }


    function prepararAccionDespuesCrearModulo(event, boton) {
        event.preventDefault();

        const bloque = boton.closest('[data-bloque-pagina-seccion]');

        if (!bloque) {
            return;
        }

        const inputTitulo = bloque.querySelector('input[name="secciones_titulo[]"]');

        if (!inputTitulo || inputTitulo.value.trim() === '') {
            mostrarAvisoLocalModulo(inputTitulo, 'Primero escribe el título de la sección.');
            if (inputTitulo) {
                inputTitulo.focus();
            }
            return;
        }

        const formulario = boton.closest('form');

        if (!formulario) {
            return;
        }

        const inputTituloModulo = formulario.querySelector('input[name="titulo"]');

        if (!inputTituloModulo || inputTituloModulo.value.trim() === '') {
            mostrarAvisoLocalModulo(inputTituloModulo, 'Primero escribe el título del módulo.');
            if (inputTituloModulo) {
                inputTituloModulo.focus();
            }
            return;
        }

        const bloques = obtenerBloquesSeccionesModalCrearModulo();
        const indice = bloques.indexOf(bloque);

        if (indice < 0) {
            return;
        }

        const inputAccion = document.getElementById('accionDespuesCrearModulo');
        const inputIndice = document.getElementById('indiceSeccionDespuesCrearModulo');

        inputAccion.value = boton.dataset.accionCrearModulo;
        inputIndice.value = indice;

        if (typeof sincronizarEditoresSeccionesModulo === 'function') {
            sincronizarEditoresSeccionesModulo();
        }

        formulario.submit();
    }


    document.addEventListener('click', function (event) {
        const boton = event.target.closest('[data-accion-crear-modulo]');

        if (!boton) {
            return;
        }

        prepararAccionDespuesCrearModulo(event, boton);
    });


    </script>
</x-app-layout>