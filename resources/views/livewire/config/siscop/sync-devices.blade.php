<x-form.modal.card persistent title="Vincular Dispositivos - {{ $acceso?->nombre ?? '' }} ({{ $acceso?->tipo ?? '' }})"
    wire:model.live="showModal" align="center" max-width="6xl">

    <div class="grid grid-cols-12 gap-6">
        {{-- Buscador --}}
        <div class="col-span-12">
            <x-form.input wire:model.live="search" icon="magnifying-glass"
                placeholder="Buscar dispositivos por placa, nombre o ID..." label="Buscar Dispositivos" />
        </div>

        {{-- Tabla de dispositivos --}}
        <div class="col-span-12">
            <div class="overflow-x-auto max-h-96 border rounded-lg">
                <table class="table-auto w-full">
                    <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800">
                        <tr class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">
                            <th class="px-3 py-2 text-left">
                                <x-form.checkbox wire:click="toggleAll"
                                    @if ($this->isAllSelected()) checked @endif />
                            </th>
                            <th class="px-3 py-2 text-left">Imagen</th>
                            <th class="px-3 py-2 text-left">ID Wialon</th>
                            <th class="px-3 py-2 text-left">Nombre</th>
                            <th class="px-3 py-2 text-left">Placa</th>
                            <th class="px-3 py-2 text-left">Acceso Actual</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($devices as $device)
                            <tr wire:key="device-{{ $device->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-3 py-2">
                                    <input type="checkbox" wire:click="toggleDevice({{ $device->id }})"
                                        @if (in_array($device->id, $selectedDevices)) checked @endif class="form-checkbox" />
                                </td>
                                <td class="px-3 py-2">
                                    <div
                                        class="w-10 h-8 flex items-center justify-center bg-slate-100 dark:bg-slate-700 rounded">
                                        @if ($device->imagen)
                                            <img class="rounded object-cover" src="{{ $device->imagen->url }}"
                                                width="32" height="32" alt="{{ $device->name }}"
                                                title="{{ $device->imagen->url }}" />
                                        @else
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-2 font-medium text-sky-500">
                                    <div>{{ $device->id_wialon }}</div>
                                </td>
                                <td class="px-3 py-2 text-slate-800 dark:text-slate-100">
                                    {{ $device->name }}
                                </td>
                                <td class="px-3 py-2 text-slate-600 dark:text-slate-400">
                                    {{ $device->plate }}
                                </td>
                                <td class="px-3 py-2">
                                    @if ($device->acceso)
                                        <x-form.badge :color="$device->acceso_id === $acceso?->id ? 'positive' : 'warning'" :label="$device->acceso->nombre" />
                                    @else
                                        <x-form.badge color="gray" label="Sin asignar" />
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-4 text-center text-sm text-gray-500">
                                    No se encontraron dispositivos
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paginación --}}
        <div class="col-span-12">
            {{ $devices->links() }}
        </div>

        {{-- Resumen de selección --}}
        <div class="col-span-12">
            <x-form.card>
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <strong>{{ count($selectedDevices) }}</strong> dispositivos seleccionados
                </p>
            </x-form.card>
        </div>
    </div>

    <x-slot name="footer">
        <div class="flex justify-end gap-x-4">
            <div class="flex gap-2">
                <x-form.button flat label="Cancelar" wire:click="closeModal" />
                <x-form.button primary label="Guardar Vinculación" wire:click.prevent="save" spinner="save" />
            </div>
        </div>
    </x-slot>
</x-form.modal.card>
