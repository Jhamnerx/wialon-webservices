<?php

namespace App\Exports;

use App\Models\Log;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LogsExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Log::orderBy('created_at', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Placa',
            'Servicio',
            'IMEI',
            'Método',
            'Estado',
            'Tipo Envío',
            'Tramas Enviadas',
            'Fecha Posición',
            'Request',
            'Response',
            'Datos Adicionales',
            'Mensaje Error',
            'Creado',
        ];
    }

    /**
     * @param Log $log
     * @return array
     */
    public function map($log): array
    {
        return [
            $log->id,
            $log->plate_number ?? 'N/A',
            $log->service_name ?? 'N/A',
            $log->imei ?? 'N/A',
            $log->method ?? 'N/A',
            $log->status,
            $log->tipo_envio ?? 'normal',
            $log->tramas_enviadas ?? 0,
            $log->fecha_hora_posicion ? date('Y-m-d H:i:s', strtotime($log->fecha_hora_posicion)) : 'N/A',
            $log->request ? (is_string($log->request) ? $log->request : json_encode($log->request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) : 'N/A',
            $log->response ? (is_string($log->response) ? $log->response : json_encode($log->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) : 'N/A',
            $log->additional_data ? json_encode($log->additional_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'N/A',
            $log->mensaje_error ?? '',
            $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : 'N/A',
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 12,  // Placa
            'C' => 15,  // Servicio
            'D' => 18,  // IMEI
            'E' => 12,  // Método
            'F' => 10,  // Estado
            'G' => 12,  // Tipo Envío
            'H' => 15,  // Tramas Enviadas
            'I' => 20,  // Fecha Posición
            'J' => 20,  // Fecha Inicio
            'K' => 20,  // Fecha Fin
            'L' => 50,  // Request
            'M' => 50,  // Response
            'N' => 30,  // Datos Adicionales
            'O' => 40,  // Mensaje Error
            'P' => 20,  // Creado
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
