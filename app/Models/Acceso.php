<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Acceso extends Model
{
    protected $table = 'accesos';

    protected $fillable = [
        'tipo',
        'nombre',
        'idMunicipalidad',
        'idTransmision',
        'codigoComisaria',
        'ubigeo'
    ];

    // Relaciones
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'acceso_id');
    }

    // Scopes
    public function scopeSerenazgos($query)
    {
        return $query->where('tipo', 'serenazgo');
    }

    public function scopePoliciales($query)
    {
        return $query->where('tipo', 'policial');
    }

    // Accessors
    public function getIdentificadorAttribute(): string
    {
        return $this->tipo === 'serenazgo'
            ? $this->idMunicipalidad
            : $this->idTransmision . '-' . $this->codigoComisaria;
    }
}
