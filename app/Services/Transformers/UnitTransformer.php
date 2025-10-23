<?php

namespace App\Services\Transformers;

use DateTime;
use DateTimeZone;

class UnitTransformer
{
    public function transform(array $rawUnits): array
    {
        return array_map(function ($rawUnit) {
            return $this->normalizeUnit($rawUnit);
        }, $rawUnits);
    }

    private function normalizeUnit(array $unit): array
    {
        // Usar timestamp del último mensaje (lmsg.t) en lugar del timestamp de posición (pos.t)
        // porque el GPS puede enviar mensajes de comunicación más recientes que la última posición
        $timestamp = $unit['lmsg']['t'] ?? $unit['pos']['t'] ?? null;

        // Extraer datos de sensores
        $sensors = $unit['sens'] ?? [];
        $ignition = null;
        $batteryVoltage = null;
        $batteryInternal = null;

        // Buscar sensor de motor (ignición)
        foreach ($sensors as $sensor) {
            if (isset($sensor['t']) && $sensor['t'] === 'engine operation') {
                $ignition = $unit['lmsg']['p'][$sensor['p']] ?? null;
            }
            if (isset($sensor['t']) && $sensor['t'] === 'voltage') {
                $batteryVoltage = $unit['lmsg']['p'][$sensor['p']] ?? null;
            }
            if (isset($sensor['p']) && $sensor['p'] === 'pwr_int') {
                $batteryInternal = $unit['lmsg']['p']['pwr_int'] ?? null;
            }
        }

        return [
            'id' => $unit['id'] ?? null,
            'name' => $unit['nm'] ?? null,
            'class' => $unit['cls'] ?? null,
            'measurement_units' => $unit['mu'] ?? null,
            'position' => [
                'time' => $timestamp,
                'latitude' => $unit['pos']['y'] ?? null,
                'longitude' => $unit['pos']['x'] ?? null,
                'altitude' => $unit['pos']['z'] ?? null,
                'speed' => $unit['pos']['s'] ?? null,
                'course' => $unit['pos']['c'] ?? null,
                'satellites' => $unit['pos']['sc'] ?? null,
                'flags' => $unit['pos']['f'] ?? null,
                'location_correction' => $unit['pos']['lc'] ?? null,
            ],
            'last_message' => [
                'time' => $unit['lmsg']['t'] ?? null,
                'type' => $unit['lmsg']['tp'] ?? null,
                'flags' => $unit['lmsg']['f'] ?? null,
                'server_time' => $unit['lmsg']['rt'] ?? null,
                'params' => $unit['lmsg']['p'] ?? [],
            ],
            'sensors' => [
                'ignition' => $ignition,
                'battery_external' => $batteryVoltage,
                'battery_internal' => $batteryInternal,
                'gsm_signal' => $unit['lmsg']['p']['gsm'] ?? null,
                'satellites' => $unit['pos']['sc'] ?? null,
                'hdop' => $unit['lmsg']['p']['hdop'] ?? null,
                'pdop' => $unit['lmsg']['p']['pdop'] ?? null,
            ],
            'network' => [
                'mcc' => $unit['lmsg']['p']['mcc'] ?? null,
                'mnc' => $unit['lmsg']['p']['mnc'] ?? null,
                'gsm_signal' => $unit['lmsg']['p']['gsm'] ?? null,
            ],
            'counters' => [
                'calculation_flags' => $unit['cfl'] ?? null,
                'total_odometer' => $unit['cnm'] ?? 0, // Mileage counter (km or miles)
                'total_engine_hours' => $unit['cneh'] ?? 0, // Engine hours counter (h)
                'gprs_traffic' => $unit['cnkb'] ?? 0, // GPRS traffic counter (KB)
                'total_odometer_km' => $unit['cnm_km'] ?? ($unit['cnm'] ?? 0), // Odometer in km
            ],
            'raw_sensors' => $unit['sens'] ?? [],
            'access_level' => $unit['uacl'] ?? null,
        ];
    }
}
