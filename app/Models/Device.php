<?php

namespace App\Models;

use App\Models\Acceso;
use App\Models\Imagen;
use App\Models\DeviceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    protected $table = 'devices';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'last_position' => 'json'
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function acceso(): BelongsTo
    {
        return $this->belongsTo(Acceso::class);
    }
    // Relaciones
    public function deviceServices(): HasMany
    {
        return $this->hasMany(DeviceService::class, 'device_id');
    }

    public function imagen(): MorphOne
    {
        return $this->morphOne(Imagen::class, 'imageable');
    }

    public function activeServices(): HasMany
    {
        return $this->hasMany(DeviceService::class, 'device_id')->where('active', true);
    }

    public function scopeWithActiveService($query, string $serviceName)
    {
        return $query->whereHas('deviceServices', function ($q) use ($serviceName) {
            $q->where('name', $serviceName)->where('active', true);
        });
    }

    // Helpers
    public function hasActiveService(string $serviceName): bool
    {
        return $this->deviceServices()
            ->where('name', $serviceName)
            ->where('active', true)
            ->exists();
    }

    public function getActiveServices(): array
    {
        return $this->activeServices->pluck('name')->toArray();
    }

    public function enableService(string $serviceName, array $configuration = []): void
    {
        $this->deviceServices()->updateOrCreate(
            ['name' => $serviceName],
            [
                'active' => true,
                'configuration' => $configuration,
            ]
        );
    }

    public function disableService(string $serviceName): void
    {
        $this->deviceServices()
            ->where('name', $serviceName)
            ->update(['active' => false]);
    }
}
