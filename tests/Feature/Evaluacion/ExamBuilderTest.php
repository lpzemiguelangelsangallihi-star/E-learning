<?php

use App\Livewire\Professor\ConstructorExamen;
use App\Models\Examen;
use App\Services\Evaluacion\ExamAttemptService;
use Livewire\Livewire;

require_once __DIR__.'/../../Support/EvaluacionHelpers.php';

/* Creación, publicación y cierre de exámenes por el profesor. */

it('el profesor crea un examen en borrador desde el formulario', function () {
    $esc = evalEscenario();

    $this->actingAs($esc->profesor)
        ->post(route('professor.exams.store'), [
            'leccion_id' => $esc->leccion->id,
            'titulo' => 'Parcial 1',
            'intentos_permitidos' => 2,
            'puntaje_maximo' => 100,
            'tiempo_limite' => 30,
        ])
        ->assertRedirect();

    $examen = Examen::firstOrFail();
    expect($examen->estado)->toBe('borrador')->and($examen->intentos_permitidos)->toBe(2);
});

it('no permite crear exámenes en temas de otro profesor', function () {
    $esc = evalEscenario();

    $this->actingAs(evalUsuario('profesor'))
        ->post(route('professor.exams.store'), [
            'leccion_id' => $esc->leccion->id,
            'titulo' => 'Intruso',
            'intentos_permitidos' => 1,
            'puntaje_maximo' => 100,
        ])
        ->assertForbidden();
});

it('no publica un examen sin preguntas', function () {
    $esc = evalEscenario();
    $examen = Examen::create([
        'leccion_id' => $esc->leccion->id, 'profesor_id' => $esc->profesor->id,
        'titulo' => 'Vacío', 'intentos_permitidos' => 1, 'puntaje_maximo' => 100, 'estado' => 'borrador',
    ]);

    $this->actingAs($esc->profesor);

    Livewire::test(ConstructorExamen::class, ['examenId' => $examen->id])
        ->call('cambiarEstado', 'publicado')
        ->assertSet('error', 'Agrega al menos una pregunta antes de publicar.');

    expect($examen->fresh()->estado)->toBe('borrador');
});

it('publica un examen válido y lo cierra después', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc, ['estado' => 'borrador']);

    $this->actingAs($esc->profesor);

    Livewire::test(ConstructorExamen::class, ['examenId' => $x->examen->id])
        ->call('cambiarEstado', 'publicado')
        ->assertSet('error', '')
        ->call('cambiarEstado', 'cerrado')
        ->assertSet('error', '');

    expect($x->examen->fresh()->estado)->toBe('cerrado');
});

it('no publica un examen con una pregunta mal armada', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc, ['estado' => 'borrador']);

    // Opción múltiple sin ninguna correcta.
    $x->multiple->opciones()->update(['es_correcta' => false]);

    $this->actingAs($esc->profesor);

    Livewire::test(ConstructorExamen::class, ['examenId' => $x->examen->id])
        ->call('cambiarEstado', 'publicado');

    expect($x->examen->fresh()->estado)->toBe('borrador');
});

it('bloquea editar preguntas y volver a borrador cuando ya hay intentos', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);

    app(ExamAttemptService::class)->iniciar($x->examen, $esc->estudiante);

    $this->actingAs($esc->profesor);

    Livewire::test(ConstructorExamen::class, ['examenId' => $x->examen->id])
        ->call('eliminarPregunta', $x->multiple->id)
        ->assertSet('error', 'Este examen ya tiene intentos de estudiantes; sus preguntas no se pueden modificar.')
        ->call('cambiarEstado', 'borrador');

    expect($x->examen->fresh()->estado)->toBe('publicado')
        ->and($x->examen->preguntas()->count())->toBe(4);
});

it('no elimina un examen con intentos', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);
    app(ExamAttemptService::class)->iniciar($x->examen, $esc->estudiante);

    $this->actingAs($esc->profesor)
        ->delete(route('professor.exams.destroy', $x->examen))
        ->assertSessionHas('error');

    expect(Examen::count())->toBe(1);
});

it('otro profesor no puede abrir el constructor de un examen ajeno', function () {
    $esc = evalEscenario();
    $x = evalExamen($esc);

    $this->actingAs(evalUsuario('profesor'))
        ->get(route('professor.exams.edit', $x->examen))
        ->assertForbidden();
});
