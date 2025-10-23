<?php

namespace App\Services\Senders;

use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\Config;
use App\Models\Device;
use App\Models\Service;
use App\Services\LogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

class SiscopSender implements UnitSenderInterface
{
    public $logService;
    protected $config;
    protected $service;
    protected $sentCount = 0;
    protected $successCount = 0;
    protected $failedCount = 0;

    const URL_SERENAZGO_BETA = 'https://transmision.mininter.gob.pe/retransmisionGPS/puntosGPS';
    const URL_SERENAZGO = 'https://transmision.mininter.gob.pe/retransmisionGPS/ubicacionGPS';
    const URL_POLICIAL_BETA = 'https://transmision.mininter.gob.pe/retransmisionpolicial/puntosGPS';
    const URL_POLICIAL = 'https://transmision.mininter.gob.pe/retransmisionpolicial/ubicacion/gps-policial';

    public function __construct(Service $service)
    {
        $this->logService = app(LogService::class);
        $this->config = Config::first();
        $this->service = $service;
    }

    /**
     * Obtiene la URL de serenazgo según el entorno
     */
    protected function getSerenazgoUrl(): string
    {
        return config('app.env') === 'local' ? self::URL_SERENAZGO_BETA : self::URL_SERENAZGO;
    }

    /**
     * Obtiene la URL policial según el entorno
     */
    protected function getPolicialUrl(): string
    {
        return config('app.env') === 'local' ? self::URL_POLICIAL_BETA : self::URL_POLICIAL;
    }

    public function send(array $tramas, $url = null): void
    {
        // Separar las tramas por tipo de envío
        $serenazgoTramas = [];
        $policialTramas = [];

        foreach ($tramas as $trama) {
            // Obtener el dispositivo para verificar el tipo de acceso
            $device = Device::with('acceso')->where('id_wialon', $trama['device_id'])->first();

            if (!$device || !$device->acceso) {
                Log::warning("SISCOP: Dispositivo sin acceso asignado", ['device_id' => $trama['device_id']]);

                if ($this->service && $this->service->logs_enabled) {
                    $this->logService->logToDatabase(
                        'Device',
                        'SISCOP',
                        $trama['placa'] ?? 'N/A',
                        'error',
                        $trama,
                        ['message' => 'Dispositivo sin acceso asignado'],
                        ['device_id' => $trama['device_id']],
                        $trama['fechaHora'] ?? null,
                        $trama['imei'] ?? null
                    );
                }
                continue;
            }

            if ($device->acceso->tipo === 'serenazgo') {
                $serenazgoTramas[] = [
                    'trama' => $trama,
                    'device' => $device
                ];
            } else {
                $policialTramas[] = [
                    'trama' => $trama,
                    'device' => $device
                ];
            }
        }

        // Enviar tramas de serenazgo
        if (!empty($serenazgoTramas)) {
            Log::info("SISCOP: Enviando " . count($serenazgoTramas) . " tramas de serenazgo");
            $this->sendBatchRequests($serenazgoTramas, $this->getSerenazgoUrl(), 'serenazgo');
        }

        // Enviar tramas policiales
        if (!empty($policialTramas)) {
            Log::info("SISCOP: Enviando " . count($policialTramas) . " tramas policiales");
            $this->sendBatchRequests($policialTramas, $this->getPolicialUrl(), 'policial');
        }

        // Actualizar contadores globales
        $this->updateCounterService($this->successCount, $this->failedCount, $this->sentCount);

        Log::info("SISCOP: Resumen total del envío", [
            'total' => $this->sentCount,
            'exitosas' => $this->successCount,
            'fallidas' => $this->failedCount
        ]);
    }

    protected function sendBatchRequests(array $tramasWithDevice, string $url, string $tipo): void
    {
        $client = new Client([
            'verify' => false,
            'timeout' => 15,
            'connect_timeout' => 5,
        ]);

        // Preparar solicitudes para el pool
        $requests = [];
        foreach ($tramasWithDevice as $item) {
            $trama = $item['trama'];
            $device = $item['device'];

            $requests[] = [
                'request' => new Request('POST', $url, [
                    'Content-Type' => 'application/json',
                ], json_encode($trama)),
                'trama' => $trama,
                'device' => $device,
                'tipo' => $tipo
            ];
        }

        $requestCount = count($requests);
        Log::info("SISCOP: Enviando lote de {$requestCount} solicitudes {$tipo} al endpoint: {$url}");

        // Crear pool de solicitudes
        $pool = new Pool($client, array_column($requests, 'request'), [
            'concurrency' => 10,
            'fulfilled' => function ($response, $index) use ($requests) {
                $this->sentCount++;
                $this->handleSuccess($response, $requests[$index]);
            },
            'rejected' => function ($reason, $index) use ($requests) {
                $this->sentCount++;
                $this->failedCount++;
                $this->handleError($reason, $requests[$index]);
            }
        ]);

        // Ejecutar las solicitudes
        $promise = $pool->promise();
        $promise->wait();

        Log::info("SISCOP: Lote {$tipo} procesado - Total: {$requestCount}, Exitosas: " .
            ($requestCount - $this->failedCount) . ", Fallidas: {$this->failedCount}");
    }

    protected function handleSuccess($response, array $requestData): void
    {
        $this->successCount++;
        $trama = $requestData['trama'];
        $device = $requestData['device'];
        $tipo = $requestData['tipo'];

        $statusCode = $response->getStatusCode();

        // Obtener el cuerpo de la respuesta
        $responseContent = $response->getBody()->getContents();

        // Si la respuesta está vacía (común con 201 Created), usar respuesta genérica
        if (empty($responseContent)) {
            $responseBody = [
                'status' => 'success',
                'message' => 'Trama recibida correctamente',
                'http_status' => $statusCode
            ];
        } else {
            $responseBody = json_decode($responseContent, true);

            // Si json_decode falla, usar respuesta genérica con el contenido crudo
            if (json_last_error() !== JSON_ERROR_NONE) {
                $responseBody = [
                    'status' => 'success',
                    'message' => 'Trama recibida correctamente',
                    'http_status' => $statusCode,
                    'raw_response' => $responseContent
                ];
            }
        }
        Log::info("SISCOP: Trama enviada exitosamente", [
            'plate' => $trama['placa'],
            'tipo' => $tipo,
            'http_status' => $statusCode
        ]);

        // Convertir fecha de d/m/Y H:i:s a Y-m-d H:i:s para el log
        $fechaLima = Carbon::createFromFormat('d/m/Y H:i:s', $trama['fechaHora'])->format('Y-m-d H:i:s');

        if ($this->service && $this->service->logs_enabled) {
            $this->logService->logToDatabase(
                'Device',
                'SISCOP',
                $trama['placa'],
                'success',
                $trama,
                $responseBody,
                [
                    'tipo' => $tipo,
                    'endpoint' => $tipo === 'serenazgo' ? $this->getSerenazgoUrl() : $this->getPolicialUrl(),
                    'http_status' => $statusCode,
                    'acceso_tipo' => $device->acceso->tipo ?? null,
                    'acceso_id' => $device->acceso->id ?? null
                ],
                $fechaLima,
                $trama['imei']
            );
        }

        // Actualizar dispositivo con última posición
        $device->update([
            'last_status' => 'success',
            'last_position' => [
                'lat' => $trama['latitud'],
                'lng' => $trama['longitud'],
                'altitude' => $trama['altitud']
            ],
            'last_update' => $fechaLima,
            'latest_position_id' => $trama['id'],
        ]);
    }

    protected function handleError($reason, array $requestData): void
    {
        $trama = $requestData['trama'];
        $device = $requestData['device'];
        $tipo = $requestData['tipo'];

        $errorMessage = 'Error de conexión';
        $errorDetails = [];
        $statusCode = null;

        if ($reason instanceof RequestException && $reason->hasResponse()) {
            $response = $reason->getResponse();
            $statusCode = $response->getStatusCode();

            try {
                $body = $response->getBody()->getContents();
                $errorDetails = json_decode($body, true);
                $errorMessage = $errorDetails['message'] ?? 'Error del servidor';
            } catch (\Exception $e) {
                $errorDetails = ['raw_response' => $body ?? 'No response body'];
                $errorMessage = 'Error al parsear respuesta del servidor';
            }
        } else {
            $errorMessage = $reason->getMessage();
            $errorDetails = ['exception' => get_class($reason)];
        }

        Log::error("SISCOP: Error al enviar trama {$tipo}", [
            'device_id' => $trama['device_id'],
            'placa' => $trama['placa'],
            'error' => $errorMessage,
            'http_status' => $statusCode,
            'endpoint' => $tipo === 'serenazgo' ? $this->getSerenazgoUrl() : $this->getPolicialUrl()
        ]);

        // Convertir fecha de d/m/Y H:i:s a Y-m-d H:i:s para el log
        $fechaLima = Carbon::createFromFormat('d/m/Y H:i:s', $trama['fechaHora'])->format('Y-m-d H:i:s');

        if ($this->service && $this->service->logs_enabled) {
            $this->logService->logToDatabase(
                'Device',
                'SISCOP',
                $trama['placa'],
                'error',
                $trama,
                $errorDetails,
                [
                    'error_message' => $errorMessage,
                    'tipo' => $tipo,
                    'endpoint' => $tipo === 'serenazgo' ? $this->getSerenazgoUrl() : $this->getPolicialUrl(),
                    'http_status' => $statusCode,
                    'acceso_tipo' => $device->acceso->tipo ?? null,
                    'acceso_id' => $device->acceso->id ?? null
                ],
                $fechaLima,
                $trama['imei']
            );
        }
    }

    protected function updateCounterService(int $successCount, int $failedCount, int $totalSent): void
    {
        DB::transaction(function () use ($successCount, $failedCount, $totalSent) {
            $counterService = $this->config->counterServices()->firstOrCreate(
                [
                    'serviceable_type' => 'App\Models\Config',
                    'serviceable_id' => $this->config->id,
                ],
                ['data' => []]
            );

            $data = $counterService->data ?? [];

            $data['sent'] = ($data['sent'] ?? 0) + $totalSent;
            $data['success'] = ($data['success'] ?? 0) + $successCount;
            $data['failed'] = ($data['failed'] ?? 0) + $failedCount;
            $data['last_error'] = $failedCount > 0 ? 'Errores en algunas tramas SISCOP' : null;
            $data['last_attempt'] = now()->toDateTimeString();

            $counterService->update(['data' => $data]);
        });
    }
}
