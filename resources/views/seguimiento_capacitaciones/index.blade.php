<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                {{ ($esInstructorSeguimiento ?? false) ? 'Seguimiento del instructor' : 'Seguimiento administrativo' }}
            </p>

            <h2 class="esf-seguimiento-title">
                {{ ($esInstructorSeguimiento ?? false) ? 'Seguimiento de mis capacitaciones' : 'Seguimiento de capacitaciones' }}
            </h2>

            <p class="esf-seguimiento-subtitle">
                {{ ($esInstructorSeguimiento ?? false)
                    ? 'Revisa únicamente el avance, vencimientos y actividad de los empleados asignados a tus capacitaciones.'
                    : 'Revisa el avance, vencimientos, estado y actividad de los empleados asignados.' }}
            </p>
        </div>
    </x-slot>

    <div class="py-4 esf-seguimiento-page esf-tracking-compact">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-5 rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-800 shadow-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <dl class="esf-tracking-summary" aria-label="Resumen de seguimiento">
                <div class="esf-tracking-stat-slate"><dt>Registros</dt><dd>{{ $totalRegistros }}</dd></div>
                <div class="esf-tracking-stat-blue"><dt>En proceso</dt><dd>{{ $totalEnProceso }}</dd></div>
                <div class="esf-tracking-stat-green"><dt>Aprobadas</dt><dd>{{ $totalAprobadas }}</dd></div>
                <div class="esf-tracking-stat-red"><dt>Reprobadas</dt><dd>{{ $totalReprobadas }}</dd></div>
                <div class="esf-tracking-stat-amber"><dt>Pendientes</dt><dd>{{ $totalPendientes }}</dd></div>
                <div class="esf-tracking-stat-red"><dt>Vencidas</dt><dd>{{ $totalVencidas }}</dd></div>
                <div class="esf-tracking-stat-amber"><dt>Por vencer · 30 días</dt><dd>{{ $totalPorVencer }}</dd></div>
                <div class="esf-tracking-stat-purple"><dt>Por revisar</dt><dd>{{ $totalPendientesRevision }}</dd></div>
                <div class="esf-tracking-stat-blue"><dt>Con avance</dt><dd>{{ $totalConAvance }}</dd></div>
                <div class="esf-tracking-stat-slate"><dt>Sin avance</dt><dd>{{ $totalSinAvance }}</dd></div>
            </dl>

            <form method="GET" action="{{ route('seguimiento_capacitaciones.index') }}"
                  class="esf-tracking-filters">
                <div class="esf-tracking-primary-filters">
                    <div>
                        <label class="block mb-1 text-sm font-medium">Buscar</label>
                        <input
                            type="text"
                            name="buscar"
                            value="{{ $buscar }}"
                            placeholder="Empleado, código, identidad o capacitación"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm focus:border-blue-300 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                        >
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium">Capacitación</label>
                        <select name="id_capacitacion" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm focus:border-blue-300 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">Todas</option>
                            @foreach($capacitaciones as $capacitacion)
                                <option value="{{ $capacitacion->id_capacitacion }}" {{ (string) $idCapacitacion === (string) $capacitacion->id_capacitacion ? 'selected' : '' }}>
                                    {{ $capacitacion->capacitacion }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium">Estado</label>
                        <select name="estado" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm focus:border-blue-300 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">Todos</option>
                            @foreach($estados as $itemEstado)
                                @php
                                    $itemEstadoTexto = match($itemEstado) {
                                        'pendiente' => 'Pendiente',
                                        'en_proceso' => 'En proceso',
                                        'aprobada' => 'Aprobada',
                                        'reprobada' => 'Reprobada por evaluación',
                                        'vencida' => 'Reprobada por fecha límite',
                                        'cancelada' => 'Cancelada',
                                        default => ucfirst(str_replace('_', ' ', $itemEstado)),
                                    };
                                @endphp

                                <option value="{{ $itemEstado }}" {{ $estado === $itemEstado ? 'selected' : '' }}>
                                    {{ $itemEstadoTexto }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="esf-tracking-filter-actions">
                        <button type="submit"
                                class="esf-btn esf-btn-primary ">
                            Filtrar
                        </button>

                        <a href="{{ route('seguimiento_capacitaciones.index') }}"
                        class="esf-btn esf-btn-soft  text-center">
                            Limpiar
                        </a>
                    </div>
                </div>
                @php
                    $filtrosAdicionales = collect([$idDepartamento, $idPuestoTrabajoMatriz, $seguimiento, $vencimiento, $fechaDesde, $fechaHasta])->filter(fn ($valor) => filled($valor))->count();
                @endphp
                <details class="esf-tracking-more-filters" @if($filtrosAdicionales) open @endif>
                    <summary>Más filtros @if($filtrosAdicionales)<span>· {{ $filtrosAdicionales }} activos</span>@endif</summary>
                    <div class="esf-tracking-advanced-filters">
                    <div>
                        <label class="block mb-1 text-sm font-medium">Departamento</label>
                        <select name="id_departamento" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm focus:border-blue-300 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">Todos</option>
                            @foreach($departamentos as $departamento)
                                <option value="{{ $departamento->id_departamento }}" {{ (string) $idDepartamento === (string) $departamento->id_departamento ? 'selected' : '' }}>
                                    {{ $departamento->departamento }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium">Puesto</label>
                        <select name="id_puesto_trabajo_matriz" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm focus:border-blue-300 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">Todos</option>
                            @foreach($puestos as $puesto)
                                <option value="{{ $puesto->id_puesto_trabajo_matriz }}" {{ (string) $idPuestoTrabajoMatriz === (string) $puesto->id_puesto_trabajo_matriz ? 'selected' : '' }}>
                                    {{ $puesto->puesto_trabajo_matriz }}
                                    @if($puesto->departamento)
                                        - {{ $puesto->departamento->departamento }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium">Seguimiento</label>
                        <select name="seguimiento" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm focus:border-blue-300 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">Todos</option>
                            <option value="con_avance" {{ $seguimiento === 'con_avance' ? 'selected' : '' }}>Han continuado</option>
                            <option value="sin_avance" {{ $seguimiento === 'sin_avance' ? 'selected' : '' }}>No han continuado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium">Vencimiento</label>
                        <select name="vencimiento" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm focus:border-blue-300 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">Todos</option>
                            <option value="vencidas" {{ $vencimiento === 'vencidas' ? 'selected' : '' }}>Vencidas</option>
                            <option value="por_vencer" {{ $vencimiento === 'por_vencer' ? 'selected' : '' }}>Próximas a vencer</option>
                            <option value="sin_fecha" {{ $vencimiento === 'sin_fecha' ? 'selected' : '' }}>Sin fecha de vencimiento</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium">Fecha asignación desde</label>
                        <input
                            type="date"
                            name="fecha_desde"
                            value="{{ $fechaDesde }}"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm focus:border-blue-300 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                        >
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-black text-slate-700 dark:text-slate-200">
                            Fecha asignación hasta
                        </label>

                        <input
                            type="date"
                            name="fecha_hasta"
                            value="{{ $fechaHasta }}"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm focus:border-blue-300 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                        >
                    </div>
                    </div>
                </details>
            </form>

            <div class="esf-seguimiento-table-card esf-admin-sheet-card" x-data="{ todasLasColumnas: false }">
                <div class="esf-tracking-table-toolbar">
                    <span><strong>{{ $seguimientos->total() }}</strong> registros encontrados</span>
                    <label><input type="checkbox" x-model="todasLasColumnas"> Ver todas las columnas</label>
                </div>
                <div class="esf-seguimiento-table-scroll esf-no-sticky-fields">
                    <table class="esf-seguimiento-table-modern esf-tracking-table" :class="{ 'esf-tracking-table-expanded': todasLasColumnas }">
                        <thead>
                            <tr class="bg-slate-50 text-[11px] uppercase tracking-[0.14em] text-slate-500 dark:bg-slate-900/80 dark:text-slate-300">
                                <th class="px-4 py-4 text-left font-black">Empleado</th>
                                <th class="px-4 py-4 text-left font-black">Código</th>
                                <th class="px-4 py-4 text-left font-black">Puesto</th>
                                <th class="px-4 py-4 text-left font-black">Departamento</th>
                                <th class="px-4 py-4 text-left font-black">Capacitación</th>
                                <th class="px-4 py-4 text-center font-black">Estado</th>
                                <th class="px-4 py-4 text-center font-black">Vencimiento</th>
                                <th class="px-4 py-4 text-center font-black">Días</th>
                                <th class="px-4 py-4 text-left font-black">Progreso</th>
                                <th class="px-4 py-4 text-center font-black">Nota final</th>
                                <th class="px-4 py-4 text-center font-black">Última actividad</th>
                                <th class="px-4 py-4 text-center font-black">Ejercicios</th>
                                <th class="px-4 py-4 text-center font-black">Comp.</th>
                                <th class="px-4 py-4 text-center font-black">Pend.</th>
                                <th class="px-4 py-4 text-center font-black">Rev. manual</th>
                                <th class="px-4 py-4 text-center font-black">Últ. actividad ejercicios</th>
                                <th class="px-4 py-4 text-center font-black">Continuó</th>
                                <th class="px-4 py-4 text-center font-black">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($seguimientos as $item)
                               @php
                                    $estadoVisual = $item->estado_visual_admin ?? $item->estado;

                                    $estadoTexto = match($estadoVisual) {
                                        'pendiente' => 'Pendiente',
                                        'en_proceso' => 'En proceso',
                                        'aprobada' => 'Aprobada',
                                        'reprobada' => 'Reprobada por evaluación',
                                        'vencida' => 'Reprobada por fecha límite',
                                        'cancelada' => 'Cancelada',
                                        'pendiente_revision' => 'Pendiente de revisión',
                                        default => ucfirst(str_replace('_', ' ', $estadoVisual)),
                                    };

                                    $vencimientoClase = match($item->vencimiento_visual_admin ?? 'sin_fecha') {
                                        'vencida' => 'bg-orange-50 text-orange-700 border border-orange-200',
                                        'por_vencer' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                        'vigente' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                        default => 'bg-slate-100 text-slate-700 border border-slate-200',
                                    };

                                    $vencimientoTexto = match($item->vencimiento_visual_admin ?? 'sin_fecha') {
                                        'vencida' => 'Vencida',
                                        'por_vencer' => 'Próxima a vencer',
                                        'vigente' => 'Vigente',
                                        default => 'Sin fecha',
                                    };

                                    $nombreEmpleadoTabla = $item->empleado?->nombre_completo ?? '-';

                                    $inicialesEmpleadoTabla = collect(preg_split('/\s+/', trim($nombreEmpleadoTabla)))
                                        ->filter()
                                        ->take(2)
                                        ->map(fn ($parteNombre) => mb_strtoupper(mb_substr($parteNombre, 0, 1)))
                                        ->implode('');

                                    if ($inicialesEmpleadoTabla === '' || $nombreEmpleadoTabla === '-') {
                                        $inicialesEmpleadoTabla = 'NA';
                                    }
                                @endphp

                                <tr class="border-b border-slate-100 bg-white text-sm text-slate-700 transition hover:bg-blue-50/40 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-200 dark:hover:bg-slate-900">
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2 min-w-[180px]">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-sm font-black uppercase text-blue-700 ring-1 ring-blue-100 dark:bg-blue-900/40 dark:text-blue-100 dark:ring-blue-700/60">
                                                {{ $inicialesEmpleadoTabla }}
                                            </div>

                                            <div>
                                                <div class="font-black text-slate-900 dark:text-slate-100">
                                                    {{ $nombreEmpleadoTabla }}
                                                </div>

                                                <div class="text-xs font-semibold text-slate-400">
                                                    {{ $item->empleado?->codigo_empleado ?? 'Sin código' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 font-semibold text-slate-600 dark:text-slate-300">
                                        {{ $item->empleado?->codigo_empleado ?? '-' }}
                                    </td>

                                    <td class="px-4 py-4">
                                        {{ $item->empleado?->puestoTrabajo?->puesto_trabajo_matriz ?? '-' }}
                                    </td>

                                    <td class="px-4 py-4">
                                        {{ $item->empleado?->puestoTrabajo?->departamento?->departamento ?? '-' }}
                                    </td>

                                    <td class="px-4 py-4 font-semibold text-slate-900 dark:text-slate-100 min-w-[180px]">
                                        {{ $item->capacitacion?->capacitacion ?? '-' }}
                                    </td>

                                    <td class="px-4 py-4 text-center">
                                        @php
                                            $estadoVisual = $item->estado ?? 'pendiente';

                                            $estadoClase = match($estadoVisual) {
                                                'pendiente' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                                'en_proceso' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                                'aprobada' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                                'reprobada' => 'bg-red-50 text-red-700 border border-red-200',
                                                'vencida' => 'bg-orange-50 text-orange-700 border border-orange-200',
                                                'cancelada' => 'bg-slate-100 text-slate-700 border border-slate-200',
                                                'pendiente_revision' => 'bg-purple-50 text-purple-700 border border-purple-200',
                                                default => 'bg-slate-100 text-slate-700 border border-slate-200',
                                            };

                                            $estadoTexto = match($estadoVisual) {
                                                'pendiente' => 'Pendiente',
                                                'en_proceso' => 'En proceso',
                                                'aprobada' => 'Aprobada',
                                                'reprobada' => 'Reprobada por evaluación',
                                                'vencida' => 'Reprobada por fecha límite',
                                                'cancelada' => 'Cancelada',
                                                'pendiente_revision' => 'Pendiente de revisión',
                                                default => ucfirst(str_replace('_', ' ', $estadoVisual)),
                                            };
                                        @endphp

                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-black {{ $estadoClase }}">
                                            {{ $estadoTexto }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-black {{ $vencimientoClase }}">
                                            {{ $vencimientoTexto }}
                                        </span>

                                        @if($item->fecha_vencimiento)
                                            <div class="mt-1 text-xs font-semibold text-slate-400">
                                                {{ $item->fecha_vencimiento->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4 text-center font-semibold">
                                        @if(is_null($item->dias_vencimiento_admin))
                                            -
                                        @elseif($item->dias_vencimiento_admin < 0)
                                            {{ abs($item->dias_vencimiento_admin) }} día(s) vencida
                                        @elseif($item->dias_vencimiento_admin === 0)
                                            Vence hoy
                                        @else
                                            Faltan {{ $item->dias_vencimiento_admin }} día(s)
                                        @endif
                                    </td>

                                    <td class="px-4 py-4 min-w-[170px]">
                                        @php
                                            $progresoTabla = (float) ($item->progreso ?? 0);
                                        @endphp

                                        <div class="esf-seguimiento-progress-cell">
                                            <div class="esf-seguimiento-progress-top">
                                                <span>Avance</span>
                                                <span class="esf-seguimiento-progress-value">
                                                    {{ number_format($progresoTabla, 2) }}%
                                                </span>
                                            </div>

                                            <div class="esf-seguimiento-progress-track">
                                                <div class="esf-seguimiento-progress-bar"
                                                    style="width: {{ min(100, max(0, $progresoTabla)) }}%;">
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 text-center font-black">
                                        {{ !is_null($item->nota_final) ? number_format((float) $item->nota_final, 2) . '%' : '-' }}
                                    </td>

                                    <td class="px-4 py-4 text-center text-xs font-semibold text-slate-500">
                                        {{ $item->ultima_actividad_admin ? $item->ultima_actividad_admin->format('d/m/Y H:i') : '-' }}
                                    </td>

                                    <td class="px-4 py-4 text-center font-black">
                                        {{ $item->resumen_ejercicios['total'] ?? 0 }}
                                    </td>

                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex min-w-[34px] justify-center rounded-full bg-emerald-50 px-2 py-1 text-xs font-black text-emerald-700 ring-1 ring-emerald-100">
                                            {{ $item->resumen_ejercicios['completados'] ?? 0 }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex min-w-[34px] justify-center rounded-full bg-amber-50 px-2 py-1 text-xs font-black text-amber-700 ring-1 ring-amber-100">
                                            {{ $item->resumen_ejercicios['pendientes'] ?? 0 }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex min-w-[34px] justify-center rounded-full bg-purple-50 px-2 py-1 text-xs font-black text-purple-700 ring-1 ring-purple-100">
                                            {{ $item->resumen_ejercicios['pendientes_revision'] ?? 0 }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 text-center text-xs font-semibold text-slate-500">
                                        {{ !empty($item->resumen_ejercicios['ultima_actividad']) ? \Illuminate\Support\Carbon::parse($item->resumen_ejercicios['ultima_actividad'])->format('d/m/Y H:i') : '-' }}
                                    </td>

                                    <td class="px-4 py-4 text-center">
                                        @if($item->ha_continuado_admin)
                                            <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700 ring-1 ring-emerald-100">
                                                Sí
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600 ring-1 ring-slate-200">
                                                No
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="flex min-w-[150px] flex-col gap-2">
                                            @if($item->empleado)
                                                <a href="{{ route('seguimiento_capacitaciones.expediente_empleado', $item->empleado->id_empleado) }}"
                                                class="esf-action-btn esf-action-edit justify-center text-center">
                                                    Ver expediente
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="18" class="px-6 py-10 text-center">
                                        <div class="mx-auto max-w-md rounded-3xl border border-slate-200 bg-slate-50 px-6 py-7 text-slate-500 shadow-sm">
                                            <p class="text-base font-black text-slate-800">
                                                No hay registros con esos filtros.
                                            </p>

                                            <p class="mt-1 text-sm">
                                                Prueba limpiar los filtros o buscar otro empleado/capacitación.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $seguimientos->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>