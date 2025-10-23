<?php

namespace App\Services\Fetchers;

use App\Models\Device;
use Xint0\WialonPhp\Wialon;
use Illuminate\Support\Facades\Log;

class WialonFetcher
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function fetchUnits(): array
    {
        $wialon = new Wialon($this->config->base_uri);
        $wialon->login($this->config->token);

        try {
            // Obtener IDs de dispositivos con servicios activos
            $devicesWithActiveServices = Device::whereHas('deviceServices', function ($query) {
                $query->where('active', true);
            })
                ->pluck('id_wialon')
                ->filter()
                ->toArray();

            if (empty($devicesWithActiveServices)) {
                Log::info('No hay dispositivos con servicios activos.');
                return [];
            }

            // Crear el propValueMask con los IDs separados por |
            $propValueMask = implode('|', $devicesWithActiveServices);

            // 13313 = sensores (4096) + last message y location (1024) + básico (1) + counters (8192)
            $response = $wialon->searchItems('avl_unit', 'sys_id', $propValueMask, 13313);
            //$response = $wialon->unitByName('*', 13313);

            $units = $response['items'] ?? [];
            Log::info('Unidades obtenidas de Wialon: ', $units);

            return $units;
        } catch (\Exception $e) {
            Log::error('Error al obtener unidades desde Wialon: ' . $e->getMessage());
            return [];
        }
    }
}
