<?php

namespace App\Jobs\Wialon;

use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use App\Models\Config;
use App\Models\Device;
use App\Models\Service;
use Xint0\WialonPhp\Wialon;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\Senders\SiscopSender;
use App\Services\Senders\SutranSender;
use App\Services\Senders\OsinergminSender;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ReenviarHistorialMultiServicio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $historialData;

    public function __construct(array $historialData)
    {
        $this->historialData = $historialData;
        $this->onQueue('reenviar-historial');
    }

    public function handle(): void
    {
        $config = Config::first();
        if (!$config || !$config->hasValidToken()) {
            Log::error('No se encontr� configuraci�n v�lida');
            return;
        }

        try {
            // Inicializar cliente Wialon
            $wialon = new Wialon($config->base_uri);

            // Login con token
            $loginResult = $wialon->login($config->token);

            if (!$loginResult) {
                Log::error('Error al autenticar con Wialon para reenvío historial');
                return;
            }

            // Procesar el dispositivo
            $this->procesarDispositivo($wialon, $this->historialData);

            // Logout
            //$wialon->logout();
        } catch (\Exception $e) {
            Log::error('Error en ReenviarHistorialMultiServicio: ' . $e->getMessage());
            throw $e;
        }
    }

    private function procesarDispositivo(Wialon $wialon, array $data): void
    {
        $deviceId = $data['id'];
        $timeFrom = $data['last_update'];
        $timeTo = $data['current_time'];
        $serviceName = $data['service'];

        // Obtener dispositivo
        $device = Device::where('id_wialon', $deviceId)->first();

        if (!$device) {
            Log::warning("Dispositivo con id_wialon {$deviceId} no encontrado");
            return;
        }

        // Obtener servicio
        $service = Service::where('name', $serviceName)->where('active', true)->first();

        if (!$service) {
            Log::warning("Servicio {$serviceName} no encontrado o inactivo");
            return;
        }

        try {
            Log::info("Cargando intervalo para dispositivo {$deviceId} ({$device->plate}), servicio: {$serviceName}");

            // 1. Cargar intervalo de mensajes usando loadInterval
            $loadResult = $wialon->loadInterval(
                $deviceId,
                $timeFrom,
                $timeTo,
                1, // flags: 0x0001 - data messages with location
                65281, // flagsMask: 0xFF01 - tipo de mensaje (0xFF00) + ubicación (0x0001)
                0xffffffff // loadCount: todos los mensajes
            );

            if (!isset($loadResult['count']) || $loadResult['count'] === 0) {
                Log::info("No hay mensajes para el dispositivo {$deviceId} en el rango especificado");
                $wialon->unload();
                return;
            }

            $expectedCount = $loadResult['count'];
            $messagesFromInterval = $loadResult['messages'] ?? [];
            $actualCount = is_array($messagesFromInterval) ? count($messagesFromInterval) : 0;

            Log::info("LoadInterval - Count esperado: {$expectedCount}, Mensajes recibidos: {$actualCount}");

            $allMessages = [];

            // 2. Verificar si coincide el count con la cantidad de mensajes
            if ($actualCount === $expectedCount && $actualCount > 0) {
                // Los mensajes están completos en loadInterval
                Log::info("Mensajes completos en loadInterval, procesando directamente");
                $allMessages = is_array($messagesFromInterval) ? array_values($messagesFromInterval) : [];
            } else if ($expectedCount > $actualCount) {
                // Faltan mensajes, usar getMessages para obtenerlos todos
                Log::warning("Discrepancia: count={$expectedCount} pero solo se recibieron {$actualCount} mensajes. Usando getMessages()");

                $batchSize = 1000; // Procesar en lotes de 1000 mensajes

                for ($indexFrom = 0; $indexFrom < $expectedCount; $indexFrom += $batchSize) {
                    $indexTo = min($indexFrom + $batchSize - 1, $expectedCount - 1);

                    $messagesResult = $wialon->getMessages($indexFrom, $indexTo);

                    if (isset($messagesResult) && is_array($messagesResult)) {
                        $allMessages = array_merge($allMessages, $messagesResult);
                    }

                    Log::info("Descargados mensajes del índice {$indexFrom} al {$indexTo} de {$expectedCount}");
                }
            } else {
                // Caso raro: más mensajes de los esperados
                Log::warning("Caso inesperado: recibidos {$actualCount} mensajes pero count={$expectedCount}");
                $allMessages = is_array($messagesFromInterval) ? array_values($messagesFromInterval) : [];
            }

            Log::info("Total de mensajes a procesar: " . count($allMessages));

            // Validar que tenemos mensajes para procesar
            if (empty($allMessages)) {
                Log::warning("No se obtuvieron mensajes para procesar del dispositivo {$deviceId}");
                $wialon->unload();
                return;
            }

            // 3. Formatear mensajes según el servicio
            $tramas = $this->formatearMensajes($device, $allMessages, $serviceName, $service);

            // 4. Enviar tramas al servicio correspondiente
            if (!empty($tramas)) {
                $this->enviarTramas($tramas, $service, $serviceName, $device);
                Log::info("Proceso completado: {$expectedCount} mensajes procesados y enviados a {$serviceName}");
            } else {
                Log::warning("No se generaron tramas para enviar a {$serviceName}");
            }

            // 5. Limpiar el message loader
            $wialon->unload();
            Log::info("Message loader limpiado para dispositivo {$deviceId}");
        } catch (\Exception $e) {
            Log::error("Error procesando dispositivo {$deviceId}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            // Intentar limpiar el loader incluso si hay error
            try {
                $wialon->unload();
            } catch (\Exception $unloadError) {
                Log::error("Error al limpiar message loader: " . $unloadError->getMessage());
            }
            throw $e;
        }
    }

    private function formatearMensajes(Device $device, array $messages, string $serviceName, Service $service): array
    {
        switch ($serviceName) {
            case 'siscop':
                return $this->formatSiscop($device, $messages);
            case 'osinergmin':
                return $this->formatOsinergmin($device, $messages, $service);
            case 'sutran':
                return $this->formatSutran($device, $messages);
            default:
                return [];
        }
    }

    private function enviarTramas(array $tramas, Service $service, string $serviceName, Device $device): void
    {
        Log::info("Enviando " . count($tramas) . " tramas a {$serviceName}");

        try {
            switch ($serviceName) {
                case 'siscop':
                    $sender = new SiscopSender($service);
                    $sender->send($tramas);
                    break;
                case 'osinergmin':
                    $url = "https://prod.osinergmin-agent-2021.com/api/v1/trama";
                    $sender = new OsinergminSender($service);
                    $sender->send($tramas, $url);
                    break;
                case 'sutran':
                    $url = $service->api_url ?? 'https://api.sutran.gob.pe/v1/trama';
                    $sender = new SutranSender($service);
                    $sender->send($tramas, $url);
                    break;
            }

            Log::info("Tramas enviadas a {$serviceName}");
        } catch (\Exception $e) {
            Log::error("Error enviando a {$serviceName}: " . $e->getMessage());
            throw $e;
        }
    }

    private function formatOsinergmin(Device $device, array $messages, Service $service): array
    {
        return array_map(function ($message) use ($device, $service) {
            $utcTime = $message['t'];

            // Timestamp en formato ISO 8601 UTC con milisegundos
            $gpsDate = gmdate('Y-m-d\TH:i:s.v\Z', $utcTime);

            return [
                'id' => $device->id_wialon,
                'event' => 'none',
                'gpsDate' => $gpsDate,
                'plate' => trim($device->plate),
                'speed' => intval($message['pos']['s'] ?? 0),
                'position' => [
                    'latitude' => doubleval($message['pos']['y'] ?? 0),
                    'longitude' => doubleval($message['pos']['x'] ?? 0),
                    'altitude' => doubleval($message['pos']['z'] ?? 0),
                ],
                'tokenTrama' => $service->token ?? '',
                'odometer' => intval($message['p']['mileage'] ?? 0),
                'imei' => intval($device->imei),
                'idTrama' => $device->id_wialon,
            ];
        }, $messages);
    }

    private function formatSiscop(Device $device, array $messages): array
    {
        $device->load('acceso');

        if (!$device->acceso) {
            Log::warning("Dispositivo {$device->plate} sin acceso SISCOP");
            return [];
        }

        $tramas = [];

        foreach ($messages as $message) {
            $utcTime = $message['t'];
            $date = new DateTime("@{$utcTime}");
            $date->setTimezone(new DateTimeZone('America/Lima'));

            // Obtener mileage en metros y convertir a kilómetros
            $mileageMetros = intval($message['p']['mileage'] ?? 0);
            $mileageKm = round($mileageMetros / 1000, 2); // Convertir a km con 2 decimales

            // Datos base comunes para ambos tipos (igual que SiscopFormatter)
            $baseData = [
                'id' => $message['t'], // Usar timestamp como ID
                'device_id' => $device->id_wialon, // ID del dispositivo Wialon (REQUERIDO por SiscopSender)
                'alarma' => 0,
                'altitud' => doubleval($message['pos']['z'] ?? 0),
                'angulo' => intval($message['pos']['c'] ?? 0),
                'distancia' => doubleval($mileageKm), // En kilómetros (max 5 dígitos enteros, 2 decimales)
                'fechaHora' => $date->format('d/m/Y H:i:s'),
                'horasMotor' => 0, // No disponible en mensajes históricos
                'ignition' => intval($message['p']['acc_status'] ?? 0),
                'imei' => intval($device->imei),
                'latitud' => doubleval($message['pos']['y'] ?? 0),
                'longitud' => doubleval($message['pos']['x'] ?? 0),
                'motion' => intval($message['pos']['s'] ?? 0) > 0,
                'placa' => trim($device->plate),
                'totalDistancia' => doubleval($mileageKm), // En kilómetros
                'totalHorasMotor' => 0,
                'valid' => true,
                'velocidad' => intval($message['pos']['s'] ?? 0),
            ];

            // Determinar el tipo de envío según el acceso
            if ($device->acceso->tipo === 'serenazgo') {
                $tramas[] = array_merge($baseData, [
                    'idTransmision' => $device->id_wialon . $device->plate,
                    'idMunicipalidad' => $device->acceso->idMunicipalidad ?? '',
                    'ubigeo' => $device->acceso->ubigeo ?? '',
                ]);
            } else {
                $tramas[] = array_merge($baseData, [
                    'codigoComisaria' => $device->acceso->codigoComisaria ?? '',
                    'idTransmision' => $device->acceso->idTransmision ?? '',
                    'ubigeo' => $device->acceso->ubigeo ?? '',
                ]);
            }
        }

        return $tramas;
    }

    private function formatSutran(Device $device, array $messages): array
    {
        return array_map(function ($message) use ($device) {
            $utcTime = $message['t'];
            $date = new DateTime("@{$utcTime}");
            $date->setTimezone(new DateTimeZone('America/Lima'));

            return [
                'id' => $device->id_wialon,
                'plate' => trim(str_replace('-', '', $device->plate)),
                'geo' => [
                    doubleval($message['pos']['y'] ?? 0),
                    doubleval($message['pos']['x'] ?? 0)
                ],
                'direction' => intval($message['pos']['c'] ?? 0),
                'event' => intval($message['pos']['s'] ?? 0) > 5 ? 'ER' : 'PA',
                'speed' => intval($message['pos']['s'] ?? 0),
                'time_device' => $date->format('Y-m-d H:i:s'),
                'imei' => intval($device->imei),
                'idTrama' => $device->id_wialon,
            ];
        }, $messages);
    }
}
