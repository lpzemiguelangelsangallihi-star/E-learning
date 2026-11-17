<?php

use App\Services\IOP\MaximinService;

test('Maximin selecciona la mejor alternativa', function () {
    $servicio = new MaximinService();

    $matriz = [
        'teorico' => [8, 7, 5],
        'practico' => [7, 9, 8],
        'visual' => [9, 8, 6],
    ];

    $resultado = $servicio->recomendar($matriz);

    expect($resultado['minimos'])->toBe([
        'teorico' => 5,
        'practico' => 7,
        'visual' => 6,
    ]);

    expect($resultado['alternativa'])->toBe('practico');
    expect((float) $resultado['valor_maximin'])->toBe(7.0);
});

test('Maximin permite utilidades negativas', function () {
    $servicio = new MaximinService();

    $resultado = $servicio->recomendar([
        'teorico' => [-5, -3, -4],
        'practico' => [-2, -1, -3],
        'visual' => [-8, -2, -1],
    ]);

    expect($resultado['alternativa'])->toBe('practico');
    expect((float) $resultado['valor_maximin'])->toBe(-3.0);
});

test('Maximin rechaza una matriz incompleta', function () {
    $servicio = new MaximinService();

    $servicio->recomendar([
        'teorico' => [1, 2, 3],
        'practico' => [4, 5, 6],
    ]);
})->throws(InvalidArgumentException::class);

test('Maximin rechaza utilidades no finitas', function () {
    $servicio = new MaximinService();

    $servicio->recomendar([
        'teorico' => [1, INF, 3],
        'practico' => [4, 5, 6],
        'visual' => [7, 8, 9],
    ]);
})->throws(InvalidArgumentException::class);

test('Maximin resuelve empates de forma estable', function () {
    $servicio = new MaximinService();

    $resultado = $servicio->recomendar([
        'teorico' => [7, 8, 9],
        'practico' => [7, 9, 8],
        'visual' => [5, 6, 7],
    ]);

    expect($resultado['alternativa'])->toBe('teorico');
});
