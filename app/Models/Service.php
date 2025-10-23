<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'services';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'active' => 'boolean',
        'logs_enabled' => 'boolean',
        'configuration' => 'json',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeWithLogs($query)
    {
        return $query->where('logs_enabled', true);
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->active;
    }

    public function hasValidToken(): bool
    {
        return !empty($this->token);
    }
}
