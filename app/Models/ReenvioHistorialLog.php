<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReenvioHistorialLog extends Model
{
    protected $table = 'reenvio_logs';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
