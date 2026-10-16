<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RespuestaEstudiante extends Model
{
    protected $table = 'respuestas_estudiante';

    public $timestamps = false;

    protected $fillable = [
        'intento_id',
        'pregunta_id',
        'opcion_id',
        'respuesta_texto',
        'es_correcta',
        'puntaje_obtenido',
    ];

    protected function casts(): array
    {
        return [
            'es_correcta' => 'boolean',
            'puntaje_obtenido' => 'decimal:2',
        ];
    }

    public function intento(): BelongsTo
    {
        return $this->belongsTo(IntentoExamen::class, 'intento_id');
    }

    public function pregunta(): BelongsTo
    {
        return $this->belongsTo(Pregunta::class, 'pregunta_id');
    }

    public function opcion(): BelongsTo
    {
        return $this->belongsTo(OpcionPregunta::class, 'opcion_id');
    }
}
