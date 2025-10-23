<?php

namespace App\Livewire\Config;

use Exception;
use Gpswox\Wox;
use App\Models\Acceso;
use App\Models\Config;
use App\Models\Service;
use Livewire\Component;
use Livewire\Attributes\On;

class General extends Component
{
    public $user, $token, $status, $custom_host, $base_uri, $host;
    public array $services = [];

    public $url_login = 'https://hosting.wialon.us';

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
        'host' => 'required|url',
        'services.*.active' => 'boolean',
        'services.*.token' => 'nullable|string',
        'services.*.logs_enabled' => 'boolean',
    ];

    public function mount(Config $config, $servicios)
    {
        $this->user = $config->user;
        $this->token = $config->token;
        $this->status = $config->status;
        $this->custom_host = $config->custom_host ? true : false;
        $this->base_uri = $config->base_uri;
        $this->host = $config->host;

        $this->services = $servicios->map(function ($service) {
            return [
                'id' => $service->id,
                'display_name' => $service->display_name,
                'description' => $service->description,
                'active' => (bool) $service->active,
                'token' => $service->token,
                'logs_enabled' => (bool) $service->logs_enabled,
            ];
        })->toArray();


        $this->generateUrlLogin();

        // Cargar servicios y convertir a array para Livewire binding
        // $servicesCollection = Service::all();
        // $this->services = $servicesCollection->map(function ($service) {
        //     return [
        //         'id' => $service->id,
        //         'display_name' => $service->display_name,
        //         'description' => $service->description,
        //         'active' => (bool) $service->active,
        //         'token' => $service->token,
        //         'logs_enabled' => (bool) $service->logs_enabled,
        //     ];
        // })->toArray();
    }

    #[On('actualizarTablaAccesos')]
    public function actualizarTablaAccesos()
    {
        $this->render();
    }
    public function render()
    {
        $accesos = Acceso::withCount('devices')->paginate(10, ['*'], 'accesos_page');

        return view('livewire.config.general', compact('accesos'));
    }

    public function generateUrlLogin()
    {
        $config = Config::first();
        $redirect_uri = route('config.token');

        $this->url_login = $config->url_login . '/login.html?user=' . $config->user . '&redirect_uri=' . $redirect_uri . '&access_type=768&duration=0&client_id=WialonWebServices';
    }

    public function saveGeneralConfig()
    {

        $this->validate([
            'user' => 'required',
            //'token' => 'required',
            'status' => 'required',
            'custom_host' => 'required',
            'base_uri' => 'required',
            'host' => 'required',
        ]);

        try {

            $this->config->update([
                'user' => $this->user,
                'token' => $this->token,
                'status' => $this->status,
                'custom_host' => $this->custom_host,
                'base_uri' => $this->base_uri,
                'host' => $this->host,
            ]);

            $this->dispatch(
                'notify-toast',
                icon: 'success',
                title: 'CONFIGURACIÓN ACTUALIZADA',
                mensaje: 'La configuración general se ha actualizado correctamente'
            );

            $this->generateUrlLogin();
        } catch (\Throwable $th) {
            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'ERROR',
                mensaje: 'Error al actualizar la configuración: ' . $th->getMessage()
            );
        }
    }
    public function saveService($serviceId)
    {
        $service = Service::find($serviceId);

        if (!$service) {
            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'ERROR',
                mensaje: 'Servicio no encontrado'
            );
            return;
        }

        // Encontrar el servicio en el array local
        $serviceIndex = collect($this->services)->search(function ($item) use ($serviceId) {
            return $item['id'] == $serviceId;
        });

        if ($serviceIndex !== false) {
            $serviceData = $this->services[$serviceIndex];

            // Debug: ver qué valores estamos intentando guardar
            logger()->info('Guardando servicio', [
                'service_id' => $serviceId,
                'active' => $serviceData['active'],
                'token' => $serviceData['token'],
                'logs_enabled' => $serviceData['logs_enabled']
            ]);

            try {
                $service->update([
                    'active' => (bool) $serviceData['active'],
                    'token' => $serviceData['token'],
                    'logs_enabled' => (bool) $serviceData['logs_enabled'],
                ]);

                // Refrescar desde la base de datos para asegurar sincronización
                $service->refresh();

                // Actualizar el array local para mantener sincronización
                $this->services[$serviceIndex] = [
                    'id' => $service->id,
                    'display_name' => $service->display_name,
                    'description' => $service->description,
                    'active' => (bool) $service->active,
                    'token' => $service->token,
                    'logs_enabled' => (bool) $service->logs_enabled,
                ];

                $this->dispatch(
                    'notify-toast',
                    icon: 'success',
                    title: 'SERVICIO ACTUALIZADO',
                    mensaje: "El servicio {$service->display_name} se ha actualizado correctamente"
                );
            } catch (\Throwable $th) {
                logger()->error('Error al guardar servicio', [
                    'service_id' => $serviceId,
                    'error' => $th->getMessage()
                ]);

                $this->dispatch(
                    'notify-toast',
                    icon: 'error',
                    title: 'ERROR',
                    mensaje: 'Error al actualizar el servicio: ' . $th->getMessage()
                );
            }
        }
    }
    public function updatedServices($value, $key)
    {
        // Auto-guardar cuando se cambie el estado active o logs_enabled
        if (str_contains($key, '.active') || str_contains($key, '.logs_enabled')) {
            $serviceIndex = explode('.', $key)[0];
            $serviceId = $this->services[$serviceIndex]['id'];

            logger()->info('updatedServices llamado', [
                'key' => $key,
                'value' => $value,
                'service_index' => $serviceIndex,
                'service_id' => $serviceId,
                'current_services_data' => $this->services[$serviceIndex]
            ]);

            // Guardar inmediatamente
            $this->saveService($serviceId);

            // Verificar que se guardó
            $this->checkServiceStatus($serviceId);
        }
    }

    public function checkServiceStatus($serviceId)
    {
        $service = Service::find($serviceId);
        if ($service) {
            logger()->info('Estado actual del servicio en BD', [
                'service_id' => $serviceId,
                'display_name' => $service->display_name,
                'active' => $service->active,
                'logs_enabled' => $service->logs_enabled,
                'token' => $service->token
            ]);
        }
    }

    // Método alternativo para toggle directo (para debugging)
    public function toggleService($serviceId, $field)
    {
        $service = Service::find($serviceId);

        if (!$service) {
            $this->dispatch('notify-toast', icon: 'error', title: 'ERROR', mensaje: 'Servicio no encontrado');
            return;
        }

        // Toggle directo en la base de datos
        $newValue = !$service->$field;
        $service->update([$field => $newValue]);

        // Actualizar el array local
        $serviceIndex = collect($this->services)->search(function ($item) use ($serviceId) {
            return $item['id'] == $serviceId;
        });

        if ($serviceIndex !== false) {
            $this->services[$serviceIndex][$field] = $newValue;
        }

        $this->dispatch(
            'notify-toast',
            icon: 'success',
            title: 'ACTUALIZADO',
            mensaje: "Campo {$field} actualizado a " . ($newValue ? 'activo' : 'inactivo')
        );
    }
    // ===== MÉTODOS PARA MANEJAR ACCESOS =====

    public function openModalCreateAcceso()
    {
        $this->dispatch('openModalCreateAcceso');
    }

    public function vincularDispositivos($accesoId)
    {
        $this->dispatch('openModalVincularDispositivos', accesoId: $accesoId);
    }

    public function editarAcceso($accesoId)
    {
        $this->dispatch('openModalEditarAcceso', accesoId: $accesoId);
    }

    public function verDispositivos($accesoId)
    {
        $this->dispatch('openModalVerDispositivos', accesoId: $accesoId);
    }
}
