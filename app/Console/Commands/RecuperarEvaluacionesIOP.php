<?php

namespace App\Console\Commands;

use App\Models\Examen;
use App\Models\IntentoExamen;
use App\Models\IopSeguimiento;
use App\Models\IopTransicion;
use App\Services\IOP\IntegracionEvaluacionIOPService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RecuperarEvaluacionesIOP extends Command
{
    protected $signature = 'iop:recuperar-evaluaciones
                            {--execute : Procesar los intentos pendientes}
                            {--limit=100 : Máximo de pendientes por ejecución}';

    protected $description =
        'Detecta y recupera evaluaciones IOP finalizadas no procesadas';

    public function handle(
        IntegracionEvaluacionIOPService $integracion
    ): int {
        $ejecutar = (bool) $this->option('execute');

        $limite = filter_var(
            $this->option('limit'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($limite === false || $limite > 1000) {
            $this->error(
                'El límite debe estar entre 1 y 1000.'
            );

            return self::FAILURE;
        }

        $this->info(
            $ejecutar
                ? 'Modo ejecución: recuperación de IOP.'
                : 'Modo consulta: no se modificarán datos.'
        );

        $consultados = 0;
        $pendientes = 0;
        $recuperados = 0;
        $fallidos = 0;

        // Orden cronológico para conservar la secuencia
        // de diagnóstico y evaluaciones posteriores.
        $intentos = IntentoExamen::query()
            ->with('examen')
            ->where('estado', 'finalizado')
            ->whereNotNull('fecha_fin')
            ->whereNotNull('calificacion')
            ->whereHas('examen', function ($query) {
                $query->whereIn('tipo_iop', [
                    Examen::IOP_DIAGNOSTICO,
                    Examen::IOP_REFUERZO,
                ]);
            })
            ->orderBy('fecha_fin')
            ->orderBy('id')
            ->lazy(100);

        foreach ($intentos as $intento) {
            $consultados++;

            $tipo = $intento->examen->tipo_iop;

            if ($tipo === Examen::IOP_DIAGNOSTICO) {
                $procesado = IopSeguimiento::query()
                    ->where(
                        'intento_diagnostico_id',
                        $intento->id
                    )
                    ->exists();
            } else {
                $procesado = IopTransicion::query()
                    ->where(
                        'referencia_externa',
                        'intento_examen:' . $intento->id
                    )
                    ->exists();
            }

            if ($procesado) {
                continue;
            }

            if ($pendientes >= $limite) {
                break;
            }

            $pendientes++;

            $descripcion = sprintf(
                'Intento #%d | Estudiante #%d | %s',
                $intento->id,
                $intento->estudiante_id,
                $tipo
            );

            if (!$ejecutar) {
                $this->line(
                    'PENDIENTE: ' . $descripcion
                );

                continue;
            }

            try {
                if ($tipo === Examen::IOP_DIAGNOSTICO) {
                    $integracion->procesarDiagnostico(
                        (int) $intento->id
                    );
                } else {
                    $integracion->procesarSeguimiento(
                        (int) $intento->id
                    );
                }

                $recuperados++;

                $this->info(
                    'RECUPERADO: ' . $descripcion
                );
            } catch (\Throwable $e) {
                $fallidos++;

                $this->warn(
                    'NO PROCESADO: ' . $descripcion
                    . ' | ' . $e->getMessage()
                );

                Log::warning(
                    'Recuperación de evaluación IOP fallida.',
                    [
                        'intento_id' => $intento->id,
                        'tipo_iop' => $tipo,
                        'error' => $e->getMessage(),
                    ]
                );
            }
        }

        $this->newLine();

        $this->table(
            ['Concepto', 'Cantidad'],
            [
                ['Intentos examinados', $consultados],
                ['Pendientes detectados', $pendientes],
                ['Recuperados', $recuperados],
                ['Fallidos', $fallidos],
            ]
        );

        if (!$ejecutar) {
            $this->comment(
                'Para procesar, utiliza --execute.'
            );
        }

        return $fallidos > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
