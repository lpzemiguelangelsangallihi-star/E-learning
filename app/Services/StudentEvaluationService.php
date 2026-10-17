<?php

namespace App\Services;

use App\Models\Entrega;
use App\Models\Examen;
use App\Models\Inscripcion;
use App\Models\IntentoExamen;
use App\Models\Tarea;
use Illuminate\Support\Collection;

/**
 * Evaluaciones (tareas y exámenes) vistas desde el estudiante.
 */
class StudentEvaluationService
{
    /**
     * Evaluaciones que el estudiante todavía puede resolver.
     */
    public function pending(int $estudianteId): Collection
    {
        $cursoIds = Inscripcion::cursoIdsDe($estudianteId);

        if (empty($cursoIds)) {
            return collect();
        }

        $delCurso = fn ($query) => $query->whereIn('curso_id', $cursoIds);

        $tareas = Tarea::query()
            ->with('leccion.modulo.curso')
            ->where('estado', 'publicada')
            ->whereHas('leccion.modulo', $delCurso)
            ->whereDoesntHave(
                'entregas',
                fn ($query) => $query->where('estudiante_id', $estudianteId)
            )
            ->get()
            ->map(fn (Tarea $tarea) => [
                'tipo' => 'tarea',
                'titulo' => $tarea->titulo,
                'curso' => $tarea->leccion?->modulo?->curso,
                'leccion' => $tarea->leccion,
                'fecha_limite' => $tarea->fecha_limite,
                'vencida' => $tarea->fecha_limite?->isPast() ?? false,
                'puntaje' => $tarea->puntaje_maximo,
                'detalle' => null,
            ]);

        $examenes = Examen::query()
            ->with('leccion.modulo.curso')
            ->withCount([
                'intentos as intentos_realizados' => fn ($query) => $query
                    ->where('estudiante_id', $estudianteId)
                    ->where('estado', 'finalizado'),
            ])
            ->where('estado', 'publicado')
            ->whereHas('leccion.modulo', $delCurso)
            ->get()
            ->filter(
                fn (Examen $examen) =>
                    $examen->intentos_realizados < $examen->intentos_permitidos
            )
            ->map(fn (Examen $examen) => [
                'tipo' => 'examen',
                'titulo' => $examen->titulo,
                'curso' => $examen->leccion?->modulo?->curso,
                'leccion' => $examen->leccion,
                'fecha_limite' => null,
                'vencida' => false,
                'puntaje' => $examen->puntaje_maximo,
                'detalle' => $examen->tiempo_limite
                    ? "{$examen->tiempo_limite} min"
                    : 'Sin límite de tiempo',
            ]);

        // Primero lo que vence antes; lo que no tiene fecha va al final.
        return $tareas
            ->concat($examenes)
            ->sortBy(fn (array $item) => $item['fecha_limite']?->timestamp ?? PHP_INT_MAX)
            ->values();
    }

    /**
     * Exámenes finalizados y tareas entregadas, de la más reciente a la más antigua.
     */
    public function history(int $estudianteId): Collection
    {
        $examenes = IntentoExamen::query()
            ->with('examen.leccion.modulo.curso')
            ->where('estudiante_id', $estudianteId)
            ->where('estado', 'finalizado')
            ->get()
            ->map(fn (IntentoExamen $intento) => [
                'tipo' => 'examen',
                'titulo' => $intento->examen?->titulo ?? 'Examen eliminado',
                'curso' => $intento->examen?->leccion?->modulo?->curso,
                'fecha' => $intento->fecha_fin ?? $intento->fecha_inicio,
                'calificacion' => $intento->calificacion,
                'maximo' => $intento->examen?->puntaje_maximo,
                'estado' => 'finalizado',
                'detalle' => "Intento {$intento->numero_intento}",
            ]);

        $tareas = Entrega::query()
            ->with('tarea.leccion.modulo.curso')
            ->where('estudiante_id', $estudianteId)
            ->get()
            ->map(fn (Entrega $entrega) => [
                'tipo' => 'tarea',
                'titulo' => $entrega->tarea?->titulo ?? 'Tarea eliminada',
                'curso' => $entrega->tarea?->leccion?->modulo?->curso,
                'fecha' => $entrega->fecha_entrega,
                'calificacion' => $entrega->calificacion,
                'maximo' => $entrega->tarea?->puntaje_maximo,
                'estado' => $entrega->estado,
                'detalle' => $entrega->retroalimentacion,
            ]);

        return $examenes
            ->concat($tareas)
            ->sortByDesc(fn (array $item) => $item['fecha']?->timestamp ?? 0)
            ->values();
    }
}
