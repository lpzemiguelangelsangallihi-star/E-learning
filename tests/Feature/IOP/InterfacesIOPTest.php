<?php

use App\Livewire\Estudiante\SeguimientoIOP;
use App\Livewire\Professor\PanelSeguimientoIOP;
use App\Livewire\Professor\RevisionRefuerzosIOP;
use Livewire\Livewire;

require_once __DIR__ . '/../../Support/EvaluacionHelpers.php';

test('el estudiante inscrito puede abrir seguimiento IOP', function () {
    $esc = evalEscenario();

    $this->actingAs($esc->estudiante);

    Livewire::test(SeguimientoIOP::class, [
        'leccionId' => $esc->leccion->id,
    ])->assertOk();
});

test('un estudiante no inscrito no puede abrir seguimiento IOP', function () {
    $esc = evalEscenario();
    $otro = evalUsuario('estudiante');

    $this->actingAs($otro);

    Livewire::test(SeguimientoIOP::class, [
        'leccionId' => $esc->leccion->id,
    ])->assertForbidden();
});

test('el profesor puede abrir su panel de seguimientos', function () {
    $esc = evalEscenario();

    $this->actingAs($esc->profesor);

    Livewire::test(PanelSeguimientoIOP::class)
        ->assertOk();
});

test('el estudiante no puede abrir el panel docente de seguimientos', function () {
    $esc = evalEscenario();

    $this->actingAs($esc->estudiante);

    Livewire::test(PanelSeguimientoIOP::class)
        ->assertForbidden();
});

test('el profesor puede abrir la revision de refuerzos', function () {
    $esc = evalEscenario();

    $this->actingAs($esc->profesor);

    Livewire::test(RevisionRefuerzosIOP::class)
        ->assertOk();
});

test('la proyeccion Markov limita los ciclos a diez', function () {
    $esc = evalEscenario();

    $this->actingAs($esc->estudiante);

    Livewire::test(SeguimientoIOP::class, [
        'leccionId' => $esc->leccion->id,
    ])
        ->set('ciclos', 20)
        ->assertSet('ciclos', 10)
        ->set('ciclos', 0)
        ->assertSet('ciclos', 1);
});
