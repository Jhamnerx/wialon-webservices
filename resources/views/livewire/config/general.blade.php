<div>
    <div
        class="my-4 px-4 flex flex-col md:flex-row items-start md:items-center justify-between pb-4 border-b border-gray-300">
        <div class="mt-2 md:mt-0">
            <h4 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-200">CONFIGURACIÓN GENERAL</h4>
            <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Configuración de conexión y servicios</p>
        </div>
    </div>

    <div class="p-2 shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-2 bg-slate-100 dark:bg-gray-700 sm:p-2">
            <div class="grid grid-cols-12 gap-4">

                <!-- Configuración de Conexión -->
                <div class="col-span-12">
                    <div
                        class="grid grid-cols-12 gap-2 bg-white dark:bg-gray-800 items-start border rounded-md p-6 mb-2">

                        {{-- LOGO --}}
                        <div class="col-span-12 lg:col-span-2">
                            <div>
                                <img width="100"
                                    src="https://play-lh.googleusercontent.com/yAlU8TWv8EoNur8XOB_wcom5FmDBez91BmxWis1OoWd2Rl6rK2EAALtRO0MxR3P8QQ=w240-h480-rw">
                            </div>
                        </div>

                        {{-- DATOS DE LA EMPRESA --}}
                        <div
                            class="col-span-12 lg:col-span-4 xl:col-span-4 pl-6 self-center overflow-hidden text-ellipsis">
                            <div class="mb-0" style="line-height: initial;">
                                <span class="font-bold text-slate-800 dark:text-gray-200">
                                    WIALON
                                </span>
                                <br>

                            </div>
                        </div>


                    </div>

                    <div class="bg-white dark:bg-gray-800 border rounded-md p-6">

                        <h5 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                            Configuración de Conexión
                        </h5>


                        <div class="grid grid-cols-12 gap-4">

                            <div class="col-span-12 md:col-span-6">
                                <x-form.input wire:model.live="user" label="Usuario:" />
                            </div>


                            @if (!$custom_host)
                                <div class="col-span-12 md:col-span-6">
                                    <x-form.input wire:model.live="base_uri" label="Base URI:"
                                        description="URI base del servicio Ex. https://hst-api.wialon.com" />
                                </div>
                            @endif

                            @if ($custom_host)
                                <div class="col-span-12 md:col-span-6">
                                    <x-form.input wire:model.live="host" label="Host:"
                                        description="Ingresa tu host personalizado Ex. https://hst-api.wialon.com" />
                                </div>
                            @endif

                            <div class="col-span-12 md:col-span-6 flex items-center">
                                <x-form.toggle id="color-positive" name="custom_host" wire:model.live="custom_host"
                                    label="Custom Host?" positive xl value="true" />
                            </div>
                        </div>
                        {{ json_encode($errors->all()) }}
                        <div class="flex flex-col sm:flex-row justify-between gap-3 mt-6">
                            <x-form.button wire:click.prevent="saveGeneralConfig" spinner="saveGeneralConfig" positive
                                rounded="md" label="Guardar Configuración" />

                            <a href="{{ $url_login }}"
                                class="inline-flex justify-center items-center group hover:shadow-sm focus:ring-offset-background-white dark:focus:ring-offset-background-dark transition-all ease-in-out duration-200 focus:ring-2 text-white bg-warning-500 dark:bg-warning-700 hover:text-white hover:bg-warning-600 dark:hover:bg-warning-600 rounded-md gap-x-2 text-sm px-4 py-2 
                             @if (!$host && !$base_uri) cursor-not-allowed opacity-80 @endif"
                                @if (!$host && !$base_uri) onclick="return false;" @endif>
                                Obtener Token
                            </a>
                        </div>

                        @if ($token)
                            <div
                                class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                                            Token de Acceso Generado
                                        </p>
                                        <div class="relative">
                                            <input type="text" id="token-display" value="{{ $token }}"
                                                readonly
                                                class="form-input w-full pr-20 font-mono text-sm bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600">
                                            <button type="button" onclick="copyToken()"
                                                class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 px-3 py-1 rounded text-xs transition">
                                                Copiar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                    Este token es necesario para autenticar las solicitudes a la API.
                                </p>
                            </div>

                            <script>
                                function copyToken() {
                                    const tokenInput = document.getElementById('token-display');
                                    tokenInput.select();
                                    tokenInput.setSelectionRange(0, 99999);
                                    navigator.clipboard.writeText(tokenInput.value).then(function() {
                                        // Cambiar temporalmente el texto del botón
                                        const button = event.target;
                                        const originalText = button.textContent;
                                        button.textContent = '¡Copiado!';
                                        button.classList.add('bg-green-100', 'text-green-700');
                                        setTimeout(() => {
                                            button.textContent = originalText;
                                            button.classList.remove('bg-green-100', 'text-green-700');
                                        }, 2000);
                                    });
                                }
                            </script>
                        @endif

                    </div>
                </div>

                <!-- Servicios Habilitados -->
                <div class="col-span-12 mt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Servicios Habilitados</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($services as $service)
                            <div
                                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="relative">
                                        <x-form.toggle id="service_{{ $service['id'] }}"
                                            wire:model.live="services.{{ $loop->index }}.active"
                                            label="{{ $service['display_name'] }}"
                                            name="service_{{ $service['id'] }}" />
                                    </div>
                                </div>

                                @if ($service['description'])
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                        {{ $service['description'] }}
                                    </p>
                                @endif

                                @if ($service['active'])
                                    <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                                        <div class="grid grid-cols-1 gap-3">
                                            @if ($service['display_name'] !== 'SISCOP')
                                                <div>
                                                    <label class="block text-sm font-medium mb-1">
                                                        Token del Servicio
                                                    </label>
                                                    <input type="text"
                                                        wire:model="services.{{ $loop->index }}.token"
                                                        placeholder="Token del servicio" class="form-input w-full">
                                                </div>
                                            @endif


                                            <div class="flex items-center justify-between">
                                                <x-form.toggle id="logs_{{ $service['id'] }}"
                                                    wire:model.live="services.{{ $loop->index }}.logs_enabled"
                                                    label="Habilitar logs" name="logs_{{ $service['id'] }}" />

                                                <x-form.button wire:click="saveService({{ $service['id'] }})"
                                                    spinner="saveService({{ $service['id'] }})"
                                                    class="bg-violet-500 hover:bg-violet-600 text-white text-sm px-3 py-1 rounded transition">
                                                    Guardar
                                                </x-form.button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Sección de Accesos -->
                @php
                    $siscopService = collect($services)->firstWhere('display_name', 'SISCOP');
                    $siscopActivo = $siscopService && $siscopService['active'];
                @endphp

                @if ($siscopActivo)
                    <div class="col-span-12 mt-6">
                        <div class="bg-white dark:bg-gray-800 border rounded-md p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h5 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                    Municipalidades y Comisarías
                                </h5>
                                <x-form.button wire:click="openModalCreateAcceso" spinner="openModalCreateAcceso"
                                    positive rounded="md" label="Crear Acceso" />
                            </div>

                            <div class="overflow-x-auto">
                                <table class="table-auto w-full dark:text-slate-300">
                                    <thead
                                        class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/20 border-t border-b border-slate-200 dark:border-slate-700">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Tipo</th>
                                            <th class="px-3 py-2 text-left">Nombre</th>
                                            <th class="px-3 py-2 text-left">Identificador</th>
                                            <th class="px-3 py-2 text-left">Ubigeo</th>
                                            <th class="px-3 py-2 text-left">Dispositivos</th>
                                            <th class="px-3 py-2 text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm divide-y divide-slate-200 dark:divide-slate-700">
                                        @forelse ($accesos as $acceso)
                                            <tr wire:key="acceso-{{ $acceso->id }}">
                                                <td class="px-3 py-2">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    {{ $acceso->tipo === 'municipalidad' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                                        {{ ucfirst($acceso->tipo) }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2 font-medium text-slate-800 dark:text-slate-100">
                                                    {{ $acceso->nombre }}
                                                </td>
                                                <td class="px-3 py-2 text-slate-600 dark:text-slate-400">
                                                    {{ $acceso->identificador }}
                                                </td>
                                                <td class="px-3 py-2 text-slate-600 dark:text-slate-400">
                                                    {{ $acceso->ubigeo }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs 
                                                    {{ $acceso->devices_count > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                                                        {{ $acceso->devices_count }} dispositivos
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2 text-center">
                                                    <div class="flex justify-center space-x-2">
                                                        <button wire:click="vincularDispositivos({{ $acceso->id }})"
                                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                                            <svg class="w-4 h-4 mr-1" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                            </svg>
                                                            Vincular
                                                        </button>
                                                        <button wire:click="verDispositivos({{ $acceso->id }})"
                                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300"
                                                            title="Ver {{ $acceso->devices_count }} dispositivos">
                                                            <svg class="w-4 h-4 mr-1" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                            Ver ({{ $acceso->devices_count }})
                                                        </button>
                                                        <button wire:click="editarAcceso({{ $acceso->id }})"
                                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-amber-600 dark:text-amber-400 hover:text-amber-900 dark:hover:text-amber-300">
                                                            <svg class="w-4 h-4 mr-1" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                            Editar
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6"
                                                    class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                                    No hay municipalidades o comisarías registradas
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($accesos instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                <div class="mt-4">
                                    {{ $accesos->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

@push('modals')
    @livewire('config.siscop.create')
    @livewire('config.siscop.edit')
    @livewire('config.siscop.sync-devices')
    @livewire('config.siscop.show-devices')
@endpush
