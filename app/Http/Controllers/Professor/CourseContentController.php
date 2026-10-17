<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Professor\Concerns\ResolvesCourseContent;
use App\Models\Curso;

class CourseContentController extends Controller
{
    use ResolvesCourseContent;

    /**
     * Estructura del curso: unidades → temas.
     */
    public function show(Curso $curso)
    {
        $this->ownCourse($curso);

        $curso->load([
            'materia',
            'modulos.lecciones' => fn ($query) => $query->withCount(['videos', 'materiales']),
        ]);

        $estudiantes = $curso->inscripciones()->where('estado', 'activo')->count();

        return view('professor.courses.structure', compact('curso', 'estudiantes'));
    }

    /**
     * Genera un nuevo código de clase. El anterior deja de funcionar,
     * pero los estudiantes ya inscritos conservan su acceso.
     */
    public function regenerateCode(Curso $curso)
{
    $this->ownCourse($curso);

    // Invalida los códigos anteriores de ambas versiones.
    $curso->forceFill([
        'codigo_clase' => Curso::generarCodigo(),
        'codigo_acceso' => null,
    ])->save();

    return back()->with(
        'success',
        'Se generó un nuevo código de clase.'
    );
}
}
