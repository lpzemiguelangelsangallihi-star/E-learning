
<?php

use App\Livewire\Professor\CatalogoRefuerzosIOP;
use App\Models\IopEjercicioRefuerzo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

require_once __DIR__ . '/../../Support/EvaluacionHelpers.php';

test('profesor crea un ejercicio IOP desde Livewire', function () {
    $esc = evalEscenario();

    $this->actingAs($esc->profesor);

    Livewire::test(CatalogoRefuerzosIOP::class, [
        'leccionId' => $esc->leccion->id,
    ])
        ->call('nuevo')
        ->set('titulo', 'Refuerzo de programación lineal')
        ->set('descripcion', 'Resolver un problema de optimización.')
        ->set('tipo', 'practico')
        ->set('instrucciones', 'Plantear las restricciones y resolver.')
        ->set('orden', 1)
        ->call('guardar')
        ->assertHasNoErrors();

    $ejercicio = IopEjercicioRefuerzo::firstOrFail();

    expect($ejercicio->tipo)->toBe('practico');
    expect($ejercicio->activo)->toBeTrue();
    expect((int) $ejercicio->leccion_id)
        ->toBe((int) $esc->leccion->id);
});

test('catalogo rechaza tipos de ejercicio invalidos', function () {
    $esc = evalEscenario();

    $this->actingAs($esc->profesor);

    Livewire::test(CatalogoRefuerzosIOP::class, [
        'leccionId' => $esc->leccion->id,
    ])
        ->call('nuevo')
        ->set('titulo', 'Ejercicio inválido')
        ->set('descripcion', 'Descripción válida.')
        ->set('tipo', 'inventado')
        ->set('instrucciones', 'Resolver el ejercicio.')
        ->call('guardar')
        ->assertHasErrors(['tipo']);

    expect(IopEjercicioRefuerzo::count())->toBe(0);
});

test('profesor desactiva un ejercicio IOP', function () {
    $esc = evalEscenario();

    $ejercicio = IopEjercicioRefuerzo::create([
        'leccion_id' => $esc->leccion->id,
        'titulo' => 'Ejercicio visual',
        'descripcion' => 'Interpretar un gráfico.',
        'tipo' => 'visual',
        'instrucciones' => 'Analizar los resultados.',
        'orden' => 1,
        'activo' => true,
    ]);

    $this->actingAs($esc->profesor);

    Livewire::test(CatalogoRefuerzosIOP::class, [
        'leccionId' => $esc->leccion->id,
    ])
        ->call('cambiarEstado', $ejercicio->id)
        ->assertHasNoErrors();

    expect($ejercicio->fresh()->activo)->toBeFalse();
});

test('profesor ajeno no administra catalogos de otro curso', function () {
    $esc = evalEscenario();
    $ajeno = evalUsuario('profesor');

    $this->actingAs($ajeno);

    expect(
        fn () => Livewire::test(CatalogoRefuerzosIOP::class, [
            'leccionId' => $esc->leccion->id,
        ])
    )->toThrow(ModelNotFoundException::class);
});

test('catalogo valida longitud del titulo', function () {
    $esc = evalEscenario();

    $this->actingAs($esc->profesor);

    Livewire::test(CatalogoRefuerzosIOP::class, [
        'leccionId' => $esc->leccion->id,
    ])
        ->call('nuevo')
        ->set('titulo', 'AB')
        ->set('descripcion', 'Descripción válida.')
        ->set('tipo', 'teorico')
        ->set('instrucciones', 'Explica el concepto.')
        ->call('guardar')
        ->assertHasErrors(['titulo']);
});
