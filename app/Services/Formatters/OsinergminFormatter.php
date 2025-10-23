<?php

namespace App\Services\Formatters;

use App\Models\Service;
use App\Models\Device;
use App\Services\Transformers\UnitTransformer;

class OsinergminFormatter implements UnitFormatterInterface
{
    protected $transformer;

    public function __construct(UnitTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function format(array $units, $serviceOrSource): array
    {
        $normalizedUnits = $this->transformer->transform($units);

        return array_map(function ($unit) use ($serviceOrSource) {
            // Obtener el dispositivo con verificación de servicio activo
            $device = Device::with(['deviceServices' => function ($query) {
                $query->where('name', 'osinergmin')->where('active', true);
            }])->where('id_wialon', $unit['id'])->first();

            if (!$device) {
                throw new \Exception("Dispositivo no encontrado: ID {$unit['id']}");
            }

            // Verificar que el dispositivo tiene el servicio OSINERGMIN activo
            if (!$device->hasActiveService('osinergmin')) {
                throw new \Exception("Dispositivo {$unit['id']} no tiene servicio OSINERGMIN activo");
            }

            // Timestamp en formato ISO 8601 UTC con milisegundos
            $timestamp = $unit['position']['time'];
            $gpsDate = gmdate('Y-m-d\TH:i:s.v\Z', $timestamp);

            return [
                'id' => $unit['id'],
                'event' => 'none',
                'gpsDate' => $gpsDate,
                'plate' => trim($device->plate),
                'speed' => intval($unit['position']['speed']),
                'position' => [
                    'latitude' => doubleval($unit['position']['latitude']),
                    'longitude' => doubleval($unit['position']['longitude']),
                    'altitude' => doubleval($unit['position']['altitude']),
                ],
                'tokenTrama' => $serviceOrSource->token,
                'odometer' => intval($unit['counters']['total_odometer_km'] ?? 0),
                'imei' => intval($device->imei),
                'idTrama' => $unit['id'],
            ];
        }, $normalizedUnits);
    }
}
