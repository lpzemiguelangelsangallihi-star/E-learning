<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IopRecomendacion extends Model
{
    protected $table = 'iop_recomendaciones';

    protected $fillable = [
        'estudiante_id',
        'leccion_id',
        'modelo_decision_id',
        'calificacion_inicial',
        'alternativa_recomendada',
        'valor_maximin',
        'minimos',
        'matriz_utilizada',
    ];

    protected $casts = [
        'calificacion_inicial' => 'decimal:2',
        'valor_maximin' => 'decimal:2',
        'minimos' => 'array',
        'matriz_utilizada' => 'array',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    public function leccion(): BelongsTo
    {
        return $this->belongsTo(Leccion::class, 'leccion_id');
    }

    public function modeloDecision(): BelongsTo
    {
        return $this->belongsTo(
            IopModeloDecision::class,
            'modelo_decision_id'
        );
    }
    public function asignacionRefuerzo(): HasOne
    {
        return $this->hasOne(
            IopAsignacionRefuerzo::class,
            'recomendacion_id'
        );
    }
}
