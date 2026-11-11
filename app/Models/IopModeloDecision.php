<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IopModeloDecision extends Model
{
    protected $table = 'iop_modelos_decision';

    protected $fillable = [
        'nombre',
        'matriz_utilidades',
        'metodo',
        'version',
        'activo',
    ];

    protected $casts = [
        'matriz_utilidades' => 'array',
        'activo' => 'boolean',
        'version' => 'integer',
    ];
}
