<?php

namespace App\Services\IOP;

use InvalidArgumentException;

class MatrizAdaptativaService
{
    /**
     * Política experimental de adaptación.
     *
     * Se añade una bonificación uniforme a los
     * escenarios de una alternativa, según la nota.
     *
     * La matriz original nunca se modifica.
     */
    public function adaptar(
        array $matriz,
        float $calificacion
    ): array {
        if (
            !is_finite($calificacion) ||
            $calificacion < 0 ||
            $calificacion > 100
        ) {
            throw new InvalidArgumentException(
                'La calificación debe estar entre 0 y 100.'
            );
        }

        $bonificaciones = match (true) {
            $calificacion <= 50 => [
                'teorico' => 4,
                'practico' => 0,
                'visual' => 0,
            ],
            $calificacion < 70 => [
                'teorico' => 0,
                'practico' => 2,
                'visual' => 0,
            ],
            default => [
                'teorico' => 0,
                'practico' => 0,
                'visual' => 2,
            ],
        };

        $adaptada = [];

        foreach (
            ['teorico', 'practico', 'visual']
            as $alternativa
        ) {
            if (
                !isset($matriz[$alternativa]) ||
                !is_array($matriz[$alternativa]) ||
                count($matriz[$alternativa]) !== 3
            ) {
                throw new InvalidArgumentException(
                    "Matriz inválida para {$alternativa}."
                );
            }

            $adaptada[$alternativa] = [];

            foreach ($matriz[$alternativa] as $valor) {
                if (
                    !is_numeric($valor) ||
                    !is_finite((float) $valor)
                ) {
                    throw new InvalidArgumentException(
                        'La matriz contiene utilidades inválidas.'
                    );
                }

                $adaptada[$alternativa][] =
                    (float) $valor
                    + $bonificaciones[$alternativa];
            }
        }

        return $adaptada;
    }
}
