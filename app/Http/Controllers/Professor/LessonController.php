<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Professor\Concerns\ResolvesCourseContent;
use App\Models\Curso;
use App\Models\Leccion;
use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Temas (tabla `lecciones`), asociados a una unidad y por lo tanto a un curso.
 */
class LessonController extends Controller
{
    use ResolvesCourseContent;

    public function create(Curso $curso, Modulo $modulo)
    {
        $this->ownCourse($curso);
        $this->moduleOf($curso, $modulo);

        return view('professor.lessons.create', compact('curso', 'modulo'));
    }

    public function store(Request $request, Curso $curso, Modulo $modulo)
    {
        $this->ownCourse($curso);
        $this->moduleOf($curso, $modulo);

        $validated = $request->validate($this->rules());

        Leccion::create([
            'modulo_id' => $modulo->id,
            'titulo' => trim($validated['titulo']),
            'descripcion' => ! empty($validated['descripcion']) ? trim($validated['descripcion']) : null,
            'contenido' => ! empty($validated['contenido']) ? trim($validated['contenido']) : null,
            'orden' => ((int) $modulo->lecciones()->max('orden')) + 1,
            'estado' => $validated['estado'],
        ]);

        return redirect()
            ->route('professor.courses.structure', $curso)
            ->with('success', 'Tema creado correctamente.');
    }

    public function edit(Curso $curso, Modulo $modulo, Leccion $leccion)
    {
        $this->resolveLesson($curso, $modulo, $leccion);

        return view('professor.lessons.edit', compact('curso', 'modulo', 'leccion'));
    }

    public function update(Request $request, Curso $curso, Modulo $modulo, Leccion $leccion)
    {
        $this->resolveLesson($curso, $modulo, $leccion);

        $validated = $request->validate($this->rules());

        $leccion->update([
            'titulo' => trim($validated['titulo']),
            'descripcion' => ! empty($validated['descripcion']) ? trim($validated['descripcion']) : null,
            'contenido' => ! empty($validated['contenido']) ? trim($validated['contenido']) : null,
            'estado' => $validated['estado'],
        ]);

        return redirect()
            ->route('professor.courses.structure', $curso)
            ->with('success', 'Tema actualizado correctamente.');
    }

    public function destroy(Curso $curso, Modulo $modulo, Leccion $leccion)
    {
        $this->resolveLesson($curso, $modulo, $leccion);

        if ($this->lessonHasBlockers($leccion)) {
            return back()->with('error', 'No se puede eliminar un tema que tiene tareas o exámenes.');
        }

        DB::transaction(function () use ($modulo, $leccion) {
            $this->deleteLessonCascade($leccion);

            $this->normalizeOrder($modulo->lecciones()->get());
        });

        return redirect()
            ->route('professor.courses.structure', $curso)
            ->with('success', 'Tema eliminado correctamente.');
    }

    public function move(Request $request, Curso $curso, Modulo $modulo, Leccion $leccion)
    {
        $this->resolveLesson($curso, $modulo, $leccion);

        $request->validate(['direccion' => ['required', Rule::in(['up', 'down'])]]);

        $this->moveItem($modulo->lecciones()->get(), $leccion->id, $request->input('direccion'));

        return back();
    }

    /** Publicar u ocultar un tema desde la estructura. */
    public function toggleStatus(Curso $curso, Modulo $modulo, Leccion $leccion)
    {
        $this->resolveLesson($curso, $modulo, $leccion);

        $publicar = $leccion->estado !== 'publicado';

        $leccion->update(['estado' => $publicar ? 'publicado' : 'oculto']);

        return back()->with(
            'success',
            $publicar ? 'Tema publicado: ya lo ven los estudiantes.' : 'Tema oculto para los estudiantes.'
        );
    }

    private function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'contenido' => ['nullable', 'string'],
            'estado' => ['required', Rule::in(['borrador', 'publicado', 'oculto'])],
        ];
    }
}
