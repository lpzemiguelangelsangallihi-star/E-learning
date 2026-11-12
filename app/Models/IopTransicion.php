<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IopTransicion extends Model
{
    protected $table = 'iop_transiciones';

    protected $fillable = [
        'seguimiento_id',
        'ciclo',
        'estado_anterior',
        'estado_siguiente',
        'calificacion_anterior',
        'calificacion_nueva',
        'origen',
        'referencia_externa',
    ];

    protected $casts = [
        'ciclo' => 'integer',
        'calificacion_anterior' => 'decimal:2',
        'calificacion_nueva' => 'decimal:2',
    ];

    public function seguimiento(): BelongsTo
    {
        return $this->belongsTo(
            IopSeguimiento::class,
            'seguimiento_id'
        );
    }
}
