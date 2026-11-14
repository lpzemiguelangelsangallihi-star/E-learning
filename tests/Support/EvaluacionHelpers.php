<?php

use App\Models\Curso;
use App\Models\Examen;
use App\Models\Inscripcion;
use App\Models\Leccion;
use App\Models\Modulo;
use App\Models\OpcionPregunta;
use App\Models\Pregunta;
use App\Models\Rol;
use App\Models\Tarea;
use App\Models\User;

/*
| Ayudas compartidas por las pruebas del módulo de evaluaciones.
| Se cargan con require_once desde cada archivo de prueba.
*/

if (! function_exists('evalUsuario')) {
    function evalUsuario(string $rol): User
    {
        static $n = 0;
        $n++;

        $r = Rol::firstOrCreate(['nombre' => $rol], ['descripcion' => $rol]);

        return User::create([
            'rol_id' => $r->id,
            'nombre' => ucfirst($rol).$n,
            'apellido_paterno' => 'Prueba',
            'apellido_materno' => 'Test',
            'email' => "{$rol}{$n}-".uniqid().'@example.test',
            'password' => 'password',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);
    }
}

if (! function_exists('evalEscenario')) {
    /** Profesor + curso publicado + unidad + tema publicado + estudiante inscrito. */
    function evalEscenario(): object
    {
        $profesor = evalUsuario('profesor');
        $estudiante = evalUsuario('estudiante');

        $curso = Curso::create([
            'profesor_id' => $profesor->id,
            'titulo' => 'Curso de prueba',
            'estado' => 'publicado',
        ]);

        $modulo = Modulo::create(['curso_id' => $curso->id, 'titulo' => 'Unidad 1', 'orden' => 1, 'estado' => 'activo']);
        $leccion = Leccion::create(['modulo_id' => $modulo->id, 'titulo' => 'Tema 1', 'orden' => 1, 'estado' => 'publicado']);

        Inscripcion::create(['curso_id' => $curso->id, 'estudiante_id' => $estudiante->id, 'estado' => 'activo']);

        return (object) compact('profesor', 'estudiante', 'curso', 'modulo', 'leccion');
    }
}

if (! function_exists('evalPregunta')) {
    /**
     * @param  array<int, array{0:string,1:bool}>  $opciones  [texto, es_correcta]
     */
    function evalPregunta(Examen $examen, string $tipo, array $opciones, float $puntaje = 1, int $orden = 1): Pregunta
    {
        $p = Pregunta::create([
            'examen_id' => $examen->id,
            'pregunta' => "Pregunta {$tipo} {$orden}",
            'tipo' => $tipo,
            'puntaje' => $puntaje,
            'orden' => $orden,
        ]);

        foreach ($opciones as [$texto, $ok]) {
            OpcionPregunta::create(['pregunta_id' => $p->id, 'opcion' => $texto, 'es_correcta' => $ok]);
        }

        return $p->load('opciones');
    }
}

if (! function_exists('evalExamen')) {
    /** Examen publicado con una pregunta de cada tipo (1 punto cada una, nota máxima 100). */
    function evalExamen(object $esc, array $attrs = []): object
    {
        $examen = Examen::create($attrs + [
            'leccion_id' => $esc->leccion->id,
            'profesor_id' => $esc->profesor->id,
            'titulo' => 'Examen de prueba',
            'tiempo_limite' => null,
            'intentos_permitidos' => 1,
            'puntaje_maximo' => 100,
            'estado' => 'publicado',
        ]);

        $multiple = evalPregunta($examen, 'opcion_multiple', [['A', false], ['B', true], ['C', false]], 1, 1);
        $vf = evalPregunta($examen, 'verdadero_falso', [['Verdadero', true], ['Falso', false]], 1, 2);
        $varias = evalPregunta($examen, 'varias_respuestas', [['W', true], ['X', true], ['Y', false], ['Z', false]], 1, 3);
        $corta = evalPregunta($examen, 'respuesta_corta', [['Investigación', true], ['operativa', true]], 1, 4);

        return (object) compact('examen', 'multiple', 'vf', 'varias', 'corta');
    }
}

if (! function_exists('evalOpcion')) {
    function evalOpcion(\App\Models\Pregunta $pregunta, string $texto): int
    {
        return (int) $pregunta->opciones->firstWhere('opcion', $texto)->id;
    }
}

if (! function_exists('evalTarea')) {
    function evalTarea(object $esc, array $attrs = []): Tarea
    {
        return Tarea::create($attrs + [
            'leccion_id' => $esc->leccion->id,
            'profesor_id' => $esc->profesor->id,
            'titulo' => 'Tarea de prueba',
            'instrucciones' => 'Resuelve los ejercicios.',
            'fecha_limite' => null,
            'puntaje_maximo' => 20,
            'estado' => 'publicada',
        ]);
    }
}
