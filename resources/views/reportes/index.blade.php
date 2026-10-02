<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                Reportes administrativos
            </p>

            <h2 class="esf-seguimiento-title">
                Reportes de capacitaciones
            </h2>

            <p class="esf-seguimiento-subtitle">
                Filtra, revisa y exporta el avance de capacitaciones por empleado.
            </p>
        </div>
    </x-slot>

    <div class="py-8 esf-seguimiento-page esf-reports-page">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

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

            <div class="esf-seguimiento-kpi-grid esf-kpi-balanced-reportes">
                <div class="esf-seguimiento-kpi esf-seguimiento-kpi-slate">
                    <p>Total registros</p>
                    <p>{{ $resumen['total'] }}</p>
                </div>

                <div class="esf-seguimiento-kpi esf-seguimiento-kpi-amber">
                    <p>Pendientes</p>
                    <p>{{ $resumen['pendientes'] }}</p>
                </div>

                <div class="esf-seguimiento-kpi esf-seguimiento-kpi-blue">
                    <p>En proceso</p>
                    <p>{{ $resumen['en_proceso'] }}</p>
                </div>

                <div class="esf-seguimiento-kpi esf-seguimiento-kpi-green">
                    <p>Aprobadas</p>
                    <p>{{ $resumen['aprobadas'] }}</p>
                </div>

                <div class="esf-seguimiento-kpi esf-seguimiento-kpi-red">
                    <p>Reprobadas</p>
                    <p>{{ $resumen['reprobadas'] }}</p>
                </div>

                <div class="esf-seguimiento-kpi esf-seguimiento-kpi-amber">
                    <p>Vencidas</p>
                    <p>{{ $resumen['vencidas'] }}</p>
                </div>

                <div class="esf-seguimiento-kpi esf-seguimiento-kpi-amber">
                    <p>Próximas a vencer</p>
                    <p>{{ $resumen['por_vencer'] }}</p>
                </div>

            </div>

            <form method="GET"
                  action="{{ route('reportes.index') }}"
                  class="esf-seguimiento-panel esf-reports-filters">
                <div class="esf-reports-filter-heading">
                    <span class="font-black text-sm text-slate-700 dark:text-slate-200">Filtros del reporte</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Refina los resultados y exporta tu consulta</span>
                </div>
                <div class="esf-seguimiento-filter-grid esf-reports-filter-grid">
                    <div>
                        <label for="reporte-tipo_reporte">Tipo de reporte</label>
                        <select id="reporte-tipo_reporte" name="tipo_reporte">
                            @foreach($tiposReporte as $valor => $texto)
                                <option value="{{ $valor }}" {{ $tipoReporte === $valor ? 'selected' : '' }}>
                                    {{ $texto }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="reporte-buscar">Buscar</label>
                        <input
                            id="reporte-buscar" type="text"
                            name="buscar"
                            value="{{ $buscar }}"
                            placeholder="Empleado, código, identidad o capacitación"
                        >
                    </div>

                    <div>
                        <label for="reporte-id_empleado">Empleado</label>
                        <select id="reporte-id_empleado" name="id_empleado">
                            <option value="">Todos los empleados</option>
                            @foreach($empleados as $empleado)
                                <option value="{{ $empleado->id_empleado }}" {{ (string) $idEmpleado === (string) $empleado->id_empleado ? 'selected' : '' }}>
                                    {{ $empleado->nombre_completo }}
                                    @if($empleado->codigo_empleado)
                                        - {{ $empleado->codigo_empleado }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="reporte-id_capacitacion">Capacitación</label>
                        <select id="reporte-id_capacitacion" name="id_capacitacion">
                            <option value="">Todas las capacitaciones</option>
                            @foreach($capacitaciones as $capacitacion)
                                <option value="{{ $capacitacion->id_capacitacion }}" {{ (string) $idCapacitacion === (string) $capacitacion->id_capacitacion ? 'selected' : '' }}>
                                    {{ $capacitacion->capacitacion }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>
                <details class="esf-reports-more-filters" @if(collect([$idDepartamento, $idPuestoTrabajoMatriz, $estado, $aprobado, $fechaDesde, $fechaHasta])->contains(fn ($valor) => $valor !== null && $valor !== '')) open @endif>
                    <summary>Más filtros <span>Departamento, puesto, estado y fechas</span></summary>
                    <div class="esf-seguimiento-filter-grid esf-reports-filter-grid">
                    <div>
                        <label for="reporte-id_departamento">Departamento</label>
                        <select id="reporte-id_departamento" name="id_departamento">
                            <option value="">Todos los departamentos</option>
                            @foreach($departamentos as $departamento)
                                <option value="{{ $departamento->id_departamento }}" {{ (string) $idDepartamento === (string) $departamento->id_departamento ? 'selected' : '' }}>
                                    {{ $departamento->departamento }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="reporte-id_puesto_trabajo_matriz">Puesto</label>
                        <select id="reporte-id_puesto_trabajo_matriz" name="id_puesto_trabajo_matriz">
                            <option value="">Todos los puestos</option>
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
                        <label for="reporte-estado">Estado</label>
                        <select id="reporte-estado" name="estado">
                            <option value="">Todos los estados</option>
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

                    <div>
                        <label for="reporte-aprobado">Aprobado</label>
                        <select id="reporte-aprobado" name="aprobado">
                            <option value="">Todos</option>
                            <option value="1" {{ $aprobado === '1' ? 'selected' : '' }}>Sí</option>
                            <option value="0" {{ $aprobado === '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <div>
                        <label for="reporte-fecha_desde">Fecha asignación desde</label>
                        <input
                            id="reporte-fecha_desde" type="date"
                            name="fecha_desde"
                            value="{{ $fechaDesde }}"
                        >
                    </div>

                    <div>
                        <label for="reporte-fecha_hasta">Fecha asignación hasta</label>
                        <input
                            id="reporte-fecha_hasta" type="date"
                            name="fecha_hasta"
                            value="{{ $fechaHasta }}"
                        >
                    </div>

                    </div>
                </details>
                    <div class="esf-reports-filter-actions">
                        <button type="submit" class="esf-btn esf-btn-primary">
                            Filtrar
                        </button>

                        <a href="{{ route('reportes.index') }}"
                           class="esf-btn esf-btn-soft text-center">
                            Limpiar
                        </a>
                    </div>
            </form>

            <div class="esf-seguimiento-table-card esf-admin-sheet-card esf-history-card">
                <div class="esf-admin-table-toolbar">
                    <div>
                        <p class="esf-history-kicker">Centro de exportación</p>

                        <h3 class="esf-admin-table-title">
                            Resultados agrupados por empleado
                        </h3>

                        <p class="esf-admin-table-subtitle">
                            Capacitaciones y avance de cada empleado según los filtros seleccionados.
                        </p>
                    </div>

                    <div class="esf-admin-top-actions">
                        <a href="{{ route('reportes.excel', request()->query()) }}"
                           class="esf-btn esf-btn-green">
                            Exportar CSV / Excel
                        </a>

                        <a href="{{ route('reportes.pdf', request()->query()) }}"
                           class="esf-btn esf-btn-soft">
                            Exportar PDF general
                        </a>

                        @if(!empty($idEmpleado))
                            <a href="{{ route('reportes.empleado.expediente_pdf', $idEmpleado) }}"
                               class="esf-btn esf-btn-primary">
                                Exportar expediente PDF
                            </a>
                        @endif
                    </div>
                </div>

                @php
                    $reportesAgrupados = $reportes->getCollection()->groupBy(function ($reporte) {
                        return $reporte->empleado?->id_empleado
                            ? 'empleado-' . $reporte->empleado->id_empleado
                            : 'sin-empleado-' . $reporte->id_empleado_capacitacion;
                    });
                @endphp

                <div class="esf-report-employee-list">
                    @forelse($reportesAgrupados as $grupoReporte)
                        @php
                            $primerReporte = $grupoReporte->first();
                            $empleadoReporte = $primerReporte->empleado;
                            $nombreEmpleadoReporte = $empleadoReporte?->nombre_completo ?? 'Empleado no identificado';

                            $inicialesReporte = collect(preg_split('/\s+/', trim($nombreEmpleadoReporte)))
                                ->filter()
                                ->take(2)
                                ->map(fn ($parteNombre) => mb_strtoupper(mb_substr($parteNombre, 0, 1)))
                                ->implode('');

                            if ($inicialesReporte === '') {
                                $inicialesReporte = 'NA';
                            }
                        @endphp

                        <div class="esf-report-employee-card">
                            <div class="esf-report-employee-header">
                                <div class="flex items-start gap-3">
                                    <div class="esf-admin-initials">
                                        {{ $inicialesReporte }}
                                    </div>

                                    <div>
                                        <p class="esf-history-kicker">Empleado</p>

                                        <h4 class="esf-history-heading">
                                            {{ $nombreEmpleadoReporte }}
                                        </h4>

                                        <p class="esf-history-subtitle">
                                            Código: {{ $empleadoReporte?->codigo_empleado ?? '-' }}
                                            · Puesto: {{ $empleadoReporte?->puestoTrabajo?->puesto_trabajo_matriz ?? '-' }}
                                            · Departamento: {{ $empleadoReporte?->puestoTrabajo?->departamento?->departamento ?? '-' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="esf-history-actions">
                                    <span class="esf-history-btn-muted">
                                        {{ $grupoReporte->count() }} capacitación(es)
                                    </span>

                                    @if($empleadoReporte)
                                        <a href="{{ route('seguimiento_capacitaciones.expediente_empleado', $empleadoReporte->id_empleado) }}"
                                           class="esf-history-btn-secondary">
                                            Ver expediente
                                        </a>

                                        <a href="{{ route('reportes.empleado.expediente_pdf', $empleadoReporte->id_empleado) }}"
                                           class="esf-history-btn-green">
                                            Expediente PDF
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="esf-report-employee-body">
                                <div class="esf-report-training-grid">
                                    @foreach($grupoReporte as $reporte)
                                        @php
                                            $estadoClase = match($reporte->estado) {
                                                'pendiente' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                                'en_proceso' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                                'aprobada' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                                'reprobada' => 'bg-red-50 text-red-700 border border-red-200',
                                                'vencida' => 'bg-orange-50 text-orange-700 border border-orange-200',
                                                'cancelada' => 'bg-slate-100 text-slate-700 border border-slate-200',
                                                default => 'bg-slate-100 text-slate-700 border border-slate-200',
                                            };

                                            $estadoTextoReporte = match($reporte->estado) {
                                                'pendiente' => 'Pendiente',
                                                'en_proceso' => 'En proceso',
                                                'aprobada' => 'Aprobada',
                                                'reprobada' => 'Reprobada por evaluación',
                                                'vencida' => 'Reprobada por fecha límite',
                                                'cancelada' => 'Cancelada',
                                                default => ucfirst(str_replace('_', ' ', $reporte->estado ?? 'pendiente')),
                                            };

                                            $progresoReporte = (float) ($reporte->progreso ?? 0);
                                        @endphp

                                        <div class="esf-report-training-card">
                                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                                <div>
                                                    <p class="esf-history-kicker">Capacitación asignada</p>

                                                    <h5 class="mt-1 text-base font-black text-slate-900 dark:text-slate-100">
                                                        {{ $reporte->capacitacion?->capacitacion ?? '-' }}
                                                    </h5>

                                                    <div class="mt-2 flex flex-wrap gap-2">
                                                        <span class="esf-history-badge {{ $estadoClase }}">
                                                            {{ $estadoTextoReporte }}
                                                        </span>

                                                        <span class="esf-history-badge bg-slate-100 text-slate-700 border border-slate-200">
                                                            Obligatoria: {{ (int) ($reporte->obligatoria ?? 0) === 1 ? 'Sí' : 'No' }}
                                                        </span>

                                                        @if((int) ($reporte->aprobado ?? 0) === 1)
                                                            <span class="esf-history-badge bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                                Aprobado: Sí
                                                            </span>
                                                        @else
                                                            <span class="esf-history-badge bg-red-50 text-red-700 border border-red-200">
                                                                Aprobado: No
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="esf-history-actions">
                                                    <a href="{{ route('seguimiento_capacitaciones.show', $reporte->id_empleado_capacitacion) }}"
                                                       class="esf-history-btn-primary">
                                                        Abrir seguimiento
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="esf-report-training-meta">
                                                <div class="esf-report-meta-box">
                                                    <span class="esf-report-meta-label">Progreso</span>
                                                    <div class="esf-report-meta-value">
                                                        {{ number_format($progresoReporte, 2) }}%
                                                    </div>
                                                    <div class="esf-report-progress-track">
                                                        <div class="esf-report-progress-bar"
                                                             style="width: {{ min(100, max(0, $progresoReporte)) }}%;"></div>
                                                    </div>
                                                </div>

                                                <div class="esf-report-meta-box">
                                                    <span class="esf-report-meta-label">Nota final</span>
                                                    <div class="esf-report-meta-value">
                                                        {{ !is_null($reporte->nota_final) ? number_format((float) $reporte->nota_final, 2) . '%' : '-' }}
                                                    </div>
                                                </div>

                                                <div class="esf-report-meta-box">
                                                    <span class="esf-report-meta-label">Asignación</span>
                                                    <div class="esf-report-meta-value">
                                                        {{ $reporte->fecha_asignacion?->format('d/m/Y') ?? '-' }}
                                                    </div>
                                                </div>

                                                <div class="esf-report-meta-box">
                                                    <span class="esf-report-meta-label">Inicio</span>
                                                    <div class="esf-report-meta-value">
                                                        {{ $reporte->fecha_inicio?->format('d/m/Y H:i') ?? '-' }}
                                                    </div>
                                                </div>

                                                <div class="esf-report-meta-box">
                                                    <span class="esf-report-meta-label">Finalización</span>
                                                    <div class="esf-report-meta-value">
                                                        {{ $reporte->fecha_finalizacion?->format('d/m/Y H:i') ?? '-' }}
                                                    </div>
                                                </div>

                                                <div class="esf-report-meta-box">
                                                    <span class="esf-report-meta-label">Fecha límite</span>
                                                    <div class="esf-report-meta-value">
                                                        {{ $reporte->fecha_limite?->format('d/m/Y') ?? '-' }}
                                                    </div>
                                                </div>

                                                <div class="esf-report-meta-box">
                                                    <span class="esf-report-meta-label">Vencimiento</span>
                                                    <div class="esf-report-meta-value">
                                                        {{ $reporte->fecha_vencimiento?->format('d/m/Y') ?? '-' }}
                                                    </div>
                                                </div>

                                                <div class="esf-report-meta-box">
                                                    <span class="esf-report-meta-label">Código empleado</span>
                                                    <div class="esf-report-meta-value">
                                                        {{ $empleadoReporte?->codigo_empleado ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="esf-admin-empty">
                            <p class="font-black text-slate-800 dark:text-slate-100">
                                No hay resultados para esos filtros.
                            </p>

                            <p class="mt-1 text-sm">
                                Probá limpiar los filtros o buscar otro criterio.
                            </p>
                        </div>
                    @endforelse

                    <div class="px-1 py-3">
                        {{ $reportes->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>