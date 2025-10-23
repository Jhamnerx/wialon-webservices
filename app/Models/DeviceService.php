<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceService extends Model
{
    protected $table = 'device_services';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'active' => 'boolean',
        'configuration' => 'json',
    ];

    // Relaciones
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'name', 'name');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByService($query, string $serviceName)
    {
        return $query->where('name', $serviceName);
    }

    public function scopeActiveByService($query, string $serviceName)
    {
        return $query->byService($serviceName)->active();
    }
}
