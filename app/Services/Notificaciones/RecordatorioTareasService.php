<?php

namespace App\Services\Notificaciones;

use App\Models\Inscripcion;
use App\Models\Notificacion;
use App\Models\Tarea;

/** Genera avisos para estudiantes con tareas publicadas próximas a vencer. */
class RecordatorioTareasService
{
    /** Crea recordatorios para un estudiante o para todos los estudiantes activos. */
    public function generar(?int $estudianteId = null): int
    {
        $inscripciones = Inscripcion::query()
            ->where('estado', 'activo')
            ->when($estudianteId, fn ($q) => $q->where('estudiante_id', $estudianteId))
            ->get(['estudiante_id', 'curso_id']);

        $creadas = 0;
        $desde = now();
        $hasta = now()->addHours(48);

        foreach ($inscripciones->groupBy('estudiante_id') as $idEstudiante => $registros) {
            $cursoIds = $registros->pluck('curso_id')->unique()->values();

            $tareas = Tarea::query()
                ->with('leccion.modulo.curso')
                ->where('estado', 'publicada')
                ->whereNotNull('fecha_limite')
                ->whereBetween('fecha_limite', [$desde, $hasta])
                ->whereHas('leccion.modulo.curso', fn ($q) => $q
                    ->whereIn('cursos.id', $cursoIds)
                    ->where('cursos.estado', 'publicado'))
                ->whereDoesntHave('entregas', fn ($q) => $q->where('estudiante_id', $idEstudiante))
                ->get();

            foreach ($tareas as $tarea) {
                $notificacion = Notificacion::firstOrCreate(
                    [
                        'usuario_id' => $idEstudiante,
                        'tarea_id' => $tarea->id,
                        'tipo' => 'tarea_por_vencer',
                    ],
                    [
                        'titulo' => 'Tarea próxima a vencer',
                        'mensaje' => "La tarea «{$tarea->titulo}» sigue pendiente y vence el {$tarea->fecha_limite->format('d/m/Y H:i')}.",
                        'leida' => false,
                    ]
                );

                if ($notificacion->wasRecentlyCreated) {
                    $creadas++;
                }
            }
        }

        return $creadas;
    }
}
