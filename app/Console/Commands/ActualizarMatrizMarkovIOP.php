<?php

namespace App\Console\Commands;

use App\Models\Leccion;
use App\Services\IOP\MarkovService;
use App\Services\IOP\MatrizMarkovService;
use Illuminate\Console\Command;

class ActualizarMatrizMarkovIOP extends Command
{
    protected $signature = 'iop:actualizar-markov
        {leccionId : ID de la lección}
        {--execute : Guardar una versión si corresponde}';

    protected $description =
        'Consulta o actualiza la matriz Markov real de una lección';

    public function handle(
        MarkovService $markov,
        MatrizMarkovService $matrices
    ): int {
        $id = filter_var(
            $this->argument('leccionId'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false) {
            $this->error('ID de lección inválido.');
            return self::FAILURE;
        }

        $leccion = Leccion::find($id);

        if (!$leccion) {
            $this->error('La lección no existe.');
            return self::FAILURE;
        }

        $estimacion = $markov->estimarMatriz(
            'evaluacion',
            $id,
            1
        );

        $this->info(
            "Lección #{$id}: {$leccion->titulo}"
        );

        $this->table(
            ['Estado', 'Observaciones'],
            [
                ['S1', $estimacion['totales'][0]],
                ['S2', $estimacion['totales'][1]],
                ['S3', $estimacion['totales'][2]],
                ['S4', $estimacion['totales'][3]],
            ]
        );

        $this->line(
            'Matriz completa: ' .
            ($estimacion['completa'] ? 'Sí' : 'No')
        );

        if (!$this->option('execute')) {
            $this->comment(
                'Modo consulta. No se guardaron cambios.'
            );
            return self::SUCCESS;
        }

        $resultado = $matrices->actualizarLeccion($id);

        $accion = $resultado['accion'];
        $matriz = $resultado['matriz'];

        $this->info("Resultado: {$accion}");

        if ($matriz) {
            $this->line(
                "Versión: {$matriz->version}"
            );

            $this->line(
                'Completa: ' .
                ($matriz->completa ? 'Sí' : 'No')
            );
        }

        return self::SUCCESS;
    }
}
