<?php

namespace App\Services\IOP;

use App\Models\IopEjercicioRefuerzo;
use App\Models\IopRecomendacion;
use InvalidArgumentException;

class EjercicioRefuerzoService
{
    /**
     * Categorías compatibles con Maximin.
     */
    public const TIPOS = [
        'teorico',
        'practico',
        'visual',
    ];

    /**
     * Obtiene ejercicios activos de una lección
     * correspondientes a la categoría recomendada.
     */
    public function buscar(
        int $leccionId,
        string $tipo
    ) {
        if (!in_array($tipo, self::TIPOS, true)) {
            throw new InvalidArgumentException(
                'Tipo de ejercicio de refuerzo inválido.'
            );
        }

        return IopEjercicioRefuerzo::query()
            ->where('leccion_id', $leccionId)
            ->where('tipo', $tipo)
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();
    }

    /**
     * Obtiene ejercicios a partir de una recomendación
     * Maximin que ya está registrada.
     */
    public function buscarPorRecomendacion(
        int $recomendacionId
    ) {
        $recomendacion = IopRecomendacion::findOrFail(
            $recomendacionId
        );

        return $this->buscar(
            (int) $recomendacion->leccion_id,
            $recomendacion->alternativa_recomendada
        );
    }
}
