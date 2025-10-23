<?php

namespace App\Services;

use App\Models\Log;
use Carbon\Carbon;

class LogService
{
    /**
     * Guardar un log en la base de datos.
     *
     * @param string $proveedor El proveedor del servicio (Device, etc)
     * @param string $service El nombre del servicio (SUTRAN, OSINERGMIN, SISCOP)
     * @param string $plate El número de la placa
     * @param string $status El status del log (success, error)
     * @param array $trama Los datos de la trama enviada
     * @param array $response La respuesta del servicio
     * @param array $additionalData Datos adicionales opcionales
     * @param string|null $datePosicion La fecha/hora de la posición
     * @param string|null $imei El IMEI del dispositivo
     * @param string $tipoEnvio El tipo de envío (normal, reenvio)
     * @return void
     */
    public function logToDatabase(
        string $proveedor,
        string $service,
        string $plate,
        string $status = '',
        array $trama = [],
        array $response = [],
        array $additionalData = [],
        ?string $datePosicion = null,
        ?string $imei = null,
        string $tipoEnvio = 'normal'
    ): void {
        Log::create([
            'service_name' => $service,
            'method' => 'POST',
            'date' => Carbon::now(),
            'plate_number' => $plate,
            'request' => json_encode($trama, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'response' => json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status' => $status,
            'tipo_envio' => $tipoEnvio,
            'additional_data' => $additionalData,
            'fecha_hora_posicion' => $datePosicion,
            'imei' => $imei,
        ]);
    }

    /**
     * Guardar log de reenvío historial
     *
     * @param string $service Nombre del servicio
     * @param string $plate Placa del vehículo
     * @param string $endpoint Endpoint utilizado
     * @param int $tramasEnviadas Cantidad de tramas enviadas
     * @param Carbon $fechaInicio Fecha de inicio del reenvío
     * @param Carbon $fechaFin Fecha de fin del reenvío
     * @param string $status Estado del reenvío
     * @param array $additionalData Datos adicionales
     * @param string|null $mensajeError Mensaje de error si existe
     * @return void
     */
    public function logReenvioHistorial(
        string $service,
        string $plate,
        string $endpoint,
        int $tramasEnviadas,
        Carbon $fechaInicio,
        Carbon $fechaFin,
        string $status,
        array $additionalData = [],
        ?string $mensajeError = null
    ): void {
        Log::create([
            'service_name' => $service,
            'method' => 'POST',
            'date' => Carbon::now(),
            'plate_number' => $plate,
            'request' => json_encode(['endpoint' => $endpoint, 'tramas' => $tramasEnviadas], JSON_UNESCAPED_UNICODE),
            'response' => json_encode(['status' => $status], JSON_UNESCAPED_UNICODE),
            'status' => $status,
            'tipo_envio' => 'reenvio',
            'tramas_enviadas' => $tramasEnviadas,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'additional_data' => $additionalData,
            'mensaje_error' => $mensajeError,
            'fecha_hora_posicion' => $fechaInicio,
            'imei' => null,
        ]);
    }
}
