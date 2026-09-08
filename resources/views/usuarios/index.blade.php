<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                Gestión de personas
            </p>

            <h2 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100 leading-tight">
                Usuarios del sistema
            </h2>

            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Administra las cuentas de acceso, roles y vínculos con empleados.
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

            @if($errors->has('general'))
                <div class="esf-alert-error">
                    {{ $errors->first('general') }}
                </div>
            @endif

            <div class="esf-page-card overflow-hidden">
                <div class="p-6 sm:p-8 border-b border-slate-200/80 dark:border-slate-700/80">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                                Usuarios registrados
                            </p>

                            <h3 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">
                                Listado de usuarios
                            </h3>

                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-2xl">
                                En esta tabla se muestran los usuarios creados, su rol asignado, el empleado vinculado y las acciones disponibles para cada cuenta.
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3">
                            <input type="search"
                                id="buscadorUsuariosSistema"
                                autocomplete="off"
                                placeholder="Buscar usuario..."
                                class="w-full sm:w-72 rounded-full border border-slate-200 bg-white/90 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition placeholder:text-slate-400 focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-blue-900/40">

                            <a href="{{ route('usuarios.create') }}"
                               class="esf-btn esf-btn-primary">
                                + Nuevo usuario
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="esf-table-wrap">
                        <table class="esf-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Empleado vinculado</th>
                                    <th>Instructor vinculado</th>
                                    <th>Estado</th>
                                    <th class="text-center">Recibe avisos</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>

                            <tbody id="tablaUsuariosSistemaBody">
                                @forelse($usuarios as $usuario)
                                    @php
                                        $esMismoUsuario = auth()->id() == $usuario->id;
                                        $esAdmin = $usuario->rolesSistema->first()?->rol === 'admin';
                                        $rolUsuario = $usuario->rolesSistema->first()?->rol ?? 'Sin rol';
                                        $empleadoVinculado = $usuario->empleadoUser?->empleado?->nombre_completo ?? 'Sin vínculo';
                                        $instructorVinculado = $usuario->instructorUser?->instructor?->instructor ?? 'Sin vínculo';
                                        $inicialesUsuario = collect(explode(' ', trim($usuario->name)))
                                            ->filter()
                                            ->take(2)
                                            ->map(fn($parte) => mb_substr($parte, 0, 1))
                                            ->implode('');
                                    @endphp

                                    <tr data-usuario-sistema-row>
                                        <td>
                                            <span class="font-black text-slate-700 dark:text-slate-200">
                                                {{ $usuario->id }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="esf-user-avatar">
                                                    {{ $inicialesUsuario ?: 'US' }}
                                                </div>

                                                <div>
                                                    <p class="font-black text-slate-900 dark:text-slate-100">
                                                        {{ $usuario->name }}
                                                    </p>

                                                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                                        {{ '@'.$usuario->username }}
                                                    </p>

                                                    @if($esMismoUsuario)
                                                        <p class="text-xs font-bold text-blue-600 dark:text-blue-300">
                                                            Cuenta actual
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                {{ $usuario->email }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="esf-badge {{ $rolUsuario === 'admin' ? 'esf-badge-purple' : 'esf-badge-blue' }}">
                                                {{ ucfirst($rolUsuario) }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="{{ $empleadoVinculado === 'Sin vínculo' ? 'text-slate-400 dark:text-slate-500' : 'font-semibold text-slate-700 dark:text-slate-200' }}">
                                                {{ $empleadoVinculado }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="{{ $instructorVinculado === 'Sin vínculo' ? 'text-slate-400 dark:text-slate-500' : 'font-semibold text-slate-700 dark:text-slate-200' }}">
                                                {{ $instructorVinculado }}
                                            </span>
                                        </td>

                                        <td>
                                            @if($usuario->estado == 1)
                                                <span class="esf-badge esf-badge-green">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="esf-badge esf-badge-red">
                                                    Inactivo
                                                </span>
                                            @endif

                                            @if($usuario->debeCambiarPassword())
                                                <span class="esf-badge {{ $usuario->passwordTemporalExpirada() ? 'esf-badge-red' : 'esf-badge-purple' }} mt-1">
                                                    {{ $usuario->passwordTemporalExpirada() ? 'Clave temporal vencida' : 'Cambio de clave pendiente' }}
                                                </span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @if($usuario->estado == 1)
                                                <form action="{{ route('usuarios.avisos_correo', $usuario->id) }}"
                                                      method="POST"
                                                      class="inline-flex items-center justify-center">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden"
                                                           name="recibe_avisos_capacitaciones"
                                                           value="{{ $usuario->recibe_avisos_capacitaciones ? 0 : 1 }}">

                                                    <button type="submit"
                                                            class="relative inline-flex h-9 w-[86px] items-center rounded-full border-2 border-white font-black text-white shadow-md transition hover:scale-105 focus:outline-none focus:ring-4 {{ $usuario->recibe_avisos_capacitaciones ? 'focus:ring-emerald-200' : 'focus:ring-red-200' }}"
                                                            style="background-color: {{ $usuario->recibe_avisos_capacitaciones ? '#16a34a' : '#dc2626' }};"
                                                            role="switch"
                                                            aria-checked="{{ $usuario->recibe_avisos_capacitaciones ? 'true' : 'false' }}"
                                                            title="{{ $usuario->recibe_avisos_capacitaciones ? 'Dejar de enviar avisos' : 'Enviar avisos a este usuario' }}">
                                                        <span class="absolute text-xs tracking-wide"
                                                              style="{{ $usuario->recibe_avisos_capacitaciones ? 'left: 12px;' : 'right: 10px;' }}">
                                                            {{ $usuario->recibe_avisos_capacitaciones ? 'SÍ' : 'NO' }}
                                                        </span>
                                                        <span class="absolute h-6 w-6 rounded-full bg-white shadow transition-all"
                                                              style="left: {{ $usuario->recibe_avisos_capacitaciones ? '56px' : '4px' }};"></span>
                                                        <span class="sr-only">Cambiar recepción de avisos</span>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs font-bold text-slate-400">Inactivo</span>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="flex flex-wrap justify-center gap-2">
                                                <a href="{{ route('usuarios.edit', $usuario->id) }}"
                                                   class="esf-action-btn esf-action-edit">
                                                    Editar
                                                </a>

                                                @if(!$esMismoUsuario && $usuario->estado == 1)
                                                    <form action="{{ route('usuarios.generar_password_temporal', $usuario->id) }}"
                                                          method="POST"
                                                          @submit="if (!confirm('Se invalidará inmediatamente la contraseña actual y se enviará una nueva contraseña temporal por correo. ¿Deseas continuar?')) $event.preventDefault()">
                                                        @csrf

                                                        <button type="submit" class="esf-action-btn esf-action-edit">
                                                            Enviar nueva contraseña
                                                        </button>
                                                    </form>
                                                @endif

                                                @if(!$esMismoUsuario && !$esAdmin)

                                                    <form action="{{ route('usuarios.toggleEstado', $usuario->id) }}"
                                                          method="POST"
                                                          @submit="if (!confirm('¿Seguro que quieres cambiar el estado de este usuario?')) $event.preventDefault()">
                                                        @csrf
                                                        @method('PATCH')

                                                        <button type="submit"
                                                                class="esf-action-btn {{ $usuario->estado == 1 ? 'esf-action-status' : 'esf-action-restore' }}">
                                                            {{ $usuario->estado == 1 ? 'Inactivar' : 'Reactivar' }}
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('usuarios.destroy', $usuario->id) }}"
                                                          method="POST"
                                                          @submit="if (!confirm('¿Seguro que quieres eliminar este usuario? Esta acción solo debe usarse si fue creado por error y no tiene movimientos en el sistema.')) $event.preventDefault()">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit"
                                                                class="esf-action-btn esf-action-delete">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($esMismoUsuario)
                                                    <span class="esf-badge esf-badge-blue">
                                                        Tu cuenta
                                                    </span>
                                                @endif


                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <div class="py-10 text-center">
                                                <p class="text-lg font-black text-slate-800 dark:text-slate-100">
                                                    No hay usuarios registrados.
                                                </p>

                                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                                    Cuando creés usuarios, aparecerán en esta tabla.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse

                                <tr id="sinResultadosUsuariosSistema" class="hidden">
                                    <td colspan="9">
                                        <div class="py-10 text-center">
                                            <p class="text-lg font-black text-slate-800 dark:text-slate-100">
                                                No se encontraron usuarios con ese criterio de búsqueda.
                                            </p>

                                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                                Probá buscar por nombre, correo, rol, empleado vinculado o estado.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function () {
            const buscador = document.getElementById('buscadorUsuariosSistema');
            const filas = document.querySelectorAll('[data-usuario-sistema-row]');
            const mensajeVacio = document.getElementById('sinResultadosUsuariosSistema');

            if (!buscador || filas.length === 0) {
                return;
            }

            function normalizarTexto(texto) {
                return (texto || '')
                    .toString()
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .trim();
            }

            function filtrarUsuarios() {
                const valor = normalizarTexto(buscador.value);
                let visibles = 0;

                filas.forEach(function (fila) {
                    const celdas = Array.from(fila.querySelectorAll('td')).slice(0, 7);
                    const textoFila = normalizarTexto(celdas.map(function (celda) {
                        return celda.textContent;
                    }).join(' '));

                    const coincide = valor === '' || textoFila.includes(valor);

                    fila.style.display = coincide ? '' : 'none';

                    if (coincide) {
                        visibles++;
                    }
                });

                if (mensajeVacio) {
                    mensajeVacio.classList.toggle('hidden', visibles > 0);
                }
            }

            buscador.addEventListener('input', filtrarUsuarios);
        });
        </script>
</x-app-layout>
