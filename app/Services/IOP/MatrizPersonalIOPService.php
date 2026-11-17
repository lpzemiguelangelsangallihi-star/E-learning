<?php

namespace App\Services\IOP;

use App\Models\IopSeguimiento;

class MatrizPersonalIOPService
{
    public const TIPOS = [
        'teorico',
        'practico',
        'visual',
    ];

    /**
     * Reconstruye la evidencia individual
     * sin modificar el historial académico.
     */
    public function evidencia(
        int $estudianteId,
        int $leccionId
    ): array {
        $datos = [];

        foreach (self::TIPOS as $tipo) {
            $datos[$tipo] = [
                'observaciones' => 0,
                'mejora_total' => 0.0,
                'mejora_promedio' => null,
            ];
        }

        $seguimiento = IopSeguimiento::query()
            ->where('estudiante_id', $estudianteId)
            ->where('leccion_id', $leccionId)
            ->with([
                'transiciones',
                'ciclosRefuerzo.recomendacion.asignacionRefuerzo',
            ])
            ->first();

        if (!$seguimiento) {
            return $datos;
        }

        $ciclos = $seguimiento->ciclosRefuerzo
            ->keyBy('ciclo');

        foreach ($seguimiento->transiciones as $transicion) {
            if ($transicion->origen !== 'evaluacion') {
                continue;
            }

            $cicloAnterior = $ciclos->get(
                (int) $transicion->ciclo - 1
            );

            $recomendacion = $cicloAnterior?->recomendacion;
            $asignacion = $recomendacion?->asignacionRefuerzo;
            $tipo = $recomendacion?->alternativa_recomendada;

            if (
                !in_array($tipo, self::TIPOS, true) ||
                !$asignacion ||
                $asignacion->estado !== 'completado' ||
                !$asignacion->revisado_por ||
                !$asignacion->completado_en
            ) {
                continue;
            }

            $mejora = (float) $transicion->calificacion_nueva
                - (float) $transicion->calificacion_anterior;

            $datos[$tipo]['observaciones']++;
            $datos[$tipo]['mejora_total'] += $mejora;
        }

        foreach (self::TIPOS as $tipo) {
            $cantidad = $datos[$tipo]['observaciones'];

            if ($cantidad > 0) {
                $datos[$tipo]['mejora_promedio'] = round(
                    $datos[$tipo]['mejora_total'] / $cantidad,
                    2
                );
            }
        }

        return $datos;
    }

    /**
     * Combina las utilidades iniciales con
     * resultados individuales.
     *
     * Las bonificaciones exploratorias son
     * una política provisional, no datos reales.
     */
    public function adaptar(
        array $matrizInicial,
        int $estudianteId,
        int $leccionId
    ): array {
        $evidencia = $this->evidencia(
            $estudianteId,
            $leccionId
        );

        $total = array_sum(
            array_column($evidencia, 'observaciones')
        );

        // Sin historial: conservar la política inicial.
        if ($total === 0) {
            return $matrizInicial;
        }

        $matriz = [];

        foreach (self::TIPOS as $tipo) {
            $cantidad = $evidencia[$tipo]['observaciones'];
            $mejora = $evidencia[$tipo]['mejora_promedio'];

            // Prioridad de exploración para alternativas
            // que todavía no tienen evaluaciones.
            $exploracion = $cantidad === 0 ? 20.0 : 0.0;

            // Una mejora de 30 puntos aporta +3
            // a la utilidad, por ejemplo.
            $bonificacion = $cantidad > 0
                ? (float) $mejora / 10.0
                : 0.0;

            $matriz[$tipo] = array_map(
                fn ($valor) => round(
                    (float) $valor
                    + $bonificacion
                    + $exploracion,
                    4
                ),
                $matrizInicial[$tipo]
            );
        }

        return $matriz;
    }
}
