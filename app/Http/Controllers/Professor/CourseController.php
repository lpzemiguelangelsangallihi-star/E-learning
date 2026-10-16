<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $profesorId = Auth::id();

        $search = trim(
            $request->string('search')->toString()
        );

        $estado = $request
            ->string('estado')
            ->toString();

        $materiaId = $request
            ->integer('materia');


        $cursos = Curso::query()
            ->with('materia')

            ->withCount([
                'inscripciones as estudiantes_count' =>
                    function ($query) {
                        $query->where(
                            'estado',
                            'activo'
                        );
                    },
            ])

            ->where(
                'profesor_id',
                $profesorId
            )

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
            'professor.courses.index',
            compact(
                'cursos',
                'materias',
                'search',
                'estado',
                'materiaId'
            )
        );
    }


    public function create()
    {
        $materias = Materia::query()
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();


        return view(
            'professor.courses.create',
            compact('materias')
        );
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'materia_id' => [
                'required',
                'exists:materias,id',
            ],

            'titulo' => [
                'required',
                'string',
                'max:200',
            ],

            'descripcion' => [
                'nullable',
                'string',
            ],

            'nivel' => [
                'required',
                Rule::in([
                    'basico',
                    'intermedio',
                    'avanzado',
                ]),
            ],

            'duracion' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'tipo' => [
                'required',
                Rule::in([
                    'gratuito',
                    'pago',
                ]),
            ],

            'precio' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);


        Curso::create([
            'profesor_id' =>
                Auth::id(),

            'materia_id' =>
                $validated['materia_id'],

            'titulo' =>
                trim($validated['titulo']),

            'descripcion' =>
                !empty($validated['descripcion'])
                    ? trim($validated['descripcion'])
                    : null,

            'nivel' =>
                $validated['nivel'],

            'duracion' =>
                $validated['duracion'] ?? 0,

            'tipo' =>
                $validated['tipo'],

            'precio' =>
                $validated['tipo'] === 'pago'
                    ? ($validated['precio'] ?? 0)
                    : 0,

            'estado' =>
                'borrador',
        ]);


        return redirect()
            ->route(
                'professor.courses.index'
            )
            ->with(
                'success',
                'Curso creado correctamente.'
            );
    }


    public function edit(Curso $curso)
    {
        $this->authorizeCourse($curso);


        $materias = Materia::query()
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();


        return view(
            'professor.courses.edit',
            compact(
                'curso',
                'materias'
            )
        );
    }


    public function update(
        Request $request,
        Curso $curso
    ) {
        $this->authorizeCourse($curso);


        $validated = $request->validate([
            'materia_id' => [
                'required',
                'exists:materias,id',
            ],

            'titulo' => [
                'required',
                'string',
                'max:200',
            ],

            'descripcion' => [
                'nullable',
                'string',
            ],

            'nivel' => [
                'required',
                Rule::in([
                    'basico',
                    'intermedio',
                    'avanzado',
                ]),
            ],

            'duracion' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'tipo' => [
                'required',
                Rule::in([
                    'gratuito',
                    'pago',
                ]),
            ],

            'precio' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'estado' => [
                'required',
                Rule::in([
                    'borrador',
                    'publicado',
                    'oculto',
                ]),
            ],
        ]);


        $curso->update([
            'materia_id' =>
                $validated['materia_id'],

            'titulo' =>
                trim($validated['titulo']),

            'descripcion' =>
                !empty($validated['descripcion'])
                    ? trim($validated['descripcion'])
                    : null,

            'nivel' =>
                $validated['nivel'],

            'duracion' =>
                $validated['duracion'] ?? 0,

            'tipo' =>
                $validated['tipo'],

            'precio' =>
                $validated['tipo'] === 'pago'
                    ? ($validated['precio'] ?? 0)
                    : 0,

            'estado' =>
                $validated['estado'],
        ]);


        return redirect()
            ->route(
                'professor.courses.index'
            )
            ->with(
                'success',
                'Curso actualizado correctamente.'
            );
    }


    public function destroy(Curso $curso)
    {
        $this->authorizeCourse($curso);


        if ($curso->inscripciones()->exists()) {

            return back()->with(
                'error',
                'No puedes eliminar un curso con estudiantes inscritos.'
            );
        }


        $curso->delete();


        return redirect()
            ->route(
                'professor.courses.index'
            )
            ->with(
                'success',
                'Curso eliminado correctamente.'
            );
    }


    /**
     * Publicar u ocultar un curso.
     * borrador/oculto -> publicado | publicado -> oculto
     */
    public function toggleStatus(Curso $curso)
    {
        $this->authorizeCourse($curso);

        $publicar = $curso->estado !== 'publicado';

        $curso->update([
            'estado' => $publicar ? 'publicado' : 'oculto',
        ]);

        return back()->with(
            'success',
            $publicar
                ? 'Curso publicado correctamente.'
                : 'Curso ocultado correctamente.'
        );
    }


    /**
     * Estudiantes inscritos en un curso del profesor.
     */
    public function students(Request $request, Curso $curso)
    {
        $this->authorizeCourse($curso);

        $search = trim($request->string('search')->toString());
        $estado = $request->string('estado')->toString();

        $curso->loadMissing('materia');

        $inscripciones = $curso->inscripciones()
            ->with('estudiante')
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('estudiante', function ($q) use ($search) {
                    $q->where(function ($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido_paterno', 'like', "%{$search}%")
                            ->orWhere('apellido_materno', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                });
            })
            ->when(
                in_array($estado, ['activo', 'finalizado', 'cancelado'], true),
                fn ($query) => $query->where('estado', $estado)
            )
            ->orderByDesc('fecha_inscripcion')
            ->paginate(15)
            ->withQueryString();

        $totales = $curso->inscripciones()
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return view(
            'professor.courses.students',
            compact('curso', 'inscripciones', 'totales', 'search', 'estado')
        );
    }


    private function authorizeCourse(
        Curso $curso
    ): void {

        if (
            (int) $curso->profesor_id
            !== (int) Auth::id()
        ) {
            abort(403);
        }
    }
}