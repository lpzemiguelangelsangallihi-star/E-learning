<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IopCicloRefuerzo extends Model
{
    protected $table = 'iop_ciclos_refuerzo';

    protected $fillable = [
        'seguimiento_id',
        'ciclo',
        'recomendacion_id',
        'transicion_origen_id',
    ];

    protected $casts = [
        'seguimiento_id' => 'integer',
        'ciclo' => 'integer',
        'recomendacion_id' => 'integer',
        'transicion_origen_id' => 'integer',
    ];

    public function seguimiento(): BelongsTo
    {
        return $this->belongsTo(
            IopSeguimiento::class,
            'seguimiento_id'
        );
    }

    public function recomendacion(): BelongsTo
    {
        return $this->belongsTo(
            IopRecomendacion::class,
            'recomendacion_id'
        );
    }

    public function transicionOrigen(): BelongsTo
    {
        return $this->belongsTo(
            IopTransicion::class,
            'transicion_origen_id'
        );
    }
}
