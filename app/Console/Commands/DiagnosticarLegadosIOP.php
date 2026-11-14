<?php

namespace App\Console\Commands;

use App\Models\IopSeguimiento;
use Illuminate\Console\Command;

class DiagnosticarLegadosIOP extends Command
{
    protected $signature = 'iop:diagnosticar-legados';

    protected $description =
        'Identifica historiales IOP anteriores o incompletos sin modificarlos';

    public function handle(): int
    {
        $revisados = 0;
        $sinCicloInicial = 0;
        $sinCiclosPosteriores = 0;
        $sinTransiciones = 0;

        IopSeguimiento::query()
            ->whereNotNull('intento_diagnostico_id')
            ->with(['ciclosRefuerzo', 'transiciones'])
            ->lazyById(100)
            ->each(function ($seguimiento) use (
                &$revisados,
                &$sinCicloInicial,
                &$sinCiclosPosteriores,
                &$sinTransiciones
            ) {
                $revisados++;

                $ciclos = $seguimiento
                    ->ciclosRefuerzo
                    ->keyBy('ciclo');

                $transiciones = $seguimiento
                    ->transiciones
                    ->keyBy('ciclo');

                $actual = (int) $seguimiento->ciclo_actual;

                if (!$ciclos->has(0)) {
                    $sinCicloInicial++;

                    $this->warn(
                        "Seguimiento #{$seguimiento->id}: "
                        ."no existe el ciclo inicial."
                    );
                }

                for ($numero = 1; $numero <= $actual; $numero++) {
                    if (!$transiciones->has($numero)) {
                        $sinTransiciones++;

                        $this->warn(
                            "Seguimiento #{$seguimiento->id}: "
                            ."falta la transición {$numero}."
                        );

                        continue;
                    }

                    // El ciclo que produjo S4 no necesita
                    // generar una nueva recomendación.
                    $esCicloFinal = (
                        $seguimiento->estado_actual === 'S4'
                        && $numero === $actual
                    );

                    if (
                        !$esCicloFinal &&
                        !$ciclos->has($numero)
                    ) {
                        $sinCiclosPosteriores++;

                        $this->warn(
                            "Seguimiento #{$seguimiento->id}: "
                            ."falta el ciclo de refuerzo {$numero}."
                        );
                    }
                }

                // Un seguimiento activo debe tener
                // un refuerzo para su ciclo vigente.
                if (
                    $seguimiento->activo &&
                    !$ciclos->has($actual) &&
                    $actual === 0 &&
                    $ciclos->has(0)
                ) {
                    $this->warn(
                        "Seguimiento #{$seguimiento->id}: "
                        ."revisar ciclo vigente."
                    );
                }
            });

        $this->newLine();

        $this->table(
            ['Indicador', 'Cantidad'],
            [
                ['Seguimientos reales', $revisados],
                ['Sin ciclo inicial', $sinCicloInicial],
                ['Ciclos posteriores faltantes', $sinCiclosPosteriores],
                ['Transiciones faltantes', $sinTransiciones],
            ]
        );

        $this->info(
            'Diagnóstico terminado. No se modificaron registros.'
        );

        return self::SUCCESS;
    }
}
