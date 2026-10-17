<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscripcion extends Model
{
    protected $table = 'inscripciones';

    public $timestamps = false;

    protected $fillable = [
        'curso_id',
        'estudiante_id',
        'fecha_inscripcion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inscripcion' => 'datetime',
        ];
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    /**
     * IDs de los cursos en los que el estudiante está inscrito
     * (activos o ya finalizados).
     */
    public static function cursoIdsDe(int $estudianteId): array
    {
        return static::query()
            ->where('estudiante_id', $estudianteId)
            ->whereIn('estado', ['activo', 'finalizado'])
            ->pluck('curso_id')
            ->all();
    }
}
