<?php

use App\Services\IOP\MarkovService;

test('una matriz identidad conserva todos los estados', function () {
    $markov = new MarkovService();
    $identidad = [
        [1, 0, 0, 0],
        [0, 1, 0, 0],
        [0, 0, 1, 0],
        [0, 0, 0, 1],
    ];

    $resultado = $markov->proyectar(
        [0, 1, 0, 0],
        $identidad,
        10
    );

    foreach ($resultado as $i => $valor) {
        expect($valor)->toEqualWithDelta(
            $i === 1 ? 1.0 : 0.0,
            0.000001
        );
    }
});

test('S4 permanece absorbente', function () {
    $markov = new MarkovService();
    $matriz = MarkovService::MATRIZ_EJEMPLO;

    foreach ([0, 1, 5, 20, 100] as $n) {
        $resultado = $markov->proyectar(
            [0, 0, 0, 1],
            $matriz,
            $n
        );

        foreach ($resultado as $i => $valor) {
            expect($valor)->toEqualWithDelta(
                $i === 3 ? 1.0 : 0.0,
                0.000001
            );
        }
    }
});

test('las probabilidades suman uno', function () {
    $markov = new MarkovService();
    $matriz = MarkovService::MATRIZ_EJEMPLO;

    foreach ([0, 1, 2, 5, 10, 50] as $n) {
        $resultado = $markov->proyectar(
            [1, 0, 0, 0],
            $matriz,
            $n
        );

        expect(array_sum($resultado))
            ->toEqualWithDelta(1.0, 0.000001);

        foreach ($resultado as $probabilidad) {
            expect($probabilidad)
                ->toBeGreaterThanOrEqual(-0.000001)
                ->toBeLessThanOrEqual(1.000001);
        }
    }
});

test('dos ciclos coinciden con P al cuadrado', function () {
    $markov = new MarkovService();
    $matriz = MarkovService::MATRIZ_EJEMPLO;

    $resultado = $markov->proyectar(
        [1, 0, 0, 0],
        $matriz,
        2
    );

    $esperado = [];

    for ($j = 0; $j < 4; $j++) {
        $suma = 0.0;

        for ($k = 0; $k < 4; $k++) {
            $suma += $matriz[0][$k] * $matriz[$k][$j];
        }

        $esperado[] = $suma;
    }

    foreach ($resultado as $i => $probabilidad) {
        expect($probabilidad)->toEqualWithDelta(
            $esperado[$i],
            0.000001
        );
    }
});

test('rechaza una matriz no estocastica', function () {
    $markov = new MarkovService();
    $matriz = MarkovService::MATRIZ_EJEMPLO;

    $matriz[0] = [0.2, 0.2, 0.2, 0.2];

    $markov->validarMatriz($matriz);
})->throws(InvalidArgumentException::class);

test('rechaza una matriz donde S4 no absorbe', function () {
    $markov = new MarkovService();
    $matriz = MarkovService::MATRIZ_EJEMPLO;

    $matriz[3] = [0.1, 0, 0, 0.9];

    $markov->validarMatriz($matriz);
})->throws(InvalidArgumentException::class);

test('rechaza vectores iniciales invalidos', function () {
    $markov = new MarkovService();
    $markov->validarVector([0.5, 0.5, 0.5, 0]);
})->throws(InvalidArgumentException::class);
