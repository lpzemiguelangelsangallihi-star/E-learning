<?php

namespace App\Services\IOP;

use App\Models\IopCicloRefuerzo;
use App\Models\IopSeguimiento;
use App\Models\IopTransicion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CicloRefuerzoService
{
    public function __construct(
        private RecomendacionService $recomendaciones,
        private AsignacionRefuerzoService $asignaciones
    ) {}

    public function registrarInicial(
        int $seguimientoId
    ): IopCicloRefuerzo {
        return DB::transaction(function () use ($seguimientoId) {

            $seguimiento = IopSeguimiento::query()
                ->lockForUpdate()
                ->findOrFail($seguimientoId);

            $recomendacion = $seguimiento
                ->recomendacionInicial()
                ->with('asignacionRefuerzo')
                ->firstOrFail();

            if (!$recomendacion->asignacionRefuerzo) {
                throw ValidationException::withMessages([
                    'refuerzo' =>
                        'La recomendación inicial no tiene '
                        .'un ejercicio asignado.',
                ]);
            }

            $existente = IopCicloRefuerzo::query()
                ->where('seguimiento_id', $seguimiento->id)
                ->where('ciclo', 0)
                ->first();

            if ($existente) {
                if (
                    (int) $existente->recomendacion_id
                    !== (int) $recomendacion->id
                ) {
                    throw ValidationException::withMessages([
                        'ciclo' =>
                            'El ciclo inicial contiene otra recomendación.',
                    ]);
                }

                return $existente;
            }

            return IopCicloRefuerzo::create([
                'seguimiento_id' => $seguimiento->id,
                'ciclo' => 0,
                'recomendacion_id' => $recomendacion->id,
                'transicion_origen_id' => null,
            ]);
        });
    }

    /**
     * Genera el siguiente ciclo de refuerzo
     * después de una evaluación real.
     *
     * Conserva el historial anterior.
     */
    public function generarSiguiente(
        int $transicionId
    ): IopCicloRefuerzo {
        return DB::transaction(function () use ($transicionId) {

            $transicion = IopTransicion::query()
                ->findOrFail($transicionId);

            $seguimiento = IopSeguimiento::query()
                ->lockForUpdate()
                ->findOrFail($transicion->seguimiento_id);

            if ($transicion->origen !== 'evaluacion') {
                throw ValidationException::withMessages([
                    'transicion' =>
                        'Solo una evaluación real puede '
                        .'generar nuevos ciclos.',
                ]);
            }

            // Si el ciclo ya existe, devolverlo.
            $existente = IopCicloRefuerzo::query()
                ->where('seguimiento_id', $seguimiento->id)
                ->where('ciclo', $transicion->ciclo)
                ->first();

            if ($existente) {
                if (
                    (int) $existente->transicion_origen_id
                    !== (int) $transicion->id
                ) {
                    throw ValidationException::withMessages([
                        'ciclo' =>
                            'Este ciclo pertenece a otra transición.',
                    ]);
                }

                return $existente;
            }

            if (
                !$seguimiento->activo ||
                $seguimiento->estado_actual === 'S4'
            ) {
                throw ValidationException::withMessages([
                    'seguimiento' =>
                        'El seguimiento ya está cerrado.',
                ]);
            }

            // Evitar generar ciclos a partir de
            // transiciones antiguas o no procesadas.
            if (
                (int) $seguimiento->ciclo_actual
                !== (int) $transicion->ciclo
            ) {
                throw ValidationException::withMessages([
                    'ciclo' =>
                        'La transición no corresponde '
                        .'al ciclo actual.',
                ]);
            }

            // El ciclo anterior debe existir.
            $anterior = IopCicloRefuerzo::query()
                ->where('seguimiento_id', $seguimiento->id)
                ->where('ciclo', $transicion->ciclo - 1)
                ->with('recomendacion.asignacionRefuerzo')
                ->first();

            $asignacionAnterior = $anterior
                ?->recomendacion
                ?->asignacionRefuerzo;

            if (
                !$asignacionAnterior ||
                $asignacionAnterior->estado !== 'completado' ||
                !$asignacionAnterior->completado_en ||
                !$asignacionAnterior->revisado_por
            ) {
                throw ValidationException::withMessages([
                    'refuerzo' =>
                        'El refuerzo anterior debe estar '
                        .'aprobado antes de generar otro ciclo.',
                ]);
            }

            // La nota de la transición se utiliza
            // como referencia para la nueva recomendación.
            $nota = (float) $transicion->calificacion_nueva;

            $recomendacion = $this->recomendaciones->generar(
                (int) $seguimiento->estudiante_id,
                (int) $seguimiento->leccion_id,
                $nota
            );

            // Asignar un ejercicio correspondiente
            // a la categoría elegida por Maximin.
            $this->asignaciones->asignar(
                (int) $recomendacion->id
            );

            // Registrar el nuevo ciclo.
            return IopCicloRefuerzo::create([
                'seguimiento_id' => $seguimiento->id,
                'ciclo' => $transicion->ciclo,
                'recomendacion_id' => $recomendacion->id,
                'transicion_origen_id' => $transicion->id,
            ]);
        });
    }
}
