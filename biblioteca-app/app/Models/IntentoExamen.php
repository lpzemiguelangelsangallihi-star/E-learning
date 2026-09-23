<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IntentoExamen extends Model
{
    protected $table = 'intentos_examen';

    public $timestamps = false;

    protected $fillable = [
        'examen_id',
        'estudiante_id',
        'numero_intento',
        'fecha_inicio',
        'fecha_fin',
        'calificacion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'calificacion' => 'decimal:2',
        ];
    }

    public function examen(): BelongsTo
    {
        return $this->belongsTo(Examen::class, 'examen_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'estudiante_id');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(RespuestaEstudiante::class, 'intento_id');
    }
}
