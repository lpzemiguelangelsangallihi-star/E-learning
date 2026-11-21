<?php

namespace App\Services\IOP;

use InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use Throwable;

class MaximinService
{
    public const MATRIZ_EJEMPLO = [
        'teorico' => [8, 7, 5],
        'practico' => [7, 9, 8],
        'visual' => [9, 8, 6],
    ];

    public function __construct(
        private MaximinApiClient $apiClient
    ) {}

    public function recomendar(array $matriz): array
    {
        // Validar la matriz antes del cálculo.
        $this->validarMatriz($matriz);

        // Utilizar Spring Boot cuando esté habilitado.
        if (config('services.maximin.enabled', false)) {
            try {
                $resultado = $this->apiClient->recomendar(
                    $matriz
                );

                Log::info(
                    'Maximin calculado mediante Spring Boot',
                    [
                        'alternativa' => $resultado['alternativa'],
                        'valor_maximin' => $resultado['valor_maximin'],
                    ]
                );

                return $resultado;

            } catch (Throwable $e) {
                Log::warning(
                    'Fallo del microservicio Maximin',
                    [
                        'error' => $e->getMessage(),
                    ]
                );
            }
        }

        // Algoritmo original PHP como respaldo.
        return $this->calcularLocal($matriz);
    }

    private function validarMatriz(array $matriz): void
    {
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
        }
    }

    private function calcularLocal(array $matriz): array
    {
        $alternativas = [
            'teorico',
            'practico',
            'visual',
        ];

        $minimos = [];

        foreach ($alternativas as $alternativa) {
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
