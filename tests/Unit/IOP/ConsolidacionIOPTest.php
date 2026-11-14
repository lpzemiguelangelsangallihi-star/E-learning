
<?php

use App\Services\IOP\ConsolidacionIOPService;

beforeEach(function () {
    $this->servicio = new ConsolidacionIOPService();
});

test('consolida dos evaluaciones reales aprobadas con refuerzo verificado', function () {

    $transiciones = [
        [
            'origen' => 'evaluacion',
            'calificacion_nueva' => 76,
        ],
        [
            'origen' => 'evaluacion',
            'calificacion_nueva' => 82,
        ],
    ];

    expect(
        $this->servicio->puedeConsolidar(
            $transiciones,
            true
        )
    )->toBeTrue();
});

test('no consolida si el refuerzo no fue aprobado', function () {

    $transiciones = [
        [
            'origen' => 'evaluacion',
            'calificacion_nueva' => 80,
        ],
        [
            'origen' => 'evaluacion',
            'calificacion_nueva' => 90,
        ],
    ];

    expect(
        $this->servicio->puedeConsolidar(
            $transiciones,
            false
        )
    )->toBeFalse();
});

test('no consolida con una sola evaluacion', function () {

    $transiciones = [
        [
            'origen' => 'evaluacion',
            'calificacion_nueva' => 95,
        ],
    ];

    expect(
        $this->servicio->puedeConsolidar(
            $transiciones,
            true
        )
    )->toBeFalse();
});

test('no consolida si una evaluacion es simulada', function () {

    $transiciones = [
        [
            'origen' => 'evaluacion',
            'calificacion_nueva' => 85,
        ],
        [
            'origen' => 'simulacion',
            'calificacion_nueva' => 90,
        ],
    ];

    expect(
        $this->servicio->puedeConsolidar(
            $transiciones,
            true
        )
    )->toBeFalse();
});

test('no consolida si la ultima evaluacion es insuficiente', function () {

    $transiciones = [
        [
            'origen' => 'evaluacion',
            'calificacion_nueva' => 85,
        ],
        [
            'origen' => 'evaluacion',
            'calificacion_nueva' => 65,
        ],
    ];

    expect(
        $this->servicio->puedeConsolidar(
            $transiciones,
            true
        )
    )->toBeFalse();
});

test('consolida exactamente en el limite de 70', function () {

    $transiciones = [
        [
            'origen' => 'evaluacion',
            'calificacion_nueva' => 70,
        ],
        [
            'origen' => 'evaluacion',
            'calificacion_nueva' => 70,
        ],
    ];

    expect(
        $this->servicio->puedeConsolidar(
            $transiciones,
            true
        )
    )->toBeTrue();
});

test('una evaluacion simulada intermedia interrumpe la consecutividad', function () {

    $transiciones = [
        [
            'origen' => 'evaluacion',
            'calificacion_nueva' => 85,
        ],
        [
            'origen' => 'simulacion',
            'calificacion_nueva' => 90,
        ],
        [
            'origen' => 'evaluacion',
            'calificacion_nueva' => 80,
        ],
    ];

    expect(
        $this->servicio->puedeConsolidar(
            $transiciones,
            true
        )
    )->toBeFalse();
});

test('rechaza transiciones con calificaciones invalidas', function () {

    $transiciones = [
        [
            'origen' => 'evaluacion',
            'calificacion_nueva' => 80,
        ],
        [
            'origen' => 'evaluacion',
            'calificacion_nueva' => 120,
        ],
    ];

    $this->servicio->puedeConsolidar(
        $transiciones,
        true
    );

})->throws(InvalidArgumentException::class);
