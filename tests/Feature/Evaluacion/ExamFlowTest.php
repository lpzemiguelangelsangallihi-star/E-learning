<?php

use App\Livewire\Student\ResolverExamen;
use App\Models\Calificacion;
use App\Models\IntentoExamen;
use App\Services\Evaluacion\ExamAttemptService;
use App\Services\Evaluacion\ExamException;
use Livewire\Livewire;

require_once __DIR__.'/../../Support/EvaluacionHelpers.php';

/*
| Flujo completo del estudiante: ver examen, iniciar, resolver, entregar y ver resultado.
| IMPORTANTE: estas pruebas usan RefreshDatabase; configura una base de datos de PRUEBAS
| (DB_DATABASE distinta a la de desarrollo) antes de ejecutarlas.
*/

it('muestra la ficha del examen con la vista correcta', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);

    $this->actingAs($esc->estudiante)
        ->get(route('student.evaluations.exam.show', $x->examen))
        ->assertOk()
        ->assertSee('Comenzar examen');
});

it('inicia un intento, abre la pantalla de resolución y retoma el mismo intento', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);

    $this->actingAs($esc->estudiante)
        ->post(route('student.evaluations.exam.start', $x->examen))
        ->assertRedirect();

    $intento = IntentoExamen::firstOrFail();

    $this->get(route('student.evaluations.exam.attempt', $intento))->assertOk();

    // Iniciar otra vez no crea un segundo intento: retoma el abierto.
    $this->post(route('student.evaluations.exam.start', $x->examen))
        ->assertRedirect(route('student.evaluations.exam.attempt', $intento));

    expect(IntentoExamen::count())->toBe(1);
});

it('califica los cuatro tipos de pregunta y guarda la nota en calificaciones', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);

    $intento = app(ExamAttemptService::class)->iniciar($x->examen, $esc->estudiante);

    $this->actingAs($esc->estudiante);

    Livewire::test(ResolverExamen::class, ['intentoId' => $intento->id])
        ->set("respuestas.{$x->multiple->id}", (string) evalOpcion($x->multiple, 'B'))
        ->set("respuestas.{$x->vf->id}", (string) evalOpcion($x->vf, 'Verdadero'))
        ->set("respuestas.{$x->varias->id}", [(string) evalOpcion($x->varias, 'W'), (string) evalOpcion($x->varias, 'X')])
        ->set("respuestas.{$x->corta->id}", 'investigacion')
        ->call('finalizar')
        ->assertRedirect(route('student.evaluations.exam.result', $intento->id));

    $intento->refresh();
    expect($intento->estado)->toBe('finalizado')
        ->and((float) $intento->calificacion)->toBe(100.0);

    $cal = Calificacion::where('estudiante_id', $esc->estudiante->id)->where('examen_id', $x->examen->id)->firstOrFail();
    expect((float) $cal->calificacion)->toBe(100.0)->and($cal->tipo)->toBe('examen');
});

it('acepta respuesta corta sin importar mayúsculas ni tildes', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);

    $servicio = app(ExamAttemptService::class);
    $intento = $servicio->iniciar($x->examen, $esc->estudiante);

    $servicio->guardarRespuesta($intento, $x->corta, '  INVESTIGACION ');
    $intento = $servicio->finalizar($intento);

    // Solo la corta es correcta: 1 de 4 puntos => 25.
    expect((float) $intento->calificacion)->toBe(25.0);
});

it('da crédito parcial en selección múltiple y penaliza los errores', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);

    $servicio = app(ExamAttemptService::class);
    $intento = $servicio->iniciar($x->examen, $esc->estudiante);

    // W correcta + Y incorrecta => (1 - 1) / 2 = 0
    $servicio->guardarRespuesta($intento, $x->varias, [evalOpcion($x->varias, 'W'), evalOpcion($x->varias, 'Y')]);
    $intento = $servicio->finalizar($intento);
    expect((float) $intento->calificacion)->toBe(0.0);

    // Otro examen: solo W => 1/2 = 0.5 de 1 punto => 12.5 sobre 100 (4 preguntas de 1 pt)
    $y = evalExamen($esc, ['titulo' => 'Otro']);
    $intento2 = $servicio->iniciar($y->examen, $esc->estudiante);
    $servicio->guardarRespuesta($intento2, $y->varias, [evalOpcion($y->varias, 'W')]);
    $intento2 = $servicio->finalizar($intento2);
    expect((float) $intento2->calificacion)->toBe(12.5);
});

it('respeta el límite de intentos', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc, ['intentos_permitidos' => 1]);
    $servicio = app(ExamAttemptService::class);

    $intento = $servicio->iniciar($x->examen, $esc->estudiante);
    $servicio->finalizar($intento);

    expect(fn () => $servicio->iniciar($x->examen->fresh(), $esc->estudiante))
        ->toThrow(ExamException::class, 'Ya usaste todos tus intentos');

    $this->actingAs($esc->estudiante)
        ->post(route('student.evaluations.exam.start', $x->examen))
        ->assertRedirect(route('student.evaluations.exam.show', $x->examen))
        ->assertSessionHas('error');

    expect(IntentoExamen::count())->toBe(1);
});

it('entregar dos veces no duplica respuestas ni calificaciones', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);
    $servicio = app(ExamAttemptService::class);

    $intento = $servicio->iniciar($x->examen, $esc->estudiante);
    $servicio->guardarRespuesta($intento, $x->multiple, evalOpcion($x->multiple, 'B'));

    $primera = $servicio->finalizar($intento);
    $filas = $primera->respuestas()->count();
    $segunda = $servicio->finalizar($intento->fresh());

    expect((float) $segunda->calificacion)->toBe((float) $primera->calificacion)
        ->and($segunda->respuestas()->count())->toBe($filas)
        ->and(Calificacion::where('examen_id', $x->examen->id)->count())->toBe(1);
});

it('no permite guardar respuestas en un intento ya entregado', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);
    $servicio = app(ExamAttemptService::class);

    $intento = $servicio->iniciar($x->examen, $esc->estudiante);
    $servicio->finalizar($intento);

    // Se usa la instancia vieja (estado en memoria "en_proceso") a propósito.
    expect(fn () => $servicio->guardarRespuesta($intento, $x->multiple, evalOpcion($x->multiple, 'B')))
        ->toThrow(ExamException::class, 'ya fue entregado');
});

it('cierra el intento cuando se acaba el tiempo y rechaza respuestas tardías', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc, ['tiempo_limite' => 10]);
    $servicio = app(ExamAttemptService::class);

    $intento = $servicio->iniciar($x->examen, $esc->estudiante);
    $servicio->guardarRespuesta($intento, $x->multiple, evalOpcion($x->multiple, 'B'));

    $this->travel(11)->minutes();

    expect(fn () => $servicio->guardarRespuesta($intento, $x->vf, evalOpcion($x->vf, 'Verdadero')))
        ->toThrow(ExamException::class, 'tiempo');

    // Al abrir el intento vencido se entrega solo y redirige al resultado.
    $this->actingAs($esc->estudiante)
        ->get(route('student.evaluations.exam.attempt', $intento))
        ->assertRedirect(route('student.evaluations.exam.result', $intento));

    $intento->refresh();
    expect($intento->estado)->toBe('finalizado')
        ->and((float) $intento->calificacion)->toBe(25.0);
});

it('muestra el resultado y oculta las correctas si el profesor lo decide', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc, ['mostrar_correctas' => false]);
    $servicio = app(ExamAttemptService::class);

    $intento = $servicio->iniciar($x->examen, $esc->estudiante);
    $servicio->finalizar($intento);

    $this->actingAs($esc->estudiante)
        ->get(route('student.evaluations.exam.result', $intento))
        ->assertOk()
        ->assertSee('no mostrar las respuestas correctas');
});

it('un estudiante no puede ver el intento de otro', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);
    $intento = app(ExamAttemptService::class)->iniciar($x->examen, $esc->estudiante);

    $otro = evalUsuario('estudiante');

    $this->actingAs($otro)->get(route('student.evaluations.exam.attempt', $intento))->assertForbidden();
    $this->actingAs($otro)->get(route('student.evaluations.exam.result', $intento))->assertForbidden();
});

it('no deja entrar a un examen en borrador ni a estudiantes no inscritos', function () {
    $esc = evalEscenario();
    $borrador = evalExamen($esc, ['estado' => 'borrador']);

    $this->actingAs($esc->estudiante)
        ->get(route('student.evaluations.exam.show', $borrador->examen))
        ->assertForbidden();

    $publicado = evalExamen($esc, ['titulo' => 'Publicado']);
    $noInscrito = evalUsuario('estudiante');

    $this->actingAs($noInscrito)
        ->get(route('student.evaluations.exam.show', $publicado->examen))
        ->assertForbidden();
});

it('un examen cerrado sigue visible solo para quien ya tiene intentos', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);
    $servicio = app(ExamAttemptService::class);

    $intento = $servicio->iniciar($x->examen, $esc->estudiante);
    $servicio->finalizar($intento);

    $x->examen->update(['estado' => 'cerrado']);

    $this->actingAs($esc->estudiante)
        ->get(route('student.evaluations.exam.show', $x->examen))
        ->assertOk()
        ->assertDontSee('Comenzar examen');

    $this->actingAs($esc->estudiante)
        ->get(route('student.evaluations.exam.result', $intento))
        ->assertOk();

    $sinIntentos = evalUsuario('estudiante');
    \App\Models\Inscripcion::create(['curso_id' => $esc->curso->id, 'estudiante_id' => $sinIntentos->id, 'estado' => 'activo']);

    $this->actingAs($sinIntentos)
        ->get(route('student.evaluations.exam.show', $x->examen))
        ->assertForbidden();
});

it('lista el examen en pendientes y luego en el historial', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);

    $this->actingAs($esc->estudiante)
        ->get(route('student.evaluations.index'))
        ->assertOk()
        ->assertSee('Examen de prueba');

    $intento = app(ExamAttemptService::class)->iniciar($x->examen, $esc->estudiante);
    app(ExamAttemptService::class)->finalizar($intento);

    $this->get(route('student.evaluations.history'))
        ->assertOk()
        ->assertSee('Examen de prueba');

    $this->get(route('student.grades.index'))->assertOk();
});
