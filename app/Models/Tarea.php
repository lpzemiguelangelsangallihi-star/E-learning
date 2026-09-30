<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tarea extends Model
{
    protected $table = 'tareas';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'leccion_id',
        'profesor_id',
        'titulo',
        'instrucciones',
        'fecha_limite',
        'puntaje_maximo',
        'archivo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_limite' => 'datetime',
            'puntaje_maximo' => 'decimal:2',
        ];
    }

    public function leccion(): BelongsTo
    {
        return $this->belongsTo(Leccion::class, 'leccion_id');
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'profesor_id');
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(Entrega::class, 'tarea_id');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'tarea_id');
    }
}
