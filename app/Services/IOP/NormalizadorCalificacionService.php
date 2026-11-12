<?php

namespace App\Services\IOP;

use InvalidArgumentException;

class NormalizadorCalificacionService
{
    /**
     * Convierte una calificación a la escala 0-100.
     *
     * Ejemplo:
     * 40 puntos de 50 posibles = 80/100.
     *
     * Devolvemos una nota entera para mantener
     * los rangos S1: 0-50, S2: 51-69, S3: 70-100.
     */
    public function normalizar(
        float $calificacion,
        float $puntajeMaximo
    ): int {
        if (
            !is_finite($calificacion) ||
            !is_finite($puntajeMaximo) ||
            $puntajeMaximo <= 0 ||
            $calificacion < 0 ||
            $calificacion > $puntajeMaximo
        ) {
            throw new InvalidArgumentException(
                'La calificación o el puntaje máximo son inválidos.'
            );
        }

        $porcentaje = (
            $calificacion / $puntajeMaximo
        ) * 100;

        // Regla de redondeo adoptada para IOP.
        return (int) round(
            $porcentaje,
            0,
            PHP_ROUND_HALF_UP
        );
    }
}
