<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgresoCurso extends Model
{
    protected $table = 'progreso_cursos';

    public $timestamps = false;

    protected $fillable = [
        'curso_id',
        'estudiante_id',
        'porcentaje',
        'lecciones_completadas',
        'estado',
        'fecha_inicio',
        'fecha_finalizacion',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje' => 'decimal:2',
            'fecha_inicio' => 'datetime',
            'fecha_finalizacion' => 'datetime',
        ];
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'estudiante_id');
    }
}
