<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Log extends Model
{
    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'fecha_hora_posicion' => 'datetime',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'additional_data' => 'array',
    ];

    /**
     * Scope para filtrar por tipo de envío
     */
    public function scopeTipoEnvio(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo_envio', $tipo);
    }

    /**
     * Scope para envíos normales
     */
    public function scopeNormal(Builder $query): Builder
    {
        return $query->where('tipo_envio', 'normal');
    }

    /**
     * Scope para reenvíos históricos
     */
    public function scopeReenvio(Builder $query): Builder
    {
        return $query->where('tipo_envio', 'reenvio');
    }

    /**
     * Scope para filtrar por servicio
     */
    public function scopeServicio(Builder $query, string $servicio): Builder
    {
        return $query->where('service_name', $servicio);
    }

    /**
     * Scope para filtrar por status
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para buscar por placa
     */
    public function scopeBuscarPlaca(Builder $query, string $placa): Builder
    {
        return $query->where('plate_number', 'like', "%{$placa}%");
    }

    /**
     * Scope para buscar por IMEI
     */
    public function scopeBuscarImei(Builder $query, string $imei): Builder
    {
        return $query->where('imei', 'like', "%{$imei}%");
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeFechaEntre(Builder $query, $desde, $hasta): Builder
    {
        return $query->whereBetween('created_at', [$desde, $hasta]);
    }

    /**
     * Scope para logs del día actual
     */
    public function scopeHoy(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Accessor para obtener el badge color según el status
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'success' => 'success',
            'error' => 'danger',
            default => 'warning'
        };
    }

    /**
     * Accessor para obtener el badge color según el servicio
     */
    public function getServiceBadgeAttribute(): string
    {
        return match (strtoupper($this->service_name)) {
            'SUTRAN' => 'info',
            'OSINERGMIN' => 'warning',
            'SISCOP' => 'purple',
            default => 'secondary'
        };
    }
}
