<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IopSeguimiento extends Model
{
    protected $table = 'iop_seguimientos';

    protected $fillable = [
        'estudiante_id',
        'leccion_id',
        'recomendacion_inicial_id',
        'calificacion_inicial',
        'estado_inicial',
        'estado_actual',
        'vector_inicial',
        'ciclo_actual',
        'ultima_calificacion',
        'activo',
        'consolidado_en',
        'intento_diagnostico_id',
    ];

    protected $casts = [
        'calificacion_inicial' => 'decimal:2',
        'ultima_calificacion' => 'decimal:2',
        'vector_inicial' => 'array',
        'ciclo_actual' => 'integer',
        'activo' => 'boolean',
        'consolidado_en' => 'datetime',
        'intento_diagnostico_id' => 'integer',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'estudiante_id'
        );
    }

    public function leccion(): BelongsTo
    {
        return $this->belongsTo(
            Leccion::class,
            'leccion_id'
        );
    }

    public function recomendacionInicial(): BelongsTo
    {
        return $this->belongsTo(
            IopRecomendacion::class,
            'recomendacion_inicial_id'
        );
    }
    public function transiciones(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(
            IopTransicion::class,
            'seguimiento_id'
        );
    }
    public function ciclosRefuerzo(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(
            IopCicloRefuerzo::class,
            'seguimiento_id'
        )->orderBy('ciclo');
    }
}
