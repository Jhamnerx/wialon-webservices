<?php

namespace App\Services\Senders;

use Carbon\Carbon;
use App\Models\Config;
use App\Models\Device;
use GuzzleHttp\Client;
use App\Models\Service;
use App\Services\LogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;

class SutranSender implements UnitSenderInterface
{
    public $logService;
    public $config;
    public $service;

    public function __construct(Service $service)
    {
        $this->logService = app(LogService::class);
        $this->config = Config::first();
        $this->service = $service;
    }

    public function send(array $tramas, $url): void
    {
        $token = $this->service->token;

        Log::info("SUTRAN: Enviando " . count($tramas) . " tramas al endpoint: {$url}");

        try {
            $client = new Client([
                'verify' => false,
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

            $response = $client->request('POST', $url, [
                'headers' => [
                    'access-token' => $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $tramas,
            ]);

            $responseSutran = json_decode($response->getBody()->getContents(), true);

            Log::info("SUTRAN: Respuesta recibida", [
                'status' => $responseSutran['status'] ?? 'unknown',
                'code' => $responseSutran['code'] ?? null
            ]);

            $this->actionAfterSend($tramas, $responseSutran);
        } catch (RequestException $e) {
            $this->handleRequestException($e, $tramas, $token);
        } catch (\Exception $e) {
            Log::error("SUTRAN: Error inesperado", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Log para cada trama
            foreach ($tramas as $trama) {
                if ($this->service->logs_enabled) {
                    $this->logService->logToDatabase(
                        'Device',
                        'SUTRAN',
                        $trama['plate'] ?? 'N/A',
                        'error',
                        $trama,
                        ['message' => 'Error inesperado: ' . $e->getMessage()],
                        ['url' => $url, 'token' => substr($token, 0, 8) . '...'],
                        $trama['time_device'] ?? null,
                        $trama['imei'] ?? null
                    );
                }
            }
        }
    }

    protected function handleRequestException(RequestException $e, array $tramas, string $token): void
    {
        $errorResponse = null;
        $statusCode = null;

        if ($e->hasResponse()) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            try {
                $errorResponse = json_decode($body, true);
            } catch (\Exception $jsonError) {
                $errorResponse = ['raw_response' => $body];
            }

            Log::error("SUTRAN: Error en respuesta HTTP", [
                'status_code' => $statusCode,
                'response' => $errorResponse
            ]);
        } else {
            $errorResponse = ['message' => $e->getMessage()];
            Log::error("SUTRAN: Error de conexión", ['message' => $e->getMessage()]);
        }

        // Guardar log para cada trama del lote que falló
        foreach ($tramas as $trama) {
            if ($this->service->logs_enabled) {
                $this->logService->logToDatabase(
                    'Device',
                    'SUTRAN',
                    $trama['plate'] ?? 'N/A',
                    'error',
                    $trama,
                    $errorResponse,
                    [
                        'http_status' => $statusCode,
                        'token' => substr($token, 0, 8) . '...',
                        'error_type' => 'RequestException'
                    ],
                    $trama['time_device'] ?? null,
                    $trama['imei'] ?? null
                );
            }
        }
    }

    public function actionAfterSend($tramas, $response)
    {
        $totalSent = count($tramas);
        $successCount = 0;
        $errorCount = 0;

        // Verificar si la respuesta es exitosa (código 200 sin errores)
        if (isset($response['status']) && $response['status'] == 200 && empty($response['error_plates'])) {
            $successCount = $totalSent;

            Log::info("SUTRAN: Todas las tramas procesadas exitosamente", [
                'total' => $totalSent
            ]);

            foreach ($tramas as $trama) {
                if ($this->service->logs_enabled) {
                    $this->logService->logToDatabase(
                        'Device',
                        'SUTRAN',
                        $trama['plate'],
                        'success',
                        $trama,
                        $response,
                        ['index' => array_search($trama, $tramas), 'batch_size' => $totalSent],
                        $trama['time_device'],
                        $trama['imei']
                    );
                }

                // Actualizar dispositivo
                Device::where('id_wialon', $trama['id'])->first()?->update([
                    'last_status' => $trama['event'],
                    'last_position' => [
                        'lat' => $trama['geo'][0],
                        'lng' => $trama['geo'][1]
                    ],
                    'last_update' => $trama['time_device'],
                    'latest_position_id' => $trama['idTrama'],
                ]);
            }
        } else {
            // Respuesta con errores parciales o totales
            $errored_rows = [];

            if (isset($response['error_plates']) && is_array($response['error_plates'])) {
                foreach ($response['error_plates'] as $error) {
                    // Extraer el índice de la fila con error (formato "F:0", "F:1", etc.)
                    if (isset($error['message']) && preg_match('/F:(\d+)/', $error['message'], $matches)) {
                        $errored_rows[intval($matches[1])] = $error['message'];
                    }
                }
            }

            Log::warning("SUTRAN: Respuesta con errores", [
                'total_errors' => count($errored_rows),
                'total_tramas' => $totalSent
            ]);

            foreach ($tramas as $index => $trama) {
                if (array_key_exists($index, $errored_rows)) {
                    $errorCount++;

                    if ($this->service->logs_enabled) {
                        $this->logService->logToDatabase(
                            'Device',
                            'SUTRAN',
                            $trama['plate'],
                            'error',
                            $trama,
                            [
                                'message' => $errored_rows[$index],
                                'error_code' => $response['code'] ?? null,
                                'full_response' => $response
                            ],
                            ['index' => $index, 'batch_size' => $totalSent],
                            $trama['time_device'],
                            $trama['imei']
                        );
                    }

                    Log::error("SUTRAN: Error en trama", [
                        'plate' => $trama['plate'],
                        'index' => $index,
                        'error' => $errored_rows[$index]
                    ]);
                } else {
                    $successCount++;

                    if ($this->service->logs_enabled) {
                        $this->logService->logToDatabase(
                            'Device',
                            'SUTRAN',
                            $trama['plate'],
                            'success',
                            $trama,
                            ['message' => 'Registrado correctamente', 'full_response' => $response],
                            ['index' => $index, 'batch_size' => $totalSent],
                            $trama['time_device'],
                            $trama['imei']
                        );
                    }

                    // Actualizar dispositivo
                    Device::where('id_wialon', $trama['id'])->first()?->update([
                        'last_status' => $trama['event'],
                        'last_position' => [
                            'lat' => $trama['geo'][0],
                            'lng' => $trama['geo'][1]
                        ],
                        'last_update' => $trama['time_device'],
                        'latest_position_id' => $trama['idTrama'],
                    ]);
                }
            }
        }

        // Actualizar contadores
        $this->updateCounterService($successCount, $errorCount, $totalSent);

        Log::info("SUTRAN: Resumen del envío", [
            'total' => $totalSent,
            'exitosas' => $successCount,
            'fallidas' => $errorCount
        ]);
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
            $data['last_error'] = $failedCount > 0 ? 'Errores en algunas tramas SUTRAN' : null;
            $data['last_attempt'] = now()->toDateTimeString();

            $counterService->update(['data' => $data]);
        });
    }
}
