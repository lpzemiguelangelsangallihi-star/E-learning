<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $search = trim(
            $request->string('search')->toString()
        );

        $materiaId = $request->integer('materia');

        $estado = $request
            ->string('estado')
            ->toString();


        $cursos = Curso::query()
            ->with([
                'materia',
                'profesor',
            ])
            ->withCount([
                'inscripciones as estudiantes_count' => function ($query) {
                    $query->where('estado', 'activo');
                },
            ])

            ->when(
                $search !== '',
                function ($query) use ($search) {

                    $query->where(
                        function ($query) use ($search) {

                            $query
                                ->where(
                                    'titulo',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'descripcion',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )

            ->when(
                $materiaId,
                fn ($query) =>
                    $query->where(
                        'materia_id',
                        $materiaId
                    )
            )

            ->when(
                in_array(
                    $estado,
                    [
                        'borrador',
                        'publicado',
                        'oculto',
                    ],
                    true
                ),
                fn ($query) =>
                    $query->where(
                        'estado',
                        $estado
                    )
            )

            ->orderByDesc('creado_en')
            ->paginate(10)
            ->withQueryString();


        $materias = Materia::query()
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();


        return view(
            'admin.courses.index',
            compact(
                'cursos',
                'materias',
                'search',
                'materiaId',
                'estado'
            )
        );
    }


    public function toggleVisibility(Curso $curso)
    {
        if ($curso->estado === 'borrador') {
            return back()->with(
                'error',
                'Un curso en borrador debe ser publicado primero por el profesor.'
            );
        }


        $curso->update([
            'estado' =>
                $curso->estado === 'publicado'
                    ? 'oculto'
                    : 'publicado',
        ]);


        return back()->with(
            'success',
            $curso->estado === 'publicado'
                ? 'Curso publicado correctamente.'
                : 'Curso ocultado correctamente.'
        );
    }


    public function destroy(Curso $curso)
    {
        /*
         * Evitamos eliminar cursos que tengan
         * estudiantes inscritos.
         */
        if ($curso->inscripciones()->exists()) {

            return back()->with(
                'error',
                'No puedes eliminar un curso que tiene inscripciones registradas.'
            );
        }


        $curso->delete();


        return redirect()
            ->route('admin.courses.index')
            ->with(
                'success',
                'Curso eliminado correctamente.'
            );
    }
}