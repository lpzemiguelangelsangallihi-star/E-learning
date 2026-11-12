<?php

namespace App\Services\IOP;

use InvalidArgumentException;
use Illuminate\Support\Facades\DB;

class MarkovService
{
    public const MATRIZ_EJEMPLO = [
        [0.40, 0.45, 0.15, 0.00],
        [0.15, 0.45, 0.30, 0.10],
        [0.05, 0.15, 0.50, 0.30],
        [0.00, 0.00, 0.00, 1.00],
    ];

    /**
     * Verifica que P sea una matriz estocástica 4x4.
     */
    public function validarMatriz(array $matriz): void
    {
        if (count($matriz) !== 4) {
            throw new InvalidArgumentException(
                'La matriz debe tener 4 filas.'
            );
        }

        foreach ($matriz as $fila) {
            if (!is_array($fila) || count($fila) !== 4) {
                throw new InvalidArgumentException(
                    'Cada fila debe tener 4 columnas.'
                );
            }

            $suma = 0.0;

            foreach ($fila as $probabilidad) {
                if (
                    !is_numeric($probabilidad) ||
                    !is_finite((float) $probabilidad) ||
                    $probabilidad < 0 ||
                    $probabilidad > 1
                ) {
                    throw new InvalidArgumentException(
                        'Probabilidad inválida.'
                    );
                }

                $suma += (float) $probabilidad;
            }

            if (abs($suma - 1.0) > 0.000001) {
                throw new InvalidArgumentException(
                    'Cada fila debe sumar 1.'
                );
            }
        }

        // S4 es absorbente en el modelo de refuerzo.
        $absorbente = [0, 0, 0, 1];

        foreach ($absorbente as $j => $valor) {
            if (abs($matriz[3][$j] - $valor) > 0.000001) {
                throw new InvalidArgumentException(
                    'S4 debe ser un estado absorbente.'
                );
            }
        }
    }

    /**
     * Valida una distribución de probabilidad a0.
     */
    public function validarVector(array $vector): void
    {
        if (count($vector) !== 4) {
            throw new InvalidArgumentException(
                'El vector inicial debe tener 4 valores.'
            );
        }

        $suma = 0.0;

        foreach ($vector as $probabilidad) {
            if (
                !is_numeric($probabilidad) ||
                !is_finite((float) $probabilidad) ||
                $probabilidad < 0 ||
                $probabilidad > 1
            ) {
                throw new InvalidArgumentException(
                    'Vector de probabilidades inválido.'
                );
            }

            $suma += (float) $probabilidad;
        }

        if (abs($suma - 1.0) > 0.000001) {
            throw new InvalidArgumentException(
                'El vector debe sumar 1.'
            );
        }
    }

    /**
     * Multiplica dos matrices cuadradas 4x4.
     */
    private function multiplicarMatrices(
        array $a,
        array $b
    ): array {
        $resultado = array_fill(
            0, 4, array_fill(0, 4, 0.0)
        );

        for ($i = 0; $i < 4; $i++) {
            for ($j = 0; $j < 4; $j++) {
                for ($k = 0; $k < 4; $k++) {
                    $resultado[$i][$j] +=
                        $a[$i][$k] * $b[$k][$j];
                }
            }
        }

        return $resultado;
    }

    /**
     * Calcula P^n mediante exponenciación binaria.
     */
    public function potenciaMatriz(
        array $matriz,
        int $n
    ): array {
        $this->validarMatriz($matriz);

        if ($n < 0) {
            throw new InvalidArgumentException(
                'El número de ciclos no puede ser negativo.'
            );
        }

        $resultado = [
            [1, 0, 0, 0],
            [0, 1, 0, 0],
            [0, 0, 1, 0],
            [0, 0, 0, 1],
        ];

        $base = $matriz;

        while ($n > 0) {
            if ($n % 2 === 1) {
                $resultado = $this->multiplicarMatrices(
                    $resultado,
                    $base
                );
            }

            $base = $this->multiplicarMatrices(
                $base,
                $base
            );

            $n = intdiv($n, 2);
        }

        return $resultado;
    }

    /**
     * Calcula an = a0 * P^n.
     */
    public function proyectar(
        array $vectorInicial,
        array $matriz,
        int $ciclos
    ): array {
        $this->validarVector($vectorInicial);

        $potencia = $this->potenciaMatriz(
            $matriz,
            $ciclos
        );

        $resultado = array_fill(0, 4, 0.0);

        for ($j = 0; $j < 4; $j++) {
            for ($i = 0; $i < 4; $i++) {
                $resultado[$j] +=
                    $vectorInicial[$i] * $potencia[$i][$j];
            }
        }

        return $resultado;
    }
    /**
     * Estima P utilizando las transiciones almacenadas.
     *
     * $origen:
     * - simulacion: datos de prueba
     * - evaluacion: evaluaciones reales
     *
     * Si una fila no tiene observaciones suficientes,
     * se devuelve null para esa fila.
     */
    public function estimarMatriz(
        string $origen = 'simulacion',
        ?int $leccionId = null,
        int $minimoObservaciones = 1
    ): array {
        if (!in_array($origen, ['simulacion', 'evaluacion'], true)) {
            throw new InvalidArgumentException(
                'Origen de datos inválido.'
            );
        }

        if ($minimoObservaciones < 1) {
            throw new InvalidArgumentException(
                'El mínimo de observaciones debe ser positivo.'
            );
        }

        $estados = ['S1', 'S2', 'S3', 'S4'];

        // Matriz de conteos, inicialmente en cero.
        $conteos = array_fill(
            0,
            4,
            array_fill(0, 4, 0)
        );

        $consulta = DB::table('iop_transiciones as t')
            ->join(
                'iop_seguimientos as s',
                's.id',
                '=',
                't.seguimiento_id'
            )
            ->where('t.origen', $origen);

        // Opcional: estimar solamente para una lección.
        if ($leccionId !== null) {
            $consulta->where('s.leccion_id', $leccionId);
        }

        $registros = $consulta
            ->select(
                't.estado_anterior',
                't.estado_siguiente'
            )
            ->selectRaw('COUNT(*) as cantidad')
            ->groupBy(
                't.estado_anterior',
                't.estado_siguiente'
            )
            ->get();

        // Convertir los registros en conteos por estado.
        foreach ($registros as $registro) {
            $i = array_search(
                $registro->estado_anterior,
                $estados,
                true
            );

            $j = array_search(
                $registro->estado_siguiente,
                $estados,
                true
            );

            if ($i === false || $j === false) {
                throw new InvalidArgumentException(
                    'Existen transiciones con estados inválidos.'
                );
            }

            $conteos[$i][$j] = (int) $registro->cantidad;
        }

        $probabilidades = [];
        $totales = [];

        for ($i = 0; $i < 4; $i++) {

            $total = array_sum($conteos[$i]);
            $totales[] = $total;

            // S4 es absorbente por definición del modelo.
            if ($i === 3) {
                $probabilidades[] = [0.0, 0.0, 0.0, 1.0];
                continue;
            }

            // No inventar una distribución cuando
            // faltan observaciones.
            if ($total < $minimoObservaciones) {
                $probabilidades[] = null;
                continue;
            }

            $fila = [];

            for ($j = 0; $j < 4; $j++) {
                $fila[] = $conteos[$i][$j] / $total;
            }

            $probabilidades[] = $fila;
        }

        // Para proyectar se necesitan las tres filas
        // transitorias completamente estimadas.
        $completa = true;

        for ($i = 0; $i < 3; $i++) {
            if ($probabilidades[$i] === null) {
                $completa = false;
                break;
            }
        }

        return [
            'origen' => $origen,
            'leccion_id' => $leccionId,
            'conteos' => $conteos,
            'totales' => $totales,
            'probabilidades' => $probabilidades,
            'completa' => $completa,
            'minimo_observaciones' => $minimoObservaciones,
        ];
    }

}
