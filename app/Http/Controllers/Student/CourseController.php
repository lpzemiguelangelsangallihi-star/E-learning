<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Leccion;
use App\Models\ProgresoCurso;
use App\Models\ProgresoLeccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /**
     * Mis cursos.
     */
    public function index(Request $request)
    {
        $estudianteId = Auth::id();

        $search = trim($request->string('search')->toString());
        $estado = $request->string('estado')->toString();

        $inscripciones = Inscripcion::query()
            ->with('curso.materia', 'curso.profesor')
            ->where('estudiante_id', $estudianteId)
            ->whereIn('estado', ['activo', 'finalizado'])
            ->when(
                in_array($estado, ['activo', 'finalizado'], true),
                fn ($query) => $query->where('estado', $estado)
            )
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('curso', function ($query) use ($search) {
                    $query->where('titulo', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('fecha_inscripcion')
            ->get();

        $progresos = ProgresoCurso::query()
            ->where('estudiante_id', $estudianteId)
            ->get()
            ->keyBy('curso_id');

        return view('student.courses.index', compact(
            'inscripciones',
            'progresos',
            'search',
            'estado'
        ));
    }

    /**
     * Formulario para inscribirse.
     */
    public function join()
    {
        return view('student.courses.join');
    }

    /**
     * Inscripción compatible con los códigos de ambas versiones.
     */
    public function enroll(Request $request)
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:20'],
        ], [
            'codigo.required' => 'Ingresa el código del curso.',
        ]);

        $codigo = strtoupper(
            preg_replace('/[^A-Za-z0-9]/', '', $validated['codigo'])
        );

        $estudianteId = Auth::id();

        $curso = Curso::query()
            ->where(fn ($query) => $query
                ->where('codigo_clase', $codigo)
                ->orWhere('codigo_acceso', $codigo))
            ->where('estado', 'publicado')
            ->first();

        if (!$curso) {
            return back()
                ->withErrors([
                    'codigo' => 'El código no corresponde a ningún curso disponible.',
                ])
                ->withInput();
        }

        $inscripcion = Inscripcion::query()
            ->where('curso_id', $curso->id)
            ->where('estudiante_id', $estudianteId)
            ->first();

        if ($inscripcion && $inscripcion->estado !== 'cancelado') {
            return redirect()
                ->route('student.courses.show', $curso)
                ->with('info', 'Ya estás inscrito en este curso.');
        }

        DB::transaction(function () use (
            $inscripcion,
            $curso,
            $estudianteId
        ) {
            if ($inscripcion) {
                $inscripcion->update([
                    'estado' => 'activo',
                    'fecha_inscripcion' => now(),
                ]);
            } else {
                Inscripcion::create([
                    'curso_id' => $curso->id,
                    'estudiante_id' => $estudianteId,
                    'fecha_inscripcion' => now(),
                    'estado' => 'activo',
                ]);
            }

            ProgresoCurso::recalcular($curso->id, $estudianteId);
        });

        return redirect()
            ->route('student.courses.show', $curso)
            ->with('success', "Te inscribiste en «{$curso->titulo}».");
    }

    /**
     * Detalle del curso.
     */
    public function show(Curso $curso)
    {
        $this->authorizeEnrollment($curso);

        $estudianteId = Auth::id();

        $curso->load('materia', 'profesor');

        $modulos = $this->modulosConLecciones($curso);

        $completadas = ProgresoLeccion::query()
            ->where('estudiante_id', $estudianteId)
            ->where('completado', true)
            ->pluck('leccion_id')
            ->all();

        $progreso = ProgresoCurso::query()
            ->where('curso_id', $curso->id)
            ->where('estudiante_id', $estudianteId)
            ->first();

        $cursosDelProfesor = Curso::query()
            ->where('profesor_id', $curso->profesor_id)
            ->where('estado', 'publicado')
            ->count();

        $siguiente = $modulos
            ->flatMap->lecciones
            ->first(
                fn ($leccion) => !in_array(
                    $leccion->id,
                    $completadas,
                    true
                )
            );

        return view('student.courses.show', compact(
            'curso',
            'modulos',
            'completadas',
            'progreso',
            'cursosDelProfesor',
            'siguiente'
        ));
    }

    /**
     * Contenido de un tema.
     */
    public function lesson(Curso $curso, Leccion $leccion)
    {
        $this->authorizeEnrollment($curso);

        $leccion->load('modulo');

        abort_unless(
            $leccion->modulo
                && (int) $leccion->modulo->curso_id === (int) $curso->id
                && $leccion->modulo->estado === 'activo'
                && $leccion->estado === 'publicado',
            404
        );

        $estudianteId = Auth::id();

        $leccion->load([
            'videos' => fn ($query) => $query->orderBy('orden'),
            'materiales',
            'tareas' => fn ($query) => $query->where('estado', 'publicada'),
            'examenes' => fn ($query) => $query->where('estado', 'publicado'),
        ]);

        $entregas = DB::table('entregas')
            ->where('estudiante_id', $estudianteId)
            ->whereIn('tarea_id', $leccion->tareas->pluck('id'))
            ->get()
            ->keyBy('tarea_id');

        $intentos = DB::table('intentos_examen')
            ->where('estudiante_id', $estudianteId)
            ->where('estado', 'finalizado')
            ->whereIn('examen_id', $leccion->examenes->pluck('id'))
            ->select(
                'examen_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('MAX(calificacion) as mejor')
            )
            ->groupBy('examen_id')
            ->get()
            ->keyBy('examen_id');

        $completado = ProgresoLeccion::query()
            ->where('leccion_id', $leccion->id)
            ->where('estudiante_id', $estudianteId)
            ->where('completado', true)
            ->exists();

        $lista = $this->modulosConLecciones($curso)
            ->flatMap->lecciones
            ->values();

        $posicion = $lista->search(
            fn ($item) => (int) $item->id === (int) $leccion->id
        );

        $anterior = $posicion !== false && $posicion > 0
            ? $lista[$posicion - 1]
            : null;

        $siguiente = $posicion !== false
            ? $lista->get($posicion + 1)
            : null;

        return view('student.courses.lesson', compact(
            'curso',
            'leccion',
            'entregas',
            'intentos',
            'completado',
            'anterior',
            'siguiente'
        ));
    }

    /**
     * Completa un tema y actualiza progreso, XP y racha.
     */
    public function completeLesson(Curso $curso, Leccion $leccion)
    {
        $this->authorizeEnrollment($curso);

        $leccion->load('modulo');

        abort_unless(
            $leccion->modulo
                && (int) $leccion->modulo->curso_id === (int) $curso->id
                && $leccion->modulo->estado === 'activo'
                && $leccion->estado === 'publicado',
            404
        );

        $estudianteId = Auth::id();
        $nuevo = false;

        DB::transaction(function () use (
            $curso,
            $leccion,
            $estudianteId,
            &$nuevo
        ) {
            $progreso = ProgresoLeccion::firstOrNew([
                'leccion_id' => $leccion->id,
                'estudiante_id' => $estudianteId,
            ]);

            $nuevo = !$progreso->completado;

            $progreso->porcentaje = 100;
            $progreso->completado = true;
            $progreso->fecha_inicio = $progreso->fecha_inicio ?? now();
            $progreso->fecha_completado = $progreso->fecha_completado ?? now();
            $progreso->save();

            ProgresoCurso::recalcular($curso->id, $estudianteId);

            if ($nuevo) {
                $this->registrarActividad($estudianteId);
            }
        });

        return back()->with(
            'success',
            $nuevo
                ? 'Tema completado. ¡Ganaste 10 XP!'
                : 'Este tema ya estaba completado.'
        );
    }

    /**
     * Verifica inscripción y disponibilidad del curso.
     */
    private function authorizeEnrollment(Curso $curso): Inscripcion
    {
        abort_unless($curso->estado === 'publicado', 404);

        $inscripcion = Inscripcion::query()
            ->where('curso_id', $curso->id)
            ->where('estudiante_id', Auth::id())
            ->whereIn('estado', ['activo', 'finalizado'])
            ->first();

        abort_unless(
            $inscripcion,
            403,
            'No estás inscrito en este curso.'
        );

        return $inscripcion;
    }

    private function modulosConLecciones(Curso $curso)
    {
        return $curso->modulos()
            ->where('estado', 'activo')
            ->orderBy('orden')
            ->with([
                'lecciones' => fn ($query) => $query
                    ->where('estado', 'publicado')
                    ->orderBy('orden')
                    ->withCount(['videos', 'materiales']),
            ])
            ->get();
    }

    /**
     * Registra experiencia y racha diaria.
     */
    private function registrarActividad(int $estudianteId): void
    {
        $hoy = now()->toDateString();

        $existe = DB::table('usuario_experiencia')
            ->where('estudiante_id', $estudianteId)
            ->exists();

        if ($existe) {
            DB::table('usuario_experiencia')
                ->where('estudiante_id', $estudianteId)
                ->increment('experiencia_total', 10);
        } else {
            DB::table('usuario_experiencia')->insert([
                'estudiante_id' => $estudianteId,
                'experiencia_total' => 10,
            ]);
        }

        $racha = DB::table('rachas')
            ->where('estudiante_id', $estudianteId)
            ->first();

        if (!$racha) {
            DB::table('rachas')->insert([
                'estudiante_id' => $estudianteId,
                'racha_actual' => 1,
                'mejor_racha' => 1,
                'ultima_fecha' => $hoy,
            ]);

            return;
        }

        if ($racha->ultima_fecha === $hoy) {
            return;
        }

        $actual = $racha->ultima_fecha === now()->subDay()->toDateString()
            ? $racha->racha_actual + 1
            : 1;

        DB::table('rachas')
            ->where('estudiante_id', $estudianteId)
            ->update([
                'racha_actual' => $actual,
                'mejor_racha' => max($racha->mejor_racha, $actual),
                'ultima_fecha' => $hoy,
            ]);
    }
}