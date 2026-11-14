<?php

namespace App\Services\IOP;

use App\Models\IopMatrizMarkov;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use App\Models\Leccion;
use Illuminate\Validation\ValidationException;

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

    /**
     * Publica una nueva versión estadística solamente
     * cuando cambian los conteos de transiciones.
     *
     * La lección se bloquea para serializar versiones
     * de este procedimiento.
     */
    public function actualizarLeccion(
        int $leccionId,
        int $minimoObservaciones = 1
    ): array {
        if ($minimoObservaciones < 1) {
            throw new InvalidArgumentException(
                'El mínimo de observaciones debe ser positivo.'
            );
        }

        return DB::transaction(function () use (
            $leccionId,
            $minimoObservaciones
        ) {
            Leccion::query()
                ->whereKey($leccionId)
                ->lockForUpdate()
                ->firstOrFail();

            $estimacion = $this->markov->estimarMatriz(
                'evaluacion',
                $leccionId,
                $minimoObservaciones
            );

            $ultima = IopMatrizMarkov::query()
                ->where('leccion_id', $leccionId)
                ->where('origen', 'evaluacion')
                ->orderByDesc('version')
                ->orderByDesc('id')
                ->first();

            // No publicar una versión sin observaciones.
            if (array_sum($estimacion['totales']) === 0) {
                return [
                    'accion' => 'sin_datos',
                    'matriz' => $ultima,
                ];
            }

            // No crear otra versión con los mismos datos
            // y la misma política de estimación.
            if (
                $ultima &&
                $ultima->conteos === $estimacion['conteos'] &&
                $ultima->probabilidades === $estimacion['probabilidades'] &&
                (int) $ultima->minimo_observaciones
                    === $minimoObservaciones
            ) {
                return [
                    'accion' => 'sin_cambios',
                    'matriz' => $ultima,
                ];
            }

            $matriz = IopMatrizMarkov::create([
                'leccion_id' => $leccionId,
                'origen' => 'evaluacion',
                'version' => ($ultima?->version ?? 0) + 1,
                'conteos' => $estimacion['conteos'],
                'probabilidades' =>
                    $estimacion['probabilidades'],
                'totales' => $estimacion['totales'],
                'completa' => $estimacion['completa'],
                'minimo_observaciones' =>
                    $minimoObservaciones,
                'fecha_calculo' => now(),
            ]);

            return [
                'accion' => 'creada',
                'matriz' => $matriz,
            ];
        }, 3);
    }
}
