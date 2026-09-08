<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                Comunicación automática
            </p>

            <h2 class="esf-seguimiento-title">
                Avisos por correo
            </h2>

            <p class="esf-seguimiento-subtitle">
                Revisa los avisos automáticos generados por el sistema para asignaciones, vencimientos, retrasos y finalizaciones.
            </p>
        </div>
    </x-slot>

    <div class="py-8 esf-seguimiento-page">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="esf-seguimiento-kpi esf-seguimiento-kpi-blue">
                    <p>Asignaciones</p>
                    <p>{{ $resumen['asignaciones'] }}</p>
                </div>

                <div class="esf-seguimiento-kpi esf-seguimiento-kpi-amber">
                    <p>Retrasadas</p>
                    <p>{{ $resumen['retrasadas'] }}</p>
                </div>

                <div class="esf-seguimiento-kpi esf-seguimiento-kpi-amber">
                    <p>Pendientes</p>
                    <p>{{ $resumen['pendientes'] }}</p>
                </div>

                <div class="esf-seguimiento-kpi esf-seguimiento-kpi-green">
                    <p>Enviados</p>
                    <p>{{ $resumen['enviados'] }}</p>
                </div>

                <div class="esf-seguimiento-kpi esf-seguimiento-kpi-red">
                    <p>Errores</p>
                    <p>{{ $resumen['errores'] }}</p>
                </div>
            </div>

            <div class="esf-seguimiento-table-card esf-admin-sheet-card">
                <div class="esf-admin-table-toolbar">
                    <div>
                        <h3 class="esf-admin-table-title">
                            Reglas automáticas de avisos
                        </h3>

                        <p class="esf-admin-table-subtitle">
                            Estas reglas ya quedan fijas en el sistema. El administrador no necesita generar ni enviar avisos manualmente.
                        </p>
                    </div>
                </div>

                @php
                    $configuracionPorTipo = $configuraciones->keyBy('tipo_aviso');

                    $reglasAvisos = [
                        'asignada' => [
                            'titulo' => 'Nueva asignación',
                            'descripcion' => 'Se envía inmediatamente al guardar una asignación manual.',
                            'programacion' => 'Inmediato al guardar',
                        ],
                        'por_vencer' => [
                            'titulo' => 'Por vencer',
                            'descripcion' => 'Se envía cuando falten exactamente 2 días para la fecha de vencimiento.',
                            'programacion' => 'Automático a las 9:00 a. m.',
                        ],
                        'vencida' => [
                            'titulo' => 'Retrasada',
                            'descripcion' => 'Se envía cuando la capacitación ya venció y sigue pendiente, en proceso o vencida.',
                            'programacion' => 'Automático a las 9:00 a. m.',
                        ],
                        'terminada' => [
                            'titulo' => 'Finalización',
                            'descripcion' => 'Se envía cuando la capacitación ya finalizó según fecha de finalización o vencimiento.',
                            'programacion' => 'Automático a las 9:00 a. m.',
                        ],
                    ];
                @endphp

                <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
                    @foreach($reglasAvisos as $tipo => $regla)
                        @php
                            $configuracion = $configuracionPorTipo->get($tipo);
                        @endphp

                        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-black text-slate-900 dark:text-slate-100">
                                        {{ $regla['titulo'] }}
                                    </p>

                                    <p class="mt-1 text-xs font-semibold text-slate-400">
                                        {{ $tipo }}
                                    </p>
                                </div>

                                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700 ring-1 ring-emerald-100">
                                    Activo
                                </span>
                            </div>

                            <p class="mt-4 text-sm font-semibold text-slate-600 dark:text-slate-300">
                                {{ $regla['descripcion'] }}
                            </p>

                            <div class="mt-4 rounded-2xl bg-slate-50 px-4 py-3 text-xs font-black text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                                {{ $regla['programacion'] }}
                            </div>

                            <div class="mt-3 text-xs font-semibold text-slate-400">
                                Destinatarios: empleado y usuarios seleccionados para avisos administrativos.
                            </div>

                            @if($tipo === 'por_vencer')
                                <div class="mt-2 text-xs font-semibold text-slate-400">
                                    Días configurados: {{ $configuracion->dias_anticipacion ?? 2 }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <form method="GET"
                  action="{{ route('avisos.index') }}"
                  class="esf-seguimiento-panel p-5 sm:p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <label>Estado</label>
                        <select name="estado">
                            <option value="">Todos</option>
                            <option value="pendiente" {{ $estado === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="enviado" {{ $estado === 'enviado' ? 'selected' : '' }}>Enviado</option>
                            <option value="error" {{ $estado === 'error' ? 'selected' : '' }}>Error</option>
                        </select>
                    </div>

                    <div>
                        <label>Tipo de aviso</label>
                        <select name="tipo_aviso">
                            <option value="">Todos</option>
                            <option value="asignada" {{ $tipoAviso === 'asignada' ? 'selected' : '' }}>Nueva asignación</option>
                            <option value="por_vencer" {{ $tipoAviso === 'por_vencer' ? 'selected' : '' }}>Por vencer</option>
                            <option value="vencida" {{ $tipoAviso === 'vencida' ? 'selected' : '' }}>Retrasada</option>
                            <option value="terminada" {{ $tipoAviso === 'terminada' ? 'selected' : '' }}>Finalización</option>
                        </select>
                    </div>

                    <div>
                        <label>Destinatario</label>
                        <select name="destinatario_tipo">
                            <option value="">Todos</option>
                            <option value="empleado" {{ $destinatarioTipo === 'empleado' ? 'selected' : '' }}>Empleado</option>
                            <option value="admin" {{ $destinatarioTipo === 'admin' ? 'selected' : '' }}>Aviso administrativo</option>
                        </select>
                    </div>

                    <div>
                        <label>Destinatario o asunto</label>
                        <input type="text"
                               name="buscar"
                               value="{{ $buscar }}"
                               placeholder="Buscar por correo, empleado o capacitación">
                    </div>

                    <div>
                        <label>Desde</label>
                        <input type="date" name="fecha_desde" value="{{ $fechaDesde }}">
                    </div>

                    <div>
                        <label>Hasta</label>
                        <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}">
                    </div>

                    <div class="md:col-span-2 flex items-end justify-end gap-3">
                        <button type="submit" class="esf-btn esf-btn-primary min-w-[110px]">
                            Filtrar
                        </button>

                        <a href="{{ route('avisos.index') }}"
                           class="esf-btn esf-btn-soft min-w-[110px] text-center">
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>

            <div class="esf-seguimiento-table-card esf-admin-sheet-card">
                <div class="esf-admin-table-toolbar">
                    <div>
                        <h3 class="esf-admin-table-title">
                            Avisos generados
                        </h3>

                        <p class="esf-admin-table-subtitle">
                            Historial de avisos creados y enviados automáticamente por el sistema.
                        </p>
                    </div>
                </div>

                <div class="esf-seguimiento-table-scroll esf-no-sticky-fields">
                    <table class="esf-seguimiento-table-modern min-w-[1200px]">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Empleado</th>
                                <th>Capacitación</th>
                                <th>Destinatario</th>
                                <th>Correo</th>
                                <th>Asunto</th>
                                <th class="text-center">Programado</th>
                                <th class="text-center">Enviado</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($avisos as $aviso)
                                @php
                                    $estadoClase = match($aviso->estado) {
                                        'pendiente' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                        'enviado' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                        'error' => 'bg-red-50 text-red-700 border border-red-200',
                                        'cancelado' => 'bg-slate-100 text-slate-700 border border-slate-200',
                                        default => 'bg-slate-100 text-slate-700 border border-slate-200',
                                    };

                                    $tipoAvisoTexto = match($aviso->tipo_aviso) {
                                        'asignada' => 'Nueva asignación',
                                        'por_vencer' => 'Por vencer',
                                        'vencida' => 'Retrasada',
                                        'terminada' => 'Finalización',
                                        default => ucfirst(str_replace('_', ' ', $aviso->tipo_aviso)),
                                    };

                                    $nombreEmpleadoAviso = $aviso->empleadoCapacitacion?->empleado?->nombre_completo ?? '-';

                                    $inicialesAviso = collect(preg_split('/\s+/', trim($nombreEmpleadoAviso)))
                                        ->filter()
                                        ->take(2)
                                        ->map(fn ($parteNombre) => mb_strtoupper(mb_substr($parteNombre, 0, 1)))
                                        ->implode('');

                                    if ($inicialesAviso === '' || $nombreEmpleadoAviso === '-') {
                                        $inicialesAviso = 'NA';
                                    }
                                @endphp

                                <tr>
                                    <td class="font-black">
                                        #{{ $aviso->id_aviso_correo }}
                                    </td>

                                    <td>
                                        <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700 ring-1 ring-blue-100">
                                            {{ $tipoAvisoTexto }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="flex items-center gap-3 min-w-[220px]">
                                            <div class="esf-admin-initials">
                                                {{ $inicialesAviso }}
                                            </div>

                                            <div>
                                                <div class="font-black text-slate-900 dark:text-slate-100">
                                                    {{ $nombreEmpleadoAviso }}
                                                </div>

                                                <div class="text-xs font-semibold text-slate-400">
                                                    Destinatario relacionado
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="min-w-[220px] font-semibold">
                                        {{ $aviso->empleadoCapacitacion?->capacitacion?->capacitacion ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $aviso->destinatario_tipo === 'admin' ? 'Administrativo' : 'Empleado' }}
                                    </td>

                                    <td class="min-w-[220px]">
                                        {{ $aviso->destinatario_email }}
                                    </td>

                                    <td class="min-w-[260px]">
                                        {{ $aviso->asunto }}
                                    </td>

                                    <td class="text-center">
                                        {{ $aviso->fecha_programada?->format('d/m/Y H:i') ?? '-' }}
                                    </td>

                                    <td class="text-center">
                                        {{ $aviso->fecha_enviada?->format('d/m/Y H:i') ?? '-' }}
                                    </td>

                                    <td class="text-center">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-black {{ $estadoClase }}">
                                            {{ ucfirst($aviso->estado) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-6 py-10 text-center">
                                        <div class="esf-admin-empty">
                                            <p class="font-black text-slate-800 dark:text-slate-100">
                                                Todavía no hay avisos generados.
                                            </p>

                                            <p class="mt-1 text-sm">
                                                Cuando el sistema genere avisos automáticos, aparecerán en esta tabla.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="px-6 py-5">
                        {{ $avisos->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
