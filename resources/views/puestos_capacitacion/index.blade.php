<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                Recursos Humanos
            </p>

            <h2 class="esf-seguimiento-title">
                Matriz puesto → capacitación
            </h2>

            <p class="esf-seguimiento-subtitle">
                Consulta de solo lectura de las capacitaciones relacionadas con cada puesto.
            </p>
        </div>
    </x-slot>

    <div class="py-8 esf-seguimiento-page">
        <div class="mx-auto w-full max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <form method="GET"
                  action="{{ route('puestos_capacitacion.index') }}"
                  class="esf-seguimiento-panel p-5 sm:p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-sm font-black text-slate-700 dark:text-slate-200">
                            Buscar puesto o departamento
                        </label>
                        <input
                            type="search"
                            name="buscar_puesto"
                            value="{{ $buscarPuesto }}"
                            placeholder="Nombre del puesto o departamento"
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-black text-slate-700 dark:text-slate-200">
                            Buscar capacitación
                        </label>
                        <input
                            type="search"
                            name="buscar_capacitacion"
                            value="{{ $buscarCapacitacion }}"
                            placeholder="Nombre de la capacitación"
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                        >
                    </div>

                    <div class="flex items-end gap-3 md:col-span-2">
                        <button type="submit" class="esf-btn esf-btn-primary">
                            Filtrar matriz
                        </button>

                        <a href="{{ route('puestos_capacitacion.index') }}" class="esf-btn esf-btn-soft">
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>

            <div class="esf-seguimiento-table-card esf-admin-sheet-card esf-readonly-matrix-card">
                <div class="esf-admin-table-toolbar esf-readonly-matrix-toolbar">
                    <div>
                        <span class="esf-readonly-badge">Consulta de RRHH</span>
                        <h3 class="esf-admin-table-title mt-2">Cobertura por puesto</h3>
                        <p class="esf-admin-table-subtitle">
                            Cruce entre puestos y capacitaciones configurado en la base de datos de Recursos Humanos.
                        </p>
                    </div>

                    <div class="esf-readonly-matrix-stats" aria-label="Resumen de la matriz">
                        <div class="esf-readonly-stat">
                            <strong>{{ $puestos->count() }}</strong>
                            <span>Puestos</span>
                        </div>
                        <div class="esf-readonly-stat">
                            <strong>{{ $capacitaciones->count() }}</strong>
                            <span>Capacitaciones</span>
                        </div>
                        <div class="esf-readonly-stat esf-readonly-stat-accent">
                            <strong>{{ $relaciones->count() }}</strong>
                            <span>Relaciones</span>
                        </div>
                    </div>
                </div>

                <div class="esf-readonly-matrix-guide">
                    <p>
                        <span class="esf-readonly-guide-icon" aria-hidden="true">↔</span>
                        Desliza horizontalmente para consultar todas las capacitaciones.
                    </p>
                    <div class="esf-readonly-matrix-legend">
                        <span><i class="esf-matrix-status is-assigned">✓</i> Asignada</span>
                        <span><i class="esf-matrix-status is-empty">—</i> Sin asignar</span>
                    </div>
                </div>

                <div class="esf-readonly-matrix-scroll">
                    <table class="esf-readonly-matrix">
                        <thead>
                            <tr>
                                <th class="esf-matrix-position-heading">
                                    <span class="esf-matrix-heading-kicker">Estructura</span>
                                    <span class="esf-matrix-heading-title">Puesto de trabajo</span>
                                </th>
                                <th class="esf-matrix-department-heading">
                                    <span class="esf-matrix-heading-kicker">Área</span>
                                    <span class="esf-matrix-heading-title">Departamento</span>
                                </th>

                                @forelse($capacitaciones as $capacitacion)
                                    <th class="esf-matrix-training-heading">
                                        <span class="esf-matrix-training-icon" aria-hidden="true">C</span>
                                        <span class="esf-matrix-training-name">
                                            {{ $capacitacion->capacitacion }}
                                        </span>
                                    </th>
                                @empty
                                    <th class="esf-matrix-training-heading">
                                        No hay capacitaciones para mostrar
                                    </th>
                                @endforelse
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($puestos as $puesto)
                                <tr>
                                    <td class="esf-matrix-position-cell">
                                        {{ $puesto->puesto_trabajo_matriz }}
                                    </td>

                                    <td class="esf-matrix-department-cell">
                                        <span>{{ $puesto->departamento ?: 'Sin departamento' }}</span>
                                    </td>

                                    @foreach($capacitaciones as $capacitacion)
                                        @php
                                            $clave = $puesto->id_puesto_trabajo_matriz.'-'.$capacitacion->id_capacitacion;
                                            $asignada = isset($relaciones[$clave]);
                                        @endphp

                                        <td class="esf-matrix-status-cell">
                                            @if($asignada)
                                                <span
                                                    class="esf-matrix-status is-assigned"
                                                    title="Capacitación asignada"
                                                    aria-label="Capacitación asignada"
                                                >✓</span>
                                            @else
                                                <span
                                                    class="esf-matrix-status is-empty"
                                                    title="Capacitación no asignada"
                                                    aria-label="Capacitación no asignada"
                                                >—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ max($capacitaciones->count() + 2, 3) }}" class="esf-matrix-empty-state">
                                        No se encontraron puestos con los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="esf-readonly-matrix-footer">
                    <span class="esf-readonly-lock" aria-hidden="true">✓</span>
                    Información de solo lectura. Los valores se muestran tal como están relacionados en RRHH.
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
