<?php

namespace App\Services\Formatters;

use DateTime;
use DateTimeZone;
use App\Models\Device;
use App\Models\Service;
use Illuminate\Support\Facades\Log;
use App\Services\Transformers\UnitTransformer;

class SiscopFormatter implements UnitFormatterInterface
{
    protected $transformer;

    public function __construct(UnitTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function format(array $units, $serviceOrSource): array
    {
        $normalizedUnits = $this->transformer->transform($units);
        $validTramas = [];

        foreach ($normalizedUnits as $unit) {

            try {
                // Obtener el dispositivo con verificación de servicio activo y relación con acceso
                $device = Device::with([
                    'deviceServices' => function ($query) {
                        $query->where('name', 'siscop')->where('active', true);
                    },
                    'acceso'
                ])->where('id_wialon', $unit['id'])->first();

                if (!$device) {
                    Log::warning("SISCOP: Dispositivo no encontrado: ID {$unit['id']}");
                    continue;
                }

                // Verificar que el dispositivo tiene el servicio SISCOP activo
                if (!$device->hasActiveService('siscop')) {
                    Log::warning("SISCOP: Dispositivo {$unit['id']} no tiene servicio SISCOP activo");
                    continue;
                }

                // Verificar que el dispositivo tiene un acceso asignado
                if (!$device->acceso) {
                    Log::warning("SISCOP: Dispositivo {$unit['id']} no tiene acceso asignado - omitiendo envío");
                    continue;
                }

                // Convertir timestamp UTC a fecha en timezone de Lima
                $timestamp = $unit['position']['time'];
                $datetime = new DateTime('@' . $timestamp);
                $datetime->setTimezone(new DateTimeZone('America/Lima'));

                // Obtener odómetro en km y asegurar formato con 2 decimales
                $odometerKm = round(doubleval($unit['counters']['total_odometer_km'] ?? 0), 2);

                // Datos base comunes para ambos tipos
                $baseData = [
                    'id' => $unit['position']['time'], // Usar timestamp como ID
                    'device_id' => $unit['id'], // ID del dispositivo Wialon
                    'alarma' => 0, // Valor por defecto
                    'altitud' => doubleval($unit['position']['altitude']),
                    'angulo' => intval($unit['position']['course'] ?? 0),
                    'distancia' => $odometerKm, // En kilómetros con 2 decimales (max 5 enteros, 2 decimales)
                    'fechaHora' => $datetime->format('d/m/Y H:i:s'),
                    'horasMotor' => doubleval($unit['counters']['total_engine_hours'] ?? 0),
                    'ignition' => intval($unit['sensors']['ignition'] ?? 0),
                    'imei' => intval($device->imei),
                    'latitud' => doubleval($unit['position']['latitude']),
                    'longitud' => doubleval($unit['position']['longitude']),
                    'motion' => intval($unit['position']['speed']) > 0 ? true : false,
                    'placa' => trim($device->plate),
                    'totalDistancia' => $odometerKm, // En kilómetros con 2 decimales
                    'totalHorasMotor' => doubleval($unit['counters']['total_engine_hours'] ?? 0),
                    'valid' => true,
                    'velocidad' => intval($unit['position']['speed']),
                ];

                // Determinar el tipo de envío según el acceso
                if ($device->acceso->tipo === 'serenazgo') {
                    // Envío para municipalidad/serenazgo
                    $validTramas[] = array_merge($baseData, [
                        'idTransmision' => $unit['id'] . $device->plate,
                        'idMunicipalidad' => $device->acceso->idMunicipalidad ?? '',
                        'ubigeo' => $device->acceso->ubigeo ?? '',
                    ]);
                } else {
                    // Envío para comisaría/policial
                    $validTramas[] = array_merge($baseData, [
                        'codigoComisaria' => $device->acceso->codigoComisaria ?? '',
                        'idTransmision' => $device->acceso->idTransmision ?? '', // Para casos policiales que necesiten idTransmision
                        'ubigeo' => $device->acceso->ubigeo ?? '',
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("SISCOP: Error al procesar dispositivo {$unit['id']}: " . $e->getMessage());
                continue;
            }
        }

        Log::info("SISCOP: Procesados " . count($validTramas) . " de " . count($normalizedUnits) . " dispositivos");

        return $validTramas;
    }
}
