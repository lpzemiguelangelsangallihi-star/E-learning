<?php

use App\Models\Curso;
use App\Models\Entrega;
use App\Models\Inscripcion;
use App\Models\Leccion;
use App\Models\Material;
use App\Models\Modulo;
use App\Models\Rol;
use App\Models\Tarea;
use App\Models\User;
use App\Models\Video;
use Database\Seeders\UsersSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Seguridad de acceso: sesiones inactivas, archivos, propiedad y borrado
|--------------------------------------------------------------------------
*/

function seguridadUsuario(string $rol, array $atributos = []): User
{
    $rolModel = Rol::firstOrCreate(
        ['nombre' => $rol],
        ['descripcion' => "Rol de prueba {$rol}"]
    );

    return User::create(array_merge([
        'rol_id' => $rolModel->id,
        'nombre' => ucfirst($rol),
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Seguridad',
        'email' => $rol.'-'.uniqid().'@example.test',
        'password' => 'password',
        'estado' => 'activo',
        'email_verified_at' => now(),
    ], $atributos));
}

function seguridadCurso(User $profesor, array $atributos = []): Curso
{
    return Curso::create(array_merge([
        'profesor_id' => $profesor->id,
        'titulo' => 'Curso de prueba',
        'estado' => 'publicado',
    ], $atributos));
}

function seguridadLeccion(Curso $curso, array $atributos = []): Leccion
{
    $modulo = Modulo::create([
        'curso_id' => $curso->id,
        'titulo' => 'Unidad de prueba',
        'orden' => 1,
        'estado' => 'activo',
    ]);

    return Leccion::create(array_merge([
        'modulo_id' => $modulo->id,
        'titulo' => 'Tema de prueba',
        'orden' => 1,
        'estado' => 'publicado',
    ], $atributos));
}

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
});

it('cierra la sesión de un usuario desactivado aunque ya estuviera autenticado', function () {
    $estudiante = seguridadUsuario('estudiante', ['estado' => 'inactivo']);

    $this->actingAs($estudiante)
        ->get(route('student.dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest('web');
});

it('borra las sesiones abiertas cuando un administrador desactiva al usuario', function () {
    $admin = seguridadUsuario('administrador');
    $estudiante = seguridadUsuario('estudiante');

    DB::table('sessions')->insert([
        'id' => 'sesion-de-prueba',
        'user_id' => $estudiante->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Prueba',
        'payload' => 'contenido',
        'last_activity' => now()->timestamp,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.users.toggle-status', $estudiante))
        ->assertSessionHas('success');

    expect($estudiante->fresh()->estado)->toBe('inactivo')
        ->and(DB::table('sessions')->where('user_id', $estudiante->id)->exists())->toBeFalse();
});

it('redirige al profesor a su panel cuando intenta abrir el panel admin', function () {
    $profesor = seguridadUsuario('profesor');

    $this->actingAs($profesor)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('professor.dashboard'))
        ->assertSessionHas('error');
});

it('redirige al estudiante a su panel cuando intenta abrir el panel del profesor', function () {
    $estudiante = seguridadUsuario('estudiante');

    $this->actingAs($estudiante)
        ->get(route('professor.dashboard'))
        ->assertRedirect(route('student.dashboard'))
        ->assertSessionHas('error');
});

it('mantiene el 403 para peticiones JSON de otro rol', function () {
    $profesor = seguridadUsuario('profesor');

    $this->actingAs($profesor)
        ->getJson(route('admin.dashboard'))
        ->assertForbidden();
});

it('permite el acceso normal a un usuario activo', function () {
    $estudiante = seguridadUsuario('estudiante');

    $this->actingAs($estudiante)
        ->get(route('student.dashboard'))
        ->assertOk();
});

it('solo entrega un material al profesor dueño o a estudiantes inscritos', function () {
    $profesor = seguridadUsuario('profesor');
    $otroProfesor = seguridadUsuario('profesor');
    $estudiante = seguridadUsuario('estudiante');
    $ajeno = seguridadUsuario('estudiante');

    $curso = seguridadCurso($profesor);
    $leccion = seguridadLeccion($curso);

    Inscripcion::create([
        'curso_id' => $curso->id,
        'estudiante_id' => $estudiante->id,
        'estado' => 'activo',
    ]);

    Storage::disk('local')->put('materiales/lecciones/1/guia.pdf', 'contenido privado');

    $material = Material::create([
        'leccion_id' => $leccion->id,
        'titulo' => 'Guía privada',
        'tipo' => 'pdf',
        'archivo' => 'materiales/lecciones/1/guia.pdf',
    ]);

    $this->actingAs($profesor)
        ->get(route('files.material', $material))
        ->assertOk();

    $this->actingAs($estudiante)
        ->get(route('files.material', $material))
        ->assertOk();

    $this->actingAs($ajeno)
        ->get(route('files.material', $material))
        ->assertForbidden();

    $this->actingAs($otroProfesor)
        ->get(route('files.material', $material))
        ->assertForbidden();
});

it('no expone videos locales mediante el enlace público de storage', function () {
    $profesor = seguridadUsuario('profesor');
    $estudiante = seguridadUsuario('estudiante');

    $curso = seguridadCurso($profesor);
    $leccion = seguridadLeccion($curso);

    Inscripcion::create([
        'curso_id' => $curso->id,
        'estudiante_id' => $estudiante->id,
        'estado' => 'activo',
    ]);

    Storage::disk('local')->put('videos/lecciones/1/clase.mp4', 'video');

    $video = Video::create([
        'leccion_id' => $leccion->id,
        'titulo' => 'Clase',
        'url' => 'videos/lecciones/1/clase.mp4',
        'orden' => 1,
    ]);

    expect($video->archivo_url)
        ->toContain('/archivos/videos/'.$video->id)
        ->not->toContain('/storage/');

    $this->actingAs($estudiante)
        ->get(route('files.video', $video))
        ->assertOk();

    $this->actingAs(seguridadUsuario('estudiante'))
        ->get(route('files.video', $video))
        ->assertForbidden();
});

it('solo entrega el adjunto de una tarea a quien puede verla o administrarla', function () {
    $profesor = seguridadUsuario('profesor');
    $estudiante = seguridadUsuario('estudiante');

    $curso = seguridadCurso($profesor);
    $leccion = seguridadLeccion($curso);

    Inscripcion::create([
        'curso_id' => $curso->id,
        'estudiante_id' => $estudiante->id,
        'estado' => 'activo',
    ]);

    Storage::disk('local')->put('tareas/enunciado.pdf', 'enunciado');

    $tarea = Tarea::create([
        'leccion_id' => $leccion->id,
        'profesor_id' => $profesor->id,
        'titulo' => 'Tarea',
        'puntaje_maximo' => 100,
        'archivo' => 'tareas/enunciado.pdf',
        'estado' => 'publicada',
    ]);

    $this->actingAs($estudiante)
        ->get(route('files.tarea', $tarea))
        ->assertOk();

    $this->actingAs($profesor)
        ->get(route('files.tarea', $tarea))
        ->assertOk();

    $this->actingAs(seguridadUsuario('estudiante'))
        ->get(route('files.tarea', $tarea))
        ->assertForbidden();
});

it('elimina los archivos del curso al borrarlo', function () {
    $profesor = seguridadUsuario('profesor');
    $curso = seguridadCurso($profesor);
    $leccion = seguridadLeccion($curso);

    Storage::disk('local')->put('materiales/lecciones/1/guia.pdf', 'contenido');

    Material::create([
        'leccion_id' => $leccion->id,
        'titulo' => 'Guía',
        'tipo' => 'pdf',
        'archivo' => 'materiales/lecciones/1/guia.pdf',
    ]);

    $this->actingAs($profesor)
        ->delete(route('professor.courses.destroy', $curso))
        ->assertRedirect(route('professor.courses.index'));

    expect(Curso::find($curso->id))->toBeNull()
        ->and(Storage::disk('local')->exists('materiales/lecciones/1/guia.pdf'))->toBeFalse();
});

it('impide eliminar a un profesor que todavía tiene cursos', function () {
    $admin = seguridadUsuario('administrador');
    $profesor = seguridadUsuario('profesor');
    seguridadCurso($profesor);

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $profesor))
        ->assertSessionHas('error');

    expect(User::find($profesor->id))->not->toBeNull();
});

it('elimina las entregas y sus archivos al borrar a un estudiante', function () {
    $admin = seguridadUsuario('administrador');
    $profesor = seguridadUsuario('profesor');
    $estudiante = seguridadUsuario('estudiante');

    $curso = seguridadCurso($profesor);
    $leccion = seguridadLeccion($curso);

    $tarea = Tarea::create([
        'leccion_id' => $leccion->id,
        'profesor_id' => $profesor->id,
        'titulo' => 'Tarea',
        'puntaje_maximo' => 100,
        'estado' => 'publicada',
    ]);

    Storage::disk('local')->put('entregas/1/trabajo.pdf', 'trabajo');

    Entrega::create([
        'tarea_id' => $tarea->id,
        'estudiante_id' => $estudiante->id,
        'archivo' => 'entregas/1/trabajo.pdf',
        'estado' => 'entregada',
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $estudiante))
        ->assertRedirect(route('admin.users.index'));

    expect(User::find($estudiante->id))->toBeNull()
        ->and(Entrega::where('estudiante_id', $estudiante->id)->exists())->toBeFalse()
        ->and(Storage::disk('local')->exists('entregas/1/trabajo.pdf'))->toBeFalse();
});

it('exige una contraseña de demostración configurada para sembrar usuarios', function () {
    config(['demo.password' => null]);

    expect(fn () => (new UsersSeeder)->run())
        ->toThrow(RuntimeException::class);
});

it('usa la contraseña configurada y no una credencial fija en el seeder', function () {
    foreach (['administrador', 'profesor', 'estudiante'] as $rol) {
        Rol::firstOrCreate(['nombre' => $rol], ['descripcion' => $rol]);
    }

    config(['demo.password' => 'clave-solo-pruebas']);

    (new UsersSeeder)->run();

    $admin = User::where('email', 'admin@elearning.test')->first();

    expect($admin)->not->toBeNull()
        ->and(Hash::check('clave-solo-pruebas', $admin->password))->toBeTrue()
        ->and(Hash::check('password', $admin->password))->toBeFalse();
});
