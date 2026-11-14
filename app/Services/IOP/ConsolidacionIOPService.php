<?php

namespace App\Services\IOP;

use InvalidArgumentException;

class ConsolidacionIOPService
{
    public const NOTA_MINIMA = 70;

    public const EVALUACIONES_CONSECUTIVAS = 2;

    /**
     * Determina si un estudiante cumple
     * las condiciones para alcanzar S4.
     *
     * Las transiciones deben estar ordenadas
     * cronológicamente, de más antigua a más reciente.
     *
     * Cada transición contiene:
     * - origen
     * - calificacion_nueva
     */
    public function puedeConsolidar(
        array $transiciones,
        bool $refuerzoVerificado
    ): bool {

        if (!$refuerzoVerificado) {
            return false;
        }

        if (count($transiciones) < self::EVALUACIONES_CONSECUTIVAS) {
            return false;
        }

        $ultimas = array_slice(
            $transiciones,
            -self::EVALUACIONES_CONSECUTIVAS
        );

        foreach ($ultimas as $transicion) {

            if (
                !is_array($transicion) ||
                !array_key_exists('origen', $transicion) ||
                !array_key_exists('calificacion_nueva', $transicion)
            ) {
                throw new InvalidArgumentException(
                    'Transición incompleta.'
                );
            }

            $nota = $transicion['calificacion_nueva'];

            if (
                !is_numeric($nota) ||
                !is_finite((float) $nota) ||
                (float) $nota < 0 ||
                (float) $nota > 100
            ) {
                throw new InvalidArgumentException(
                    'Calificación de transición inválida.'
                );
            }

            if ($transicion['origen'] !== 'evaluacion') {
                return false;
            }

            if ((float) $nota < self::NOTA_MINIMA) {
                return false;
            }
        }

        return true;
    }
}
