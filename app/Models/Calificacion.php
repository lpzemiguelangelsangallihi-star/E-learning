<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calificacion extends Model
{
    protected $table = 'calificaciones';

    public $timestamps = false;

    protected $fillable = [
        'estudiante_id',
        'profesor_id',
        'curso_id',
        'tarea_id',
        'examen_id',
        'tipo',
        'calificacion',
        'retroalimentacion',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'calificacion' => 'decimal:2',
            'fecha' => 'datetime',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function tarea(): BelongsTo
    {
        return $this->belongsTo(Tarea::class, 'tarea_id');
    }

    public function examen(): BelongsTo
    {
        return $this->belongsTo(Examen::class, 'examen_id');
    }
}
