<?php

namespace App\Services\Evaluacion;

use App\Models\Calificacion;
use App\Models\Entrega;
use App\Models\Tarea;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/** Entrega y calificación de tareas. */
class TaskService
{
    /**
     * Crea o reemplaza la entrega del estudiante.
     * Si pasó la fecha límite queda marcada como "tarde".
     */
    public function entregar(Tarea $tarea, User $estudiante, ?string $respuesta, ?UploadedFile $archivo): Entrega
    {
        return DB::transaction(function () use ($tarea, $estudiante, $respuesta, $archivo) {
            $entrega = Entrega::query()
                ->where('tarea_id', $tarea->id)
                ->where('estudiante_id', $estudiante->id)
                ->lockForUpdate()
                ->first();

            if ($entrega && $entrega->estado === 'calificada') {
                throw new ExamException('Esta entrega ya fue calificada y no se puede modificar.');
            }

            $rutaNueva = $archivo
                ? $archivo->store("entregas/{$tarea->id}", 'local')
                : null;

            $tarde = $tarea->fecha_limite && now()->greaterThan($tarea->fecha_limite);

            $datos = [
                'respuesta' => $respuesta,
                'fecha_entrega' => now(),
                'estado' => $tarde ? 'tarde' : 'entregada',
            ];

            if ($rutaNueva) {
                if ($entrega?->archivo) {
                    Storage::disk('local')->delete($entrega->archivo);
                }

                $datos['archivo'] = $rutaNueva;
            }

            if ($entrega) {
                $entrega->update($datos);

                return $entrega;
            }

            return Entrega::create($datos + [
                'tarea_id' => $tarea->id,
                'estudiante_id' => $estudiante->id,
                'archivo' => $rutaNueva,
            ]);
        });
    }

    /** Califica una entrega, guarda la nota en "calificaciones" y audita el cambio. */
    public function calificar(Entrega $entrega, float $nota, ?string $retroalimentacion): Entrega
    {
        return DB::transaction(function () use ($entrega, $nota, $retroalimentacion) {
            $entrega->loadMissing('tarea.leccion.modulo', 'estudiante');
            $tarea = $entrega->tarea;

            $antes = $entrega->calificacion !== null ? (float) $entrega->calificacion : null;

            $entrega->calificacion = round($nota, 2);
            $entrega->retroalimentacion = $retroalimentacion;
            $entrega->estado = 'calificada';
            $entrega->save();

            $maximo = (float) $tarea->puntaje_maximo;
            $sobreCien = $maximo > 0 ? round(($nota / $maximo) * 100, 2) : 0;

            Calificacion::updateOrCreate(
                [
                    'estudiante_id' => $entrega->estudiante_id,
                    'tarea_id' => $tarea->id,
                    'tipo' => 'tarea',
                ],
                [
                    'profesor_id' => $tarea->profesor_id,
                    'curso_id' => $tarea->leccion->modulo->curso_id,
                    'calificacion' => $sobreCien,
                    'retroalimentacion' => $retroalimentacion,
                    'fecha' => now(),
                ]
            );

            $antesTxt = $antes === null ? 'sin nota' : rtrim(rtrim(number_format($antes, 2), '0'), '.');
            $despuesTxt = rtrim(rtrim(number_format($nota, 2), '0'), '.');

            BitacoraService::registrar(
                "Calificó la entrega #{$entrega->id} de {$entrega->estudiante?->nombre_completo} "
                ."en la tarea «{$tarea->titulo}»: {$antesTxt} → {$despuesTxt}"
            );

            return $entrega;
        });
    }
}
