<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Leccion;
<<<<<<< HEAD
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
=======
use App\Models\ProgresoCurso;
use App\Models\ProgresoLeccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
>>>>>>> bb5ded3887008384a4b1ffae92a0fd0dc2279343

class CourseController extends Controller
{
    /**
<<<<<<< HEAD
     * Inscripción mediante código de clase.
     */
    public function join(Request $request)
    {
        $request->validate(
            ['codigo' => ['required', 'string', 'max:20']],
            ['codigo.required' => 'Ingresa el código de clase.']
        );

        $codigo = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->input('codigo')));

        $curso = Curso::where('codigo_clase', $codigo)->first();

        if (! $curso) {
            return back()
                ->withErrors(['codigo' => 'El código no corresponde a ningún curso.'])
                ->withInput();
        }

        if ($curso->estado !== 'publicado') {
            return back()
                ->withErrors(['codigo' => 'Este curso todavía no está disponible.'])
                ->withInput();
        }

        $inscripcion = Inscripcion::where('curso_id', $curso->id)
            ->where('estudiante_id', Auth::id())
            ->first();

        if ($inscripcion) {
            if ($inscripcion->estado === 'cancelado') {
                $inscripcion->update([
                    'estado' => 'activo',
                    'fecha_inscripcion' => now(),
                ]);

                return redirect()
                    ->route('student.courses.show', $curso)
                    ->with('success', 'Volviste a inscribirte en el curso.');
            }

            return redirect()
                ->route('student.courses.show', $curso)
                ->with('success', 'Ya estabas inscrito en este curso.');
        }

        Inscripcion::create([
            'curso_id' => $curso->id,
            'estudiante_id' => Auth::id(),
            'fecha_inscripcion' => now(),
            'estado' => 'activo',
        ]);
=======
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
     * Formulario para inscribirse con código.
     */
    public function join()
    {
        return view('student.courses.join');
    }

    /**
     * Inscribe al estudiante usando el código de acceso del curso.
     */
    public function enroll(Request $request)
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:20'],
        ], [
            'codigo.required' => 'Ingresa el código del curso.',
        ]);

        $codigo = strtoupper(trim($validated['codigo']));
        $estudianteId = Auth::id();

        $curso = Curso::query()
            ->where('codigo_acceso', $codigo)
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

        DB::transaction(function () use ($inscripcion, $curso, $estudianteId) {

            if ($inscripcion) {
                // Reactiva una inscripción cancelada.
                $inscripcion->update(['estado' => 'activo']);
            } else {
                Inscripcion::create([
                    'curso_id' => $curso->id,
                    'estudiante_id' => $estudianteId,
                    'estado' => 'activo',
                ]);
            }

            ProgresoCurso::recalcular($curso->id, $estudianteId);
        });
>>>>>>> bb5ded3887008384a4b1ffae92a0fd0dc2279343

        return redirect()
            ->route('student.courses.show', $curso)
            ->with('success', "Te inscribiste en «{$curso->titulo}».");
    }

    /**
<<<<<<< HEAD
     * Estructura del curso: unidades activas con temas publicados.
     */
    public function show(Curso $curso)
    {
        $this->authorizeAccess($curso);

        $curso->load(['materia', 'profesor']);

        $modulos = $this->visibleStructure($curso);

        return view('student.courses.show', compact('curso', 'modulos'));
    }

    /**
     * Contenido de un tema: texto, videos y materiales.
     */
    public function lesson(Curso $curso, Leccion $leccion)
    {
        $this->authorizeAccess($curso);

        $leccion->load('modulo');

        if (
            ! $leccion->modulo
            || (int) $leccion->modulo->curso_id !== (int) $curso->id
            || $leccion->modulo->estado !== 'activo'
            || $leccion->estado !== 'publicado'
        ) {
            abort(404);
        }

        $leccion->load(['videos', 'materiales']);

        // Navegación anterior / siguiente entre los temas visibles.
        $plano = $this->visibleStructure($curso)
            ->flatMap(fn ($modulo) => $modulo->lecciones)
            ->values();

        $posicion = $plano->search(fn ($item) => (int) $item->id === (int) $leccion->id);

        $anterior = $posicion !== false && $posicion > 0 ? $plano[$posicion - 1] : null;
        $siguiente = $posicion !== false ? $plano->get($posicion + 1) : null;

        return view('student.lessons.show', compact('curso', 'leccion', 'anterior', 'siguiente'));
    }

    private function authorizeAccess(Curso $curso): void
    {
        $inscrito = Inscripcion::where('curso_id', $curso->id)
            ->where('estudiante_id', Auth::id())
            ->where('estado', 'activo')
            ->exists();

        if (! $inscrito || $curso->estado !== 'publicado') {
            abort(403);
        }
    }

    private function visibleStructure(Curso $curso)
    {
        return $curso->modulos()
            ->where('estado', 'activo')
            ->with(['lecciones' => fn ($query) => $query
                ->where('estado', 'publicado')
                ->withCount(['videos', 'materiales'])])
            ->get();
    }
=======
     * Detalle del curso: profesor, unidades y temas.
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

        // Primer tema sin completar, para el botón "Continuar".
        $siguiente = $modulos
            ->flatMap->lecciones
            ->first(fn ($leccion) => !in_array($leccion->id, $completadas, true));

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
     * Tema (lección): contenido, videos, materiales y evaluaciones.
     */
    public function lesson(Curso $curso, Leccion $leccion)
    {
        $this->authorizeEnrollment($curso);

        $leccion->load('modulo');

        abort_unless(
            $leccion->modulo
                && $leccion->modulo->curso_id === $curso->id
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
            ->select('examen_id', DB::raw('COUNT(*) as total'), DB::raw('MAX(calificacion) as mejor'))
            ->groupBy('examen_id')
            ->get()
            ->keyBy('examen_id');

        $completado = ProgresoLeccion::query()
            ->where('leccion_id', $leccion->id)
            ->where('estudiante_id', $estudianteId)
            ->where('completado', true)
            ->exists();

        // Lista plana de temas para navegar anterior / siguiente.
        $lista = $this->modulosConLecciones($curso)->flatMap->lecciones->values();
        $posicion = $lista->search(fn ($item) => $item->id === $leccion->id);

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
     * Marca un tema como completado y actualiza progreso, XP y racha.
     */
    public function completeLesson(Curso $curso, Leccion $leccion)
    {
        $this->authorizeEnrollment($curso);

        $leccion->load('modulo');

        abort_unless(
            $leccion->modulo
                && $leccion->modulo->curso_id === $curso->id
                && $leccion->modulo->estado === 'activo'
                && $leccion->estado === 'publicado',
            404
        );

        $estudianteId = Auth::id();

        $nuevo = false;

        DB::transaction(function () use ($curso, $leccion, $estudianteId, &$nuevo) {

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
            $nuevo ? 'Tema completado. ¡Ganaste 10 XP!' : 'Este tema ya estaba completado.'
        );
    }

    /* ------------------------------------------------------------------ */

    /**
     * Solo estudiantes inscritos pueden ver un curso publicado.
     */
    private function authorizeEnrollment(Curso $curso): Inscripcion
    {
        abort_unless($curso->estado === 'publicado', 404);

        $inscripcion = Inscripcion::query()
            ->where('curso_id', $curso->id)
            ->where('estudiante_id', Auth::id())
            ->whereIn('estado', ['activo', 'finalizado'])
            ->first();

        abort_unless($inscripcion, 403, 'No estás inscrito en este curso.');

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
     * +10 XP y actualización de la racha diaria.
     */
    private function registrarActividad(int $estudianteId): void
    {
        $hoy = now()->toDateString();

        // Experiencia.
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

        // Racha.
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
>>>>>>> bb5ded3887008384a4b1ffae92a0fd0dc2279343
}
