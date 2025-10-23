<div>
    <div
        class="my-4 container px-10 mx-auto flex flex-col md:flex-row items-start md:items-center justify-between pb-4 border-b border-gray-300 dark:border-gray-600">
        <div class="mt-2 md:mt-0">
            <h4 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-200">CONFIGURACIÓN DE DISPOSITIVOS
            </h4>
            <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Gestión de dispositivos y sus servicios</p>
        </div>

        <div class="flex gap-2 mt-2 md:mt-0">
            <x-form.button primary wire:click="loadDevicesFromWialon" spinner="loadDevicesFromWialon" icon="arrow-path">
                Sincronizar desde Wialon
            </x-form.button>
        </div>
    </div>

    <div class="p-2 shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-2 bg-slate-100 dark:bg-gray-700 sm:p-2">

            <!-- Filtros -->
            <div class="bg-white dark:bg-gray-800 border rounded-md p-4 mb-4">
                <div class="flex flex-col md:flex-row gap-4 items-center">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Buscar
                        </label>
                        <input type="text" wire:model.live.debounce.500ms="search"
                            placeholder="Buscar por placa, nombre, ID o IMEI..."
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div class="w-full md:w-auto">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Filtrar por servicio
                        </label>
                        <select wire:model.live="selectedService"
                            class="w-full md:w-48 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Todos los servicios</option>
                            @foreach ($services->where('active', true) as $service)
                                <option value="{{ $service->name }}">{{ $service->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col justify-end">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Filtros adicionales
                        </label>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" wire:model.live="activeServicesOnly"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Solo con servicios
                                    activos</span>
                            </label>

                            @if ($search || $selectedService || $activeServicesOnly)
                                <button wire:click="clearFilters"
                                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    Limpiar filtros
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de filtros aplicados -->
            @if ($search || $selectedService || $activeServicesOnly)
                <div
                    class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-3 mb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 text-sm text-blue-700 dark:text-blue-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z">
                                </path>
                            </svg>
                            <span class="font-medium">Filtros aplicados:</span>
                            @if ($search)
                                <span class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded text-xs">Búsqueda:
                                    "{{ $search }}"</span>
                            @endif
                            @if ($selectedService)
                                @php $serviceDisplay = $services->where('name', $selectedService)->first(); @endphp
                                <span class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded text-xs">Servicio:
                                    {{ $serviceDisplay ? $serviceDisplay->display_name : $selectedService }}</span>
                            @endif
                            @if ($activeServicesOnly)
                                <span class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded text-xs">Solo con servicios
                                    activos</span>
                            @endif
                        </div>
                        <button wire:click="clearFilters"
                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                            Limpiar filtros
                        </button>
                    </div>
                    <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                        Mostrando {{ $devices->total() }} dispositivo(s)
                        {{ $devices->total() === 1 ? 'encontrado' : 'encontrados' }}
                    </div>
                </div>
            @endif

            <!-- Lista de Dispositivos -->
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Dispositivo
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Placa
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    IMEI
                                </th>
                                @foreach ($services as $service)
                                    @if ($service->active)
                                        <th
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            {{ $service->display_name }}
                                        </th>
                                    @endif
                                @endforeach
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($devices as $device)
                                <tr wire:key="device-{{ $device->id }}"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if ($device->imagen)
                                                    <img src="{{ $device->imagen->url }}" alt="{{ $device->name }}"
                                                        class="h-10 w-10 rounded-lg object-cover"
                                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div style="display: none;"
                                                        class="h-10 w-10 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                        <svg class="h-5 w-5 text-gray-400" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                @else
                                                    <div
                                                        class="h-10 w-10 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                        <svg class="h-5 w-5 text-gray-400" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $device->name }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    ID: {{ $device->id_wialon }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="w-32">
                                            <input type="text" value="{{ $device->plate ?: '' }}"
                                                placeholder="Ingresa placa" maxlength="10"
                                                class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md px-2 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase font-mono"
                                                wire:blur="updatePlate({{ $device->id }}, $event.target.value)"
                                                title="Formatos válidos: ABC-123, AB-1234, A1B-234, E UA-123, M6I-831, etc." />
                                            @php
                                                $platePatterns = [
                                                    // Formatos actuales (SIIV - desde 2010)
                                                    '/^[A-Z]{3}-[0-9]{3}$/', // ABC-123 (formato antiguo - 3 letras, 3 números)
                                                    '/^[A-Z]{3}-[0-9]{4}$/', // ABC-1234 (formato nuevo - 3 letras, 4 números)
                                                    '/^[A-Z]{2}-[0-9]{4}$/', // AB-1234 (motocicletas - 2 letras, 4 números)
                                                    '/^[A-Z][0-9][A-Z]-[0-9]{3}$/', // A1B-234 (alfanumérico - letra, número, letra, 3 números)
                                                    '/^[A-Z]{2}[0-9]-[0-9]{3}$/', // AB1-234 (alfanumérico - 2 letras, número, 3 números)
                                                    '/^[0-9]{4}-[A-Z]{2}$/', // 1234-AB (formato especial invertido)

                                                    // Formatos especiales con prefijo E
                                                    '/^E [A-Z]{2}-[0-9]{3}$/', // E UA-123 (emergencia, diplomáticas, etc.)
                                                    '/^E [A-Z]{2}-[0-9]{4}$/', // E CD-1234 (diplomáticas extendidas)

                                                    // Formatos mixtos alfanuméricos (incluye casos como M6I-831)
                                                    '/^[A-Z0-9]{3}-[0-9]{3}$/', // M6I-831 (3 caracteres alfanuméricos, 3 números)
                                                    '/^[A-Z0-9]{3}-[0-9]{4}$/', // M6I-8310 (3 caracteres alfanuméricos, 4 números)
                                                    '/^[A-Z0-9]{2}-[0-9]{4}$/', // M6-8310 (2 caracteres alfanuméricos, 4 números)

                                                    // Formatos históricos (1974-1995)
                                                    '/^[A-Z]{2}-[0-9]{3}$/', // AB-123 (formato histórico)
                                                    '/^[A-Z]-[0-9]{4}$/', // A-1234 (formato histórico simple)

                                                    // Formatos muy antiguos (1924-1973) - flexibles
                                                    '/^[0-9]{1,2}-[0-9]{2}-[0-9]{2}$/', // 1-23-45 formato muy antiguo
                                                    '/^[0-9]{2}\s+[0-9]{2}-[0-9]{2}$/', // 12 34-56 formato con espacio

                                                    // Formato flexible para casos edge (más restrictivo)
                                                    '/^[A-Z0-9]{1,3}[-\s][A-Z0-9]{2,4}$/', // Patrón flexible general (máximo 3 caracteres antes del guión)
                                                ];
                                                $isValidPlate = false;
                                                if ($device->plate) {
                                                    foreach ($platePatterns as $pattern) {
                                                        if (preg_match($pattern, strtoupper(trim($device->plate)))) {
                                                            $isValidPlate = true;
                                                            break;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            @if ($device->plate && !$isValidPlate)
                                                <div class="text-xs text-red-500 mt-1">
                                                    <svg class="inline h-3 w-3 mr-1" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                        </path>
                                                    </svg>
                                                    Formato inválido
                                                </div>
                                            @elseif($device->plate)
                                                <div class="text-xs text-green-500 mt-1">
                                                    <svg class="inline h-3 w-3 mr-1" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Válida
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($device->imei)
                                            <div class="flex items-center">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-mono font-semibold bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300 border border-violet-200 dark:border-violet-700">
                                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                                        </path>
                                                    </svg>
                                                    {{ $device->imei }}
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-1">
                                                {{ strlen($device->imei) }} dígitos
                                            </div>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300 border border-red-200 dark:border-red-700">
                                                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                                    </path>
                                                </svg>
                                                Sin IMEI
                                            </span>
                                        @endif
                                    </td>
                                    @foreach ($services as $service)
                                        @if ($service->active)
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @php
                                                    $isServiceActive = $this->getDeviceServiceStatus(
                                                        $device,
                                                        $service->name,
                                                    );
                                                    $isPlateValid = preg_match(
                                                        '/^[A-Z0-9]{3}-[A-Z0-9]{3}$/',
                                                        $device->plate,
                                                    );
                                                @endphp

                                                <label class="flex items-center justify-center group cursor-pointer">
                                                    <div class="relative">
                                                        <input type="checkbox" {{ $isServiceActive ? 'checked' : '' }}
                                                            {{ !$isPlateValid ? 'disabled' : '' }}
                                                            wire:change="toggleDeviceService({{ $device->id }}, '{{ $service->name }}')"
                                                            class="sr-only peer">
                                                        <div
                                                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-violet-300 dark:peer-focus:ring-violet-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-violet-600 {{ !$isPlateValid ? 'opacity-50 cursor-not-allowed' : '' }}">
                                                        </div>
                                                    </div>
                                                </label>

                                                @if ($isServiceActive)
                                                    <div
                                                        class="inline-flex items-center text-xs text-green-600 dark:text-green-400 mt-1 font-medium">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor"
                                                            viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                        Activo
                                                    </div>
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach
                                    <!-- Columna de Acciones -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if ($this->hasAnyActiveService($device, ['siscop', 'osinergmin']))
                                            <button wire:click="abrirModalReenvio({{ $device->id }})"
                                                class="inline-flex items-center px-3 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors duration-200"
                                                title="Reenviar historial">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Reenvío
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 3 + $services->where('active', true)->count() }}"
                                        class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center py-8">
                                            <svg class="h-12 w-12 text-gray-300 dark:text-gray-600 mb-4"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                                </path>
                                            </svg>
                                            <p class="text-lg font-medium">No hay dispositivos</p>
                                            <p class="text-sm">Sincroniza desde plataforma para cargar los dispositivos
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if ($devices->hasPages())
                    <div
                        class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                        {{ $devices->links() }}
                    </div>
                @endif
            </div>

            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                @foreach ($services->where('active', true) as $service)
                    <div class="bg-white dark:bg-gray-800 border rounded-md p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    {{ $service->display_name }}
                                </p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $devices->sum(function ($device) use ($service) {
                                        return $this->getDeviceServiceStatus($device, $service->name) ? 1 : 0;
                                    }) }}
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800 dark:bg-violet-900 dark:text-violet-200">
                                    Activos
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Modal de Reenvío Historial -->
    <x-form.modal.card persistent title="Reenvío Historial" wire:model.live="showReenvioModal" align="center">
        @if ($selectedDevice)
            <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    <strong>Dispositivo:</strong> {{ $selectedDevice->plate }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <strong>IMEI:</strong> {{ $selectedDevice->imei }}
                </p>
            </div>
        @endif

        <!-- Selector de Servicio -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Servicio de Destino
            </label>
            <select wire:model.live="selectedServiceReenvio"
                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Selecciona un servicio</option>
                @if ($selectedDevice)
                    @foreach ($selectedDevice->deviceServices->where('active', true) as $deviceService)
                        @if ($deviceService->service && $deviceService->service->active)
                            <option value="{{ $deviceService->service->name }}">
                                {{ $deviceService->service->display_name }}
                            </option>
                        @endif
                    @endforeach
                @endif
            </select>
            @error('selectedServiceReenvio')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12 sm:col-span-6">
                <x-form.datetime.picker wire:model.live="fechaInicio" label="Fecha y Hora de Inicio"
                    placeholder="Seleccionar fecha de inicio" :max="now()" />
            </div>

            <div class="col-span-12 sm:col-span-6">
                <x-form.datetime.picker wire:model.live="fechaFin" label="Fecha y Hora de Fin"
                    placeholder="Seleccionar fecha de fin" :max="now()" />
            </div>
        </div>

        <!-- Botones de Selección Rápida -->
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                Selección Rápida
            </label>
            <div class="flex flex-wrap gap-2">
                <x-form.button xs outline label="Hace 1 hora" wire:click="seleccionarHaceUnaHora" class="text-xs"
                    icon="clock" />
                <x-form.button xs outline label="Hoy" wire:click="seleccionarHoy" class="text-xs"
                    icon="calendar" />
                <x-form.button xs outline label="Ayer" wire:click="seleccionarAyer" class="text-xs"
                    icon="calendar" />
            </div>
        </div>

        <div
            class="mt-4 text-xs text-gray-500 dark:text-gray-400 bg-blue-50 dark:bg-blue-900/20 p-3 rounded border border-blue-200 dark:border-blue-800">
            <div class="flex items-start">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd"></path>
                </svg>
                <div>
                    <strong class="text-blue-700 dark:text-blue-300">Nota:</strong>
                    <span class="text-blue-600 dark:text-blue-400">Se procesarán todas las posiciones del vehículo en
                        el rango de fechas seleccionado y se enviarán al servicio seleccionado. La fecha de fin no puede
                        ser posterior a
                        la fecha actual.</span>
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex justify-end gap-x-4">
                <div class="flex gap-2">
                    <x-form.button flat label="Cancelar" wire:click="cerrarModalReenvio" :disabled="$processingReenvio" />
                    <x-form.button primary label="Procesar Reenvío" wire:click.prevent="procesarReenvio"
                        spinner="procesarReenvio" :disabled="$processingReenvio" />
                </div>
            </div>
        </x-slot>
    </x-form.modal.card>
</div>
