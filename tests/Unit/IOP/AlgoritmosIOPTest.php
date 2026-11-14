<?php

use App\Services\IOP\MaximinService;
use App\Services\IOP\MarkovService;
use App\Services\IOP\NormalizadorCalificacionService;
use App\Services\IOP\SeguimientoService;

/*
|--------------------------------------------------------------------------
| PRUEBAS DE MAXIMIN
|--------------------------------------------------------------------------
*/

test('Maximin selecciona la alternativa con mayor utilidad minima', function () {
    $servicio = new MaximinService();

    $resultado = $servicio->recomendar(
        MaximinService::MATRIZ_EJEMPLO
    );

    expect($resultado['alternativa'])->toBe('practico')
        ->and((float) $resultado['valor_maximin'])->toBe(7.0)
        ->and($resultado['minimos'])->toEqual([
            'teorico' => 5,
            'practico' => 7,
            'visual' => 6,
        ]);
});

test('Maximin rechaza matrices incompletas', function () {
    $servicio = new MaximinService();

    $servicio->recomendar([
        'teorico' => [8, 7, 5],
        'practico' => [7, 9, 8],
    ]);
})->throws(InvalidArgumentException::class);

test('Maximin mantiene un desempate determinista', function () {
    $servicio = new MaximinService();

    $resultado = $servicio->recomendar([
        'teorico' => [7, 8, 9],
        'practico' => [7, 9, 8],
        'visual' => [5, 6, 7],
    ]);

    // Ante igualdad, el algoritmo actual
    // prioriza el orden teorico, practico, visual.
    expect($resultado['alternativa'])->toBe('teorico');
});

/*
|--------------------------------------------------------------------------
| PRUEBAS DE NORMALIZACION DE CALIFICACIONES
|--------------------------------------------------------------------------
*/

test('Normaliza calificaciones a escala de cien puntos', function () {
    $servicio = new NormalizadorCalificacionService();

    expect($servicio->normalizar(40, 50))->toBe(80)
        ->and($servicio->normalizar(17, 20))->toBe(85)
        ->and($servicio->normalizar(32.5, 50))->toBe(65)
        ->and($servicio->normalizar(0, 50))->toBe(0)
        ->and($servicio->normalizar(50, 50))->toBe(100);
});

test('Normaliza notas usando redondeo al entero mas cercano', function () {
    $servicio = new NormalizadorCalificacionService();

    expect($servicio->normalizar(50.49, 100))->toBe(50)
        ->and($servicio->normalizar(50.50, 100))->toBe(51)
        ->and($servicio->normalizar(69.50, 100))->toBe(70);
});

test('Rechaza calificaciones y puntajes maximos invalidos', function () {
    $servicio = new NormalizadorCalificacionService();

    foreach ([
        [-1, 100],
        [110, 100],
        [20, 0],
        [20, -5],
    ] as [$nota, $maximo]) {
        try {
            $servicio->normalizar($nota, $maximo);

            test()->fail(
                'Se esperaba rechazar una calificacion invalida.'
            );
        } catch (InvalidArgumentException $e) {
            expect($e->getMessage())->not->toBeEmpty();
        }
    }
});

/*
|--------------------------------------------------------------------------
| PRUEBAS DE ESTADOS DE MARKOV
|--------------------------------------------------------------------------
*/

test('Clasifica correctamente los estados S1 S2 y S3', function () {
    $servicio = new SeguimientoService();

    expect($servicio->clasificarEstado(0))->toBe('S1')
        ->and($servicio->clasificarEstado(50))->toBe('S1')
        ->and($servicio->clasificarEstado(51))->toBe('S2')
        ->and($servicio->clasificarEstado(69))->toBe('S2')
        ->and($servicio->clasificarEstado(70))->toBe('S3')
        ->and($servicio->clasificarEstado(100))->toBe('S3');
});

test('Genera correctamente el vector inicial', function () {
    $servicio = new SeguimientoService();

    expect($servicio->generarVectorInicial('S1'))
        ->toEqual([1, 0, 0, 0])
        ->and($servicio->generarVectorInicial('S2'))
        ->toEqual([0, 1, 0, 0])
        ->and($servicio->generarVectorInicial('S3'))
        ->toEqual([0, 0, 1, 0])
        ->and($servicio->generarVectorInicial('S4'))
        ->toEqual([0, 0, 0, 1]);
});

/*
|--------------------------------------------------------------------------
| PRUEBAS DE MATRICES Y PROYECCIONES
|--------------------------------------------------------------------------
*/

test('La matriz de Markov tiene filas estocasticas', function () {
    $servicio = new MarkovService();

    $matriz = MarkovService::MATRIZ_EJEMPLO;

    $servicio->validarMatriz($matriz);

    foreach ($matriz as $fila) {
        expect(array_sum($fila))
            ->toBeGreaterThanOrEqual(0.999999)
            ->toBeLessThanOrEqual(1.000001);
    }
});

test('Rechaza matrices con probabilidades invalidas', function () {
    $servicio = new MarkovService();

    $matriz = MarkovService::MATRIZ_EJEMPLO;

    $matriz[0] = [0.4, 0.4, 0.1, 0.0];

    $servicio->validarMatriz($matriz);
})->throws(InvalidArgumentException::class);

test('La proyeccion a cero ciclos conserva el vector inicial', function () {
    $servicio = new MarkovService();

    $resultado = $servicio->proyectar(
        [1, 0, 0, 0],
        MarkovService::MATRIZ_EJEMPLO,
        0
    );

    expect($resultado)->toEqual([1, 0, 0, 0]);
});

test('La proyeccion a un ciclo coincide con la primera fila de P', function () {
    $servicio = new MarkovService();

    $resultado = $servicio->proyectar(
        [1, 0, 0, 0],
        MarkovService::MATRIZ_EJEMPLO,
        1
    );

    foreach ($resultado as $i => $probabilidad) {
        expect($probabilidad)->toEqualWithDelta(
            MarkovService::MATRIZ_EJEMPLO[0][$i],
            0.000001
        );
    }
});

test('La proyeccion conserva una distribucion de probabilidades', function () {
    $servicio = new MarkovService();

    $resultado = $servicio->proyectar(
        [1, 0, 0, 0],
        MarkovService::MATRIZ_EJEMPLO,
        10
    );

    expect(array_sum($resultado))
        ->toEqualWithDelta(1.0, 0.000001);

    foreach ($resultado as $probabilidad) {
        expect($probabilidad)
            ->toBeGreaterThanOrEqual(0.0)
            ->toBeLessThanOrEqual(1.0);
    }
});

test('S4 permanece absorbente independientemente de los ciclos', function () {
    $servicio = new MarkovService();

    foreach ([0, 1, 3, 10, 20] as $ciclos) {
        $resultado = $servicio->proyectar(
            [0, 0, 0, 1],
            MarkovService::MATRIZ_EJEMPLO,
            $ciclos
        );

        foreach ($resultado as $i => $valor) {
            expect($valor)->toEqualWithDelta(
                $i === 3 ? 1.0 : 0.0,
                0.000001
            );
        }
    }
});

test('Markov rechaza un numero negativo de ciclos', function () {
    $servicio = new MarkovService();

    $servicio->proyectar(
        [1, 0, 0, 0],
        MarkovService::MATRIZ_EJEMPLO,
        -1
    );
})->throws(InvalidArgumentException::class);
