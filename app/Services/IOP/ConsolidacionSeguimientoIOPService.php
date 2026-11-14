<?php

namespace App\Services\IOP;

use App\Models\IntentoExamen;
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

            // Una consolidación anterior no se repite.
            if ($seguimiento->estado_actual === 'S4') {
                return false;
            }

            if (
                !$seguimiento->activo ||
                $seguimiento->intento_diagnostico_id === null
            ) {
                return false;
            }

            // No consolidar historiales que contengan simulaciones.
            if ($seguimiento->transiciones()
                ->where('origen', 'simulacion')
                ->exists()
            ) {
                return false;
            }

            // Comprobar que existe refuerzo aprobado.
            $recomendacion = $seguimiento
                ->recomendacionInicial()
                ->with('asignacionRefuerzo')
                ->first();

            $asignacion = $recomendacion
                ?->asignacionRefuerzo;

            if (
                !$asignacion ||
                $asignacion->estado !== 'completado' ||
                !$asignacion->completado_en ||
                !$asignacion->revisado_por
            ) {
                return false;
            }

            // Recuperar las dos transiciones más recientes.
            $ultimas = $seguimiento->transiciones()
                ->orderByDesc('ciclo')
                ->limit(2)
                ->get()
                ->reverse()
                ->values();

            if ($ultimas->count() !== 2) {
                return false;
            }

            $datos = [];

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

                $intentoId = substr(
                    $transicion->referencia_externa,
                    strlen('intento_examen:')
                );

                if (
                    !ctype_digit($intentoId) ||
                    (int) $intentoId < 1
                ) {
                    return false;
                }

                $intento = IntentoExamen::query()
                    ->with('examen')
                    ->find((int) $intentoId);

                if (
                    !$intento ||
                    $intento->estado !== 'finalizado' ||
                    !$intento->fecha_fin ||
                    !$intento->examen ||
                    (int) $intento->estudiante_id
                        !== (int) $seguimiento->estudiante_id ||
                    (int) $intento->examen->leccion_id
                        !== (int) $seguimiento->leccion_id
                ) {
                    return false;
                }

                // El refuerzo debe estar aprobado antes
                // de la fecha real de cada evaluación.
                if (
                    !$intento->fecha_fin->greaterThan(
                        $asignacion->completado_en
                    )
                ) {
                    return false;
                }

                $datos[] = [
                    'origen' => $transicion->origen,
                    'calificacion_nueva' =>
                        (float) $transicion->calificacion_nueva,
                ];
            }

            // Exigir orden temporal real de los intentos.
            $primerId = (int) substr(
                $ultimas[0]->referencia_externa,
                strlen('intento_examen:')
            );

            $segundoId = (int) substr(
                $ultimas[1]->referencia_externa,
                strlen('intento_examen:')
            );

            $primerIntento = IntentoExamen::findOrFail($primerId);
            $segundoIntento = IntentoExamen::findOrFail($segundoId);

            if (
                !$segundoIntento->fecha_fin->greaterThan(
                    $primerIntento->fecha_fin
                )
            ) {
                return false;
            }

            if (
                !$this->criterio->puedeConsolidar(
                    $datos,
                    true
                )
            ) {
                return false;
            }

            // Registrar la consolidación en el último
            // ciclo, sin duplicar una evaluación.
            $ultimaTransicion = $ultimas->last();

            $ultimaTransicion->update([
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
