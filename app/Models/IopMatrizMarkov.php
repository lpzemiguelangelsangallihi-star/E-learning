<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IopMatrizMarkov extends Model
{
    protected $table = 'iop_matrices_markov';

    protected $fillable = [
        'leccion_id',
        'origen',
        'version',
        'conteos',
        'probabilidades',
        'totales',
        'completa',
        'minimo_observaciones',
        'fecha_calculo',
    ];

    protected $casts = [
        'version' => 'integer',
        'conteos' => 'array',
        'probabilidades' => 'array',
        'totales' => 'array',
        'completa' => 'boolean',
        'minimo_observaciones' => 'integer',
        'fecha_calculo' => 'datetime',
    ];

    public function leccion(): BelongsTo
    {
        return $this->belongsTo(
            Leccion::class,
            'leccion_id'
        );
    }
}
