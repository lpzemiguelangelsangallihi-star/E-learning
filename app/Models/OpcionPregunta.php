<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpcionPregunta extends Model
{
    protected $table = 'opciones_preguntas';

    public $timestamps = false;

    protected $fillable = [
        'pregunta_id',
        'opcion',
        'es_correcta',
    ];

    protected function casts(): array
    {
        return [
            'es_correcta' => 'boolean',
        ];
    }

    public function pregunta(): BelongsTo
    {
        return $this->belongsTo(Pregunta::class, 'pregunta_id');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(RespuestaEstudiante::class, 'opcion_id');
    }
}
