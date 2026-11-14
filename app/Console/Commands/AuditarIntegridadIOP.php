<?php

namespace App\Console\Commands;

use App\Models\IopSeguimiento;
use Illuminate\Console\Command;

class AuditarIntegridadIOP extends Command
{
    protected $signature = 'iop:auditar-integridad';

    protected $description =
        'Audita los seguimientos IOP reales sin modificar datos';

    public function handle(): int
    {
        $revisados = 0;
        $incidencias = 0;

        $seguimientos = IopSeguimiento::query()
            ->whereNotNull('intento_diagnostico_id')
            ->with([
                'ciclosRefuerzo.recomendacion.asignacionRefuerzo',
                'transiciones',
            ])
            ->orderBy('id')
            ->lazyById(100);

        foreach ($seguimientos as $seguimiento) {
            $revisados++;

            $ciclos = $seguimiento->ciclosRefuerzo
                ->keyBy('ciclo');

            $transiciones = $seguimiento->transiciones
                ->keyBy('ciclo');

            $numeroActual = (int) $seguimiento->ciclo_actual;

            // En S4 no existe un nuevo refuerzo:
            // la última evaluación cerró el seguimiento.
            $ultimoCicloEsperado =
                $seguimiento->estado_actual === 'S4'
                    ? $numeroActual - 1
                    : $numeroActual;

            for (
                $numero = 0;
                $numero <= $ultimoCicloEsperado;
                $numero++
            ) {
                $ciclo = $ciclos->get($numero);

                if (!$ciclo) {
                    $this->reportar(
                        $seguimiento->id,
                        "Falta el ciclo de refuerzo {$numero}",
                        $incidencias
                    );

                    continue;
                }

                $recomendacion = $ciclo->recomendacion;

                if (
                    !$recomendacion ||
                    (int) $recomendacion->estudiante_id
                        !== (int) $seguimiento->estudiante_id ||
                    (int) $recomendacion->leccion_id
                        !== (int) $seguimiento->leccion_id
                ) {
                    $this->reportar(
                        $seguimiento->id,
                        "Recomendación inválida en ciclo {$numero}",
                        $incidencias
                    );

                    continue;
                }

                if (!$recomendacion->asignacionRefuerzo) {
                    $this->reportar(
                        $seguimiento->id,
                        "Sin ejercicio asignado en ciclo {$numero}",
                        $incidencias
                    );
                }

                if ($numero === 0) {
                    if (
                        (int) $ciclo->recomendacion_id
                            !== (int) $seguimiento->recomendacion_inicial_id ||
                        $ciclo->transicion_origen_id !== null
                    ) {
                        $this->reportar(
                            $seguimiento->id,
                            'Ciclo inicial inconsistente',
                            $incidencias
                        );
                    }

                    continue;
                }

                $transicion = $transiciones->get($numero);

                if (
                    !$transicion ||
                    (int) $ciclo->transicion_origen_id
                        !== (int) $transicion->id
                ) {
                    $this->reportar(
                        $seguimiento->id,
                        "Transición de origen inválida en ciclo {$numero}",
                        $incidencias
                    );
                }
            }

            // Cada transición debe corresponder a
            // un refuerzo del ciclo anterior.
            for ($numero = 1; $numero <= $numeroActual; $numero++) {
                $transicion = $transiciones->get($numero);

                if (!$transicion) {
                    $this->reportar(
                        $seguimiento->id,
                        "Falta la transición académica {$numero}",
                        $incidencias
                    );

                    continue;
                }

                if ($transicion->origen !== 'evaluacion') {
                    $this->reportar(
                        $seguimiento->id,
                        "Transición {$numero} no es evaluación real",
                        $incidencias
                    );
                }

                $cicloAnterior = $ciclos->get($numero - 1);

                $asignacion = $cicloAnterior
                    ?->recomendacion
                    ?->asignacionRefuerzo;

                if (
                    !$asignacion ||
                    $asignacion->estado !== 'completado' ||
                    !$asignacion->completado_en ||
                    !$asignacion->revisado_por
                ) {
                    $this->reportar(
                        $seguimiento->id,
                        "Refuerzo del ciclo anterior a {$numero} sin aprobación",
                        $incidencias
                    );
                }
            }

            if (
                $seguimiento->estado_actual === 'S4' &&
                ($seguimiento->activo ||
                    !$seguimiento->consolidado_en)
            ) {
                $this->reportar(
                    $seguimiento->id,
                    'S4 no está cerrado correctamente',
                    $incidencias
                );
            }

            if (
                $seguimiento->estado_actual !== 'S4' &&
                !$seguimiento->activo
            ) {
                $this->reportar(
                    $seguimiento->id,
                    'Seguimiento no consolidado marcado como inactivo',
                    $incidencias
                );
            }
        }

        $this->newLine();

        $this->table(
            ['Concepto', 'Cantidad'],
            [
                ['Seguimientos reales revisados', $revisados],
                ['Incidencias detectadas', $incidencias],
            ]
        );

        if ($incidencias === 0) {
            $this->info(
                'No se detectaron inconsistencias estructurales.'
            );
        } else {
            $this->warn(
                'Se encontraron registros que requieren revisión.'
            );
        }

        return $incidencias > 0
            ? self::FAILURE
            : self::SUCCESS;
    }

    private function reportar(
        int $seguimientoId,
        string $mensaje,
        int &$incidencias
    ): void {
        $incidencias++;

        $this->warn(
            "Seguimiento #{$seguimientoId}: {$mensaje}"
        );
    }
}
