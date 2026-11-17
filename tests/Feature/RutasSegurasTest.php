<?php

use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Leccion;
use App\Models\Material;
use App\Models\Modulo;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/*
|--------------------------------------------------------------------------
| Rutas públicas con ULID, bindings anidados y archivos firmados
|--------------------------------------------------------------------------
*/

function rutasSegurasUsuario(string $rol, array $atributos = []): User
{
    $rolModel = Rol::firstOrCreate(
        ['nombre' => $rol],
        ['descripcion' => "Rol de prueba {$rol}"]
    );

    return User::create(array_merge([
        'rol_id' => $rolModel->id,
        'nombre' => ucfirst($rol),
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Rutas',
        'email' => $rol.'-'.uniqid().'@example.test',
        'password' => 'password',
        'estado' => 'activo',
        'email_verified_at' => now(),
    ], $atributos));
}

function rutasSegurasCurso(User $profesor, array $atributos = []): Curso
{
    return Curso::create(array_merge([
        'profesor_id' => $profesor->id,
        'titulo' => 'Curso de rutas',
        'estado' => 'publicado',
    ], $atributos));
}

function rutasSegurasModulo(Curso $curso, array $atributos = []): Modulo
{
    return Modulo::create(array_merge([
        'curso_id' => $curso->id,
        'titulo' => 'Unidad de rutas',
        'orden' => 1,
        'estado' => 'activo',
    ], $atributos));
}

function rutasSegurasLeccion(Modulo $modulo, array $atributos = []): Leccion
{
    return Leccion::create(array_merge([
        'modulo_id' => $modulo->id,
        'titulo' => 'Tema de rutas',
        'orden' => 1,
        'estado' => 'publicado',
    ], $atributos));
}

beforeEach(function () {
    Storage::fake('local');
});

it('genera URLs con ULID en los recursos académicos', function () {
    $profesor = rutasSegurasUsuario('profesor');
    $curso = rutasSegurasCurso($profesor);
    $modulo = rutasSegurasModulo($curso);
    $leccion = rutasSegurasLeccion($modulo);

    $material = Material::create([
        'leccion_id' => $leccion->id,
        'titulo' => 'Guía',
        'tipo' => 'pdf',
        'archivo' => 'materiales/guia.pdf',
    ]);

    expect($curso->ulid)->toHaveLength(26)
        ->and($modulo->ulid)->toHaveLength(26)
        ->and($leccion->ulid)->toHaveLength(26)
        ->and($material->ulid)->toHaveLength(26)
        ->and(route('professor.courses.structure', $curso))
        ->toContain('/cursos/'.$curso->ulid.'/contenido')
        ->and(route('student.lessons.show', [$curso, $leccion]))
        ->toContain('/temas/'.$leccion->ulid);
});

it('rechaza el identificador numérico cuando la ruta espera un ULID', function () {
    $profesor = rutasSegurasUsuario('profesor');
    $curso = rutasSegurasCurso($profesor);

    $this->actingAs($profesor)
        ->get('/profesor/cursos/'.$curso->id.'/editar')
        ->assertNotFound();
});

it('no permite mezclar un módulo de otro curso en una ruta anidada', function () {
    $profesorA = rutasSegurasUsuario('profesor');
    $cursoA = rutasSegurasCurso($profesorA);
    $moduloA = rutasSegurasModulo($cursoA);
    $leccionA = rutasSegurasLeccion($moduloA);

    $profesorB = rutasSegurasUsuario('profesor');
    $cursoB = rutasSegurasCurso($profesorB);
    $moduloB = rutasSegurasModulo($cursoB);
    $leccionB = rutasSegurasLeccion($moduloB);

    $this->actingAs($profesorA)
        ->get(route('professor.materials.index', [$cursoA, $moduloB, $leccionB]))
        ->assertNotFound();

    $this->actingAs($profesorA)
        ->get(route('professor.materials.index', [$cursoA, $moduloA, $leccionA]))
        ->assertOk();
});

it('no permite borrar un material que pertenece a otra lección', function () {
    $profesor = rutasSegurasUsuario('profesor');

    $cursoA = rutasSegurasCurso($profesor);
    $moduloA = rutasSegurasModulo($cursoA);
    $leccionA = rutasSegurasLeccion($moduloA);

    $cursoB = rutasSegurasCurso($profesor);
    $moduloB = rutasSegurasModulo($cursoB);
    $leccionB = rutasSegurasLeccion($moduloB);

    $materialB = Material::create([
        'leccion_id' => $leccionB->id,
        'titulo' => 'Material ajeno',
        'tipo' => 'pdf',
        'archivo' => 'materiales/ajeno.pdf',
    ]);

    $this->actingAs($profesor)
        ->delete(route('professor.materials.destroy', [$cursoA, $moduloA, $leccionA, $materialB]))
        ->assertNotFound();
});

it('no permite abrir una lección de otro curso desde el panel del estudiante', function () {
    $profesorA = rutasSegurasUsuario('profesor');
    $cursoA = rutasSegurasCurso($profesorA);
    $moduloA = rutasSegurasModulo($cursoA);

    $profesorB = rutasSegurasUsuario('profesor');
    $cursoB = rutasSegurasCurso($profesorB);
    $moduloB = rutasSegurasModulo($cursoB);
    $leccionB = rutasSegurasLeccion($moduloB);

    $estudiante = rutasSegurasUsuario('estudiante');

    Inscripcion::create([
        'curso_id' => $cursoA->id,
        'estudiante_id' => $estudiante->id,
        'estado' => 'activo',
    ]);

    $this->actingAs($estudiante)
        ->get(route('student.lessons.show', [$cursoA, $leccionB]))
        ->assertNotFound();
});

it('exige una URL firmada y autorización para los archivos privados', function () {
    $profesor = rutasSegurasUsuario('profesor');
    $curso = rutasSegurasCurso($profesor);
    $modulo = rutasSegurasModulo($curso);
    $leccion = rutasSegurasLeccion($modulo);

    Storage::disk('local')->put('materiales/guia.pdf', 'contenido privado');

    $material = Material::create([
        'leccion_id' => $leccion->id,
        'titulo' => 'Guía privada',
        'tipo' => 'pdf',
        'archivo' => 'materiales/guia.pdf',
    ]);

    $urlFirmada = URL::temporarySignedRoute(
        'files.material',
        now()->addMinutes(30),
        ['material' => $material]
    );

    $this->actingAs($profesor)
        ->get(route('files.material', $material))
        ->assertForbidden();

    $this->actingAs($profesor)
        ->get($urlFirmada)
        ->assertOk();

    $this->actingAs(rutasSegurasUsuario('estudiante'))
        ->get($urlFirmada)
        ->assertForbidden();
});

it('configura límites para archivos y evaluaciones', function () {
    expect(RateLimiter::limiter('archivos'))->not->toBeNull()
        ->and(RateLimiter::limiter('evaluaciones'))->not->toBeNull();
});
