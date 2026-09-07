<x-app-layout>

    <div class="py-6 sm:py-8">
        <div class="curso-capacitate-wrapper mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            @php
                $estadoModuloActual = $avanceModulo->estado ?? 'pendiente';
                $capacitacionFinalizadaParaUsuario = (bool) ($capacitacionFinalizadaParaUsuario ?? in_array($miCapacitacion->estado, ['vencida', 'reprobada', 'aprobada', 'cancelada'], true));

                $estadoModuloClase = match($estadoModuloActual) {
                    'completado' => 'border-green-300 bg-green-100 text-green-800',
                    'reprobado' => 'border-red-300 bg-red-100 text-red-800',
                    'en_proceso' => 'border-blue-300 bg-blue-100 text-blue-800',
                    'vencido' => 'border-orange-300 bg-orange-100 text-orange-800',
                    default => 'border-gray-300 bg-gray-100 text-gray-800',
                };

                $progresoModuloUsuario = max(0, min(100, (float) ($avanceModulo->progreso ?? 0)));
                $progresoModuloUsuarioEntero = (int) round($progresoModuloUsuario);

                $descripcionInicioCurso = trim((string) ($miCapacitacion->capacitacion?->descripcion ?? ''));
                $mostrarInicioDescripcionCurso = $descripcionInicioCurso !== '';

                $moduloSinEvaluacion = (int) $modulo->requiere_evaluacion !== 1;

                $seccionActivaModulo = request('seccion', 'modulo');

                if (!in_array($seccionActivaModulo, ['modulo', 'recursos', 'ejercicios', 'evaluaciones'], true)) {
                    $seccionActivaModulo = 'modulo';
                }

                $recursosTeoriaModulo = $modulo->recursos->where('tipo_recurso', 'teoria');
                $recursosGeneralesModulo = $modulo->recursos->where('tipo_recurso', '!=', 'teoria');

                $urlBaseModuloUsuario = route('mis_modulos.show', [
                    $miCapacitacion->id_empleado_capacitacion,
                    $modulo->id_capacitacion_modulo
                ]);

                $urlBaseModuloUsuario = route('mis_modulos.show', [
                    $miCapacitacion->id_empleado_capacitacion,
                    $modulo->id_capacitacion_modulo
                ]);

                $ejercicioIntegradoActivoId = (int) request('ejercicio_integrado', 0);
                $resultadoEjercicioIntegradoActivoId = (int) request('resultado_ejercicio', 0);
                $evaluacionIntegradaActivaId = (int) request('evaluacion_integrada', 0);
                $resultadoEvaluacionIntegradaActivaId = (int) request('resultado_evaluacion', 0);

                if ($capacitacionFinalizadaParaUsuario) {
                    $ejercicioIntegradoActivoId = 0;
                    $evaluacionIntegradaActivaId = 0;
                }

                $vistaIntegradaModuloActiva =
                    $ejercicioIntegradoActivoId > 0
                    || $resultadoEjercicioIntegradoActivoId > 0
                    || $evaluacionIntegradaActivaId > 0
                    || $resultadoEvaluacionIntegradaActivaId > 0;
            @endphp







            @php
                $seccionesBaseCursoUsuario = $modulo->secciones
                    ->where('estado', 1)
                    ->values();

                $seccionesPrincipalesCursoUsuario = $seccionesBaseCursoUsuario
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

                $subseccionesAgrupadasCursoUsuario = $seccionesBaseCursoUsuario
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

                $seccionesCursoUsuario = collect();
                $numerosSeccionesCisco = [];
                $idsSeccionesCursoIncluidas = collect();

                $numeroPrincipalCisco = 0;

                foreach ($seccionesPrincipalesCursoUsuario as $seccionPrincipalCurso) {
                    $numeroPrincipalCisco++;
                    $numeroSubCisco = 0;

                    $seccionesCursoUsuario->push($seccionPrincipalCurso);
                    $idsSeccionesCursoIncluidas->push((int) $seccionPrincipalCurso->id_capacitacion_modulo_seccion);

                    $numerosSeccionesCisco[$seccionPrincipalCurso->id_capacitacion_modulo_seccion] = (string) $numeroPrincipalCisco;

                    $subseccionesCurso = $subseccionesAgrupadasCursoUsuario
                        ->get($seccionPrincipalCurso->id_capacitacion_modulo_seccion, collect())
                        ->values();

                    foreach ($subseccionesCurso as $subseccionCurso) {
                        $numeroSubCisco++;

                        $seccionesCursoUsuario->push($subseccionCurso);
                        $idsSeccionesCursoIncluidas->push((int) $subseccionCurso->id_capacitacion_modulo_seccion);

                        $numerosSeccionesCisco[$subseccionCurso->id_capacitacion_modulo_seccion] = $numeroPrincipalCisco . '.' . $numeroSubCisco;
                    }
                }

                $seccionesHuerfanasCursoUsuario = $seccionesBaseCursoUsuario
                    ->reject(function ($seccion) use ($idsSeccionesCursoIncluidas) {
                        return $idsSeccionesCursoIncluidas->contains((int) $seccion->id_capacitacion_modulo_seccion);
                    })
                    ->sortBy(function ($seccion) {
                        return sprintf(
                            '%04d-%010d',
                            (int) ($seccion->orden ?? 0),
                            (int) $seccion->id_capacitacion_modulo_seccion
                        );
                    })
                    ->values();

                foreach ($seccionesHuerfanasCursoUsuario as $seccionHuerfanaCurso) {
                    $numeroPrincipalCisco++;

                    $seccionesCursoUsuario->push($seccionHuerfanaCurso);
                    $numerosSeccionesCisco[$seccionHuerfanaCurso->id_capacitacion_modulo_seccion] = (string) $numeroPrincipalCisco;
                }

                $seccionesCursoUsuario = $seccionesCursoUsuario->values();

                $evaluacionesFinalModulo = $modulo->evaluaciones
                    ->sortBy('orden')
                    ->values();

                $recursosSinSeccion = $modulo->recursos
                    ->whereNull('id_capacitacion_modulo_seccion')
                    ->values();

                $ejerciciosSinSeccion = $modulo->ejercicios
                    ->whereNull('id_capacitacion_modulo_seccion')
                    ->values();

                $evaluacionesSinSeccion = $modulo->evaluaciones
                    ->whereNull('id_capacitacion_modulo_seccion')
                    ->values();

                $totalElementosModuloCisco = $modulo->secciones->count()
                    + $modulo->recursos->count()
                    + $modulo->ejercicios->count()
                    + $modulo->evaluaciones->count();

                $elementosCompletadosCisco = 0;
            @endphp

            @php
                $estadoCapacitacionUsuario = $miCapacitacion->estado ?? 'pendiente';

                $estadoCapacitacionTexto = match($estadoCapacitacionUsuario) {
                    'pendiente' => 'Pendiente',
                    'en_proceso' => 'En proceso',
                    'aprobada' => 'Aprobada',
                    'reprobada' => 'Reprobada',
                    'vencida' => 'Retrasada',
                    'cancelada' => 'Cancelada',
                    default => ucfirst(str_replace('_', ' ', $estadoCapacitacionUsuario)),
                };

                $estadoCapacitacionBadge = match($estadoCapacitacionUsuario) {
                    'pendiente' => 'esf-badge-amber',
                    'en_proceso' => 'esf-badge-blue',
                    'aprobada' => 'esf-badge-green',
                    'reprobada' => 'esf-badge-red',
                    'vencida' => 'esf-badge-amber',
                    'cancelada' => 'esf-badge-slate',
                    default => 'esf-badge-slate',
                };

                $progresoCapacitacionUsuario = max(0, min(100, (float) ($miCapacitacion->progreso ?? 0)));
            @endphp

            <section id="cursoCapacitateHeroUsuario" class="curso-capacitate-hero">
                <div class="curso-capacitate-hero-icon">
                    @if($miCapacitacion->capacitacion?->ruta_portada)
                        <img src="{{ asset('storage/' . $miCapacitacion->capacitacion->ruta_portada) }}"
                            alt="Portada de {{ $miCapacitacion->capacitacion?->capacitacion }}">
                    @else
                        <span>{{ mb_substr($miCapacitacion->capacitacion?->capacitacion ?? 'C', 0, 1) }}</span>
                    @endif
                </div>

                <div class="curso-capacitate-hero-text">
                    <p class="curso-capacitate-eyebrow">
                        Capacitación asignada
                    </p>

                    <div class="flex flex-wrap items-center gap-3">
                        <h3 class="curso-capacitate-title">
                            {{ $miCapacitacion->capacitacion?->capacitacion }}
                        </h3>

                        <span class="esf-badge {{ $estadoCapacitacionBadge }}">
                            {{ $estadoCapacitacionTexto }}
                        </span>
                    </div>

                    <p class="curso-capacitate-description">
                        Sigue el contenido en orden. Primero revisa la teoría y los recursos; luego realiza los ejercicios y la evaluación final cuando corresponda.
                    </p>

                    <div class="curso-capacitate-stats">
                        <div>
                            <span>Progreso</span>
                            <strong data-progreso-modulo-texto>{{ number_format($progresoModuloUsuario, 2) }}%</strong>
                        </div>

                        <div>
                            <span>Nota final</span>
                            <strong>{{ is_null($miCapacitacion->nota_final) ? '-' : number_format((float) $miCapacitacion->nota_final, 2) }}</strong>
                        </div>

                        <div>
                            <span>Fecha límite</span>
                            <strong>{{ $miCapacitacion->fecha_limite ? $miCapacitacion->fecha_limite->format('d/m/Y') : '-' }}</strong>
                        </div>

                        <div>
                            <span>Módulo actual</span>
                            <strong>{{ $modulo->titulo }}</strong>
                        </div>
                    </div>
                </div>

                <div class="curso-capacitate-hero-action">
                    <a href="{{ route('mis_capacitaciones.index') }}" class="esf-btn esf-btn-soft">
                        Volver
                    </a>
                </div>
            </section>

            <div id="cursoCapacitateShellUsuario" class="esf-learning-shell curso-capacitate-shell">
                <div x-data="{ sidebarModuloAbierto: true }"
                    class="esf-learning-layout curso-capacitate-layout grid grid-cols-1 lg:grid-cols-12 relative">

                    <button type="button"
                            @click="sidebarModuloAbierto = !sidebarModuloAbierto"
                            class="curso-capacitate-sidebar-toggle"
                            :class="sidebarModuloAbierto ? 'is-open' : 'is-closed'"
                            :aria-label="sidebarModuloAbierto ? 'Ocultar plan del módulo' : 'Mostrar plan del módulo'">
                        <span class="curso-capacitate-sidebar-toggle-icon" aria-hidden="true">
                            <span></span>
                            <span></span>
                            <span></span>
                        </span>
                    </button>

                    <aside x-show="sidebarModuloAbierto"
                        x-transition
                        class="esf-learning-sidebar curso-capacitate-sidebar lg:col-span-4 xl:col-span-3">
                        <div class="curso-capacitate-sidebar-head">
                            <p class="curso-capacitate-sidebar-label">
                                Plan de capacitación
                            </p>

                            <h3>
                                Sigue el orden del curso
                            </h3>

                            <div class="curso-capacitate-sidebar-progress">
                                <span data-progreso-modulo-barra style="width: {{ $progresoModuloUsuario }}%"></span>
                            </div>

                            <p>
                                Progreso del módulo actual: <span data-progreso-modulo-entero>{{ $progresoModuloUsuarioEntero }}</span>%
                            </p>
                        </div>

                            @foreach($modulosCapacitacion as $moduloMenu)
                                @php
                                    $moduloActualMenu = (int) $moduloMenu->id_capacitacion_modulo === (int) $modulo->id_capacitacion_modulo;

                                    $seccionesPrincipalesSidebar = $moduloMenu->secciones
                                        ->where('nivel', 1)
                                        ->sortBy(function ($seccion) {
                                            return sprintf(
                                                '%04d-%010d',
                                                (int) ($seccion->orden ?? 0),
                                                (int) $seccion->id_capacitacion_modulo_seccion
                                            );
                                        })
                                        ->values();

                                    $subseccionesSidebar = $moduloMenu->secciones
                                        ->where('nivel', 2)
                                        ->sortBy(function ($seccion) {
                                            return sprintf(
                                                '%010d-%04d-%010d',
                                                (int) ($seccion->id_seccion_padre ?? 0),
                                                (int) ($seccion->orden ?? 0),
                                                (int) $seccion->id_capacitacion_modulo_seccion
                                            );
                                        })
                                        ->groupBy('id_seccion_padre');
                                @endphp

                                <details {{ $moduloActualMenu ? 'open' : '' }} class="rounded-2xl bg-white/5 border border-white/10 overflow-hidden">
                                    <summary class="list-none cursor-pointer select-none px-4 py-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <p class="text-[11px] uppercase tracking-[0.16em] font-black text-slate-400">
                                                    Módulo {{ $moduloMenu->orden }}
                                                </p>

                                                <p class="mt-1 text-sm font-black {{ $moduloActualMenu ? 'text-white' : 'text-slate-300' }}">
                                                    {{ $moduloMenu->titulo }}
                                                </p>
                                            </div>

                                            <span class="text-slate-300">⌄</span>
                                        </div>
                                    </summary>

                                    <div class="px-3 pb-3 space-y-1">
                                        @if(!$moduloActualMenu)
                                            <a href="{{ route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $moduloMenu->id_capacitacion_modulo]) }}"
                                            class="esf-learning-nav-link">
                                                <span class="esf-learning-dot"></span>
                                                <span>Abrir este módulo</span>
                                            </a>
                                        @endif

                                        @foreach($seccionesPrincipalesSidebar as $seccionSidebar)
                                            @php
                                                $subseccionesDeSidebar = $subseccionesSidebar
                                                    ->get($seccionSidebar->id_capacitacion_modulo_seccion, collect())
                                                    ->values();

                                                $seccionVistaSidebar = isset($avancesContenidoUsuario['seccion:' . $seccionSidebar->id_capacitacion_modulo_seccion]);

                                                $hrefSeccionSidebar = $moduloActualMenu
                                                    ? '#seccion-curso-' . $seccionSidebar->id_capacitacion_modulo_seccion
                                                    : route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $moduloMenu->id_capacitacion_modulo]) . '#seccion-curso-' . $seccionSidebar->id_capacitacion_modulo_seccion;

                                                $recursosSidebar = $moduloMenu->recursos
                                                    ->where('id_capacitacion_modulo_seccion', $seccionSidebar->id_capacitacion_modulo_seccion);

                                                $ejerciciosSidebar = $moduloMenu->ejercicios
                                                    ->where('id_capacitacion_modulo_seccion', $seccionSidebar->id_capacitacion_modulo_seccion);
                                            @endphp

                                            <a href="{{ $hrefSeccionSidebar }}"
                                            data-menu-section="{{ $moduloActualMenu ? $seccionSidebar->id_capacitacion_modulo_seccion : '' }}"
                                            class="esf-learning-nav-link esf-learning-nav-section">
                                                <span data-circulo-avance="seccion:{{ $seccionSidebar->id_capacitacion_modulo_seccion }}"
                                                    class="esf-learning-dot {{ $seccionVistaSidebar ? 'esf-learning-dot-done' : '' }}"></span>

                                                <span class="block font-bold">
                                                    {{ $seccionSidebar->titulo }}
                                                </span>
                                            </a>

                                            <div class="esf-learning-branch">
                                                @foreach($subseccionesDeSidebar as $subSidebar)
                                                    @php
                                                        $subVistaSidebar = isset($avancesContenidoUsuario['seccion:' . $subSidebar->id_capacitacion_modulo_seccion]);

                                                        $hrefSubSidebar = $moduloActualMenu
                                                            ? '#seccion-curso-' . $subSidebar->id_capacitacion_modulo_seccion
                                                            : route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $moduloMenu->id_capacitacion_modulo]) . '#seccion-curso-' . $subSidebar->id_capacitacion_modulo_seccion;

                                                        $recursosSubSidebar = $moduloMenu->recursos
                                                            ->where('id_capacitacion_modulo_seccion', $subSidebar->id_capacitacion_modulo_seccion)
                                                            ->sortBy('orden');

                                                        $ejerciciosSubSidebar = $moduloMenu->ejercicios
                                                            ->where('id_capacitacion_modulo_seccion', $subSidebar->id_capacitacion_modulo_seccion)
                                                            ->sortBy('orden');
                                                    @endphp

                                                    <a href="{{ $hrefSubSidebar }}"
                                                    data-menu-section="{{ $moduloActualMenu ? $subSidebar->id_capacitacion_modulo_seccion : '' }}"
                                                    class="esf-learning-nav-link esf-learning-nav-subsection">
                                                        <span data-circulo-avance="seccion:{{ $subSidebar->id_capacitacion_modulo_seccion }}"
                                                            class="esf-learning-dot {{ $subVistaSidebar ? 'esf-learning-dot-done' : '' }}"></span>

                                                        <span class="block font-semibold">
                                                            {{ $subSidebar->titulo }}
                                                        </span>
                                                    </a>

                                                    @foreach($recursosSubSidebar as $recursoSubSidebar)
                                                        <a href="{{ $moduloActualMenu ? '#contenido-recurso-' . $recursoSubSidebar->id_capacitacion_recurso : route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $moduloMenu->id_capacitacion_modulo]) . '#contenido-recurso-' . $recursoSubSidebar->id_capacitacion_recurso }}"
                                                        class="esf-learning-nav-link esf-learning-nav-subitem">
                                                            <span data-circulo-avance="recurso:{{ $recursoSubSidebar->id_capacitacion_recurso }}"
                                                                class="esf-learning-dot {{ isset($avancesContenidoUsuario['recurso:' . $recursoSubSidebar->id_capacitacion_recurso]) ? 'esf-learning-dot-done' : '' }}"></span>
                                                            <span>Recurso</span>
                                                        </a>
                                                    @endforeach

                                                    @foreach($ejerciciosSubSidebar as $ejercicioSubSidebar)
                                                        <a href="{{ $moduloActualMenu ? '#contenido-ejercicio-' . $ejercicioSubSidebar->id_ejercicio : route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $moduloMenu->id_capacitacion_modulo]) . '#contenido-ejercicio-' . $ejercicioSubSidebar->id_ejercicio }}"
                                                        class="esf-learning-nav-link esf-learning-nav-subitem">
                                                            <span data-circulo-avance="ejercicio:{{ $ejercicioSubSidebar->id_ejercicio }}"
                                                                class="esf-learning-dot {{ isset($avancesContenidoUsuario['ejercicio:' . $ejercicioSubSidebar->id_ejercicio]) || ($ejercicioSubSidebar->completado_usuario ?? false) ? 'esf-learning-dot-done' : '' }}"></span>
                                                            <span>Ejercicio</span>
                                                        </a>
                                                    @endforeach
                                                @endforeach

                                                @foreach($recursosSidebar as $recursoSidebar)
                                                    <a href="{{ $moduloActualMenu ? '#contenido-recurso-' . $recursoSidebar->id_capacitacion_recurso : route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $moduloMenu->id_capacitacion_modulo]) . '#contenido-recurso-' . $recursoSidebar->id_capacitacion_recurso }}"
                                                    class="esf-learning-nav-link esf-learning-nav-section-item">
                                                        <span data-circulo-avance="recurso:{{ $recursoSidebar->id_capacitacion_recurso }}"
                                                            class="esf-learning-dot {{ isset($avancesContenidoUsuario['recurso:' . $recursoSidebar->id_capacitacion_recurso]) ? 'esf-learning-dot-done' : '' }}"></span>
                                                        <span>Recurso</span>
                                                    </a>
                                                @endforeach

                                                @foreach($ejerciciosSidebar as $ejercicioSidebar)
                                                    <a href="{{ $moduloActualMenu ? '#contenido-ejercicio-' . $ejercicioSidebar->id_ejercicio : route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $moduloMenu->id_capacitacion_modulo]) . '#contenido-ejercicio-' . $ejercicioSidebar->id_ejercicio }}"
                                                    class="esf-learning-nav-link esf-learning-nav-section-item">
                                                        <span data-circulo-avance="ejercicio:{{ $ejercicioSidebar->id_ejercicio }}"
                                                            class="esf-learning-dot {{ isset($avancesContenidoUsuario['ejercicio:' . $ejercicioSidebar->id_ejercicio]) || ($ejercicioSidebar->completado_usuario ?? false) ? 'esf-learning-dot-done' : '' }}"></span>
                                                        <span>Ejercicio</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endforeach

                                        @if($moduloMenu->evaluaciones->count() > 0)
                                            @php
                                                $evaluacionFinalSidebar = $moduloMenu->evaluaciones->sortBy('orden')->first();
                                                $evaluacionFinalSidebarVista = $evaluacionFinalSidebar
                                                    ? ($evaluacionesFinalizadasUsuarioIds ?? collect())->contains((int) $evaluacionFinalSidebar->id_evaluacion)
                                                    : false;
                                            @endphp

                                            <a href="{{ $moduloActualMenu ? '#examen-general-modulo' : route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $moduloMenu->id_capacitacion_modulo]) . '#examen-general-modulo' }}"
                                            class="esf-learning-nav-link">
                                                <span data-circulo-avance="{{ $evaluacionFinalSidebar ? 'evaluacion:' . $evaluacionFinalSidebar->id_evaluacion : '' }}"
                                                    class="esf-learning-dot {{ $evaluacionFinalSidebarVista ? 'esf-learning-dot-done' : '' }}"></span>
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
                    </aside>

                    <section class="esf-learning-main curso-capacitate-main"
                            :class="sidebarModuloAbierto ? 'lg:col-span-8 xl:col-span-9' : 'lg:col-span-12 xl:col-span-12 curso-capacitate-main-full'">
                        <div class="curso-capacitate-content curso-capacitate-content-ajustado">

                            @if($vistaIntegradaModuloActiva)
                                <section class="esf-module-integrated-full">
                                    <div class="esf-module-integrated-header">
                                        <p class="esf-module-integrated-eyebrow">
                                            Actividad en curso
                                        </p>

                                        <h2>
                                            @if($ejercicioIntegradoActivoId > 0)
                                                Resolver ejercicio
                                            @elseif($resultadoEjercicioIntegradoActivoId > 0)
                                                Resultado del ejercicio
                                            @elseif($evaluacionIntegradaActivaId > 0)
                                                Presentar evaluación
                                            @else
                                                Resultado de evaluación
                                            @endif
                                        </h2>

                                        <p>
                                            Esta actividad se está mostrando dentro del módulo. Al finalizar, regresarás automáticamente al recorrido del curso.
                                        </p>
                                    </div>

                                    <div class="esf-module-integrated-body">
                                        @if($ejercicioIntegradoActivoId > 0)
                                            <iframe
                                                src="{{ route('mis_ejercicios.show', [$miCapacitacion->id_empleado_capacitacion, $ejercicioIntegradoActivoId]) }}?integrado_modulo=1"
                                                class="esf-module-embedded-frame-full"
                                                scrolling="yes"
                                                @load="
                                                    if (window.matchMedia('(max-width: 767px)').matches) {
                                                        $el.style.height = 'calc(100dvh - 5.25rem)';
                                                    } else {
                                                        $el.style.height = ($el.contentWindow.document.documentElement.scrollHeight + 40) + 'px';
                                                    }
                                                ">
                                            </iframe>
                                        @elseif($resultadoEjercicioIntegradoActivoId > 0)
                                            <iframe
                                                src="{{ route('mis_ejercicios.resultado', [$miCapacitacion->id_empleado_capacitacion, $resultadoEjercicioIntegradoActivoId]) }}?integrado_modulo=1"
                                                class="esf-module-embedded-frame-full"
                                                scrolling="yes"
                                                @load="
                                                    if (window.matchMedia('(max-width: 767px)').matches) {
                                                        $el.style.height = 'calc(100dvh - 5.25rem)';
                                                    } else {
                                                        $el.style.height = ($el.contentWindow.document.documentElement.scrollHeight + 40) + 'px';
                                                    }
                                                ">
                                            </iframe>
                                        @elseif($evaluacionIntegradaActivaId > 0)
                                            <iframe
                                                src="{{ route('mis_evaluaciones.show', [$miCapacitacion->id_empleado_capacitacion, $evaluacionIntegradaActivaId]) }}?integrado_modulo=1"
                                                class="esf-module-embedded-frame-full"
                                                scrolling="yes"
                                                @load="
                                                    if (window.matchMedia('(max-width: 767px)').matches) {
                                                        $el.style.height = 'calc(100dvh - 5.25rem)';
                                                    } else {
                                                        $el.style.height = ($el.contentWindow.document.documentElement.scrollHeight + 40) + 'px';
                                                    }
                                                ">
                                            </iframe>
                                        @elseif($resultadoEvaluacionIntegradaActivaId > 0)
                                            <iframe
                                                src="{{ route('mis_evaluaciones.resultado', [$miCapacitacion->id_empleado_capacitacion, $resultadoEvaluacionIntegradaActivaId]) }}?integrado_modulo=1"
                                                class="esf-module-embedded-frame-full"
                                                scrolling="yes"
                                                @load="
                                                    if (window.matchMedia('(max-width: 767px)').matches) {
                                                        $el.style.height = 'calc(100dvh - 5.25rem)';
                                                    } else {
                                                        $el.style.height = ($el.contentWindow.document.documentElement.scrollHeight + 40) + 'px';
                                                    }
                                                ">
                                            </iframe>
                                        @endif
                                    </div>
                                </section>
                            @else

                            <div class="esf-learning-hero curso-capacitate-module-intro">
                                <h1 class="mt-2 text-3xl font-black text-slate-900 dark:text-slate-100">
                                    {{ $modulo->titulo }}
                                </h1>

                                @php
                                    $descripcionModuloUsuario = trim((string) ($modulo->descripcion ?? ''));
                                    $objetivoModuloUsuario = trim((string) ($modulo->objetivo ?? ''));
                                @endphp

                                @if(session('success') || $errors->has('modulo'))
                                    <div class="mt-5 space-y-3">
                                        @if(session('success'))
                                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 shadow-sm">
                                                {{ session('success') }}
                                            </div>
                                        @endif

                                        @if($errors->has('modulo'))
                                            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700 shadow-sm">
                                                {{ $errors->first('modulo') }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            @forelse($seccionesCursoUsuario as $seccionContenido)
                                @php
                                    $recursosSeccion = $modulo->recursos
                                        ->where('id_capacitacion_modulo_seccion', $seccionContenido->id_capacitacion_modulo_seccion)
                                        ->sortBy('orden');

                                    $ejerciciosSeccion = $modulo->ejercicios
                                        ->where('id_capacitacion_modulo_seccion', $seccionContenido->id_capacitacion_modulo_seccion)
                                        ->sortBy('orden');

                                    $itemsContenidoSeccion = collect();

                                    foreach ($recursosSeccion as $recursoItem) {
                                        $itemsContenidoSeccion->push([
                                            'tipo' => 'recurso',
                                            'orden' => $recursoItem->orden,
                                            'data' => $recursoItem,
                                        ]);
                                    }

                                    foreach ($ejerciciosSeccion as $ejercicioItem) {
                                        $itemsContenidoSeccion->push([
                                            'tipo' => 'ejercicio',
                                            'orden' => $ejercicioItem->orden,
                                            'data' => $ejercicioItem,
                                        ]);
                                    }

                                    $itemsContenidoSeccion = $itemsContenidoSeccion
                                        ->sortBy('orden')
                                        ->values();

                                    $contenidoSeccionHtml = trim((string) ($seccionContenido->contenido ?? ''));
                                    $contenidoSeccionHtml = preg_replace('/^(<p>(\s|&nbsp;|<br\s*\/?>)*<\/p>\s*)+/i', '', $contenidoSeccionHtml);
                                    $contenidoSeccionHtml = preg_replace('/(\s*<p>(\s|&nbsp;|<br\s*\/?>)*<\/p>)+$/i', '', $contenidoSeccionHtml);
                                    $contenidoSeccionHtml = trim($contenidoSeccionHtml);

                                    $seccionTieneContenidoEscrito = $contenidoSeccionHtml !== '' && $contenidoSeccionHtml !== '<p><br></p>';
                                    $seccionTieneItems = $itemsContenidoSeccion->count() > 0;
                                    $margenHeaderSeccion = $seccionTieneContenidoEscrito || $seccionTieneItems ? 'mb-1' : 'mb-0';
                                @endphp

                                <article id="seccion-curso-{{ $seccionContenido->id_capacitacion_modulo_seccion }}"
                                        class="esf-learning-section curso-capacitate-page contenido-avance-scroll-final"
                                        data-section-observer="{{ $seccionContenido->id_capacitacion_modulo_seccion }}"
                                        data-avance-contenido
                                        data-tipo-contenido="seccion"
                                        data-id-contenido="{{ $seccionContenido->id_capacitacion_modulo_seccion }}">

                                    <div class="curso-capacitate-section-title border-b border-slate-200 dark:border-slate-700 {{ $margenHeaderSeccion }}">
                                        <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100">
                                            {{ $seccionContenido->titulo }}
                                        </h2>
                                    </div>

                                    @if($seccionTieneContenidoEscrito)
                                        <div class="ql-snow">
                                            <div class="contenido-teoria-render ql-editor max-w-none text-slate-800 dark:text-slate-100 leading-relaxed bg-transparent p-0">
                                                {!! $contenidoSeccionHtml !!}
                                            </div>
                                        </div>
                                    @endif

                                    @if($itemsContenidoSeccion->count() > 0)
                                        <div class="{{ $seccionTieneContenidoEscrito ? 'mt-5' : 'mt-3' }} space-y-5">
                                            @foreach($itemsContenidoSeccion as $itemContenido)
                                                @php
                                                    $tipoItem = $itemContenido['tipo'];
                                                    $dataItem = $itemContenido['data'];

                                                    $idContenidoItem = match ($tipoItem) {
                                                        'recurso' => 'contenido-recurso-' . $dataItem->id_capacitacion_recurso,
                                                        'ejercicio' => 'contenido-ejercicio-' . $dataItem->id_ejercicio,
                                                        default => 'contenido-item-' . $loop->iteration,
                                                    };
                                                @endphp

                                                @if($tipoItem === 'recurso')
                                                    @php
                                                        $rutaArchivoRecurso = $dataItem->ruta_archivo
                                                            ? asset('storage/' . $dataItem->ruta_archivo)
                                                            : null;

                                                        $extensionRecurso = strtolower(pathinfo($dataItem->ruta_archivo ?? '', PATHINFO_EXTENSION));

                                                        $recursoVisto = isset($avancesContenidoUsuario['recurso:' . $dataItem->id_capacitacion_recurso])
                                                            || in_array((int) $dataItem->id_capacitacion_recurso, $recursosAbiertosIds ?? [], true);

                                                        $esImagenRecurso = in_array($extensionRecurso, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);
                                                        $esPdfRecurso = $extensionRecurso === 'pdf';
                                                        $esVideoRecurso = in_array($extensionRecurso, ['mp4', 'webm', 'mov', 'm4v'], true);
                                                        $esAudioRecurso = in_array($extensionRecurso, ['mp3', 'wav', 'ogg', 'm4a'], true);
                                                        $esOfficeRecurso = in_array($extensionRecurso, ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'], true);

                                                        $urlEsLocal = !\App\Support\VistaPreviaRecurso::esUrlAccesibleDesdeInternet($rutaArchivoRecurso);

                                                        $urlOfficeViewer = $rutaArchivoRecurso
                                                            ? 'https://view.officeapps.live.com/op/embed.aspx?src=' . urlencode($rutaArchivoRecurso)
                                                            : null;

                                                        $extensionUrlRecurso = $dataItem->url_recurso
                                                            ? strtolower(pathinfo(parse_url($dataItem->url_recurso, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION))
                                                            : null;

                                                        $esImagenUrlRecurso = in_array($extensionUrlRecurso, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);
                                                    @endphp

                                                    <div id="{{ $idContenidoItem }}"
                                                        class="esf-learning-content-card curso-capacitate-activity-card"
                                                        data-avance-contenido
                                                        data-tipo-contenido="recurso"
                                                        data-id-contenido="{{ $dataItem->id_capacitacion_recurso }}">

                                                        <div class="flex items-start gap-3">
                                                            <span data-circulo-avance="recurso:{{ $dataItem->id_capacitacion_recurso }}"
                                                                class="esf-learning-dot {{ $recursoVisto ? 'esf-learning-dot-done' : '' }}"></span>

                                                            <div class="flex-1">
                                                                <p class="text-xs uppercase tracking-[0.16em] font-black text-blue-600 dark:text-blue-300">
                                                                    Recurso
                                                                </p>

                                                                <h3 class="mt-1 text-xl font-black text-slate-900 dark:text-slate-100">
                                                                    {{ $dataItem->titulo }}
                                                                </h3>

                                                                @if($dataItem->descripcion)
                                                                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                                                        {{ $dataItem->descripcion }}
                                                                    </p>
                                                                @endif

                                                                @if($rutaArchivoRecurso)
                                                                    <div class="esf-resource-preview">
                                                                        @if($esImagenRecurso)
                                                                            <img src="{{ $rutaArchivoRecurso }}"
                                                                                alt="{{ $dataItem->titulo }}">
                                                                        @elseif($esPdfRecurso)
                                                                            <iframe src="{{ $rutaArchivoRecurso }}#toolbar=1&navpanes=0"
                                                                                title="Vista previa de {{ $dataItem->titulo ?: 'recurso PDF' }}"></iframe>
                                                                        @elseif($esVideoRecurso)
                                                                            <video controls preload="metadata" playsinline>
                                                                                <source src="{{ $rutaArchivoRecurso }}">
                                                                                Tu navegador no puede reproducir este video. Puedes descargarlo desde el botón inferior.
                                                                            </video>
                                                                        @elseif($esAudioRecurso)
                                                                            <div class="p-5">
                                                                                <audio controls class="w-full">
                                                                                    <source src="{{ $rutaArchivoRecurso }}">
                                                                                </audio>
                                                                            </div>
                                                                        @elseif($esOfficeRecurso && !$urlEsLocal)
                                                                            <iframe src="{{ $urlOfficeViewer }}"></iframe>
                                                                        @else
                                                                            <div class="p-5">
                                                                                <p class="font-black text-slate-900 dark:text-slate-100">
                                                                                    Archivo disponible
                                                                                </p>

                                                                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                                                                    Este tipo de archivo se podrá previsualizar cuando el sistema esté publicado con una URL accesible. En local, abrilo desde el botón.
                                                                                </p>

                                                                                <a href="{{ $rutaArchivoRecurso }}"
                                                                                target="_blank"
                                                                                data-marcar-recurso="1"
                                                                                data-tipo-contenido="recurso"
                                                                                data-id-contenido="{{ $dataItem->id_capacitacion_recurso }}"
                                                                                class="mt-4 esf-learning-big-action esf-learning-action-blue">
                                                                                    Abrir archivo
                                                                                </a>
                                                                            </div>
                                                                        @endif
                                                                    </div>

                                                                    @if((int) $dataItem->permite_descarga === 1)
                                                                        <a href="{{ $rutaArchivoRecurso }}"
                                                                        download
                                                                        data-marcar-recurso="1"
                                                                        data-tipo-contenido="recurso"
                                                                        data-id-contenido="{{ $dataItem->id_capacitacion_recurso }}"
                                                                        class="mt-4 esf-learning-big-action esf-learning-action-blue">
                                                                            Descargar recurso
                                                                        </a>
                                                                    @endif
                                                                @endif

                                                                @if($dataItem->url_recurso && $esImagenUrlRecurso)
                                                                    <div class="esf-resource-preview">
                                                                        <img src="{{ $dataItem->url_recurso }}"
                                                                            alt="{{ $dataItem->titulo }}">
                                                                    </div>
                                                                @endif

                                                                @if($dataItem->url_recurso)
                                                                    <a href="{{ $dataItem->url_recurso }}"
                                                                    target="_blank"
                                                                    data-marcar-recurso="1"
                                                                    data-tipo-contenido="recurso"
                                                                    data-id-contenido="{{ $dataItem->id_capacitacion_recurso }}"
                                                                    class="mt-4 esf-learning-big-action esf-learning-action-blue">
                                                                        {{ $esImagenUrlRecurso ? 'Abrir imagen' : 'Abrir enlace' }}
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($tipoItem === 'ejercicio')
                                                    @php
                                                        $intentosEjercicio = $dataItem->mis_intentos ?? collect();
                                                        $intentosRealizadosEjercicio = $intentosEjercicio->count();

                                                        $intentosMaximosEjercicio = $dataItem->intentos_maximos
                                                            ? (int) $dataItem->intentos_maximos
                                                            : null;

                                                        $ejercicioAprobado = $intentosEjercicio->contains(function ($intentoEjercicio) {
                                                            return in_array($intentoEjercicio->estado, ['finalizado', 'revisado'], true)
                                                                && (int) ($intentoEjercicio->aprobado ?? 0) === 1;
                                                        });

                                                        $intentosAgotadosEjercicio = !is_null($intentosMaximosEjercicio)
                                                            && $intentosRealizadosEjercicio >= $intentosMaximosEjercicio;

                                                        $puedeIntentarEjercicio = !$ejercicioAprobado
                                                            && (is_null($intentosMaximosEjercicio) || $intentosRealizadosEjercicio < $intentosMaximosEjercicio);

                                                        $puedeVerResultadoEjercicio = $ejercicioAprobado || $intentosAgotadosEjercicio;

                                                        $textoBotonEjercicio = $intentosRealizadosEjercicio === 0
                                                            ? 'Comenzar ejercicio'
                                                            : ($puedeIntentarEjercicio ? 'Intentar nuevamente' : 'Ver resultado');

                                                        $ultimoIntentoEjercicio = $intentosEjercicio->first();

                                                        $mostrarResolverEjercicio = (int) request('ejercicio_integrado') === (int) $dataItem->id_ejercicio;

                                                        $mostrarResultadoEjercicio = $ultimoIntentoEjercicio
                                                            && (int) request('resultado_ejercicio') === (int) $ultimoIntentoEjercicio->id_ejercicio_intento;

                                                        $urlModuloEjercicio = route('mis_modulos.show', [
                                                            $miCapacitacion->id_empleado_capacitacion,
                                                            $modulo->id_capacitacion_modulo,
                                                        ]);

                                                        $urlAccionEjercicio = ($puedeVerResultadoEjercicio && $ultimoIntentoEjercicio)
                                                            ? $urlModuloEjercicio . '?resultado_ejercicio=' . $ultimoIntentoEjercicio->id_ejercicio_intento . '#contenido-ejercicio-' . $dataItem->id_ejercicio
                                                            : $urlModuloEjercicio . '?ejercicio_integrado=' . $dataItem->id_ejercicio . '#contenido-ejercicio-' . $dataItem->id_ejercicio;
                                                    @endphp

                                                    <div id="{{ $idContenidoItem }}"
                                                        class="esf-learning-content-card curso-capacitate-activity-card"
                                                        data-avance-contenido
                                                        data-tipo-contenido="ejercicio"
                                                        data-id-contenido="{{ $dataItem->id_ejercicio }}">

                                                        <div class="flex items-start gap-3">
                                                            <span data-circulo-avance="ejercicio:{{ $dataItem->id_ejercicio }}"
                                                                class="esf-learning-dot {{ ($dataItem->completado_usuario ?? false) ? 'esf-learning-dot-done' : '' }}"></span>

                                                            <div class="flex-1">
                                                                <p class="text-xs uppercase tracking-[0.16em] font-black text-emerald-600 dark:text-emerald-300">
                                                                    Ejercicio
                                                                </p>

                                                                <h3 class="mt-1 text-xl font-black text-slate-900 dark:text-slate-100">
                                                                    {{ $dataItem->titulo }}
                                                                </h3>

                                                                @if($dataItem->descripcion)
                                                                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                                                        {{ $dataItem->descripcion }}
                                                                    </p>
                                                                @endif

                                                                @php
                                                                    $resultadoEjercicioLocal = session('resultado_ejercicio');
                                                                    $idEjercicioAviso = session('id_ejercicio_aviso');

                                                                    $mostrarResultadoEjercicioLocal = is_array($resultadoEjercicioLocal)
                                                                        && (
                                                                            (int) $idEjercicioAviso === (int) $dataItem->id_ejercicio
                                                                            || (
                                                                                empty($idEjercicioAviso)
                                                                                && ($resultadoEjercicioLocal['titulo'] ?? '') === $dataItem->titulo
                                                                            )
                                                                        );

                                                                    $mostrarErrorEjercicioLocal = $errors->has('ejercicio')
                                                                        && (int) $idEjercicioAviso === (int) $dataItem->id_ejercicio;

                                                                    $claseResultadoEjercicioLocal = 'border-slate-200 bg-slate-50 text-slate-700';

                                                                    if ($mostrarResultadoEjercicioLocal) {
                                                                        $claseResultadoEjercicioLocal = match($resultadoEjercicioLocal['estado'] ?? '') {
                                                                            'aprobado' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                                                                            'reprobado' => 'border-red-200 bg-red-50 text-red-700',
                                                                            'pendiente_revision' => 'border-amber-200 bg-amber-50 text-amber-800',
                                                                            default => 'border-slate-200 bg-slate-50 text-slate-700',
                                                                        };
                                                                    }
                                                                @endphp

                                                                @if($mostrarErrorEjercicioLocal)
                                                                    <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700 shadow-sm">
                                                                        {{ $errors->first('ejercicio') }}
                                                                    </div>
                                                                @endif

                                                                @if($mostrarResultadoEjercicioLocal)
                                                                    <div class="mt-4 rounded-2xl border px-4 py-3 text-sm font-bold shadow-sm {{ $claseResultadoEjercicioLocal }}">
                                                                        <p>
                                                                            <span class="font-black">Resultado del ejercicio:</span>
                                                                            @if(($resultadoEjercicioLocal['estado'] ?? '') === 'aprobado')
                                                                                Aprobado
                                                                            @elseif(($resultadoEjercicioLocal['estado'] ?? '') === 'reprobado')
                                                                                Reprobado
                                                                            @elseif(($resultadoEjercicioLocal['estado'] ?? '') === 'pendiente_revision')
                                                                                Pendiente de revisión
                                                                            @else
                                                                                -
                                                                            @endif
                                                                        </p>

                                                                        <p class="mt-1">
                                                                            <span class="font-black">Porcentaje:</span>
                                                                            {{ !is_null($resultadoEjercicioLocal['porcentaje'] ?? null) ? number_format((float) $resultadoEjercicioLocal['porcentaje'], 2) . '%' : '-' }}
                                                                        </p>

                                                                        <p class="mt-1">
                                                                            <span class="font-black">Intentos restantes:</span>
                                                                            {{ is_null($resultadoEjercicioLocal['intentos_restantes'] ?? null) ? 'Ilimitados' : $resultadoEjercicioLocal['intentos_restantes'] }}
                                                                        </p>
                                                                    </div>
                                                                @endif

                                                                <div class="mt-4 flex flex-wrap gap-2">
                                                                    <span class="esf-badge esf-badge-blue">
                                                                        Intentos realizados: {{ $intentosRealizadosEjercicio }}
                                                                    </span>

                                                                    <span class="esf-badge esf-badge-slate">
                                                                        Máximo: {{ is_null($intentosMaximosEjercicio) ? 'Ilimitado' : $intentosMaximosEjercicio }}
                                                                    </span>
                                                                </div>

                                                                @if($capacitacionFinalizadaParaUsuario && !$puedeVerResultadoEjercicio)
                                                                    <div class="mt-5 rounded-2xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm font-bold text-orange-800">
                                                                        Esta capacitación ya finalizó. Solo podés consultar el contenido del módulo; no se permite realizar ejercicios.
                                                                    </div>
                                                                @else
                                                                    <a href="{{ $urlAccionEjercicio }}"
                                                                        class="mt-5 esf-learning-big-action {{ $puedeVerResultadoEjercicio ? 'esf-learning-action-blue' : 'esf-learning-action-green' }}">
                                                                            {{ $textoBotonEjercicio }}
                                                                    </a>
                                                                @endif
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

                            @if($evaluacionesFinalModulo->count() > 0)
                            @php
                                $evaluacionAvanceFinalModulo = $evaluacionesFinalModulo->first();
                            @endphp

                            <section id="examen-general-modulo"
                                class="esf-learning-section curso-capacitate-page curso-capacitate-exam">
                                    <div class="border-b border-slate-200 dark:border-slate-700 pb-3 mb-1">
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
                                        @foreach($evaluacionesFinalModulo as $evaluacionFinal)
                                            @php
                                                $evalDataFinal = ($evaluacionesUsuarioPorId ?? collect())->get($evaluacionFinal->id_evaluacion);
                                                $intentosEvalFinal = $evalDataFinal['intentos_realizados'] ?? 0;
                                                $maximoEvalFinal = $evalDataFinal['maximo_intentos_alcanzado'] ?? false;
                                                $aprobadaEvalFinal = $evalDataFinal['aprobado'] ?? false;
                                                $puedeVerRevisionFinal = $evalDataFinal['puede_ver_revision'] ?? false;

                                                $evaluacionCerradaFinal = $aprobadaEvalFinal || $maximoEvalFinal;

                                                $ultimoIntentoEvaluacionFinal = $evalDataFinal['ultimo_intento'] ?? null;

                                                $mostrarResolverEvaluacion = (int) request('evaluacion_integrada') === (int) $evaluacionFinal->id_evaluacion;

                                                $mostrarResultadoEvaluacion = $ultimoIntentoEvaluacionFinal
                                                    && (int) request('resultado_evaluacion') === (int) $ultimoIntentoEvaluacionFinal->id_evaluacion_intento;

                                                $urlModuloEvaluacion = route('mis_modulos.show', [
                                                    $miCapacitacion->id_empleado_capacitacion,
                                                    $modulo->id_capacitacion_modulo,
                                                ]);

                                                $urlAccionEvaluacion = (($evaluacionCerradaFinal || $puedeVerRevisionFinal) && $ultimoIntentoEvaluacionFinal)
                                                    ? $urlModuloEvaluacion . '?resultado_evaluacion=' . $ultimoIntentoEvaluacionFinal->id_evaluacion_intento . '#examen-general-modulo'
                                                    : $urlModuloEvaluacion . '?evaluacion_integrada=' . $evaluacionFinal->id_evaluacion . '#examen-general-modulo';
                                            @endphp

                                            <div class="esf-learning-content-card curso-capacitate-activity-card">
                                                <p class="text-xs uppercase tracking-[0.16em] font-black text-purple-600 dark:text-purple-300">
                                                    Evaluación
                                                </p>

                                                <h3 class="mt-1 text-xl font-black text-slate-900 dark:text-slate-100">
                                                    {{ $evaluacionFinal->titulo }}
                                                </h3>

                                                @if($evaluacionFinal->descripcion)
                                                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                                        {{ $evaluacionFinal->descripcion }}
                                                    </p>
                                                @endif

                                                @php
                                                    $resultadoEvaluacionLocal = session('resultado_evaluacion');
                                                    $idEvaluacionAviso = session('id_evaluacion_aviso');

                                                    $mostrarResultadoEvaluacionLocal = is_array($resultadoEvaluacionLocal)
                                                        && (int) $idEvaluacionAviso === (int) $evaluacionFinal->id_evaluacion;

                                                    $mostrarErrorEvaluacionLocal = $errors->has('evaluacion')
                                                        && (int) $idEvaluacionAviso === (int) $evaluacionFinal->id_evaluacion;

                                                    $claseResultadoEvaluacionLocal = 'border-slate-200 bg-slate-50 text-slate-700';

                                                    if ($mostrarResultadoEvaluacionLocal) {
                                                        $claseResultadoEvaluacionLocal = match($resultadoEvaluacionLocal['estado'] ?? '') {
                                                            'aprobado' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                                                            'reprobado' => 'border-red-200 bg-red-50 text-red-700',
                                                            default => 'border-slate-200 bg-slate-50 text-slate-700',
                                                        };
                                                    }
                                                @endphp

                                                @if($mostrarErrorEvaluacionLocal)
                                                    <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700 shadow-sm">
                                                        {{ $errors->first('evaluacion') }}
                                                    </div>
                                                @endif

                                                @if($mostrarResultadoEvaluacionLocal)
                                                    <div class="mt-4 rounded-2xl border px-4 py-3 text-sm font-bold shadow-sm {{ $claseResultadoEvaluacionLocal }}">
                                                        <p>
                                                            <span class="font-black">Resultado de evaluación:</span>
                                                            {{ ($resultadoEvaluacionLocal['estado'] ?? '') === 'aprobado' ? 'Aprobada' : 'Reprobada' }}
                                                        </p>

                                                        <p class="mt-1">
                                                            <span class="font-black">Nota:</span>
                                                            {{ !is_null($resultadoEvaluacionLocal['porcentaje'] ?? null) ? number_format((float) $resultadoEvaluacionLocal['porcentaje'], 2) . '%' : '-' }}
                                                        </p>

                                                        <p class="mt-1">
                                                            <span class="font-black">Intentos restantes:</span>
                                                            {{ is_null($resultadoEvaluacionLocal['intentos_restantes'] ?? null) ? 'Ilimitados' : $resultadoEvaluacionLocal['intentos_restantes'] }}
                                                        </p>
                                                    </div>
                                                @endif

                                                <div class="mt-4 flex flex-wrap gap-2">
                                                    <span class="esf-badge esf-badge-blue">
                                                        Intentos realizados: {{ $intentosEvalFinal }}
                                                    </span>

                                                    @if($aprobadaEvalFinal)
                                                        <span class="esf-badge esf-badge-green">
                                                            Aprobada
                                                        </span>
                                                    @elseif($maximoEvalFinal)
                                                        <span class="esf-badge esf-badge-red">
                                                            Intentos agotados
                                                        </span>
                                                    @else
                                                        <span class="esf-badge esf-badge-purple">
                                                            Disponible
                                                        </span>
                                                    @endif
                                                </div>

                                                @if($capacitacionFinalizadaParaUsuario && !$evaluacionCerradaFinal && !$puedeVerRevisionFinal)
                                                    <div class="mt-5 rounded-2xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm font-bold text-orange-800">
                                                        Esta capacitación ya finalizó. Solo puedes consultar el contenido del módulo; no se permite presentar evaluaciones.
                                                    </div>
                                                @else
                                                    <a href="{{ $urlAccionEvaluacion }}"
                                                        class="mt-5 esf-learning-big-action {{ $evaluacionCerradaFinal ? 'esf-learning-action-blue' : 'esf-learning-action-purple' }}">
                                                            @if($evaluacionCerradaFinal || $puedeVerRevisionFinal)
                                                                Ver resultado
                                                            @elseif($intentosEvalFinal > 0)
                                                                Presentar nuevamente
                                                            @else
                                                                Comenzar examen
                                                            @endif
                                                    </a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @endif

                            @if($recursosSinSeccion->count() || $ejerciciosSinSeccion->count() || $evaluacionesSinSeccion->count())
                                <div class="esf-learning-section p-6">
                                    <p class="font-black text-orange-700 dark:text-orange-300">
                                        Contenido pendiente de ubicar
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        Estos elementos todavía no han sido organizados por el administrador en una sección.
                                    </p>
                                </div>
                            @endif

        @endif

                        </div>
                    </section>
                </div>
            </div>

        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">

    <style>
        .ql-editor {
            font-size: 17px;
            line-height: 1.85;
        }

        .ql-editor img {
            max-width: 100%;
            height: auto;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 0 !important;
        }

        .ql-editor [style*="column-count"] {
            column-gap: 24px;
        }

        .ql-editor h1,
        .ql-editor h2,
        .ql-editor h3 {
            font-weight: 800;
            margin-top: 1.2rem;
            margin-bottom: 0.85rem;
            color: #0f172a;
        }

        .dark .ql-editor h1,
        .dark .ql-editor h2,
        .dark .ql-editor h3 {
            color: #f8fafc;
        }

        .ql-editor ul {
            list-style-type: disc;
            padding-left: 1.5rem;
        }

        .ql-editor ol {
            list-style-type: decimal;
            padding-left: 1.5rem;
        }

        .ql-editor a {
            color: #2563eb;
            text-decoration: underline;
            font-weight: 700;
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

        .curso-capacitate-content-ajustado {
            display: flex !important;
            flex-direction: column !important;
            gap: 0 !important;
        }

        .curso-capacitate-content-ajustado > * {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        .curso-capacitate-content-ajustado > * + * {
            margin-top: 0.85rem !important;
        }

        .curso-capacitate-page {
            clear: none !important;
            overflow: visible !important;
            padding: 0 !important;
            margin: 0 !important;
            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            min-height: 0 !important;
        }

        .curso-capacitate-page > div:first-child,
        .curso-capacitate-page > .curso-capacitate-section-title {
            clear: none !important;
        }

        .curso-capacitate-section-title {
            border-top: 2px solid rgba(14, 116, 144, 0.32) !important;
            border-bottom: 2px solid rgba(14, 116, 144, 0.32) !important;
            padding: 0.48rem 0 !important;
            margin: 0 0 0.82rem 0 !important;
        }

        .curso-capacitate-section-title h2 {
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.08 !important;
        }

        .contenido-teoria-render {
            display: block !important;
            clear: none !important;
            overflow: visible !important;
            margin: 0 !important;
            padding: 0 !important;
            min-height: 0 !important;
            line-height: 1.62 !important;
        }

        .contenido-teoria-render::after {
            content: none !important;
            display: none !important;
            clear: none !important;
        }

        .contenido-teoria-render > :first-child {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .contenido-teoria-render > :last-child {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }

        .contenido-teoria-render p {
            margin-top: 0 !important;
            margin-bottom: 0.42rem !important;
        }

        .contenido-teoria-render p:empty {
            display: none !important;
            margin: 0 !important;
            padding: 0 !important;
            height: 0 !important;
            line-height: 0 !important;
        }

        .curso-capacitate-activity-card {
            clear: none !important;
        }

        .contenido-teoria-render img[style*="float: left"],
        .contenido-teoria-render img[style*="float:left"],
        .contenido-teoria-render img[style*="float: right"],
        .contenido-teoria-render img[style*="float:right"] {
            margin-bottom: 0.45rem !important;
        }

        @media (max-width: 768px) {
            .contenido-teoria-render img[style*="float"] {
                float: none !important;
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 1rem auto !important;
            }
        }

        .curso-capacitate-page {
            padding-top: 1.75rem;
            padding-bottom: 1.75rem;
        }

        .curso-capacitate-page + .curso-capacitate-page {
            margin-top: 1.25rem;
        }

        .curso-capacitate-activity-card {
            margin-top: 1rem;
        }

        .curso-capacitate-page .border-b {
            margin-bottom: 0.9rem;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function () {
            const urlAvanceContenido = "{{ route('mis_modulos.avance_contenido', [$miCapacitacion->id_empleado_capacitacion, $modulo->id_capacitacion_modulo]) }}";
            const tokenAvanceContenido = "{{ csrf_token() }}";

            const capacitacionFinalizadaParaUsuario = @json($capacitacionFinalizadaParaUsuario);

            const avancesRegistradosEnPantalla = new Set(@json($avancesContenidoUsuario->keys()->values()));
            const avancesEnviandoseEnPantalla = new Set();

            const buscadorContenidoModuloUsuario = document.getElementById('buscarContenidoModuloUsuario');

            function normalizarBusquedaModuloUsuario(texto) {
                return (texto || '')
                    .toString()
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .trim();
            }

            function filtrarSidebarModuloUsuario() {
                if (!buscadorContenidoModuloUsuario) {
                    return;
                }

                const valor = normalizarBusquedaModuloUsuario(buscadorContenidoModuloUsuario.value);
                const gruposModulo = document.querySelectorAll('.curso-capacitate-sidebar nav details');

                gruposModulo.forEach(function (grupo) {
                    const resumen = grupo.querySelector('summary');
                    const textoResumen = normalizarBusquedaModuloUsuario(resumen ? resumen.textContent : '');
                    const enlaces = grupo.querySelectorAll('.esf-learning-nav-link');

                    const moduloCoincide = valor === '' || textoResumen.includes(valor);
                    let tieneCoincidencias = moduloCoincide;

                    enlaces.forEach(function (enlace) {
                        const textoEnlace = normalizarBusquedaModuloUsuario(enlace.textContent);
                        const coincide = valor === '' || moduloCoincide || textoEnlace.includes(valor);

                        enlace.style.display = coincide ? '' : 'none';

                        if (coincide) {
                            tieneCoincidencias = true;
                        }
                    });

                    grupo.style.display = tieneCoincidencias ? '' : 'none';

                    if (valor !== '' && tieneCoincidencias) {
                        grupo.open = true;
                    }
                });
            }

            if (buscadorContenidoModuloUsuario) {
                buscadorContenidoModuloUsuario.addEventListener('input', filtrarSidebarModuloUsuario);
            }

            function actualizarProgresoModuloVisual(progreso) {
                if (progreso === null || progreso === undefined || Number.isNaN(Number(progreso))) {
                    return;
                }

                const progresoSeguro = Math.max(0, Math.min(100, Number(progreso)));

                document.querySelectorAll('[data-progreso-modulo-barra]').forEach(function (barra) {
                    barra.style.width = progresoSeguro + '%';
                });

                document.querySelectorAll('[data-progreso-modulo-texto]').forEach(function (texto) {
                    texto.textContent = progresoSeguro.toFixed(2) + '%';
                });

                document.querySelectorAll('[data-progreso-modulo-entero]').forEach(function (texto) {
                    texto.textContent = Math.round(progresoSeguro);
                });
            }

            function pintarCirculoAvance(tipo, id) {
                const clave = tipo + ':' + id;
                const circulos = document.querySelectorAll('[data-circulo-avance="' + clave + '"]');

                circulos.forEach(function (circulo) {
                    circulo.classList.remove('bg-white', 'border-green-400');
                    circulo.classList.add('esf-learning-dot-done', 'bg-green-500', 'border-green-500', 'text-white');

                    if (!circulo.textContent.trim()) {
                        circulo.innerHTML = '<span class="text-[10px] text-white">✓</span>';
                    }
                });
            }

            function registrarAvanceContenido(tipo, id) {
                if (!tipo || !id) {
                    return;
                }

                if (capacitacionFinalizadaParaUsuario) {
                    return;
                }

                if (tipo === 'evaluacion') {
                    return;
                }

                const clave = tipo + ':' + id;

                if (avancesRegistradosEnPantalla.has(clave)) {
                    return;
                }

                if (avancesEnviandoseEnPantalla.has(clave)) {
                    return;
                }

                avancesEnviandoseEnPantalla.add(clave);

                fetch(urlAvanceContenido, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': tokenAvanceContenido,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        tipo_contenido: tipo,
                        id_contenido: id
                    })
                })
                    .then(function (respuesta) {
                        if (!respuesta.ok) {
                            avancesEnviandoseEnPantalla.delete(clave);
                            return null;
                        }

                        return respuesta.json();
                    })
                    .then(function (data) {
                        if (!data || data.ok !== true) {
                            avancesEnviandoseEnPantalla.delete(clave);
                            return;
                        }

                        avancesRegistradosEnPantalla.add(clave);
                        avancesEnviandoseEnPantalla.delete(clave);

                        pintarCirculoAvance(tipo, id);

                        if (data.progreso_modulo !== undefined) {
                            actualizarProgresoModuloVisual(data.progreso_modulo);
                        }
                    })
                    .catch(function () {
                        avancesEnviandoseEnPantalla.delete(clave);
                    });
            }

            function obtenerRectContenedorLecturaModulo() {
                const contenedor = document.querySelector('.curso-capacitate-main');

                if (contenedor) {
                    return contenedor.getBoundingClientRect();
                }

                return {
                    top: 0,
                    bottom: window.innerHeight
                };
            }

            function elementoYaFuePasadoModulo(elemento, siguienteElemento) {
                const rectContenedor = obtenerRectContenedorLecturaModulo();
                const lineaLectura = rectContenedor.top + 90;

                if (siguienteElemento) {
                    return siguienteElemento.getBoundingClientRect().top <= lineaLectura;
                }

                const rectElemento = elemento.getBoundingClientRect();

                return rectElemento.bottom <= rectContenedor.bottom - 20
                    && rectElemento.top < rectContenedor.bottom;
            }

            function revisarAvancesPorScrollFinal() {
                const elementos = Array.from(document.querySelectorAll('.contenido-avance-scroll-final[data-avance-contenido]'));

                elementos.forEach(function (elemento, indice) {
                    if (elemento.dataset.avanceRegistrado === '1') {
                        return;
                    }

                    const claveElemento = elemento.dataset.tipoContenido + ':' + elemento.dataset.idContenido;

                    if (avancesRegistradosEnPantalla.has(claveElemento)) {
                        elemento.dataset.avanceRegistrado = '1';
                        return;
                    }

                    const siguienteElemento = elementos[indice + 1] || null;

                    if (elementoYaFuePasadoModulo(elemento, siguienteElemento)) {
                        elemento.dataset.avanceRegistrado = '1';

                        registrarAvanceContenido(
                            elemento.dataset.tipoContenido,
                            elemento.dataset.idContenido
                        );
                    }
                });
            }

            let esperandoFrameAvance = false;

            function programarRevisionAvance() {
                if (esperandoFrameAvance) {
                    return;
                }

                esperandoFrameAvance = true;

                window.requestAnimationFrame(function () {
                    revisarAvancesPorScrollFinal();
                    esperandoFrameAvance = false;
                });
            }

            const contenedorScrollModulo = document.querySelector('.curso-capacitate-main');

            if (contenedorScrollModulo) {
                contenedorScrollModulo.addEventListener('scroll', programarRevisionAvance);
            }


            window.addEventListener('scroll', programarRevisionAvance);
            window.addEventListener('resize', programarRevisionAvance);

            document.querySelectorAll('video, audio').forEach(function (medio) {
                const contenedorRecurso = medio.closest('[data-tipo-contenido="recurso"]');

                if (!contenedorRecurso) {
                    return;
                }

                medio.addEventListener('ended', function () {
                    contenedorRecurso.dataset.avanceRegistrado = '1';

                    registrarAvanceContenido(
                        contenedorRecurso.dataset.tipoContenido,
                        contenedorRecurso.dataset.idContenido
                    );
                });
            });

            document.querySelectorAll('[data-marcar-recurso="1"]').forEach(function (enlaceRecurso) {
                enlaceRecurso.addEventListener('click', function () {
                    registrarAvanceContenido(
                        enlaceRecurso.dataset.tipoContenido,
                        enlaceRecurso.dataset.idContenido
                    );
                });
            });

            const enlacesMenuSeccion = document.querySelectorAll('[data-menu-section]');
            const seccionesObservablesMenu = document.querySelectorAll('[data-section-observer]');

            enlacesMenuSeccion.forEach(function (enlaceMenuSeccion) {
                enlaceMenuSeccion.addEventListener('click', function () {
                    const idSeccion = enlaceMenuSeccion.dataset.menuSection;

                    if (!idSeccion) {
                        return;
                    }

                    window.setTimeout(function () {
                        registrarAvanceContenido('seccion', idSeccion);
                        programarRevisionAvance();
                    }, 350);
                });
            });

            if (seccionesObservablesMenu.length > 0 && enlacesMenuSeccion.length > 0) {
                const observadorMenu = new IntersectionObserver(function (entradas) {
                    entradas.forEach(function (entrada) {
                        if (!entrada.isIntersecting) {
                            return;
                        }

                        const idSeccionVisible = entrada.target.dataset.sectionObserver;

                        enlacesMenuSeccion.forEach(function (enlace) {
                            enlace.classList.toggle(
                                'esf-learning-active',
                                enlace.dataset.menuSection === idSeccionVisible
                            );
                        });
                    });
                }, {
                    root: document.querySelector('.esf-learning-main'),
                    threshold: 0.35
                });

                seccionesObservablesMenu.forEach(function (seccion) {
                    observadorMenu.observe(seccion);
                });
            }

            revisarAvancesPorScrollFinal();
        });
    </script>
</x-app-layout>
