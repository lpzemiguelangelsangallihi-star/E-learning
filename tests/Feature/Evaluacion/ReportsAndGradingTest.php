<?php

use App\Livewire\Professor\ResultadosEvaluaciones;
use App\Models\Calificacion;
use App\Models\IntentoExamen;
use App\Models\RespuestaEstudiante;
use App\Services\Evaluacion\AcademicReportService;
use App\Services\Evaluacion\ExamAttemptService;
use Livewire\Livewire;

require_once __DIR__.'/../../Support/EvaluacionHelpers.php';

/* Reportes académicos del profesor y corrección manual de intentos. */

function evalIntentoCompleto(object $esc, object $x, $estudiante, bool $correcto): IntentoExamen
{
    $servicio = app(ExamAttemptService::class);
    $intento = $servicio->iniciar($x->examen->fresh(), $estudiante);

    if ($correcto) {
        $servicio->guardarRespuesta($intento, $x->multiple, evalOpcion($x->multiple, 'B'));
        $servicio->guardarRespuesta($intento, $x->vf, evalOpcion($x->vf, 'Verdadero'));
        $servicio->guardarRespuesta($intento, $x->varias, [evalOpcion($x->varias, 'W'), evalOpcion($x->varias, 'X')]);
        $servicio->guardarRespuesta($intento, $x->corta, 'operativa');
    }

    return $servicio->finalizar($intento);
}

it('abre la pantalla de reportes del profesor', function () {
    $esc = evalEscenario();

    $this->actingAs($esc->profesor)
        ->get(route('professor.reports.index'))
        ->assertOk();

    // Un estudiante no entra a los reportes.
    $this->actingAs($esc->estudiante)
        ->get(route('professor.reports.index'))
        ->assertForbidden();
});

it('calcula el resumen: intentos, estudiantes, promedio y aprobación', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc, ['intentos_permitidos' => 3]);
    $otro = evalUsuario('estudiante');
    \App\Models\Inscripcion::create(['curso_id' => $esc->curso->id, 'estudiante_id' => $otro->id, 'estado' => 'activo']);

    evalIntentoCompleto($esc, $x, $esc->estudiante, true);   // 100
    evalIntentoCompleto($esc, $x, $otro, false);             // 0

    $r = app(AcademicReportService::class)->resumen($esc->profesor, null, null);

    expect($r['intentos'])->toBe(2)
        ->and($r['estudiantes'])->toBe(2)
        ->and($r['promedio'])->toBe(50.0)
        ->and($r['aprobacion'])->toBe(50.0);

    // Las demás vistas del reporte no deben fallar.
    $svc = app(AcademicReportService::class);
    expect($svc->porExamen($esc->profesor, null, null))->toHaveCount(1)
        ->and($svc->porEstudiante($esc->profesor, null, null))->toHaveCount(2)
        ->and($svc->porTema($esc->profesor, null, null))->toHaveCount(1)
        ->and($svc->evolucion($esc->profesor, null, null))->toHaveCount(12)
        ->and($svc->intentos($esc->profesor, null, null)->get())->toHaveCount(2);
});

it('otro profesor no ve datos de cursos ajenos', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);
    evalIntentoCompleto($esc, $x, $esc->estudiante, true);

    $ajeno = evalUsuario('profesor');

    expect(app(AcademicReportService::class)->resumen($ajeno, null, null)['intentos'])->toBe(0);
});

it('el profesor corrige manualmente una respuesta y se actualiza la nota y la calificación', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);
    $intento = evalIntentoCompleto($esc, $x, $esc->estudiante, false);   // todo vacío = 0

    $fila = RespuestaEstudiante::where('intento_id', $intento->id)->where('pregunta_id', $x->corta->id)->firstOrFail();

    $this->actingAs($esc->profesor);

    Livewire::test(ResultadosEvaluaciones::class)
        ->call('verDetalle', $intento->id)
        ->set("ajustes.{$fila->id}", '1')
        ->call('guardarAjustes')
        ->assertHasNoErrors();

    expect((float) $intento->fresh()->calificacion)->toBe(25.0)
        ->and((float) Calificacion::where('examen_id', $x->examen->id)->first()->calificacion)->toBe(25.0);
});

it('no permite poner más puntos que el máximo de la pregunta', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);
    $intento = evalIntentoCompleto($esc, $x, $esc->estudiante, false);
    $fila = RespuestaEstudiante::where('intento_id', $intento->id)->where('pregunta_id', $x->corta->id)->firstOrFail();

    $this->actingAs($esc->profesor);

    Livewire::test(ResultadosEvaluaciones::class)
        ->call('verDetalle', $intento->id)
        ->set("ajustes.{$fila->id}", '5')
        ->call('guardarAjustes')
        ->assertHasErrors(["ajustes.{$fila->id}"]);

    expect((float) $intento->fresh()->calificacion)->toBe(0.0);
});

it('un profesor ajeno no puede abrir ni corregir un intento', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);
    $intento = evalIntentoCompleto($esc, $x, $esc->estudiante, true);

    $this->actingAs(evalUsuario('profesor'));

    Livewire::test(ResultadosEvaluaciones::class)
        ->call('verDetalle', $intento->id)
        ->assertForbidden();
});

it('guarda la mejor nota cuando hay varios intentos', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc, ['intentos_permitidos' => 2]);

    evalIntentoCompleto($esc, $x, $esc->estudiante, true);    // 100
    evalIntentoCompleto($esc, $x, $esc->estudiante, false);   // 0

    expect(Calificacion::where('examen_id', $x->examen->id)->count())->toBe(1)
        ->and((float) Calificacion::where('examen_id', $x->examen->id)->first()->calificacion)->toBe(100.0);
});
