<?php

namespace App\Services\Formatters;

use DateTime;
use DateTimeZone;
use App\Models\Service;
use App\Models\Device;
use App\Services\Transformers\UnitTransformer;

class SutranFormatter implements UnitFormatterInterface
{
    protected $transformer;

    public function __construct(UnitTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function format(array $units, $serviceOrSource): array
    {
        $normalizedUnits = $this->transformer->transform($units);

        return array_map(function ($unit) {
            // Obtener el dispositivo con verificación de servicio activo
            $device = Device::with(['deviceServices' => function ($query) {
                $query->where('name', 'sutran')->where('active', true);
            }])->where('id_wialon', $unit['id'])->first();

            if (!$device) {
                throw new \Exception("Dispositivo no encontrado: ID {$unit['id']}");
            }

            // Verificar que el dispositivo tiene el servicio SUTRAN activo
            if (!$device->hasActiveService('sutran')) {
                throw new \Exception("Dispositivo {$unit['id']} no tiene servicio SUTRAN activo");
            }

            // Convertir timestamp UTC a zona horaria de Lima
            $timestamp = $unit['position']['time'];
            $date = new DateTime('@' . $timestamp);
            $date->setTimezone(new DateTimeZone('America/Lima'));
            $formattedDate = $date->format('Y-m-d H:i:s');

            return [
                'id' => $unit['id'],
                'plate' => trim(str_replace('-', '', $device->plate)),
                'geo' => [$unit['position']['latitude'], $unit['position']['longitude']],
                'direction' => intval($unit['position']['course'] ?? 0),
                'event' => $unit['position']['speed'] > 5 ? 'ER' : 'PA',
                'speed' => intval($unit['position']['speed']),
                'time_device' => $formattedDate,
                'imei' => intval($device->imei),
                'idTrama' => $unit['id'],
            ];
        }, $normalizedUnits);
    }
}
