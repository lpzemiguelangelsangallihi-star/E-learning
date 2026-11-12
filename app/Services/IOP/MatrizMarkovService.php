<?php

namespace App\Services\IOP;

use App\Models\IopMatrizMarkov;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MatrizMarkovService
{
    public function __construct(
        private MarkovService $markov
    ) {}

    /**
     * Calcula y almacena una nueva versión
     * de la matriz de transición.
     */
    public function generar(
        string $origen = 'simulacion',
        ?int $leccionId = null,
        int $minimoObservaciones = 1
    ): IopMatrizMarkov {

        $estimacion = $this->markov->estimarMatriz(
            $origen,
            $leccionId,
            $minimoObservaciones
        );

        return DB::transaction(function () use (
            $estimacion,
            $origen,
            $leccionId,
            $minimoObservaciones
        ) {
            // Serializamos las versiones mediante
            // un bloqueo de la tabla de origen.
            $ultimaVersion = IopMatrizMarkov::query()
                ->where('origen', $origen)
                ->where('leccion_id', $leccionId)
                ->max('version');

            $nuevaVersion = ($ultimaVersion ?? 0) + 1;

            return IopMatrizMarkov::create([
                'leccion_id' => $leccionId,
                'origen' => $origen,
                'version' => $nuevaVersion,
                'conteos' => $estimacion['conteos'],
                'probabilidades' =>
                    $estimacion['probabilidades'],
                'totales' => $estimacion['totales'],
                'completa' => $estimacion['completa'],
                'minimo_observaciones' =>
                    $minimoObservaciones,
                'fecha_calculo' => now(),
            ]);
        });
    }

    /**
     * Proyecta ciclos con una matriz almacenada.
     */
    public function proyectar(
        int $matrizId,
        array $vectorInicial,
        int $ciclos
    ): array {
        $matriz = IopMatrizMarkov::findOrFail($matrizId);

        if (!$matriz->completa) {
            throw new InvalidArgumentException(
                'No se puede proyectar con una matriz incompleta.'
            );
        }

        return $this->markov->proyectar(
            $vectorInicial,
            $matriz->probabilidades,
            $ciclos
        );
    }
}
