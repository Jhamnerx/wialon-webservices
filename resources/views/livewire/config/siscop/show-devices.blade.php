<x-form.modal.card persistent :title="'Dispositivos de ' . ($acceso ? $acceso->nombre : '')" wire:model.live="showModal" align="center" width="6xl">

    @if ($acceso)
        <!-- Información del Acceso -->
        <div class="mb-6">
            <div
                class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-lg p-4 border border-blue-100 dark:border-gray-600">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tipo</label>
                        <div class="mt-1">
                            <x-form.badge :color="$acceso->tipo === 'serenazgo' ? 'blue' : 'green'" :label="ucfirst($acceso->tipo)" lg />
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ubigeo</label>
                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $acceso->ubigeo }}</p>
                    </div>
                    @if ($acceso->tipo === 'serenazgo')
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">ID
                                Municipalidad</label>
                            <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $acceso->idMunicipalidad }}</p>
                        </div>
                    @else
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">ID
                                Transmisión</label>
                            <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $acceso->idTransmision }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Código
                                Comisaría</label>
                            <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $acceso->codigoComisaria }}</p>
                        </div>
                    @endif
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total
                            Dispositivos</label>
                        <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ $acceso->devices->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buscador -->
        <div class="mb-4">
            <x-form.input wire:model.live.debounce.300ms="search" placeholder="Buscar por placa, nombre o ID Wialon"
                icon="magnifying-glass" label="Buscar Dispositivos" />
        </div>

        <!-- Tabla de dispositivos -->
        <div class="overflow-x-auto border rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">
                        <th class="px-4 py-3 text-left">Imagen</th>
                        <th class="px-4 py-3 text-left">ID Wialon</th>
                        <th class="px-4 py-3 text-left">Nombre</th>
                        <th class="px-4 py-3 text-left">Placa</th>
                        <th class="px-4 py-3 text-left">Última Act.</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($devices as $device)
                        <tr wire:key="device-{{ $device->id }}"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex-shrink-0">
                                    @if ($device->imagen)
                                        <img class="h-12 w-12 rounded-lg object-cover border-2 border-gray-200 dark:border-gray-700 shadow-sm"
                                            src="{{ $device->imagen->url }}" alt="{{ $device->name }}"
                                            title="{{ $device->name }}">
                                    @else
                                        <div
                                            class="h-12 w-12 rounded-lg bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center border-2 border-gray-200 dark:border-gray-600">
                                            <svg class="h-6 w-6 text-gray-400 dark:text-gray-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-medium text-sky-600 dark:text-sky-400">
                                    {{ $device->id_wialon }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $device->name }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <x-form.badge color="slate" :label="$device->plate" />
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $device->last_update ? $device->last_update->format('d/m/Y H:i') : 'Sin datos' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <x-form.button wire:click="desvincularDispositivo({{ $device->id }})"
                                    onclick="return confirm('¿Estás seguro de que deseas desvincular este dispositivo?')"
                                    negative xs icon="x-mark" label="Desvincular" spinner />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        @if ($search)
                                            No se encontraron dispositivos que coincidan con "{{ $search }}"
                                        @else
                                            No hay dispositivos vinculados a este acceso
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if ($devices instanceof \Illuminate\Pagination\LengthAwarePaginator && $devices->hasPages())
            <div class="mt-4">
                {{ $devices->links() }}
            </div>
        @endif
    @endif

    <x-slot name="footer">
        <div class="flex justify-end">
            <x-form.button flat label="Cerrar" wire:click="closeModal" />
        </div>
    </x-slot>
</x-form.modal.card>
