<?php

require_once __DIR__.'/../Support/EvaluacionHelpers.php';

it('renderiza el dashboard del profesor', function () {
    $profesor = evalUsuario('profesor');

    $this->actingAs($profesor)
        ->get(route('professor.dashboard'))
        ->assertOk()
        ->assertSee('Progreso promedio')
        ->assertSee('Estudiantes que completaron');
});

it('renderiza el dashboard del administrador', function () {
    $administrador = evalUsuario('administrador');

    $this->actingAs($administrador)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Inscripciones activas')
        ->assertSee('Eventos registrados');
});
