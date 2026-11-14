<?php

use App\Livewire\Professor\ConstructorExamen;
use App\Models\IntentoExamen;
use App\Services\Evaluacion\ExamAttemptService;
use App\Services\IOP\IntegracionEvaluacionIOPService;
use Livewire\Livewire;

require_once __DIR__.'/../../Support/EvaluacionHelpers.php';

/*
| Tipo de evaluación IOP: diagnóstico, refuerzo, ordinaria y ninguno.
| IMPORTANTE: usan RefreshDatabase; configura una base de datos de PRUEBAS.
*/

function finalizarConTipo(string $tipo): IntentoExamen
{
    $esc = evalEscenario();
    $x = evalExamen($esc, ['tipo_iop' => $tipo]);

    $servicio = app(ExamAttemptService::class);
    $intento = $servicio->iniciar($x->examen, $esc->estudiante);

    return $servicio->finalizar($intento);
}

it('los exámenes nuevos no participan en IOP por defecto', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);

    expect($x->examen->fresh()->tipo_iop)->toBe('ninguno');
});

it('un examen de diagnóstico llama a procesarDiagnostico al entregar', function () {
    $mock = Mockery::mock(IntegracionEvaluacionIOPService::class);
    $mock->shouldReceive('procesarDiagnostico')->once();
    $mock->shouldNotReceive('procesarSeguimiento');
    app()->instance(IntegracionEvaluacionIOPService::class, $mock);

    expect(finalizarConTipo('diagnostico')->estado)->toBe('finalizado');
});

it('un examen de refuerzo llama a procesarSeguimiento al entregar', function () {
    $mock = Mockery::mock(IntegracionEvaluacionIOPService::class);
    $mock->shouldReceive('procesarSeguimiento')->once();
    $mock->shouldNotReceive('procesarDiagnostico');
    app()->instance(IntegracionEvaluacionIOPService::class, $mock);

    expect(finalizarConTipo('refuerzo')->estado)->toBe('finalizado');
});

it('los exámenes ordinarios o sin IOP no tocan IOP', function (string $tipo) {
    $mock = Mockery::mock(IntegracionEvaluacionIOPService::class);
    $mock->shouldNotReceive('procesarDiagnostico');
    $mock->shouldNotReceive('procesarSeguimiento');
    app()->instance(IntegracionEvaluacionIOPService::class, $mock);

    expect(finalizarConTipo($tipo)->estado)->toBe('finalizado');
})->with(['ordinaria', 'ninguno']);

it('un fallo de IOP no deshace la entrega del examen', function () {
    $mock = Mockery::mock(IntegracionEvaluacionIOPService::class);
    $mock->shouldReceive('procesarDiagnostico')->andThrow(new RuntimeException('IOP caído'));
    app()->instance(IntegracionEvaluacionIOPService::class, $mock);

    $intento = finalizarConTipo('diagnostico');

    expect($intento->fresh()->estado)->toBe('finalizado');
});

it('entregar dos veces solo notifica a IOP una vez', function () {
    $mock = Mockery::mock(IntegracionEvaluacionIOPService::class);
    $mock->shouldReceive('procesarDiagnostico')->once();
    app()->instance(IntegracionEvaluacionIOPService::class, $mock);

    $esc = evalEscenario();
    $x = evalExamen($esc, ['tipo_iop' => 'diagnostico']);

    $servicio = app(ExamAttemptService::class);
    $intento = $servicio->iniciar($x->examen, $esc->estudiante);
    $servicio->finalizar($intento);
    $servicio->finalizar($intento);
});

it('el profesor define el tipo IOP en el constructor', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);

    $this->actingAs($esc->profesor);

    Livewire::test(ConstructorExamen::class, ['examenId' => $x->examen->id])
        ->set('tipo_iop', 'diagnostico')
        ->call('guardarExamen')
        ->assertHasNoErrors();

    expect($x->examen->fresh()->tipo_iop)->toBe('diagnostico');
});

it('rechaza un tipo IOP inválido', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);

    $this->actingAs($esc->profesor);

    Livewire::test(ConstructorExamen::class, ['examenId' => $x->examen->id])
        ->set('tipo_iop', 'inventado')
        ->call('guardarExamen')
        ->assertHasErrors('tipo_iop');
});

it('no permite cambiar el tipo IOP cuando ya hay intentos', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc, ['tipo_iop' => 'ordinaria']);

    app(ExamAttemptService::class)->iniciar($x->examen, $esc->estudiante);

    $this->actingAs($esc->profesor);

    Livewire::test(ConstructorExamen::class, ['examenId' => $x->examen->id])
        ->set('tipo_iop', 'diagnostico')
        ->call('guardarExamen');

    expect($x->examen->fresh()->tipo_iop)->toBe('ordinaria');
});
