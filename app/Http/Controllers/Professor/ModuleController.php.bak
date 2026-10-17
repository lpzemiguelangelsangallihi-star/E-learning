<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Professor\Concerns\ResolvesCourseContent;
use App\Models\Curso;
use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Unidades (tabla `modulos`).
 */
class ModuleController extends Controller
{
    use ResolvesCourseContent;

    public function store(Request $request, Curso $curso)
    {
        $this->ownCourse($curso);

        $validated = $request->validateWithBag('unidad', [
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ]);

        Modulo::create([
            'curso_id' => $curso->id,
            'titulo' => trim($validated['titulo']),
            'descripcion' => ! empty($validated['descripcion']) ? trim($validated['descripcion']) : null,
            'orden' => ((int) $curso->modulos()->max('orden')) + 1,
            'estado' => 'activo',
        ]);

        return back()->with('success', 'Unidad creada correctamente.');
    }

    public function update(Request $request, Curso $curso, Modulo $modulo)
    {
        $this->ownCourse($curso);
        $this->moduleOf($curso, $modulo);

        $validated = $request->validateWithBag('unidad_'.$modulo->id, [
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
        ]);

        $modulo->update([
            'titulo' => trim($validated['titulo']),
            'descripcion' => ! empty($validated['descripcion']) ? trim($validated['descripcion']) : null,
            'estado' => $validated['estado'],
        ]);

        return back()->with('success', 'Unidad actualizada correctamente.');
    }

    public function destroy(Curso $curso, Modulo $modulo)
    {
        $this->ownCourse($curso);
        $this->moduleOf($curso, $modulo);

        $modulo->load('lecciones');

        foreach ($modulo->lecciones as $leccion) {
            if ($this->lessonHasBlockers($leccion)) {
                return back()->with(
                    'error',
                    "No se puede eliminar la unidad: el tema «{$leccion->titulo}» tiene tareas o exámenes."
                );
            }
        }

        DB::transaction(function () use ($curso, $modulo) {
            foreach ($modulo->lecciones as $leccion) {
                $this->deleteLessonCascade($leccion);
            }

            $modulo->delete();

            $this->normalizeOrder($curso->modulos()->get());
        });

        return back()->with('success', 'Unidad eliminada correctamente.');
    }

    public function move(Request $request, Curso $curso, Modulo $modulo)
    {
        $this->ownCourse($curso);
        $this->moduleOf($curso, $modulo);

        $request->validate(['direccion' => ['required', Rule::in(['up', 'down'])]]);

        $this->moveItem($curso->modulos()->get(), $modulo->id, $request->input('direccion'));

        return back();
    }
}
