<?php

use App\Models\Insignia;
use App\Models\Notificacion;
use App\Models\Rol;
use App\Models\User;
use App\Models\UsuarioExperiencia;
use App\Models\UsuarioInsignia;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    $this->rol = Rol::create([
        'nombre' => 'Estudiante',
        'descripcion' => 'Rol usado en las pruebas de integridad referencial',
    ]);

    $this->estudiante = User::create([
        'rol_id' => $this->rol->id,
        'nombre' => 'Ana',
        'apellido_paterno' => 'Pérez',
        'apellido_materno' => 'López',
        'email' => 'ana.estudiante@example.com',
        'password' => 'password',
    ]);
});

function existeLlaveForaneaAcademica(string $tabla, string $columna, string $referencia): bool
{
    foreach (Schema::getForeignKeys($tabla) as $llave) {
        if (($llave['columns'] ?? []) === [$columna]
            && ($llave['foreign_table'] ?? null) === $referencia) {
            return true;
        }
    }

    return false;
}

it('relaciona UsuarioExperiencia con User', function () {
    $experiencia = UsuarioExperiencia::create([
        'estudiante_id' => $this->estudiante->id,
        'experiencia_total' => 120,
    ]);

    expect($experiencia->estudiante)
        ->toBeInstanceOf(User::class)
        ->and($experiencia->estudiante->id)->toBe($this->estudiante->id)
        ->and($this->estudiante->experiencia->id)->toBe($experiencia->id);
});

it('relaciona UsuarioInsignia con User e Insignia', function () {
    $insignia = Insignia::create([
        'nombre' => 'Primer curso completado',
        'descripcion' => 'Insignia de prueba',
    ]);

    $usuarioInsignia = UsuarioInsignia::create([
        'estudiante_id' => $this->estudiante->id,
        'insignia_id' => $insignia->id,
    ]);

    expect($usuarioInsignia->estudiante)
        ->toBeInstanceOf(User::class)
        ->and($usuarioInsignia->estudiante->id)->toBe($this->estudiante->id)
        ->and($usuarioInsignia->insignia->id)->toBe($insignia->id)
        ->and($this->estudiante->insignias->pluck('id')->all())->toBe([$insignia->id]);
});

it('relaciona Notificacion con su usuario mediante usuario_id', function () {
    $notificacion = Notificacion::create([
        'usuario_id' => $this->estudiante->id,
        'titulo' => 'Bienvenida',
        'mensaje' => 'Tu cuenta está lista.',
    ]);

    expect($notificacion->usuario)
        ->toBeInstanceOf(User::class)
        ->and($notificacion->usuario->id)->toBe($this->estudiante->id)
        ->and($this->estudiante->notificaciones->pluck('id')->all())->toBe([$notificacion->id]);
});

it('registra las llaves foráneas de las tablas académicas', function () {
    $relaciones = [
        ['usuario_experiencia', 'estudiante_id', 'users'],
        ['usuario_insignias', 'estudiante_id', 'users'],
        ['usuario_insignias', 'insignia_id', 'insignias'],
        ['notificaciones', 'usuario_id', 'users'],
        ['cursos', 'profesor_id', 'users'],
        ['modulos', 'curso_id', 'cursos'],
        ['lecciones', 'modulo_id', 'modulos'],
        ['intentos_examen', 'estudiante_id', 'users'],
        ['calificaciones', 'curso_id', 'cursos'],
        ['inscripciones', 'estudiante_id', 'users'],
    ];

    foreach ($relaciones as [$tabla, $columna, $referencia]) {
        expect(existeLlaveForaneaAcademica($tabla, $columna, $referencia))
            ->toBeTrue("Falta la llave foránea {$tabla}.{$columna} -> {$referencia}.id");
    }
});

it('impide insertar registros huérfanos en las relaciones corregidas', function () {
    $idInexistente = 999999;

    expect(fn () => DB::table('usuario_experiencia')->insert([
        'estudiante_id' => $idInexistente,
        'experiencia_total' => 10,
    ]))->toThrow(QueryException::class);

    expect(fn () => DB::table('usuario_insignias')->insert([
        'estudiante_id' => $idInexistente,
        'insignia_id' => $idInexistente,
    ]))->toThrow(QueryException::class);

    expect(fn () => DB::table('notificaciones')->insert([
        'usuario_id' => $idInexistente,
        'titulo' => 'Huérfana',
        'mensaje' => 'No debería existir.',
    ]))->toThrow(QueryException::class);
});

it('elimina en cascada los datos personales al borrar al usuario', function () {
    UsuarioExperiencia::create([
        'estudiante_id' => $this->estudiante->id,
        'experiencia_total' => 50,
    ]);

    $insignia = Insignia::create(['nombre' => 'Insignia de prueba']);

    UsuarioInsignia::create([
        'estudiante_id' => $this->estudiante->id,
        'insignia_id' => $insignia->id,
    ]);

    Notificacion::create([
        'usuario_id' => $this->estudiante->id,
        'titulo' => 'Notificación',
        'mensaje' => 'Contenido',
    ]);

    $this->estudiante->delete();

    expect(UsuarioExperiencia::count())->toBe(0)
        ->and(UsuarioInsignia::count())->toBe(0)
        ->and(Notificacion::count())->toBe(0);
});
