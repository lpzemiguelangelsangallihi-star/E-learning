<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Examen;
use App\Models\Leccion;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/** CRUD de exámenes del profesor (las preguntas se editan en el componente Livewire). */
class ExamController extends Controller
{
    public function index(Request $request)
    {
        $profesorId = Auth::id();
        $cursoId = $request->integer('curso') ?: null;
        $estado = $request->string('estado')->toString();
        $buscar = trim($request->string('q')->toString());

        $examenes = Examen::query()
            ->with('leccion.modulo.curso')
            ->withCount(['preguntas', 'intentos'])
            ->whereHas('leccion.modulo.curso', function ($q) use ($profesorId, $cursoId) {
                $q->where('profesor_id', $profesorId)
                    ->when($cursoId, fn ($q) => $q->where('cursos.id', $cursoId));
            })
            ->when(in_array($estado, ['borrador', 'publicado', 'cerrado'], true), fn ($q) => $q->where('estado', $estado))
            ->when($buscar !== '', fn ($q) => $q->where('titulo', 'like', "%{$buscar}%"))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $cursos = Curso::where('profesor_id', $profesorId)->orderBy('titulo')->get(['id', 'titulo']);

        return view('professor.exams.index', compact('examenes', 'cursos', 'cursoId', 'estado', 'buscar'));
    }

    public function create(Request $request)
    {
        $lecciones = $this->leccionesDelProfesor();

        return view('professor.exams.create', [
            'lecciones' => $lecciones,
            'leccionSeleccionada' => $request->integer('leccion') ?: null,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'leccion_id' => ['required', 'integer'],
            'titulo' => ['required', 'string', 'min:3', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'tiempo_limite' => ['nullable', 'integer', 'min:1', 'max:600'],
            'intentos_permitidos' => ['required', 'integer', 'min:1', 'max:20'],
            'puntaje_maximo' => ['required', 'numeric', 'min:1', 'max:999'],
        ]);

        $leccion = Leccion::with('modulo.curso')->findOrFail($datos['leccion_id']);

        abort_unless(
            (int) $leccion->modulo?->curso?->profesor_id === (int) Auth::id(),
            403
        );

        $examen = Examen::create([
            'leccion_id' => $leccion->id,
            'profesor_id' => Auth::id(),
            'titulo' => trim($datos['titulo']),
            'descripcion' => $datos['descripcion'] ?? null,
            'tiempo_limite' => $datos['tiempo_limite'] ?? null,
            'intentos_permitidos' => $datos['intentos_permitidos'],
            'puntaje_maximo' => $datos['puntaje_maximo'],
            'estado' => 'borrador',
        ]);

        BitacoraService::registrar("Creó el examen «{$examen->titulo}»");

        return redirect()
            ->route('professor.exams.edit', $examen)
            ->with('success', 'Examen creado. Ahora agrega sus preguntas.');
    }

    public function edit(Examen $examen)
    {
        $examen->load('leccion.modulo.curso');

        Gate::authorize('manage', $examen);

        return view('professor.exams.edit', compact('examen'));
    }

    public function destroy(Examen $examen)
    {
        $examen->load('leccion.modulo.curso');

        Gate::authorize('manage', $examen);

        if ($examen->intentos()->exists()) {
            return back()->with('error', 'No se puede eliminar un examen que ya tiene intentos. Ciérralo para que no reciba más.');
        }

        $titulo = $examen->titulo;

        DB::transaction(function () use ($examen) {
            foreach ($examen->preguntas as $pregunta) {
                $pregunta->opciones()->delete();
            }

            $examen->preguntas()->delete();
            $examen->calificaciones()->delete();
            $examen->delete();
        });

        BitacoraService::registrar("Eliminó el examen «{$titulo}»");

        return redirect()
            ->route('professor.exams.index')
            ->with('success', 'Examen eliminado.');
    }

    /** Temas de los cursos del profesor, para elegir dónde vive el examen/tarea. */
    private function leccionesDelProfesor()
    {
        return Leccion::query()
            ->with('modulo.curso')
            ->whereHas('modulo.curso', fn ($q) => $q->where('profesor_id', Auth::id()))
            ->orderBy('modulo_id')
            ->orderBy('orden')
            ->get();
    }
}
