<?php

use App\Services\IOP\SeguimientoService;
use App\Services\IOP\ConsolidacionIOPService;

describe('Clasificacion de estados IOP', function () {

    it('clasifica correctamente los limites', function () {
        $servicio = new SeguimientoService();

        $casos = [
            [0, 'S1'],
            [25, 'S1'],
            [50, 'S1'],
            [50.01, 'S2'],
            [50.5, 'S2'],
            [69.99, 'S2'],
            [70, 'S3'],
            [85.5, 'S3'],
            [100, 'S3'],
        ];

        foreach ($casos as [$nota, $esperado]) {
            expect($servicio->clasificarEstado($nota))
                ->toBe($esperado);
        }
    });

    it('rechaza calificaciones invalidas', function () {
        $servicio = new SeguimientoService();

        foreach ([-1, 100.01, INF, NAN] as $nota) {
            expect(
                fn () => $servicio->clasificarEstado($nota)
            )->toThrow(InvalidArgumentException::class);
        }
    });

    it('genera vectores iniciales correctos', function () {
        $servicio = new SeguimientoService();

        expect($servicio->generarVectorInicial('S1'))
            ->toBe([1, 0, 0, 0]);

        expect($servicio->generarVectorInicial('S2'))
            ->toBe([0, 1, 0, 0]);

        expect($servicio->generarVectorInicial('S3'))
            ->toBe([0, 0, 1, 0]);

        expect($servicio->generarVectorInicial('S4'))
            ->toBe([0, 0, 0, 1]);
    });

    it('rechaza estados desconocidos', function () {
        $servicio = new SeguimientoService();

        expect(
            fn () => $servicio->generarVectorInicial('S5')
        )->toThrow(InvalidArgumentException::class);
    });
});

describe('Reglas de consolidacion S4', function () {

    
    it('acepta dos evaluaciones reales aprobatorias', function () {
        $criterio = new ConsolidacionIOPService();
        $evaluaciones = [
            [
                'origen' => 'evaluacion',
                'calificacion_nueva' => 70,
            ],
            [
                'origen' => 'evaluacion',
                'calificacion_nueva' => 80,
            ],
        ];

        expect(
            $criterio->puedeConsolidar(
    $evaluaciones,
    true
)
        )->toBeTrue();
    });
 
it('rechaza un refuerzo sin aprobar', function () {
    $criterio = new ConsolidacionIOPService();

    $evaluaciones = [
        [
            'origen' => 'evaluacion',
            'calificacion_nueva' => 90,
        ],
        [
            'origen' => 'evaluacion',
            'calificacion_nueva' => 95,
        ],
    ];

    expect(
        $criterio->puedeConsolidar(
            $evaluaciones,
            false
        )
    )->toBeFalse();
});

    it('rechaza notas insuficientes', function () {
        $criterio = new ConsolidacionIOPService();
        $evaluaciones = [
            [
                'origen' => 'evaluacion',
                'calificacion_nueva' => 69.99,
            ],
            [
                'origen' => 'evaluacion',
                'calificacion_nueva' => 100,
            ],
        ];

        expect(
            $criterio->puedeConsolidar(
    $evaluaciones,
    true
)
        )->toBeFalse();
    });

    it('rechaza evaluaciones simuladas', function () {
        $criterio = new ConsolidacionIOPService();
        $evaluaciones = [
            [
                'origen' => 'simulacion',
                'calificacion_nueva' => 90,
            ],
            [
                'origen' => 'evaluacion',
                'calificacion_nueva' => 95,
            ],
        ];

        expect(
            $criterio->puedeConsolidar(
    $evaluaciones,
    true
)
        )->toBeFalse();
    });

    it('requiere al menos dos evaluaciones', function () {
        $criterio = new ConsolidacionIOPService();
        $evaluaciones = [
            [
                'origen' => 'evaluacion',
                'calificacion_nueva' => 100,
            ],
        ];

        expect(
            $criterio->puedeConsolidar(
    $evaluaciones,
    true
)
        )->toBeFalse();
    });
});
