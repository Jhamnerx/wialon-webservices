<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CounterServices;

class Config extends Model
{
    protected $table = 'config';

    protected $casts = [

        'custom_host' => 'boolean',
    ];

    protected $guarded = ['id', 'created_at', 'updated_at'];



    public function getBaseUriAttribute($value)
    {
        return $this->attributes['custom_host'] ? $this->attributes['host'] : $value;
    }

    public function setBaseUriAttribute($value)
    {
        $this->attributes['base_uri'] = $value;
    }

    public function getCustomHostAttribute($value)
    {
        return $value;
    }

    public function services()
    {
        return Service::all();
    }

    public function counterServices()
    {
        return $this->morphOne(CounterServices::class, 'serviceable');
    }

    // Helpers
    public function hasValidToken(): bool
    {
        return !empty($this->token);
    }
}
