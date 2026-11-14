<?php

use App\Models\Calificacion;
use App\Models\Entrega;
use App\Models\Inscripcion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

require_once __DIR__.'/../../Support/EvaluacionHelpers.php';

/* Tareas: ver, entregar, calificar, retroalimentación y acceso a archivos. */

it('muestra la tarea publicada al estudiante inscrito', function () {
    $esc = evalEscenario();
    $tarea = evalTarea($esc);

    $this->actingAs($esc->estudiante)
        ->get(route('student.evaluations.task.show', $tarea))
        ->assertOk()
        ->assertSee('Tarea de prueba')
        ->assertSee('Entregar');
});

it('no muestra tareas en borrador ni a estudiantes no inscritos', function () {
    $esc = evalEscenario();
    $borrador = evalTarea($esc, ['estado' => 'borrador']);
    $publicada = evalTarea($esc, ['titulo' => 'Publicada']);

    $this->actingAs($esc->estudiante)
        ->get(route('student.evaluations.task.show', $borrador))
        ->assertForbidden();

    $this->actingAs(evalUsuario('estudiante'))
        ->get(route('student.evaluations.task.show', $publicada))
        ->assertForbidden();
});

it('registra una entrega de texto y la reemplaza al reenviar', function () {
    $esc = evalEscenario();
    $tarea = evalTarea($esc);

    $this->actingAs($esc->estudiante)
        ->post(route('student.evaluations.task.submit', $tarea), ['respuesta' => 'Primera versión'])
        ->assertRedirect(route('student.evaluations.task.show', $tarea))
        ->assertSessionHas('success');

    $this->post(route('student.evaluations.task.submit', $tarea), ['respuesta' => 'Segunda versión']);

    expect(Entrega::count())->toBe(1);

    $entrega = Entrega::first();
    expect($entrega->respuesta)->toBe('Segunda versión')->and($entrega->estado)->toBe('entregada');
});

it('exige respuesta o archivo', function () {
    $esc = evalEscenario();
    $tarea = evalTarea($esc);

    $this->actingAs($esc->estudiante)
        ->post(route('student.evaluations.task.submit', $tarea), ['respuesta' => '   '])
        ->assertSessionHasErrors('respuesta');

    expect(Entrega::count())->toBe(0);
});

it('marca como tardía la entrega posterior a la fecha límite', function () {
    $esc = evalEscenario();
    $tarea = evalTarea($esc, ['fecha_limite' => now()->subDay()]);

    $this->actingAs($esc->estudiante)
        ->post(route('student.evaluations.task.submit', $tarea), ['respuesta' => 'Tarde'])
        ->assertSessionHas('success');

    expect(Entrega::first()->estado)->toBe('tarde');
});

it('no recibe entregas cuando la tarea está cerrada', function () {
    $esc = evalEscenario();
    $tarea = evalTarea($esc, ['estado' => 'cerrada']);

    $this->actingAs($esc->estudiante)
        ->get(route('student.evaluations.task.show', $tarea))
        ->assertOk()
        ->assertSee('cerrada');

    $this->post(route('student.evaluations.task.submit', $tarea), ['respuesta' => 'Intento'])
        ->assertForbidden();

    expect(Entrega::count())->toBe(0);
});

it('guarda el archivo en almacenamiento privado y solo lo descarga su dueño o el profesor', function () {
    Storage::fake('local');

    $esc = evalEscenario();
    $tarea = evalTarea($esc);

    $this->actingAs($esc->estudiante)
        ->post(route('student.evaluations.task.submit', $tarea), [
            'archivo' => UploadedFile::fake()->create('trabajo.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasNoErrors();

    $entrega = Entrega::firstOrFail();
    Storage::disk('local')->assertExists($entrega->archivo);

    // El estudiante descarga su propio archivo.
    $this->get(route('student.evaluations.task.download', $tarea))->assertOk();

    // Otro estudiante inscrito, sin entrega, no obtiene nada.
    $otro = evalUsuario('estudiante');
    Inscripcion::create(['curso_id' => $esc->curso->id, 'estudiante_id' => $otro->id, 'estado' => 'activo']);
    $this->actingAs($otro)->get(route('student.evaluations.task.download', $tarea))->assertNotFound();

    // El profesor dueño sí; otro profesor no.
    $this->actingAs($esc->profesor)
        ->get(route('professor.tasks.download', [$tarea, $entrega]))
        ->assertOk();

    $this->actingAs(evalUsuario('profesor'))
        ->get(route('professor.tasks.download', [$tarea, $entrega]))
        ->assertForbidden();
});

it('el profesor califica, el estudiante ve nota y retroalimentación y ya no puede modificar', function () {
    $esc = evalEscenario();
    $tarea = evalTarea($esc, ['puntaje_maximo' => 20]);

    $this->actingAs($esc->estudiante)
        ->post(route('student.evaluations.task.submit', $tarea), ['respuesta' => 'Mi trabajo']);

    $entrega = Entrega::firstOrFail();

    $this->actingAs($esc->profesor)
        ->put(route('professor.tasks.grade', [$tarea, $entrega]), [
            'calificacion' => 15,
            'retroalimentacion' => 'Buen trabajo, revisa el punto 3.',
        ])
        ->assertSessionHas('success');

    $entrega->refresh();
    expect($entrega->estado)->toBe('calificada')->and((float) $entrega->calificacion)->toBe(15.0);

    $cal = Calificacion::where('tarea_id', $tarea->id)->firstOrFail();
    expect((float) $cal->calificacion)->toBe(75.0)->and($cal->tipo)->toBe('tarea');

    $this->actingAs($esc->estudiante)
        ->get(route('student.evaluations.task.show', $tarea))
        ->assertOk()
        ->assertSee('Buen trabajo, revisa el punto 3.');

    $this->post(route('student.evaluations.task.submit', $tarea), ['respuesta' => 'Cambio tardío'])
        ->assertSessionHas('error');

    expect($entrega->fresh()->respuesta)->toBe('Mi trabajo');

    // Recalificar actualiza la misma calificación (sin duplicar).
    $this->actingAs($esc->profesor)
        ->put(route('professor.tasks.grade', [$tarea, $entrega]), ['calificacion' => 18]);

    expect(Calificacion::where('tarea_id', $tarea->id)->count())->toBe(1)
        ->and((float) Calificacion::where('tarea_id', $tarea->id)->first()->calificacion)->toBe(90.0);
});

it('rechaza notas superiores al máximo y a profesores ajenos', function () {
    $esc = evalEscenario();
    $tarea = evalTarea($esc, ['puntaje_maximo' => 20]);

    $this->actingAs($esc->estudiante)
        ->post(route('student.evaluations.task.submit', $tarea), ['respuesta' => 'Trabajo']);

    $entrega = Entrega::firstOrFail();

    $this->actingAs($esc->profesor)
        ->put(route('professor.tasks.grade', [$tarea, $entrega]), ['calificacion' => 25])
        ->assertSessionHasErrors('calificacion');

    $this->actingAs(evalUsuario('profesor'))
        ->put(route('professor.tasks.grade', [$tarea, $entrega]), ['calificacion' => 10])
        ->assertForbidden();

    expect($entrega->fresh()->estado)->not->toBe('calificada');
});

it('lista tareas pendientes y las quita al entregar', function () {
    $esc = evalEscenario();
    $tarea = evalTarea($esc, ['titulo' => 'Tarea pendiente X']);

    $this->actingAs($esc->estudiante)
        ->get(route('student.evaluations.index'))
        ->assertSee('Tarea pendiente X');

    $this->post(route('student.evaluations.task.submit', $tarea), ['respuesta' => 'Listo']);

    $this->get(route('student.evaluations.index'))->assertDontSee('Tarea pendiente X');
    $this->get(route('student.evaluations.history'))->assertSee('Tarea pendiente X');
});
