<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IopAsignacionRefuerzo extends Model
{
    protected $table = 'iop_asignaciones_refuerzo';

    protected $fillable = [
        'recomendacion_id',
        'ejercicio_id',
        'estado',
        'asignado_en',
        'completado_en',
    ];

    protected $casts = [
        'recomendacion_id' => 'integer',
        'ejercicio_id' => 'integer',
        'asignado_en' => 'datetime',
        'completado_en' => 'datetime',
    ];

    public function recomendacion(): BelongsTo
    {
        return $this->belongsTo(
            IopRecomendacion::class,
            'recomendacion_id'
        );
    }

    public function ejercicio(): BelongsTo
    {
        return $this->belongsTo(
            IopEjercicioRefuerzo::class,
            'ejercicio_id'
        );
    }
}
