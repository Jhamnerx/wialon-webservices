<div>
    <div
        class="my-4 container px-10 mx-auto flex flex-col md:flex-row items-start md:items-center justify-between pb-4 border-b border-gray-300">
        <!-- Add customer button -->
        <a href="">
            <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-back w-5 h-5"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M9 11l-4 4l4 4m-4 -4h11a4 4 0 0 0 0 -8h-1" />
                </svg>
                <span class="hidden xs:block ml-2">Atras</span>
            </button>
        </a>
        <div class="mt-2 md:mt-0">
            <h4 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-200">SERVICIO DE RETRANSMISION
                WOX</h4>
            <ul aria-label="current Status"
                class="flex flex-col md:flex-row items-start md:items-center text-gray-600 dark:text-gray-400 text-sm mt-3">
            </ul>
        </div>
    </div>
    <!-- Code block ends -->
    <div class="p-2 shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-2 bg-slate-100 dark:bg-gray-700 sm:p-2">
            <div class="grid grid-cols-12 gap-2">
                {{-- COLUMNA IZQUIERDA --}}

                <div class="col-span-12 md:col-span-7">
                    {{-- PRIMERA FILA --}}
                    <div
                        class="grid grid-cols-12 gap-2 bg-white dark:bg-gray-800 items-start border rounded-md m-3 p-4">

                        {{-- LOGO --}}
                        <div class="col-span-12 lg:col-span-2">
                            <div>
                                <img
                                    src="https://dbmvg3ovo90hn.cloudfront.net/addons/shared_addons/themes/gpswox/img/logo-gpswox-homepage-2.svg">
                            </div>
                        </div>

                        {{-- DATOS DE LA EMPRESA --}}
                        <div
                            class="col-span-12 lg:col-span-4 xl:col-span-4 pl-6 self-center overflow-hidden text-ellipsis">
                            <div class="mb-0" style="line-height: initial;">
                                <span class="font-bold text-slate-800 dark:text-gray-200">
                                    wox
                                </span>
                                <br>

                            </div>
                        </div>

                        <div class="col-span-12 self-end">

                            <div class="grid grid-cols-12 gap-2">

                            </div>

                        </div>

                    </div>

                    <div
                        class="col-span-12 md:col-span-9 grid grid-cols-12 gap-2 bg-white dark:bg-gray-800 items-start border rounded-md m-3 p-4">

                        <div class="col-span-6 md:col-span-4">
                            <x-form.input wire:model.live="email" label="Email:" />
                        </div>

                        <div class="col-span-6 md:col-span-4">

                            <x-form.password wire:model.live="password" label="Contraseña:"
                                autocomplete="new-password" />
                        </div>

                        <div class="col-span-6 md:col-span-4">

                            <x-form.input wire:model.live="token" label="Token:"
                                description="Genera tu token en el boton" />
                        </div>


                        <div class="col-span-6 md:col-span-4">

                            <x-form.input wire:model.live="host" label="Host:" description="Ingresa tu host Ex." />
                        </div>

                        <div class="col-span-6 md:col-span-4 align-middle items-center justify-center my-auto flex">

                            <button wire:click="getToken" target="_blank"
                                class="inline-flex justify-center items-center group hover:shadow-sm focus:ring-offset-background-white dark:focus:ring-offset-background-dark transition-all ease-in-out duration-200 focus:ring-2 text-white bg-warning-500 dark:bg-warning-700 hover:text-white hover:bg-warning-600 dark:hover:bg-warning-600 rounded-md gap-x-2 text-sm px-4 py-2 
                                 @if (!$host) cursor-not-allowed opacity-80 @endif"
                                @if (!$host) onclick="return false;" @endif>
                                Obtener Token
                            </button>


                        </div>

                        <div class="col-span-12 text-center flex justify-end">
                            <x-form.button wire:click="save" spinner="save" Positive rounded="md" label="Guardar" />
                        </div>

                    </div>
                    {{-- fila de vehiculos --}}
                    <div
                        class="col-span-12 md:col-span-9 grid grid-cols-12 gap-2 bg-white dark:bg-gray-800 items-start border rounded-md m-3 p-4">

                        <!-- Add button -->
                        <x-form.button wire:click="openModalReevio" spinner="openModalReevio" Positive rounded="md"
                            label="Reenvio data" />

                        <div
                            class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2 col-span-full pl-9">


                            <!-- Search form -->
                            <x-form.input icon="magnifying-glass" wire:model.live="search"
                                placeholder="Buscar Vehiculo" />

                            <!-- Add button -->
                            <x-form.button wire:click="loadUnits" spinner="loadUnits" Positive rounded="md"
                                label="Obtener Dispositivos" />
                        </div>

                        <div
                            class="bg-white dark:bg-slate-800 shadow-lg rounded-sm border border-slate-200 dark:border-slate-700 mb-8  col-span-full">

                            <!-- Table -->
                            <div class="overflow-x-auto">
                                <table class="table-auto w-full dark:text-slate-300">
                                    <!-- Table header -->
                                    <thead
                                        class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/20 border-t border-b border-slate-200 dark:border-slate-700">
                                        <tr>

                                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                                <div class="font-semibold text-left">IMAGEN</div>
                                            </th>
                                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                                <div class="font-semibold text-left">ID Wialon</div>
                                            </th>

                                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                                <div class="font-semibold text-left">NAME</div>
                                            </th>
                                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap min-w-44">
                                                <div class="font-semibold text-left">PLACA</div>
                                            </th>

                                            @foreach ($servicios->keys()->all() as $service)
                                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                                    <div class="font-semibold text-left">
                                                        {{ $service }}
                                                    </div>
                                                </th>
                                            @endforeach

                                        </tr>
                                    </thead>

                                    <!-- Table body -->
                                    <tbody class="text-sm divide-y divide-slate-200 dark:divide-slate-700">
                                        <!-- Row -->
                                        {{-- {{ json_encode($unidades->items()) }} --}}

                                        @foreach ($unidades as $key => $unit)
                                            <tr wire:key="device-{{ $unit->id_wialon }}">
                                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap 2xl:block">
                                                    <div class="flex items-center">
                                                        <div
                                                            class="w-14 h-10 shrink-0 flex items-center justify-center bg-slate-100 rounded-full mr-2 sm:mr-3">

                                                            @if ($unit->url_image)
                                                                <img class="rounded-full" src="{{ $unit->url_image }}"
                                                                    width="30" alt="Icon 01" />
                                                            @endif
                                                        </div>

                                                    </div>
                                                </td>

                                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                                    <div class="flex items-center">

                                                        <div class="font-medium text-sky-500">
                                                            {{ $unit->id_wialon }}
                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                                    <div class="flex items-center">

                                                        <div class="font-medium text-slate-800 dark:text-slate-100">
                                                            {{ $unit->name }}
                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                                    <div class="flex items-center">

                                                        @livewire('wox.input-plate', ['unit' => $unit], key('input-' . $unit->id))
                                                    </div>
                                                </td>
                                                @foreach ($unit->services as $service => $status)
                                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                                        <div class="text-center">
                                                            <div class="m-3 w-48">


                                                                <div class="flex items-center mt-2"
                                                                    x-data="{ checked: {{ $status['active'] ? 'true' : 'false' }} }">

                                                                    <div class="form-switch">
                                                                        <input
                                                                            name="service-{{ $unit->id }}-{{ $service }}"
                                                                            wire:click="changeServiceStatus({{ $unit->id }}, '{{ $service }}', $event.target.checked)"
                                                                            type="checkbox"
                                                                            id="switch-{{ $unit->id }}-{{ $service }}"
                                                                            class="sr-only" x-model="checked" />
                                                                        <label class="bg-slate-400"
                                                                            for="switch-{{ $unit->id }}-{{ $service }}">
                                                                            <span class="bg-white shadow-sm"
                                                                                aria-hidden="true"></span>
                                                                            <span class="sr-only">Estado</span>
                                                                        </label>
                                                                    </div>

                                                                </div>


                                                            </div>
                                                        </div>
                                                    </td>
                                                @endforeach

                                            </tr>
                                        @endforeach

                                        @if (count($unidades) == 0)
                                            <tr>
                                                <td colspan="6" class="text-center py-3">
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        No se encontraron resultados
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif

                                    </tbody>
                                </table>
                                <div class="mx-2 my-2">
                                    {{ $unidades->links() }}
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                {{-- COLUMNA DERECHA --}}

                <div class="col-span-12 md:col-span-5">

                    <div
                        class="col-span-12 md:col-span-3 grid grid-cols-12 gap-4 bg-white dark:bg-gray-800 items-start border rounded-md m-3 p-4">

                        <div class="col-span-12 text-center ">

                            <img src="https://www.sutran.gob.pe/wp-content/uploads/2017/08/logo_julio.png"
                                alt="">
                        </div>

                        <div class="col-span-6 text-center ">

                            <x-form.checkbox left-label="Activo" value="true" lg
                                wire:model.live="servicios.sutran.status" />

                        </div>
                        <div class="col-span-6 text-center ">

                            <x-form.checkbox left-label="Logs Activos" value="true" lg
                                wire:model.live="servicios.sutran.enabled_logs" />

                        </div>
                        @if ($servicios['sutran']['status'])
                            <div class="col-span-12">

                                <x-form.input wire:model.live="servicios.sutran.token" label="Token:"
                                    description="Ingresa tu token de transmisión" />
                            </div>
                        @endif

                        <div class="col-span-12 text-center flex justify-end">
                            <x-form.button wire:click="saveServicioSutran" spinner="saveServicioSutran" Positive
                                rounded="md" label="Guardar" />
                        </div>
                    </div>

                    <div
                        class="col-span-12 md:col-span-3 grid grid-cols-12 gap-4 bg-white dark:bg-gray-800 items-start border rounded-md m-3 p-4">

                        <div class="col-span-12 text-center ">

                            <img src="https://cdn.www.gob.pe/uploads/document/file/5224085/Logotipo%20azul%20sin%20descriptor-01.jpg"
                                alt="">
                        </div>

                        <div class="col-span-6 text-center ">

                            <x-form.checkbox left-label="Activo" value="true" lg id="servicio"
                                wire:model.live="servicios.osinergmin.status" />

                        </div>

                        <div class="col-span-6 text-center ">

                            <x-form.checkbox left-label="Logs Activos" value="true" lg id="servicio"
                                wire:model.live="servicios.osinergmin.enabled_logs" />

                        </div>
                        @if ($servicios['osinergmin']['status'])
                            <div class="col-span-12">

                                <x-form.input wire:model.live="servicios.osinergmin.token" label="Token:"
                                    description="Ingresa tu token de transmisión" />
                            </div>
                        @endif

                        <div class="col-span-12 text-center flex justify-end">
                            <x-form.button wire:click="saveServicioOsinergmin" spinner="saveServicioOsinergmin"
                                Positive rounded="md" label="Guardar" />
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>


</div>

@push('modals')
    @livewire('wox.reenvio.modal')
@endpush

@push('scripts')
    <script>
        document.addEventListener('keydown', function(event) {
            try {
                if (event.key === 'F2') {
                    // Ejecutar la acción deseada
                    ejecutarAccion();
                    // Prevenir la acción por defecto de la tecla F2 (renombrar en el Explorador de archivos de Windows)
                    event.preventDefault();
                }
            } catch (error) {
                console.error('Se produjo un error al presionar la tecla F2:', error);
            }
        });

        function ejecutarAccion() {
            // Aquí va la lógica de la acción a ejecutar
            @this.openModalAddProducto();
            console.log('Tecla F2 presionada. Acción ejecutada.');
        }
    </script>
@endpush
