<?php

namespace App\Services\IOP;

use InvalidArgumentException;

class MaximinService
{
    public const MATRIZ_EJEMPLO = [
        'teorico' => [8, 7, 5],
        'practico' => [7, 9, 8],
        'visual' => [9, 8, 6],
    ];

    public function recomendar(array $matriz): array
    {
        $alternativas = [
            'teorico',
            'practico',
            'visual',
        ];

        $minimos = [];

        foreach ($alternativas as $alternativa) {
            if (
                !isset($matriz[$alternativa]) ||
                !is_array($matriz[$alternativa]) ||
                count($matriz[$alternativa]) !== 3
            ) {
                throw new InvalidArgumentException(
                    "Matriz inválida para {$alternativa}"
                );
            }

            foreach ($matriz[$alternativa] as $valor) {
                if (
                    !is_numeric($valor) ||
                    !is_finite((float) $valor)
                ) {
                    throw new InvalidArgumentException(
                        'Las utilidades deben ser valores numéricos finitos.'
                    );
                }
            }

            $minimos[$alternativa] = min(
                $matriz[$alternativa]
            );
        }

        $mejorValor = max($minimos);

        $seleccionada = null;

        foreach ($alternativas as $alternativa) {
            if ($minimos[$alternativa] == $mejorValor) {
                $seleccionada = $alternativa;
                break;
            }
        }

        return [
            'alternativa' => $seleccionada,
            'valor_maximin' => $mejorValor,
            'minimos' => $minimos,
        ];
    }
}
