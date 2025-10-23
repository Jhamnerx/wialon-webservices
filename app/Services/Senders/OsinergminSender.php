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

class OsinergminSender implements UnitSenderInterface
{
    public $logService;
    protected $config;
    protected $service;

    public function __construct(Service $service)
    {
        $this->logService = app(LogService::class);
        $this->config = Config::first();
        $this->service = $service;
    }

    public function send(array $tramas, $url): void
    {
        $client = new Client([
            'verify' => false,
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);

        $successCount = 0;
        $failedCount = 0;
        $totalTramas = count($tramas);

        Log::info("OSINERGMIN: Iniciando envío de {$totalTramas} tramas al endpoint: {$url}");

        foreach ($tramas as $index => $trama) {
            try {
                $response = $client->request('POST', $url, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode($trama),
                ]);

                $responseBody = json_decode($response->getBody()->getContents(), true);

                // Verificar el status de la respuesta
                if (isset($responseBody['status']) && $responseBody['status'] === 'CREATED') {
                    $successCount++;
                    $this->handleSuccess($responseBody, $trama, $index, $totalTramas);
                } else {
                    $failedCount++;
                    $this->handleError($responseBody, $trama, $index, $totalTramas);
                }
            } catch (RequestException $e) {
                $failedCount++;
                $this->handleRequestException($e, $trama, $index, $totalTramas);
            } catch (\Exception $e) {
                $failedCount++;
                Log::error("OSINERGMIN: Error inesperado procesando trama", [
                    'plate' => $trama['plate'] ?? 'N/A',
                    'index' => $index,
                    'error' => $e->getMessage()
                ]);

                if ($this->service->logs_enabled) {
                    // Convertir timestamp UTC a Lima para el log
                    $fechaLima = isset($trama['gpsDate'])
                        ? Carbon::parse($trama['gpsDate'])->setTimezone('America/Lima')->format('Y-m-d H:i:s')
                        : null;

                    $this->logService->logToDatabase(
                        'Device',
                        'OSINERGMIN',
                        $trama['plate'] ?? 'N/A',
                        'error',
                        $trama,
                        ['message' => 'Error inesperado: ' . $e->getMessage()],
                        ['index' => $index, 'total' => $totalTramas],
                        $fechaLima,
                        $trama['imei'] ?? null
                    );
                }
            }
        }

        // Actualizar contadores globales
        $this->updateCounterService($successCount, $failedCount, $totalTramas);

        Log::info("OSINERGMIN: Resumen del envío", [
            'total' => $totalTramas,
            'exitosas' => $successCount,
            'fallidas' => $failedCount
        ]);
    }

    protected function handleSuccess(array $response, array $trama, int $index, int $total): void
    {
        Log::info("OSINERGMIN: Trama procesada exitosamente", [
            'plate' => $trama['plate'],
            'index' => $index + 1,
            'total' => $total
        ]);

        // Convertir timestamp UTC a Lima para el log
        $fechaLima = isset($trama['gpsDate'])
            ? Carbon::parse($trama['gpsDate'])->setTimezone('America/Lima')->format('Y-m-d H:i:s')
            : null;

        if ($this->service->logs_enabled) {
            $this->logService->logToDatabase(
                'Device',
                'OSINERGMIN',
                $trama['plate'],
                'success',
                $trama,
                $response,
                [
                    'index' => $index,
                    'total' => $total,
                    'timestamp_utc' => $trama['gpsDate'] ?? null
                ],
                $fechaLima,
                $trama['imei'] ?? null
            );
        }

        // Actualizar dispositivo
        $device = Device::where('id_wialon', $trama['id'])->first();
        if ($device) {
            $device->update([
                'last_status' => $trama['event'],
                'last_position' => [
                    'lat' => $trama['position']['latitude'],
                    'lng' => $trama['position']['longitude'],
                    'altitude' => $trama['position']['altitude']
                ],
                'last_update' => $fechaLima,
                'latest_position_id' => $trama['idTrama'],
            ]);
        }
    }

    protected function handleError(array $response, array $trama, int $index, int $total): void
    {
        $errorMessage = ($response['message'] ?? 'Error desconocido') . ' - ' . ($response['suggestion'] ?? 'Sin sugerencia');

        Log::error("OSINERGMIN: Error en respuesta de trama", [
            'plate' => $trama['plate'],
            'index' => $index + 1,
            'total' => $total,
            'status' => $response['status'] ?? 'unknown',
            'message' => $errorMessage
        ]);

        // Convertir timestamp UTC a Lima para el log
        $fechaLima = isset($trama['gpsDate'])
            ? Carbon::parse($trama['gpsDate'])->setTimezone('America/Lima')->format('Y-m-d H:i:s')
            : null;

        if ($this->service->logs_enabled) {
            $this->logService->logToDatabase(
                'Device',
                'OSINERGMIN',
                $trama['plate'],
                'error',
                $trama,
                $response,
                [
                    'index' => $index,
                    'total' => $total,
                    'error_type' => 'API_ERROR',
                    'timestamp_utc' => $trama['gpsDate'] ?? null
                ],
                $fechaLima,
                $trama['imei'] ?? null
            );
        }
    }

    protected function handleRequestException(RequestException $e, array $trama, int $index, int $total): void
    {
        $errorResponse = null;
        $statusCode = null;

        if ($e->hasResponse()) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            try {
                $body = $response->getBody()->getContents();
                $errorResponse = json_decode($body, true);
            } catch (\Exception $jsonError) {
                $errorResponse = ['raw_response' => $body ?? 'No response body'];
            }

            Log::error("OSINERGMIN: Error HTTP en trama", [
                'plate' => $trama['plate'],
                'index' => $index + 1,
                'total' => $total,
                'http_status' => $statusCode,
                'response' => $errorResponse
            ]);
        } else {
            $errorResponse = ['message' => $e->getMessage()];
            Log::error("OSINERGMIN: Error de conexión en trama", [
                'plate' => $trama['plate'],
                'index' => $index + 1,
                'total' => $total,
                'error' => $e->getMessage()
            ]);
        }

        // Convertir timestamp UTC a Lima para el log
        $fechaLima = isset($trama['gpsDate'])
            ? Carbon::parse($trama['gpsDate'])->setTimezone('America/Lima')->format('Y-m-d H:i:s')
            : null;

        if ($this->service->logs_enabled) {
            $this->logService->logToDatabase(
                'Device',
                'OSINERGMIN',
                $trama['plate'],
                'error',
                $trama,
                $errorResponse,
                [
                    'index' => $index,
                    'total' => $total,
                    'http_status' => $statusCode,
                    'error_type' => 'RequestException',
                    'timestamp_utc' => $trama['gpsDate'] ?? null
                ],
                $fechaLima,
                $trama['imei'] ?? null
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
            $data['last_error'] = $failedCount > 0 ? 'Errores en algunas tramas' : null;
            $data['last_attempt'] = now()->toDateTimeString();

            $counterService->update(['data' => $data]);
        });
    }
}
