<?php

use App\Models\IntentoExamen;
use App\Models\IopEjercicioRefuerzo;
use App\Models\IopModeloDecision;
use App\Services\IOP\AccesoLeccionIOPService;
use App\Services\IOP\EntregaRefuerzoService;
use App\Services\IOP\IntegracionEvaluacionIOPService;
use App\Services\IOP\MaximinService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Livewire\Estudiante\SeguimientoIOP;
use App\Livewire\Professor\RevisionRefuerzosIOP;
use Livewire\Livewire;
use App\Livewire\Professor\PanelSeguimientoIOP;


require_once __DIR__ . '/../../Support/EvaluacionHelpers.php';

/*
|--------------------------------------------------------------------------
| Preparar un seguimiento real para las pruebas de seguridad
|--------------------------------------------------------------------------
*/

function iopEscenarioSeguridad(): object
{
    $esc = evalEscenario();

    IopModeloDecision::create([
        'nombre' => 'Maximin seguridad',
        'metodo' => 'maximin',
        'version' => 1,
        'activo' => true,
        'matriz_utilidades' => MaximinService::MATRIZ_EJEMPLO,
    ]);

    foreach (['teorico', 'practico', 'visual'] as $tipo) {
        IopEjercicioRefuerzo::create([
            'leccion_id' => $esc->leccion->id,
            'titulo' => 'Refuerzo ' . $tipo,
            'descripcion' => 'Ejercicio de seguridad',
            'tipo' => $tipo,
            'instrucciones' => 'Resuelve el ejercicio.',
            'orden' => 1,
            'activo' => true,
        ]);
    }

    $examen = evalExamen($esc, [
        'tipo_iop' => 'diagnostico',
    ]);

    $intento = IntentoExamen::create([
        'examen_id' => $examen->examen->id,
        'estudiante_id' => $esc->estudiante->id,
        'numero_intento' => 1,
        'fecha_inicio' => now()->subMinute(),
        'fecha_fin' => now(),
        'calificacion' => 45,
        'estado' => 'finalizado',
    ]);

    $seguimiento = app(
        IntegracionEvaluacionIOPService::class
    )->procesarDiagnostico($intento->id);

    $ciclo = $seguimiento->ciclosRefuerzo()
        ->with('recomendacion.asignacionRefuerzo')
        ->firstOrFail();

    $esc->asignacion =
        $ciclo->recomendacion->asignacionRefuerzo;

    $esc->seguimiento = $seguimiento;

    return $esc;
}

/*
|--------------------------------------------------------------------------
| 1. Un estudiante ajeno no puede entregar el refuerzo
|--------------------------------------------------------------------------
*/

test('un estudiante no puede entregar refuerzos ajenos', function () {

    $esc = iopEscenarioSeguridad();

    $intruso = evalUsuario('estudiante');

    $servicio = app(EntregaRefuerzoService::class);

    expect(
        fn () => $servicio->entregar(
            $esc->asignacion->id,
            $intruso,
            'Respuesta de prueba suficientemente larga.'
        )
    )->toThrow(HttpException::class);

    expect($esc->asignacion->fresh()->estado)
        ->toBe('asignado');
});

/*
|--------------------------------------------------------------------------
| 2. El estudiante no puede aprobar su propio refuerzo
|--------------------------------------------------------------------------
*/

test('el estudiante no puede aprobar su propio refuerzo', function () {

    $esc = iopEscenarioSeguridad();

    $servicio = app(EntregaRefuerzoService::class);

    $servicio->entregar(
        $esc->asignacion->id,
        $esc->estudiante,
        'Esta es mi respuesta de refuerzo IOP.'
    );

    expect(
        fn () => $servicio->revisar(
            $esc->asignacion->id,
            $esc->estudiante,
            true,
            'Aprobado'
        )
    )->toThrow(HttpException::class);

    expect($esc->asignacion->fresh()->estado)
        ->toBe('en_revision');
});

/*
|--------------------------------------------------------------------------
| 3. Un profesor ajeno no puede revisar el refuerzo
|--------------------------------------------------------------------------
*/

test('un profesor ajeno no puede aprobar refuerzos', function () {

    $esc = iopEscenarioSeguridad();

    $otroProfesor = evalUsuario('profesor');

    $servicio = app(EntregaRefuerzoService::class);

    $servicio->entregar(
        $esc->asignacion->id,
        $esc->estudiante,
        'Respuesta de prueba para revisión docente.'
    );

    expect(
        fn () => $servicio->revisar(
            $esc->asignacion->id,
            $otroProfesor,
            true,
            'Aprobado'
        )
    )->toThrow(HttpException::class);

    expect($esc->asignacion->fresh()->estado)
        ->toBe('en_revision');
});

/*
|--------------------------------------------------------------------------
| 4. El profesor propietario sí puede aprobar el refuerzo
|--------------------------------------------------------------------------
*/

test('el profesor propietario puede aprobar refuerzos', function () {

    $esc = iopEscenarioSeguridad();

    $servicio = app(EntregaRefuerzoService::class);

    $servicio->entregar(
        $esc->asignacion->id,
        $esc->estudiante,
        'Respuesta válida para la revisión del profesor.'
    );

    $resultado = $servicio->revisar(
        $esc->asignacion->id,
        $esc->profesor,
        true,
        'Buen trabajo. Refuerzo aprobado.'
    );

    expect($resultado->estado)->toBe('completado');

    expect((int) $resultado->revisado_por)
        ->toBe((int) $esc->profesor->id);

    expect($resultado->completado_en)->not->toBeNull();
});

/*
|--------------------------------------------------------------------------
| 5. Un estudiante no inscrito no accede a la lección
|--------------------------------------------------------------------------
*/

test('un estudiante no inscrito no accede a IOP', function () {

    $esc = evalEscenario();

    $intruso = evalUsuario('estudiante');

    $servicio = app(AccesoLeccionIOPService::class);

    expect(
        fn () => $servicio->autorizar(
            $intruso,
            $esc->leccion->id
        )
    )->toThrow(HttpException::class);
});

/*
|--------------------------------------------------------------------------
| 6. Un estudiante inscrito sí accede a su lección
|--------------------------------------------------------------------------
*/

test('un estudiante inscrito accede a su leccion IOP', function () {

    $esc = evalEscenario();

    $servicio = app(AccesoLeccionIOPService::class);

    $leccion = $servicio->autorizar(
        $esc->estudiante,
        $esc->leccion->id
    );

    expect($leccion->id)->toBe($esc->leccion->id);
});


test('estudiante entrega refuerzo desde Livewire', function () {

    $esc = iopEscenarioSeguridad();

    $this->actingAs($esc->estudiante);

    Livewire::test(SeguimientoIOP::class, [
        'leccionId' => $esc->leccion->id,
    ])
        ->set(
            'respuesta',
            'Resolví el ejercicio aplicando el método estudiado.'
        )
        ->call('entregarRefuerzo')
        ->assertHasNoErrors()
        ->assertSet('respuesta', '');

    $asignacion = $esc->asignacion->fresh();

    expect($asignacion->estado)->toBe('en_revision');
    expect($asignacion->entregado_en)->not->toBeNull();
    expect($asignacion->respuesta_estudiante)
        ->toBe('Resolví el ejercicio aplicando el método estudiado.');
});

test('profesor aprueba un refuerzo desde Livewire', function () {

    $esc = iopEscenarioSeguridad();

    app(EntregaRefuerzoService::class)->entregar(
        $esc->asignacion->id,
        $esc->estudiante,
        'Respuesta académica que requiere revisión.'
    );

    $this->actingAs($esc->profesor);

    Livewire::test(RevisionRefuerzosIOP::class)
        ->call('verDetalle', $esc->asignacion->id)
        ->assertSet('asignacionId', $esc->asignacion->id)
        ->set('retroalimentacion', 'Respuesta correcta.')
        ->call('aprobar')
        ->assertHasNoErrors()
        ->assertSet('asignacionId', null);

    $asignacion = $esc->asignacion->fresh();

    expect($asignacion->estado)->toBe('completado');
    expect((int) $asignacion->revisado_por)
        ->toBe((int) $esc->profesor->id);
    expect($asignacion->completado_en)->not->toBeNull();
});


test('profesor ajeno no puede seleccionar un refuerzo', function () {

    $esc = iopEscenarioSeguridad();

    app(EntregaRefuerzoService::class)->entregar(
        $esc->asignacion->id,
        $esc->estudiante,
        'Respuesta de prueba para revisión.'
    );

    $otroProfesor = evalUsuario('profesor');

    $this->actingAs($otroProfesor);

    expect(
        fn () => Livewire::test(RevisionRefuerzosIOP::class)
            ->call('verDetalle', $esc->asignacion->id)
    )->toThrow(
        \Illuminate\Database\Eloquent\ModelNotFoundException::class
    );

    // El profesor ajeno no modificó el refuerzo.
    expect($esc->asignacion->fresh()->estado)
        ->toBe('en_revision');
});

test('estudiante corrige y reentrega un refuerzo desde Livewire', function () {

    $esc = iopEscenarioSeguridad();

    // 1. Primera entrega del estudiante.
    $this->actingAs($esc->estudiante);

    Livewire::test(SeguimientoIOP::class, [
        'leccionId' => $esc->leccion->id,
    ])
        ->set('respuesta', 'Mi primera solución del ejercicio.')
        ->call('entregarRefuerzo')
        ->assertHasNoErrors();

    expect($esc->asignacion->fresh()->estado)
        ->toBe('en_revision');

    // 2. El profesor solicita correcciones.
    $this->flushSession();
    $this->actingAs($esc->profesor);

    Livewire::test(RevisionRefuerzosIOP::class)
        ->call('verDetalle', $esc->asignacion->id)
        ->set(
            'retroalimentacion',
            'Revisa el procedimiento y explica tus resultados.'
        )
        ->call('solicitarCorreccion')
        ->assertHasNoErrors();

    $asignacion = $esc->asignacion->fresh();

    expect($asignacion->estado)
        ->toBe('requiere_correccion');

    expect($asignacion->completado_en)
        ->toBeNull();

    // 3. El estudiante presenta una solución corregida.
    $this->flushSession();
    $this->actingAs($esc->estudiante);

    Livewire::test(SeguimientoIOP::class, [
        'leccionId' => $esc->leccion->id,
    ])
        ->set(
            'respuesta',
            'Solución corregida con el procedimiento completo.'
        )
        ->call('entregarRefuerzo')
        ->assertHasNoErrors();

    $asignacion = $esc->asignacion->fresh();

    expect($asignacion->estado)
        ->toBe('en_revision');

    expect($asignacion->respuesta_estudiante)
        ->toBe(
            'Solución corregida con el procedimiento completo.'
        );

    // La nueva entrega elimina la revisión anterior.
    expect($asignacion->revisado_por)
        ->toBeNull();

    // 4. El profesor aprueba la nueva respuesta.
    $this->flushSession();
    $this->actingAs($esc->profesor);

    Livewire::test(RevisionRefuerzosIOP::class)
        ->call('verDetalle', $esc->asignacion->id)
        ->set(
            'retroalimentacion',
            'Procedimiento correcto. Ejercicio aprobado.'
        )
        ->call('aprobar')
        ->assertHasNoErrors();

    $asignacion = $esc->asignacion->fresh();

    expect($asignacion->estado)
        ->toBe('completado');

    expect((int) $asignacion->revisado_por)
        ->toBe((int) $esc->profesor->id);

    expect($asignacion->completado_en)
        ->not->toBeNull();
});


test('profesor solo ve seguimientos de sus cursos', function () {

    $propio = iopEscenarioSeguridad();
    $ajeno = iopEscenarioSeguridad();

    $this->actingAs($propio->profesor);

    Livewire::test(PanelSeguimientoIOP::class)
        ->assertSee($propio->estudiante->nombre_completo)
        ->assertDontSee($ajeno->estudiante->nombre_completo)
        ->assertViewHas('estadisticas', function ($datos) {
            return $datos['total'] === 1
                && $datos['en_proceso'] === 1
                && $datos['consolidados'] === 0;
        });
});

test('panel docente filtra refuerzos en revision', function () {

    $esc = iopEscenarioSeguridad();

    app(EntregaRefuerzoService::class)->entregar(
        $esc->asignacion->id,
        $esc->estudiante,
        'Respuesta académica pendiente de revisión.'
    );

    $this->actingAs($esc->profesor);

    Livewire::test(PanelSeguimientoIOP::class)
        ->set('filtro', 'en_revision')
        ->assertSee($esc->estudiante->nombre_completo)
        ->assertViewHas('estadisticas', function ($datos) {
            return $datos['en_revision'] === 1;
        })
        ->assertHasNoErrors();
});

test('panel docente permite buscar estudiantes', function () {

    $esc = iopEscenarioSeguridad();

    $this->actingAs($esc->profesor);

    Livewire::test(PanelSeguimientoIOP::class)
        ->set('busqueda', $esc->estudiante->nombre)
        ->assertSee($esc->estudiante->nombre_completo)
        ->set('busqueda', 'EstudianteInexistenteXYZ')
        ->assertSee(
            'No hay seguimientos reales'
        )
        ->assertHasNoErrors();
});

test('profesor exporta sus seguimientos IOP en CSV', function () {

    $esc = iopEscenarioSeguridad();

    $this->actingAs($esc->profesor);

    $respuesta = $this->get(
        route('professor.iop.reportes.exportar')
    );

    $respuesta->assertOk();

    $respuesta->assertHeader(
        'Content-Type',
        'text/csv; charset=UTF-8'
    );

    $csv = $respuesta->streamedContent();

    expect($csv)
        ->toContain('Estudiante')
        ->toContain('Estado actual')
        ->toContain('Alternativa Maximin')
        ->toContain($esc->estudiante->nombre_completo)
        ->toContain($esc->curso->titulo);
});

test('reporte IOP no incluye estudiantes de otros profesores', function () {

    $propio = iopEscenarioSeguridad();
    $ajeno = iopEscenarioSeguridad();

    $this->actingAs($propio->profesor);

    $respuesta = $this->get(
        route('professor.iop.reportes.exportar')
    );

    $respuesta->assertOk();

    $csv = $respuesta->streamedContent();

    expect($csv)
        ->toContain($propio->estudiante->nombre_completo)
        ->not->toContain($ajeno->estudiante->nombre_completo);
});

test('reporte IOP protege contenido que parece formula', function () {

    $esc = iopEscenarioSeguridad();

    $esc->curso->update([
        'titulo' => '=SUM(1,1)',
    ]);

    $this->actingAs($esc->profesor);

    $respuesta = $this->get(
        route('professor.iop.reportes.exportar')
    );

    $respuesta->assertOk();

    $csv = $respuesta->streamedContent();

    // El valor exportado debe comenzar con
    // un apostrofe para impedir su ejecución
    // como fórmula en una hoja de cálculo.
    expect($csv)->toContain("'=SUM(1,1)");
});


test('ejercicio asignado no puede editarse desde el catalogo', function () {

    $esc = iopEscenarioSeguridad();

    $ejercicioId = $esc->asignacion->ejercicio_id;

    $this->actingAs($esc->profesor);

    Livewire::test(
        \App\Livewire\Professor\CatalogoRefuerzosIOP::class,
        ['leccionId' => $esc->leccion->id]
    )
        ->call('editar', $ejercicioId)
        ->assertHasErrors(['ejercicio']);

    // El ejercicio sigue vinculado a la asignación.
    expect(
        (int) $esc->asignacion->fresh()->ejercicio_id
    )->toBe((int) $ejercicioId);
});


test('un refuerzo aprobado no puede entregarse otra vez', function () {
    $esc = iopEscenarioSeguridad();

    $servicio = app(EntregaRefuerzoService::class);

    $servicio->entregar(
        $esc->asignacion->id,
        $esc->estudiante,
        'Respuesta inicial del ejercicio de refuerzo.'
    );

    $servicio->revisar(
        $esc->asignacion->id,
        $esc->profesor,
        true,
        'Aprobado.'
    );

    expect(
        fn () => $servicio->entregar(
            $esc->asignacion->id,
            $esc->estudiante,
            'Intento de reemplazar la respuesta aprobada.'
        )
    )->toThrow(
        \Illuminate\Validation\ValidationException::class
    );

    expect($esc->asignacion->fresh()->estado)
        ->toBe('completado');
});
