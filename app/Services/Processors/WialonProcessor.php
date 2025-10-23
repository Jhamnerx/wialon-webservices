<?php

namespace App\Services\Processors;

use App\Jobs\Wialon\ReenviarHistorialMultiServicio;
use Carbon\Carbon;
use App\Models\Device;
use App\Models\Service;
use Illuminate\Support\Facades\Log;
use App\Jobs\Wialon\ReenviarHistorialWox;

class WialonProcessor implements UnitProcessorInterface
{
    public function processUnits(array $units): array
    {
        $result = [
            'sutran' => ['units' => [], 'service' => null],
            'osinergmin' => ['units' => [], 'service' => null],
            'siscop' => ['units' => [], 'service' => null],
        ];

        // Obtener servicios activos
        $services = Service::where('active', true)
            ->whereIn('name', ['sutran', 'osinergmin', 'siscop'])
            ->get()
            ->keyBy('name');

        if ($services->isEmpty()) {
            Log::info('No hay servicios activos para procesar.');
            return $result;
        }

        // Asignar servicios al resultado
        foreach ($services as $name => $service) {
            $result[$name]['service'] = $service;
        }

        // Obtener todos los IDs de wialon de las unidades
        $unitIds = collect($units)->pluck('id')->filter()->toArray();

        if (empty($unitIds)) {
            Log::info('No hay unidades para procesar.');
            return $result;
        }

        // Consultar dispositivos con sus servicios activos
        $devices = Device::with(['deviceServices' => function ($query) {
            $query->where('active', true);
        }])
            ->whereIn('id_wialon', $unitIds)
            ->get()
            ->keyBy('id_wialon');

        // Jobs de reenvío de historial por servicio
        $reenvioJobs = [
            'osinergmin' => [],
            'siscop' => []
        ];

        foreach ($units as $unit) {
            $unitId = $unit['id'];
            $device = $devices->get($unitId);

            if (!$device) {
                Log::warning("Dispositivo no encontrado en BD: ID {$unitId}");
                continue;
            }

            // Obtener servicios activos del dispositivo
            $activeServiceNames = $device->deviceServices->pluck('name')->toArray();

            if (empty($activeServiceNames)) {
                Log::debug("Dispositivo {$unitId} sin servicios activos");
                continue;
            }

            // Convertir timestamp de Wialon a fecha (pos.t es UTC timestamp)
            $wialonTimestamp = $unit['pos']['t'] ?? null;

            if (!$wialonTimestamp) {
                Log::warning("Dispositivo {$unitId} sin timestamp en pos.t");
                continue;
            }

            $wialonTime = Carbon::createFromTimestamp($wialonTimestamp, 'UTC');
            $deviceLastUpdate = $device->last_update ? Carbon::parse($device->last_update) : null;

            // Verificar si hay nueva posición
            $hasNewPosition = !$deviceLastUpdate || $wialonTime->greaterThan($deviceLastUpdate);

            if (!$hasNewPosition) {
                Log::debug("Dispositivo {$unitId} sin nueva posición - BD: {$deviceLastUpdate}, Wialon: {$wialonTime}");
                continue;
            }

            Log::debug("Dispositivo {$unitId} con nueva posición - BD: {$deviceLastUpdate}, Wialon: {$wialonTime}");

            // Procesar para cada servicio activo
            foreach ($activeServiceNames as $serviceName) {
                if (!isset($result[$serviceName]) || !$result[$serviceName]['service']) {
                    continue;
                }

                // Agregar unidad a la cola del servicio
                $result[$serviceName]['units'][] = $unit;
                Log::debug("Dispositivo {$unitId} agregado a cola {$serviceName}");

                // Verificar si requiere reenvío de historial (solo OSINERGMIN y SISCOP)
                if (in_array($serviceName, ['osinergmin', 'siscop']) && $deviceLastUpdate) {
                    $diffMinutes = $deviceLastUpdate->diffInMinutes($wialonTime);

                    // Si la diferencia es mayor a 3 minutos, programar reenvío
                    if ($diffMinutes > 3) {
                        $reenvioJobs[$serviceName][] = [
                            'id' => $unitId,
                            'device_id' => $device->id,
                            'last_update' => $deviceLastUpdate->timestamp,
                            'current_time' => $wialonTimestamp,
                            'diff_minutes' => $diffMinutes
                        ];

                        Log::info("Dispositivo {$unitId} requiere reenvío de historial para {$serviceName} (diferencia: {$diffMinutes} minutos)");
                    }
                }
            }
        }

        // Despachar jobs de reenvío de historial si es necesario
        foreach ($reenvioJobs as $serviceName => $jobs) {
            if (!empty($jobs) && $result[$serviceName]['service']) {
                Log::info("Despachando job de reenvío de historial para {$serviceName}: " . count($jobs) . " dispositivos");
                ReenviarHistorialMultiServicio::dispatch($jobs, $serviceName, $result[$serviceName]['service']);
            }
        }

        // Log de resumen
        foreach ($result as $serviceName => $data) {
            $count = count($data['units']);
            if ($count > 0) {
                Log::info("Servicio {$serviceName}: {$count} unidades procesadas");
            }
        }

        return $result;
    }
}
