<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Leccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    /**
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

        return redirect()
            ->route('student.courses.show', $curso)
            ->with('success', "Te inscribiste en «{$curso->titulo}».");
    }

    /**
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
}
