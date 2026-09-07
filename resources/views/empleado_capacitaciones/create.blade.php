<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Nueva asignación múltiple de capacitación
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if ($errors->any())
                        <div class="mb-6 rounded border border-red-300 bg-red-100 px-4 py-3 text-red-800">
                            <strong>Revisa los siguientes errores:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('empleado_capacitaciones.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <div class="mb-4 md:col-span-2">
                                <label for="id_capacitacion" class="block mb-1 font-medium">Capacitación</label>

                                <x-autocomplete-select
                                    name="id_capacitacion"
                                    :options="$opcionesCapacitaciones"
                                    :selected="old('id_capacitacion', '')"
                                    placeholder="Buscar capacitación por nombre o ID"
                                />

                                @error('id_capacitacion') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1 font-medium">Obligatoria</label>
                                <select name="obligatoria" class="w-full border rounded px-3 py-2 text-black @error('obligatoria') border-red-500 @enderror">
                                    <option value="1" {{ old('obligatoria') == '1' ? 'selected' : '' }}>Sí</option>
                                    <option value="0" {{ old('obligatoria', '0') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                                @error('obligatoria') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1 font-medium">Fecha de asignación</label>
                                <input type="date" name="fecha_asignacion" value="{{ old('fecha_asignacion', now()->format('Y-m-d')) }}" required
                                    class="w-full border rounded px-3 py-2 text-black @error('fecha_asignacion') border-red-500 @enderror">
                                @error('fecha_asignacion') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1 font-medium">Fecha límite</label>
                                <input type="date" name="fecha_limite" value="{{ old('fecha_limite') }}" required
                                    class="w-full border rounded px-3 py-2 text-black @error('fecha_limite') border-red-500 @enderror">
                                @error('fecha_limite') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1 font-medium">Fecha de vencimiento</label>
                                <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" required
                                    class="w-full border rounded px-3 py-2 text-black @error('fecha_vencimiento') border-red-500 @enderror">
                                @error('fecha_vencimiento') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-6 md:col-span-2">
                                <div class="rounded border border-blue-300 bg-blue-100 px-4 py-3 text-blue-800">
                                    El estado inicial de todas las nuevas asignaciones se guardará automáticamente como <strong>pendiente</strong>.
                                </div>
                            </div>

                            <div class="mb-6 md:col-span-2">
                                <label class="block mb-2 font-medium">Empleados</label>

                                <input
                                    type="text"
                                    id="filtro-empleados"
                                    placeholder="Buscar por nombre o código de empleado"
                                    class="w-full border rounded px-3 py-2 text-black mb-3"
                                >

                                <div class="flex flex-wrap gap-2 mb-3">
                                    <button type="button" id="seleccionar-visibles" class="px-3 py-2 bg-slate-700 text-white rounded text-sm">
                                        Seleccionar visibles
                                    </button>

                                    <button type="button" id="limpiar-seleccion" class="px-3 py-2 bg-gray-500 text-white rounded text-sm">
                                        Limpiar selección
                                    </button>
                                </div>

                                <div id="lista-empleados" class="max-h-80 overflow-y-auto border rounded p-3 bg-white">
                                    @forelse($empleados as $empleado)
                                        @php
                                            $textoBusqueda = mb_strtolower(
                                                trim(($empleado->nombre_completo ?? '') . ' ' . ($empleado->codigo_empleado ?? ''))
                                            );
                                        @endphp

                                        <label
                                            class="empleado-item flex items-start gap-3 border-b py-2 last:border-b-0"
                                            data-texto="{{ $textoBusqueda }}"
                                        >
                                            <input
                                                type="checkbox"
                                                name="id_empleados[]"
                                                value="{{ $empleado->id_empleado }}"
                                                class="mt-1 empleado-checkbox"
                                                {{ in_array($empleado->id_empleado, old('id_empleados', [])) ? 'checked' : '' }}
                                            >

                                            <span class="text-black">
                                                <span class="block font-medium">
                                                    {{ $empleado->nombre_completo }}
                                                </span>

                                                <span class="block text-sm text-gray-600">
                                                    Código:
                                                    {{ $empleado->codigo_empleado ?: 'Sin código' }}
                                                </span>
                                            </span>
                                        </label>
                                    @empty
                                        <p class="text-gray-600">No hay empleados activos disponibles.</p>
                                    @endforelse

                                    <p id="sin-coincidencias-empleados"
                                        class="px-3 py-5 text-center text-sm font-medium text-gray-600"
                                        hidden>
                                        No se encontraron empleados con ese nombre o código.
                                    </p>
                                </div>

                                @error('id_empleados') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                                @error('id_empleados.*') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                                Guardar asignaciones
                            </button>

                            <a href="{{ route('empleado_capacitaciones.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded">
                                Cancelar
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
