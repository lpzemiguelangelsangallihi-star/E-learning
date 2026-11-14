<?php

namespace App\Services\IOP;

use App\Models\Examen;
use App\Models\IntentoExamen;
use App\Models\IopCicloRefuerzo;
use App\Models\IopSeguimiento;
use Illuminate\Support\Facades\DB;

class ConsolidacionSeguimientoIOPService
{
    public function __construct(
        private ConsolidacionIOPService $criterio
    ) {}

    public function consolidarSiCorresponde(
        int $seguimientoId
    ): bool {
        return DB::transaction(function () use ($seguimientoId) {

            $seguimiento = IopSeguimiento::query()
                ->lockForUpdate()
                ->findOrFail($seguimientoId);

            if (
                !$seguimiento->activo ||
                $seguimiento->estado_actual === 'S4' ||
                $seguimiento->intento_diagnostico_id === null
            ) {
                return false;
            }

            // No utilizar seguimientos con simulaciones.
            if ($seguimiento->transiciones()
                ->where('origen', 'simulacion')
                ->exists()
            ) {
                return false;
            }

            $ultimas = $seguimiento->transiciones()
                ->orderByDesc('ciclo')
                ->limit(2)
                ->get()
                ->reverse()
                ->values();

            if (
                $ultimas->count() !== 2 ||
                (int) $ultimas->last()->ciclo
                    !== (int) $seguimiento->ciclo_actual
            ) {
                return false;
            }

            $datos = [];
            $fechaAnterior = null;
            $intentosUsados = [];

            foreach ($ultimas as $transicion) {

                if (
                    $transicion->origen !== 'evaluacion' ||
                    !str_starts_with(
                        (string) $transicion->referencia_externa,
                        'intento_examen:'
                    )
                ) {
                    return false;
                }

                $idTexto = substr(
                    $transicion->referencia_externa,
                    strlen('intento_examen:')
                );

                if (
                    !ctype_digit($idTexto) ||
                    (int) $idTexto < 1
                ) {
                    return false;
                }

                $intentoId = (int) $idTexto;

                if (in_array($intentoId, $intentosUsados, true)) {
                    return false;
                }

                $intentosUsados[] = $intentoId;

                $intento = IntentoExamen::query()
                    ->with('examen')
                    ->find($intentoId);

                if (
                    !$intento ||
                    $intento->estado !== 'finalizado' ||
                    !$intento->fecha_fin ||
                    !$intento->examen ||
                    $intento->examen->tipo_iop
                        !== Examen::IOP_REFUERZO ||
                    (int) $intento->estudiante_id
                        !== (int) $seguimiento->estudiante_id ||
                    (int) $intento->examen->leccion_id
                        !== (int) $seguimiento->leccion_id
                ) {
                    return false;
                }

                // La evaluación debe ser posterior
                // a la evaluación precedente.
                if (
                    $fechaAnterior !== null &&
                    !$intento->fecha_fin->greaterThan(
                        $fechaAnterior
                    )
                ) {
                    return false;
                }

                // Cada transición consume el refuerzo
                // del ciclo inmediatamente anterior.
                $cicloAnterior = (int) $transicion->ciclo - 1;

                if ($cicloAnterior < 0) {
                    return false;
                }

                $ciclo = IopCicloRefuerzo::query()
                    ->where('seguimiento_id', $seguimiento->id)
                    ->where('ciclo', $cicloAnterior)
                    ->with('recomendacion.asignacionRefuerzo')
                    ->first();

                $asignacion = $ciclo
                    ?->recomendacion
                    ?->asignacionRefuerzo;

                if (
                    !$asignacion ||
                    $asignacion->estado !== 'completado' ||
                    !$asignacion->completado_en ||
                    !$asignacion->revisado_por
                ) {
                    return false;
                }

                // La aprobación del refuerzo debe
                // preceder a su evaluación.
                if (
                    !$intento->fecha_fin->greaterThan(
                        $asignacion->completado_en
                    )
                ) {
                    return false;
                }

                // Para el segundo ciclo, el nuevo
                // refuerzo debe ser posterior a
                // la evaluación anterior.
                if (
                    $fechaAnterior !== null &&
                    !$asignacion->completado_en->greaterThan(
                        $fechaAnterior
                    )
                ) {
                    return false;
                }

                $datos[] = [
                    'origen' => 'evaluacion',
                    'calificacion_nueva' =>
                        (float) $transicion->calificacion_nueva,
                ];

                $fechaAnterior = $intento->fecha_fin;
            }

            if (!$this->criterio->puedeConsolidar(
                $datos,
                true
            )) {
                return false;
            }

            // La última evaluación real provoca S4.
            $ultima = $ultimas->last();

            $ultima->update([
                'estado_siguiente' => 'S4',
            ]);

            $seguimiento->update([
                'estado_actual' => 'S4',
                'activo' => false,
                'consolidado_en' => now(),
            ]);

            return true;
        });
    }
}
