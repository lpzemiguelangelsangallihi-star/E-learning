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
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    /**
     * Recalcula el progreso del estudiante en un curso a partir de
     * las lecciones publicadas que ya completó.
     */
    public static function recalcular(int $cursoId, int $estudianteId): self
    {
        $leccionIds = Leccion::query()
            ->where('estado', 'publicado')
            ->whereHas('modulo', function ($query) use ($cursoId) {
                $query->where('curso_id', $cursoId)
                    ->where('estado', 'activo');
            })
            ->pluck('id');

        $total = $leccionIds->count();

        $hechas = ProgresoLeccion::query()
            ->where('estudiante_id', $estudianteId)
            ->where('completado', true)
            ->whereIn('leccion_id', $leccionIds)
            ->count();

        $porcentaje = $total > 0
            ? round(($hechas / $total) * 100, 2)
            : 0;

        $estado = match (true) {
            $hechas === 0 => 'no_iniciado',
            $total > 0 && $hechas >= $total => 'completado',
            default => 'en_progreso',
        };

        $progreso = static::firstOrNew([
            'curso_id' => $cursoId,
            'estudiante_id' => $estudianteId,
        ]);

        $progreso->porcentaje = $porcentaje;
        $progreso->lecciones_completadas = $hechas;
        $progreso->estado = $estado;

        if ($hechas > 0 && !$progreso->fecha_inicio) {
            $progreso->fecha_inicio = now();
        }

        $progreso->fecha_finalizacion = $estado === 'completado'
            ? ($progreso->fecha_finalizacion ?? now())
            : null;

        $progreso->save();

        // La inscripción se marca como finalizada al completar el curso.
        Inscripcion::query()
            ->where('curso_id', $cursoId)
            ->where('estudiante_id', $estudianteId)
            ->whereIn('estado', ['activo', 'finalizado'])
            ->update([
                'estado' => $estado === 'completado'
                    ? 'finalizado'
                    : 'activo',
            ]);

        return $progreso;
    }
}
