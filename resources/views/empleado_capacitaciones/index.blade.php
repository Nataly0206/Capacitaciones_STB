<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                Gestión de asignaciones
            </p>

            <h2 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100 leading-tight">
                Asignaciones de capacitaciones
            </h2>

            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Administra las capacitaciones asignadas a empleados, revisá su estado y accedé al seguimiento.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="esf-alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="esf-alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="esf-page-card overflow-hidden">
                <div class="p-6 sm:p-8 border-b border-slate-200/80 dark:border-slate-700/80">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                                Control administrativo
                            </p>

                            <h3 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">
                                Capacitaciones asignadas
                            </h3>

                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-2xl">
                                Esta tabla muestra cada asignación registrada, el empleado, la capacitación, el avance y las acciones disponibles.
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                            <div class="rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/60 px-4 py-3">
                                <p class="text-[11px] uppercase tracking-[0.14em] font-black text-blue-500 dark:text-blue-300">
                                    Total visible
                                </p>

                                <p class="text-2xl font-black text-slate-900 dark:text-slate-100">
                                    {{ $asignaciones->count() }}
                                </p>
                            </div>

                            <a href="{{ route('empleado_capacitaciones.por_puesto') }}"
                               class="esf-btn esf-btn-soft">
                                Asignar por puesto
                            </a>

                            <a href="{{ route('empleado_capacitaciones.create') }}"
                               class="esf-btn esf-btn-primary">
                                + Nueva asignación
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6 border-b border-slate-200/80 dark:border-slate-700/80">
                    <form method="GET"
                          action="{{ route('empleado_capacitaciones.index') }}"
                          class="esf-form-panel">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="esf-form-group">
                                <label class="esf-form-label">
                                    Buscar
                                </label>

                                <input
                                    type="text"
                                    name="buscar"
                                    value="{{ $buscar }}"
                                    placeholder="Empleado, código o capacitación"
                                    class="esf-form-input"
                                >
                            </div>

                            <div class="esf-form-group">
                                <label class="esf-form-label">
                                    Estado
                                </label>

                                <select name="estado" class="esf-form-input">
                                    <option value="">Todos</option>
                                    @foreach($estados as $itemEstado)
                                        <option value="{{ $itemEstado }}" {{ $estado === $itemEstado ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $itemEstado)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="esf-form-group">
                                <label class="esf-form-label">
                                    Obligatoria
                                </label>

                                <select name="obligatoria" class="esf-form-input">
                                    <option value="">Todas</option>
                                    <option value="1" {{ $obligatoria === '1' ? 'selected' : '' }}>Sí</option>
                                    <option value="0" {{ $obligatoria === '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>

                            <div class="flex items-end gap-2">
                                <button type="submit"
                                        class="esf-btn esf-btn-primary w-full md:w-auto">
                                    Filtrar
                                </button>

                                <a href="{{ route('empleado_capacitaciones.index') }}"
                                   class="esf-btn esf-btn-soft w-full md:w-auto">
                                    Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="esf-table-wrap esf-assignments-table-wrap">
                        <table class="esf-table esf-assignments-table">
                            <colgroup>
                                <col style="width: 24%">
                                <col style="width: 25%">
                                <col style="width: 12%">
                                <col style="width: 15%">
                                <col style="width: 10%">
                                <col style="width: 14%">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Empleado</th>
                                    <th>Capacitación</th>
                                    <th>Estado</th>
                                    <th>Progreso</th>
                                    <th>Obligatoria</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($asignaciones as $asignacion)
                                    @php
                                        $estadoTexto = match($asignacion->estado) {
                                            'pendiente' => 'Pendiente',
                                            'en_proceso' => 'En proceso',
                                            'aprobada' => 'Aprobada',
                                            'reprobada' => 'Reprobada',
                                            'vencida' => 'Retrasada',
                                            'cancelada' => 'Cancelada',
                                            default => ucfirst(str_replace('_', ' ', $asignacion->estado)),
                                        };

                                        $estadoBadge = match($asignacion->estado) {
                                            'pendiente' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                                            'en_proceso' => 'esf-badge-blue',
                                            'aprobada' => 'esf-badge-green',
                                            'reprobada' => 'esf-badge-red',
                                            'vencida' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200',
                                            'cancelada' => 'esf-badge-slate',
                                            default => 'esf-badge-slate',
                                        };

                                        $progresoAsignacion = (float) $asignacion->progreso;
                                        $progresoVisual = max(0, min(100, $progresoAsignacion));

                                        $inicialesEmpleado = collect(explode(' ', trim($asignacion->empleado?->nombre_completo ?? 'Empleado')))
                                            ->filter()
                                            ->take(2)
                                            ->map(fn($parte) => mb_substr($parte, 0, 1))
                                            ->implode('');
                                    @endphp

                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-2 min-w-0">
                                                <div class="esf-user-avatar">
                                                    {{ $inicialesEmpleado ?: 'EM' }}
                                                </div>

                                                <div class="min-w-0 flex-1">
                                                    <p title="{{ $asignacion->empleado?->nombre_completo ?? 'Sin empleado' }}" class="esf-assignments-truncate font-black text-slate-900 dark:text-slate-100">
                                                        {{ $asignacion->empleado?->nombre_completo ?? 'Sin empleado' }}
                                                    </p>

                                                    <p class="text-xs font-bold text-slate-400 dark:text-slate-500">
                                                        Empleado asignado
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <p title="{{ $asignacion->capacitacion?->capacitacion ?? 'Sin capacitación' }}" class="esf-assignments-truncate font-semibold text-slate-700 dark:text-slate-200">
                                                {{ $asignacion->capacitacion?->capacitacion ?? 'Sin capacitación' }}
                                            </p>
                                        </td>

                                        <td>
                                            <span class="esf-badge {{ $estadoBadge }}">
                                                {{ $estadoTexto }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                                                    <span class="text-xs font-black text-slate-500 dark:text-slate-400">
                                                        Avance
                                                    </span>

                                                    <span class="text-sm font-black text-slate-800 dark:text-slate-100">
                                                        {{ number_format($progresoAsignacion, 2) }}%
                                                    </span>
                                                </div>

                                                <div class="esf-progress-track">
                                                    <div class="esf-progress-fill"
                                                         style="width: {{ $progresoVisual }}%;">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            @if((int) $asignacion->obligatoria === 1)
                                                <span class="esf-badge esf-badge-purple">
                                                    Sí
                                                </span>
                                            @else
                                                <span class="esf-badge esf-badge-slate">
                                                    No
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="flex flex-wrap justify-center gap-2">
                                                <a href="{{ route('seguimiento_capacitaciones.show', $asignacion->id_empleado_capacitacion) }}"
                                                   class="esf-action-btn esf-action-status">
                                                    Seguimiento
                                                </a>

                                                <a href="{{ route('empleado_capacitaciones.edit', $asignacion->id_empleado_capacitacion) }}"
                                                   class="esf-action-btn esf-action-edit">
                                                    Editar
                                                </a>

                                                <form method="POST"
                                                      action="{{ route('empleado_capacitaciones.destroy', $asignacion->id_empleado_capacitacion) }}">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            @click="if (!confirm('¿Seguro que deseas eliminar esta asignación? También se eliminará TODO su seguimiento, avances, intentos, respuestas, historial y avisos. Esta acción no se puede deshacer.')) $event.preventDefault()"
                                                            class="esf-action-btn esf-action-delete">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="py-10 text-center">
                                                <p class="text-lg font-black text-slate-800 dark:text-slate-100">
                                                    No hay asignaciones registradas con esos filtros.
                                                </p>

                                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                                    Prueba limpiar los filtros o crear una nueva asignación.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-5">
                        {{ $asignaciones->links() }}
                    </div>

                    <p class="mt-4 text-xs text-slate-400 dark:text-slate-500">
                        Consejo: usa el botón de seguimiento para revisar el avance completo del empleado en la capacitación.
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>