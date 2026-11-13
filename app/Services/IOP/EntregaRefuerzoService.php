<?php

namespace App\Services\IOP;

use App\Models\Inscripcion;
use App\Models\IopAsignacionRefuerzo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EntregaRefuerzoService
{
    /**
     * El estudiante entrega su actividad.
     */
    public function entregar(
        int $asignacionId,
        User $estudiante,
        string $respuesta
    ): IopAsignacionRefuerzo {
        $respuesta = trim($respuesta);

        if (
            mb_strlen($respuesta) < 10 ||
            mb_strlen($respuesta) > 10000
        ) {
            throw ValidationException::withMessages([
                'respuesta' =>
                    'La respuesta debe contener entre 10 y 10000 caracteres.',
            ]);
        }

        return DB::transaction(function () use (
            $asignacionId,
            $estudiante,
            $respuesta
        ) {
            $asignacion = IopAsignacionRefuerzo::query()
                ->with('recomendacion.leccion.modulo.curso')
                ->lockForUpdate()
                ->findOrFail($asignacionId);

            $recomendacion = $asignacion->recomendacion;
            $curso = $recomendacion?->leccion?->modulo?->curso;

            if (
                !$curso ||
                (int) $recomendacion->estudiante_id
                    !== (int) $estudiante->id ||
                $estudiante->rol?->nombre !== 'estudiante'
            ) {
                abort(403, 'No puedes entregar este ejercicio.');
            }

            $inscrito = Inscripcion::query()
                ->where('estudiante_id', $estudiante->id)
                ->where('curso_id', $curso->id)
                ->where('estado', 'activo')
                ->exists();

            if (!$inscrito || $curso->estado !== 'publicado') {
                abort(403, 'No tienes acceso activo a este curso.');
            }

            // En esta versión solo aceptamos entregas
            // nuevas o correcciones solicitadas.
            if (!in_array(
                $asignacion->estado,
                ['asignado', 'requiere_correccion'],
                true
            )) {
                throw ValidationException::withMessages([
                    'asignacion' =>
                        'Este ejercicio no admite una nueva entrega.',
                ]);
            }

            $asignacion->update([
                'respuesta_estudiante' => $respuesta,
                'estado' => 'en_revision',
                'entregado_en' => now(),
                'retroalimentacion' => null,
                'revisado_por' => null,
                'revisado_en' => null,
                'completado_en' => null,
            ]);

            return $asignacion->refresh();
        });
    }

    /**
     * El docente revisa una actividad entregada.
     */
    public function revisar(
        int $asignacionId,
        User $profesor,
        bool $aprobado,
        string $retroalimentacion = ''
    ): IopAsignacionRefuerzo {
        $retroalimentacion = trim($retroalimentacion);

        if (mb_strlen($retroalimentacion) > 5000) {
            throw ValidationException::withMessages([
                'retroalimentacion' =>
                    'La retroalimentación es demasiado extensa.',
            ]);
        }

        if (!$aprobado && $retroalimentacion === '') {
            throw ValidationException::withMessages([
                'retroalimentacion' =>
                    'Debes indicar qué debe corregir el estudiante.',
            ]);
        }

        return DB::transaction(function () use (
            $asignacionId,
            $profesor,
            $aprobado,
            $retroalimentacion
        ) {
            $asignacion = IopAsignacionRefuerzo::query()
                ->with('recomendacion.leccion.modulo.curso')
                ->lockForUpdate()
                ->findOrFail($asignacionId);

            $curso = $asignacion
                ->recomendacion?->leccion?->modulo?->curso;

            if (
                !$curso ||
                $profesor->rol?->nombre !== 'profesor' ||
                (int) $curso->profesor_id !== (int) $profesor->id
            ) {
                abort(403, 'No puedes revisar este ejercicio.');
            }

            if ($asignacion->estado !== 'en_revision') {
                throw ValidationException::withMessages([
                    'asignacion' =>
                        'El ejercicio no está pendiente de revisión.',
                ]);
            }

            $asignacion->update([
                'estado' => $aprobado
                    ? 'completado'
                    : 'requiere_correccion',

                'retroalimentacion' => $retroalimentacion,

                'revisado_por' => $profesor->id,
                'revisado_en' => now(),

                'completado_en' => $aprobado
                    ? now()
                    : null,
            ]);

            return $asignacion->refresh();
        });
    }
}
