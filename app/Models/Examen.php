<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Examen extends Model
{
    protected $table = 'examenes';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'leccion_id',
        'profesor_id',
        'titulo',
        'descripcion',
        'tiempo_limite',
        'intentos_permitidos',
        'puntaje_maximo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'puntaje_maximo' => 'decimal:2',
        ];
    }

    public function leccion(): BelongsTo
    {
        return $this->belongsTo(Leccion::class, 'leccion_id');
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    public function preguntas(): HasMany
    {
        return $this->hasMany(Pregunta::class, 'examen_id');
    }

    public function intentos(): HasMany
    {
        return $this->hasMany(IntentoExamen::class, 'examen_id');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'examen_id');
    }
}
