<?php

namespace App\Livewire\Devices;

use Exception;
use Carbon\Carbon;
use App\Models\Config;

use App\Models\Device;

use App\Models\Service;
use Livewire\Component;
use Jhamnerx\WialonApiPhp\Wialon;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class Index extends Component
{

    use WithPagination;

    public $search;
    public $activeServicesOnly = false;
    public $selectedService = '';
    public Config $config;
    public Collection $services;

    // Propiedades para el modal de reenvío
    public $showReenvioModal = false;
    public $selectedDevice = null;
    public $selectedServiceReenvio = '';
    public $fechaInicio = '';
    public $fechaFin = '';
    public $processingReenvio = false;

    public function mount()
    {
        $this->config = Config::first();
        $this->services = Service::all()->collect();
    }

    public function render()
    {
        $devices = Device::with(['deviceServices.service'])
            ->where(function ($query) {
                $query->where('plate', 'like', '%' . $this->search . '%')
                    ->orWhere('id_wialon', 'like', '%' . $this->search . '%')
                    ->orWhere('name', 'like', '%' . $this->search . '%')
                    ->orWhere('imei', 'like', '%' . $this->search . '%');
            })
            ->when($this->activeServicesOnly, function ($query) {
                $query->whereHas('deviceServices', function ($q) {
                    $q->where('active', true);
                });
            })
            ->when($this->selectedService, function ($query) {
                $query->whereHas('deviceServices', function ($q) {
                    $q->where('name', $this->selectedService)
                        ->where('active', true);
                });
            })
            ->paginate(15);

        return view('livewire.devices.index', compact('devices'));
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedActiveServicesOnly()
    {
        $this->resetPage();
    }

    public function updatedSelectedService()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->activeServicesOnly = false;
        $this->selectedService = '';
        $this->search = '';
        $this->resetPage();
    }

    public function updatePlate($deviceId, $newPlate)
    {
        $device = Device::find($deviceId);

        if (!$device) {
            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'ERROR',
                mensaje: 'Dispositivo no encontrado'
            );
            return;
        }

        // Validar formato de placa peruana
        if (!$this->isValidPlateFormat($newPlate)) {
            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'PLACA INVÁLIDA',
                mensaje: 'La placa debe tener un formato válido peruano (Ej: ABC-123, AB-1234, A1B-234, E UA-123, M6I-831)'
            );
            return;
        }
        $device->update(['plate' => strtoupper($newPlate)]);

        $this->dispatch(
            'notify-toast',
            icon: 'success',
            title: 'PLACA ACTUALIZADA',
            mensaje: 'La placa ha sido actualizada correctamente'
        );
    }

    private function isValidPlateFormat($plate)
    {
        if (empty($plate)) {
            return false;
        }

        $platePatterns = [
            // Formatos actuales (SIIV - desde 2010)
            '/^[A-Z]{3}-[0-9]{3}$/',      // ABC-123 (formato antiguo - 3 letras, 3 números)
            '/^[A-Z]{3}-[0-9]{4}$/',      // ABC-1234 (formato nuevo - 3 letras, 4 números)
            '/^[A-Z]{2}-[0-9]{4}$/',      // AB-1234 (motocicletas - 2 letras, 4 números)
            '/^[A-Z][0-9][A-Z]-[0-9]{3}$/', // A1B-234 (alfanumérico - letra, número, letra, 3 números)
            '/^[A-Z]{2}[0-9]-[0-9]{3}$/', // AB1-234 (alfanumérico - 2 letras, número, 3 números)
            '/^[0-9]{4}-[A-Z]{2}$/',      // 1234-AB (formato especial invertido)

            // Formatos especiales con prefijo E
            '/^E [A-Z]{2}-[0-9]{3}$/',    // E UA-123 (emergencia, diplomáticas, etc.)
            '/^E [A-Z]{2}-[0-9]{4}$/',    // E CD-1234 (diplomáticas extendidas)

            // Formatos mixtos alfanuméricos (incluye casos como M6I-831)
            '/^[A-Z0-9]{3}-[0-9]{3}$/',   // M6I-831 (3 caracteres alfanuméricos, 3 números)
            '/^[A-Z0-9]{3}-[0-9]{4}$/',   // M6I-8310 (3 caracteres alfanuméricos, 4 números)
            '/^[A-Z0-9]{2}-[0-9]{4}$/',   // M6-8310 (2 caracteres alfanuméricos, 4 números)

            // Formatos históricos (1974-1995)
            '/^[A-Z]{2}-[0-9]{3}$/',      // AB-123 (formato histórico)
            '/^[A-Z]-[0-9]{4}$/',         // A-1234 (formato histórico simple)

            // Formatos muy antiguos (1924-1973) - flexibles
            '/^[0-9]{1,2}-[0-9]{2}-[0-9]{2}$/', // 1-23-45 formato muy antiguo
            '/^[0-9]{2}\s+[0-9]{2}-[0-9]{2}$/', // 12 34-56 formato con espacio

            // Formato flexible para casos edge (más restrictivo)
            '/^[A-Z0-9]{1,3}[-\s][A-Z0-9]{2,4}$/' // Patrón flexible general (máximo 3 caracteres antes del guión)
        ];

        foreach ($platePatterns as $pattern) {
            if (preg_match($pattern, strtoupper(trim($plate)))) {
                return true;
            }
        }

        return false;
    }

    public function loadDevicesFromWialon()
    {
        if (!$this->config->hasValidToken()) {
            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'ERROR',
                mensaje: 'No tienes un token válido. Genera uno primero en la configuración general.'
            );
            return;
        }

        try {
            $wialon = new Wialon($this->config->base_uri);
            $wialon->login($this->config->token);
            $response = $wialon->unitByName('*', 273);
            Log::info('Respuesta de Wialon al cargar dispositivos', $response);
            if (!isset($response['items']) || empty($response['items'])) {
                $this->dispatch(
                    'notify-toast',
                    icon: 'warning',
                    title: 'SIN RESULTADOS',
                    mensaje: 'No se encontraron dispositivos en Wialon'
                );
                return;
            }

            $synced = $this->syncDevices($response['items']);

            $mensaje = "Sincronizados: {$synced['updated']}, Nuevos: {$synced['created']}, Eliminados: {$synced['deleted']}";

            if (!empty($synced['skipped'])) {
                $skippedCount = count($synced['skipped']);
                $skippedNames = implode(', ', $synced['skipped']);
                $mensaje .= " | Omitidos (sin IMEI): {$skippedCount} ({$skippedNames})";
            }

            $this->dispatch(
                'notify-toast',
                icon: 'success',
                title: 'DISPOSITIVOS SINCRONIZADOS',
                mensaje: $mensaje
            );
        } catch (Exception $th) {
            Log::error('Error al cargar dispositivos de Wialon', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'ERROR',
                mensaje: 'Error al cargar dispositivos: ' . $th->getMessage()
            );
        }
    }

    private function syncDevices(array $units): array
    {
        $stats = [
            'created' => 0,
            'updated' => 0,
            'deleted' => 0,
            'skipped' => []
        ];

        // IDs de Wialon que vienen en la respuesta
        $wialonIds = collect($units)->pluck('id')->filter()->toArray();

        // Base URL para las imágenes
        $baseUrl = rtrim($this->config->base_uri, '/');

        foreach ($units as $unit) {
            if (!isset($unit['id'])) {
                continue;
            }

            $idWialon = (string) $unit['id'];
            $name = $unit['nm'] ?? null;
            $imageUri = isset($unit['uri']) ? $baseUrl . $unit['uri'] : null;

            // Verificar que el dispositivo tenga IMEI (uid)
            if (!isset($unit['uid']) || empty($unit['uid'])) {
                $stats['skipped'][] = $name ?? "ID: $idWialon";
                Log::warning("Dispositivo sin IMEI omitido", [
                    'id_wialon' => $idWialon,
                    'name' => $name
                ]);
                continue;
            }

            $newImei = $unit['uid'];

            // Buscar o crear el dispositivo
            $device = Device::firstOrNew(['id_wialon' => $idWialon]);

            $isNew = !$device->exists;
            $hasChanges = false;

            // Verificar y actualizar name si cambió
            if ($device->name !== $name) {
                $device->name = $name;
                $hasChanges = true;
            }

            // Actualizar IMEI si cambió
            if ($device->imei !== $newImei) {
                $device->imei = $newImei;
                $hasChanges = true;
            }
            // Solo guardar si es nuevo o hay cambios
            if ($isNew || $hasChanges) {
                $device->save();
            }

            // Gestionar la imagen usando la relación polimórfica
            if ($imageUri) {
                $imagen = $device->imagen;

                if ($imagen) {
                    // Actualizar si cambió la URL
                    if ($imagen->url !== $imageUri) {
                        $imagen->update(['url' => $imageUri]);
                        $hasChanges = true;
                    }
                } else {
                    // Crear nueva imagen
                    $device->imagen()->create(['url' => $imageUri]);
                    $hasChanges = true;
                }
            } else {
                // Si no hay URI, eliminar imagen existente si la tiene
                if ($device->imagen) {
                    $device->imagen()->delete();
                    $hasChanges = true;
                }
            }

            if ($isNew) {
                $stats['created']++;
            } elseif ($hasChanges) {
                $stats['updated']++;
            }
        }

        // Eliminar dispositivos que ya no existen en Wialon
        $deleted = Device::whereNotIn('id_wialon', $wialonIds)->delete();
        $stats['deleted'] = $deleted;

        return $stats;
    }

    public function toggleDeviceService(Device $device, string $serviceName)
    {
        // Validar formato de placa
        if (!preg_match('/^[A-Z0-9]{3}-[A-Z0-9]{3}$/', $device->plate)) {
            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'PLACA INVÁLIDA',
                mensaje: 'La placa del dispositivo debe tener el formato ABC-123'
            );
            return;
        }

        // Verificar que el servicio existe y está activo globalmente
        $service = Service::where('name', $serviceName)->where('active', true)->first();

        if (!$service) {
            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'SERVICIO NO DISPONIBLE',
                mensaje: 'El servicio no está disponible o no está activo globalmente'
            );
            return;
        }

        try {
            $deviceService = $device->deviceServices()
                ->where('name', $serviceName)
                ->first();

            if ($deviceService) {
                // Cambiar estado
                $deviceService->update(['active' => !$deviceService->active]);
                $action = $deviceService->active ? 'activado' : 'desactivado';
                $icon = $deviceService->active ? 'success' : 'error';
                $title = $deviceService->active ? 'SERVICIO ACTIVADO' : 'SERVICIO DESACTIVADO';
            } else {
                // Crear nuevo servicio activo
                $device->deviceServices()->create([
                    'name' => $serviceName,
                    'active' => true,
                ]);
                $action = 'activado';
                $icon = 'success';
                $title = 'SERVICIO ACTIVADO';
            }

            $this->dispatch(
                'notify-toast',
                icon: $icon,
                title: $title,
                mensaje: "Servicio {$service->display_name} {$action} para {$device->plate}"
            );
        } catch (\Throwable $th) {
            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'ERROR',
                mensaje: 'Error al actualizar el servicio: ' . $th->getMessage()
            );
        }
    }

    public function getDeviceServiceStatus(Device $device, string $serviceName): bool
    {
        return $device->hasActiveService($serviceName);
    }

    /**
     * Verificar si el dispositivo tiene al menos uno de los servicios activos
     */
    public function hasAnyActiveService(Device $device, array $serviceNames): bool
    {
        foreach ($serviceNames as $serviceName) {
            if ($device->hasActiveService($serviceName)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Abrir modal de reenvío historial
     */
    public function abrirModalReenvio($deviceId)
    {
        $this->selectedDevice = Device::with('deviceServices.service')->find($deviceId);

        if (!$this->selectedDevice) {
            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'ERROR',
                mensaje: 'Dispositivo no encontrado'
            );
            return;
        }

        // Obtener servicios activos del dispositivo
        $activeServices = $this->selectedDevice->deviceServices()
            ->whereHas('service', function ($query) {
                $query->where('active', true);
            })
            ->where('active', true)
            ->with('service')
            ->get();

        if ($activeServices->isEmpty()) {
            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'SIN SERVICIOS ACTIVOS',
                mensaje: 'El dispositivo no tiene servicios activos para reenvío'
            );
            return;
        }

        // Limpiar valores anteriores
        $this->fechaInicio = '';
        $this->fechaFin = '';
        $this->selectedServiceReenvio = '';
        $this->showReenvioModal = true;
    }

    /**
     * Cerrar modal de reenvío
     */
    public function cerrarModalReenvio()
    {
        $this->showReenvioModal = false;
        $this->selectedDevice = null;
        $this->selectedServiceReenvio = '';
        $this->fechaInicio = '';
        $this->fechaFin = '';
        $this->processingReenvio = false;
    }

    /**
     * Selección rápida: Hace 1 hora
     */
    public function seleccionarHaceUnaHora()
    {
        $ahora = Carbon::now();
        $haceUnaHora = $ahora->copy()->subHour();

        $this->fechaInicio = $haceUnaHora->format('Y-m-d H:i:s');
        $this->fechaFin = $ahora->format('Y-m-d H:i:s');
    }

    /**
     * Selección rápida: Hoy
     */
    public function seleccionarHoy()
    {
        $ahora = Carbon::now();
        $inicioHoy = $ahora->copy()->startOfDay();

        $this->fechaInicio = $inicioHoy->format('Y-m-d H:i:s');
        $this->fechaFin = $ahora->format('Y-m-d H:i:s');
    }


    /**
     * Selección rápida: Ayer
     */
    public function seleccionarAyer()
    {
        $ayer = Carbon::yesterday();
        $inicioAyer = $ayer->copy()->startOfDay();
        $finAyer = $ayer->copy()->endOfDay();

        $this->fechaInicio = $inicioAyer->format('Y-m-d H:i:s');
        $this->fechaFin = $finAyer->format('Y-m-d H:i:s');
    }

    /**
     * Procesar reenvío historial
     */
    public function procesarReenvio()
    {
        // Validaciones básicas
        $this->validate([
            'selectedServiceReenvio' => 'required|string|in:siscop,osinergmin,sutran',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
        ], [
            'selectedServiceReenvio.required' => 'Debes seleccionar un servicio',
            'selectedServiceReenvio.in' => 'El servicio seleccionado no es válido',
            'fechaInicio.required' => 'La fecha de inicio es requerida',
            'fechaInicio.date' => 'La fecha de inicio debe ser una fecha válida',
            'fechaFin.required' => 'La fecha de fin es requerida',
            'fechaFin.date' => 'La fecha de fin debe ser una fecha válida',
            'fechaFin.after_or_equal' => 'La fecha de fin no puede ser anterior a la fecha de inicio',
        ]);

        // Validar que el dispositivo tenga el servicio seleccionado activo
        if (!$this->selectedDevice->hasActiveService($this->selectedServiceReenvio)) {
            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'SERVICIO NO ACTIVO',
                mensaje: 'El dispositivo no tiene el servicio seleccionado activo'
            );
            return;
        }

        // Validaciones adicionales SISCOP
        if ($this->selectedServiceReenvio === 'siscop') {
            if (!$this->selectedDevice->acceso) {
                $this->dispatch(
                    'notify-toast',
                    icon: 'error',
                    title: 'SIN ACCESO',
                    mensaje: 'El dispositivo no tiene acceso asignado para SISCOP'
                );
                return;
            }
        }

        // Validaciones adicionales con Carbon para mejor control
        $fechaInicio = \Carbon\Carbon::parse($this->fechaInicio);
        $fechaFin = \Carbon\Carbon::parse($this->fechaFin);
        $ahora = \Carbon\Carbon::now();

        // Verificar que la fecha de fin no sea anterior a la fecha de inicio
        if ($fechaFin->isBefore($fechaInicio)) {
            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'FECHAS INVÁLIDAS',
                mensaje: 'La fecha de fin no puede ser anterior a la fecha de inicio'
            );
            return;
        }

        // Verificar que las fechas no sean futuras
        if ($fechaInicio->isAfter($ahora)) {
            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'FECHA INVÁLIDA',
                mensaje: 'La fecha de inicio no puede ser posterior a la fecha actual'
            );
            return;
        }

        if ($fechaFin->isAfter($ahora)) {
            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'FECHA INVÁLIDA',
                mensaje: 'La fecha de fin no puede ser posterior a la fecha actual'
            );
            return;
        }

        // Verificar que el rango no sea demasiado amplio (opcional - máximo 7 días)
        if ($fechaInicio->diffInDays($fechaFin) > 7) {
            $this->dispatch(
                'notify-toast',
                icon: 'warning',
                title: 'RANGO AMPLIO',
                mensaje: 'El rango de fechas es muy amplio (más de 7 días). Esto puede generar muchas tramas.'
            );
            // No retornamos, solo advertimos
        }

        $this->processingReenvio = true;

        try {
            // Armar datos para el job (array simple, no anidado)
            $historialData = [
                'id' => $this->selectedDevice->id_wialon,
                'last_update' => $fechaInicio->timestamp,
                'current_time' => $fechaFin->timestamp,
                'service' => $this->selectedServiceReenvio
            ];

            // Despachar el job
            \App\Jobs\Wialon\ReenviarHistorialMultiServicio::dispatch($historialData);

            $this->dispatch(
                'notify-toast',
                icon: 'success',
                title: 'REENVÍO INICIADO',
                mensaje: "Se ha iniciado el reenvío historial para {$this->selectedDevice->plate}. Verifica los logs para ver el resultado."
            );

            $this->cerrarModalReenvio();
        } catch (\Exception $e) {
            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'ERROR',
                mensaje: 'Error al procesar reenvío: ' . $e->getMessage()
            );
        } finally {
            $this->processingReenvio = false;
        }
    }
}
