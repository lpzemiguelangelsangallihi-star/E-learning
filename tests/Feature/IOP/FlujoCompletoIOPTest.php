<?php

use App\Models\Examen;
use App\Models\IntentoExamen;
use App\Models\IopCicloRefuerzo;
use App\Models\IopEjercicioRefuerzo;
use App\Models\IopModeloDecision;
use App\Models\IopRecomendacion;
use App\Models\IopSeguimiento;
use App\Models\IopTransicion;
use App\Services\IOP\IntegracionEvaluacionIOPService;
use App\Services\IOP\MaximinService;
use Illuminate\Validation\ValidationException;
use App\Services\IOP\MarkovService;
use App\Models\IopMatrizMarkov;
use App\Services\IOP\MatrizMarkovService;



require_once __DIR__ . '/../../Support/EvaluacionHelpers.php';

/*
|--------------------------------------------------------------------------
| DATOS DEL ESCENARIO IOP
|--------------------------------------------------------------------------
| Reutilizamos los helpers existentes de evaluaciones.
| El estudiante está inscrito en un curso publicado.
*/

function iopPrepararEscenarioIntegracion(): object
{
    $esc = evalEscenario();

    IopModeloDecision::create([
        'nombre' => 'Modelo Maximin de prueba',
        'metodo' => 'maximin',
        'version' => 1,
        'activo' => true,
        'matriz_utilidades' => MaximinService::MATRIZ_EJEMPLO,
    ]);

    foreach (['teorico', 'practico', 'visual'] as $tipo) {
        IopEjercicioRefuerzo::create([
            'leccion_id' => $esc->leccion->id,
            'titulo' => 'Ejercicio ' . $tipo,
            'descripcion' => 'Ejercicio de prueba IOP',
            'tipo' => $tipo,
            'instrucciones' => 'Resuelve el ejercicio.',
            'orden' => 1,
            'activo' => true,
        ]);
    }

    return $esc;
}

/*
|--------------------------------------------------------------------------
| CREAR UN INTENTO FINALIZADO
|--------------------------------------------------------------------------
| Simula un resultado ya calificado.
| No sustituye las pruebas de calificación del examen.
*/

function iopIntentoFinalizado(
    Examen $examen,
    int $estudianteId,
    float $nota
): IntentoExamen {
    return IntentoExamen::create([
        'examen_id' => $examen->id,
        'estudiante_id' => $estudianteId,
        'numero_intento' => 1,
        'fecha_inicio' => now()->subMinute(),
        'fecha_fin' => now(),
        'calificacion' => $nota,
        'estado' => 'finalizado',
    ]);
}

/*
|--------------------------------------------------------------------------
| PRUEBA 1: DIAGNOSTICO Y PRIMERA RECOMENDACION
|--------------------------------------------------------------------------
*/

test('diagnostico genera Maximin y ciclo inicial', function () {

    $esc = iopPrepararEscenarioIntegracion();

    $examen = evalExamen($esc, [
        'titulo' => 'Diagnostico IOP',
        'tipo_iop' => 'diagnostico',
    ]);

    $intento = iopIntentoFinalizado(
        $examen->examen,
        $esc->estudiante->id,
        45
    );

    $servicio = app(IntegracionEvaluacionIOPService::class);

    $seguimiento = $servicio->procesarDiagnostico(
        $intento->id
    );

    expect($seguimiento->estado_inicial)->toBe('S1');
    expect($seguimiento->estado_actual)->toBe('S1');
    expect($seguimiento->ciclo_actual)->toBe(0);
    expect($seguimiento->activo)->toBeTrue();

    expect($seguimiento->vector_inicial)
        ->toEqual([1, 0, 0, 0]);

    expect(IopRecomendacion::count())->toBe(1);

    $ciclo = IopCicloRefuerzo::query()
        ->where('seguimiento_id', $seguimiento->id)
        ->where('ciclo', 0)
        ->with('recomendacion.asignacionRefuerzo.ejercicio')
        ->firstOrFail();

    $recomendacion = $ciclo->recomendacion;
    $asignacion = $recomendacion->asignacionRefuerzo;

    expect($asignacion)->not->toBeNull();

    expect($asignacion->ejercicio->tipo)
        ->toBe($recomendacion->alternativa_recomendada);

    // Verificar que repetir el diagnóstico no duplica datos.
    $otro = $servicio->procesarDiagnostico($intento->id);

    expect($otro->id)->toBe($seguimiento->id);

    expect(IopSeguimiento::count())->toBe(1);
    expect(IopRecomendacion::count())->toBe(1);
    expect(IopCicloRefuerzo::count())->toBe(1);


$this->actingAs($esc->profesor)
    ->get(route(
        'professor.iop.seguimientos.detalle',
        ['seguimientoId' => $seguimiento->id]
    ))
    ->assertOk()
    ->assertSee('Matriz de aprendizaje individual')
    ->assertSee('Pendiente de explorar')
    ->assertSee('El estudiante todavía no tiene');

});

/*
|--------------------------------------------------------------------------
| PRUEBA 2: SEGUIMIENTO HASTA S4
|--------------------------------------------------------------------------
*/

test('dos evaluaciones reales con refuerzos aprobados alcanzan S4', function () {

    $esc = iopPrepararEscenarioIntegracion();

    $servicio = app(IntegracionEvaluacionIOPService::class);

    // 1. Diagnóstico inicial: 45 puntos.
    $diagnostico = evalExamen($esc, [
        'titulo' => 'Diagnostico inicial',
        'tipo_iop' => 'diagnostico',
    ]);

    $intentoInicial = iopIntentoFinalizado(
        $diagnostico->examen,
        $esc->estudiante->id,
        45
    );

    $seguimiento = $servicio->procesarDiagnostico(
        $intentoInicial->id
    );

    // 2. Aprobar el primer refuerzo.
    $ciclo0 = $seguimiento->ciclosRefuerzo()
        ->where('ciclo', 0)
        ->with('recomendacion.asignacionRefuerzo')
        ->firstOrFail();

    $ciclo0->recomendacion->asignacionRefuerzo->update([
        'estado' => 'completado',
        'completado_en' => now(),
        'revisado_en' => now(),
        'revisado_por' => $esc->profesor->id,
        'retroalimentacion' => 'Aprobado',
    ]);

    // Avanzar el reloj para conservar el orden temporal.
    $this->travel(2)->minutes();

    // 3. Primera evaluación: 75 puntos.
    $examen1 = evalExamen($esc, [
        'titulo' => 'Evaluacion IOP 1',
        'tipo_iop' => 'refuerzo',
    ]);

    $intento1 = iopIntentoFinalizado(
        $examen1->examen,
        $esc->estudiante->id,
        75
    );

    $transicion1 = $servicio->procesarSeguimiento(
        $intento1->id
    );

    expect($transicion1->estado_anterior)->toBe('S1');
    expect($transicion1->estado_siguiente)->toBe('S3');
    expect($transicion1->ciclo)->toBe(1);

    $seguimiento->refresh();

    expect($seguimiento->estado_actual)->toBe('S3');
    expect($seguimiento->activo)->toBeTrue();

    // Se debe haber creado un segundo refuerzo.
    $ciclo1 = $seguimiento->ciclosRefuerzo()
        ->where('ciclo', 1)
        ->with('recomendacion.asignacionRefuerzo')
        ->firstOrFail();


$evidencia = app(
    \App\Services\IOP\MatrizPersonalIOPService::class
)->evidencia(
    $esc->estudiante->id,
    $esc->leccion->id
);

expect($evidencia['teorico']['observaciones'])
    ->toBe(1);

expect($evidencia['teorico']['mejora_promedio'])
    ->toBe(30.0);

expect($evidencia['practico']['observaciones'])
    ->toBe(0);

expect($evidencia['visual']['observaciones'])
    ->toBe(0);

expect($ciclo1->recomendacion->alternativa_recomendada)
    ->toBe('visual');


    expect($ciclo1->recomendacion->asignacionRefuerzo)
        ->not->toBeNull();

        $this->travel(2)->minutes();

    // 4. Aprobar el segundo refuerzo.
    $ciclo1->recomendacion->asignacionRefuerzo->update([
        'estado' => 'completado',
        'completado_en' => now(),
        'revisado_en' => now(),
        'revisado_por' => $esc->profesor->id,
        'retroalimentacion' => 'Aprobado',
    ]);

    $this->travel(2)->minutes();

    // 5. Segunda evaluación: 82 puntos.
    $examen2 = evalExamen($esc, [
        'titulo' => 'Evaluacion IOP 2',
        'tipo_iop' => 'refuerzo',
    ]);

    $intento2 = iopIntentoFinalizado(
        $examen2->examen,
        $esc->estudiante->id,
        82
    );

    $transicion2 = $servicio->procesarSeguimiento(
        $intento2->id
    );

    $seguimiento->refresh();

    // 6. Verificaciones de consolidación.
    expect($transicion2->estado_siguiente)->toBe('S4');
    expect($seguimiento->estado_actual)->toBe('S4');
    expect($seguimiento->activo)->toBeFalse();
    expect($seguimiento->consolidado_en)->not->toBeNull();

    expect($seguimiento->ciclo_actual)->toBe(2);

    expect(
        IopTransicion::where(
            'seguimiento_id',
            $seguimiento->id
        )->count()
    )->toBe(2);

    // No se crea otro refuerzo después de S4.
    expect(
        IopCicloRefuerzo::where(
            'seguimiento_id',
            $seguimiento->id
        )->count()
    )->toBe(2);

    // Reprocesar el mismo intento debe ser idempotente.
    $repetido = $servicio->procesarSeguimiento(
        $intento2->id
    );

    expect($repetido->id)->toBe($transicion2->id);

    expect(IopTransicion::where(
        'seguimiento_id',
        $seguimiento->id
    )->count())->toBe(2);


    
    // Verificar estimacion de Markov
    $markov = app(MarkovService::class);

    $estimacion = $markov->estimarMatriz(
        'evaluacion',
        (int) $esc->leccion->id,
        1
    );

    // Transicion S1 -> S3
    expect($estimacion['conteos'][0][2])->toBe(1);

    // Transicion S3 -> S4
    expect($estimacion['conteos'][2][3])->toBe(1);

    // S2 no tiene observaciones
    expect($estimacion['probabilidades'][1])->toBeNull();

    // S4 es absorbente
    expect($estimacion['probabilidades'][3])
        ->toEqual([0.0, 0.0, 0.0, 1.0]);

    // No hay datos suficientes para completar la matriz
    expect($estimacion['completa'])->toBeFalse();

    // Hay dos transiciones reales
    expect(array_sum($estimacion['totales']))->toBe(2);


    // Persistencia y versionado de la matriz Markov.
    
    // Markov debe actualizarse automáticamente
    // después de cada evaluación real.

    $matricesGuardadas = IopMatrizMarkov::query()
        ->where('leccion_id', $esc->leccion->id)
        ->where('origen', 'evaluacion')
        ->orderBy('version')
        ->get();

    expect($matricesGuardadas->count())->toBe(2);

    $primeraMatriz = $matricesGuardadas->first();
    $ultimaMatriz = $matricesGuardadas->last();

    expect($primeraMatriz->version)->toBe(1);
    expect(array_sum($primeraMatriz->totales))->toBe(1);

    expect($ultimaMatriz->version)->toBe(2);
    expect($ultimaMatriz->completa)->toBeFalse();
    expect(array_sum($ultimaMatriz->totales))->toBe(2);

    expect($ultimaMatriz->conteos[0][2])->toBe(1);
    expect($ultimaMatriz->conteos[2][3])->toBe(1);

    // Volver a actualizar sin nuevas transiciones
    // no debe generar otra versión.

    $matrices = app(MatrizMarkovService::class);

    $resultado = $matrices->actualizarLeccion(
        (int) $esc->leccion->id,
        1
    );

    expect($resultado['accion'])->toBe('sin_cambios');

    expect(
        IopMatrizMarkov::query()
            ->where('leccion_id', $esc->leccion->id)
            ->where('origen', 'evaluacion')
            ->count()
    )->toBe(2);


    
});

/*
|--------------------------------------------------------------------------
| PRUEBA 3: NO GENERAR DIAGNOSTICO SIN EJERCICIOS
|--------------------------------------------------------------------------
*/

test('un diagnostico falla sin ejercicios y no deja registros parciales', function () {

    $esc = iopPrepararEscenarioIntegracion();

    // Desactivar todos los ejercicios del catálogo.
    IopEjercicioRefuerzo::query()
        ->where('leccion_id', $esc->leccion->id)
        ->update(['activo' => false]);

    $diagnostico = evalExamen($esc, [
        'titulo' => 'Diagnostico sin ejercicios',
        'tipo_iop' => 'diagnostico',
    ]);

    $intento = iopIntentoFinalizado(
        $diagnostico->examen,
        $esc->estudiante->id,
        40
    );

    $servicio = app(IntegracionEvaluacionIOPService::class);

    expect(
        fn () => $servicio->procesarDiagnostico(
            $intento->id
        )
    )->toThrow(ValidationException::class);

    // La transacción debe revertir los cambios IOP.
    expect(IopRecomendacion::count())->toBe(0);
    expect(IopSeguimiento::count())->toBe(0);
    expect(IopCicloRefuerzo::count())->toBe(0);
});

test('Markov no publica una matriz sin observaciones', function () {
    $esc = evalEscenario();

    $servicio = app(MatrizMarkovService::class);

    $resultado = $servicio->actualizarLeccion(
        $esc->leccion->id,
        1
    );

    expect($resultado['accion'])->toBe('sin_datos');

    expect(
        IopMatrizMarkov::where(
            'leccion_id',
            $esc->leccion->id
        )->count()
    )->toBe(0);
});
