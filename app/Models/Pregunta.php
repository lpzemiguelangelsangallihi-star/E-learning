<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pregunta extends Model
{
    protected $table = 'preguntas';

    public $timestamps = false;

    protected $fillable = [
        'examen_id',
        'pregunta',
        'tipo',
        'puntaje',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'puntaje' => 'decimal:2',
        ];
    }

    public function examen(): BelongsTo
    {
        return $this->belongsTo(Examen::class, 'examen_id');
    }

    public function opciones(): HasMany
    {
        return $this->hasMany(OpcionPregunta::class, 'pregunta_id');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(RespuestaEstudiante::class, 'pregunta_id');
    }
}
