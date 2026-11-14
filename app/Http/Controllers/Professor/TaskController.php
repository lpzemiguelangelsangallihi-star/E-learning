<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Entrega;
use App\Models\Leccion;
use App\Models\Tarea;
use App\Services\BitacoraService;
use App\Services\CourseFileService;
use App\Services\Evaluacion\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

/** Tareas del profesor: CRUD, estados y calificación de entregas. */
class TaskController extends Controller
{
    public function __construct(
        private TaskService $tareas,
        private CourseFileService $files
    ) {}

    public function index(Request $request)
    {
        $profesorId = Auth::id();
        $cursoId = $request->integer('curso') ?: null;
        $estado = $request->string('estado')->toString();
        $buscar = trim($request->string('q')->toString());

        $lista = Tarea::query()
            ->with('leccion.modulo.curso')
            ->withCount([
                'entregas',
                'entregas as pendientes_count' => fn ($q) => $q->where('estado', '!=', 'calificada'),
            ])
            ->whereHas('leccion.modulo.curso', function ($q) use ($profesorId, $cursoId) {
                $q->where('profesor_id', $profesorId)
                    ->when($cursoId, fn ($q) => $q->where('cursos.id', $cursoId));
            })
            ->when(in_array($estado, ['borrador', 'publicada', 'cerrada'], true), fn ($q) => $q->where('estado', $estado))
            ->when($buscar !== '', fn ($q) => $q->where('titulo', 'like', "%{$buscar}%"))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $cursos = Curso::where('profesor_id', $profesorId)->orderBy('titulo')->get(['id', 'titulo']);

        return view('professor.tasks.index', [
            'tareas' => $lista,
            'cursos' => $cursos,
            'cursoId' => $cursoId,
            'estado' => $estado,
            'buscar' => $buscar,
        ]);
    }

    public function create(Request $request)
    {
        return view('professor.tasks.form', [
            'tarea' => null,
            'lecciones' => $this->leccionesDelProfesor(),
            'leccionSeleccionada' => $request->integer('leccion') ?: null,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request, true);

        $leccion = Leccion::with('modulo.curso')->findOrFail($datos['leccion_id']);

        abort_unless((int) $leccion->modulo?->curso?->profesor_id === (int) Auth::id(), 403);

        $archivo = $request->hasFile('archivo')
            ? $request->file('archivo')->store('tareas', 'local')
            : null;

        $tarea = Tarea::create([
            'leccion_id' => $leccion->id,
            'profesor_id' => Auth::id(),
            'titulo' => trim($datos['titulo']),
            'instrucciones' => $datos['instrucciones'] ?? null,
            'fecha_limite' => $datos['fecha_limite'] ?? null,
            'puntaje_maximo' => $datos['puntaje_maximo'],
            'archivo' => $archivo,
            'estado' => 'borrador',
        ]);

        BitacoraService::registrar("Creó la tarea «{$tarea->titulo}»");

        return redirect()
            ->route('professor.tasks.index')
            ->with('success', 'Tarea creada como borrador. Publícala cuando esté lista.');
    }

    public function edit(Tarea $tarea)
    {
        $tarea->load('leccion.modulo.curso');

        Gate::authorize('manage', $tarea);

        return view('professor.tasks.form', [
            'tarea' => $tarea,
            'lecciones' => $this->leccionesDelProfesor(),
            'leccionSeleccionada' => $tarea->leccion_id,
        ]);
    }

    public function update(Request $request, Tarea $tarea)
    {
        $tarea->load('leccion.modulo.curso');

        Gate::authorize('manage', $tarea);

        $datos = $this->validar($request, false);

        $cambios = [
            'titulo' => trim($datos['titulo']),
            'instrucciones' => $datos['instrucciones'] ?? null,
            'fecha_limite' => $datos['fecha_limite'] ?? null,
            'puntaje_maximo' => $datos['puntaje_maximo'],
        ];

        if ($request->boolean('quitar_archivo') && $tarea->archivo) {
            $this->files->delete($tarea->archivo);
            $cambios['archivo'] = null;
        }

        if ($request->hasFile('archivo')) {
            $this->files->delete($tarea->archivo);

            $cambios['archivo'] = $request->file('archivo')->store('tareas', 'local');
        }

        $tarea->update($cambios);

        BitacoraService::registrar("Actualizó la tarea «{$tarea->titulo}»");

        return redirect()
            ->route('professor.tasks.index')
            ->with('success', 'Tarea actualizada.');
    }

    public function destroy(Tarea $tarea)
    {
        $tarea->load('leccion.modulo.curso');

        Gate::authorize('manage', $tarea);

        if ($tarea->entregas()->exists()) {
            return back()->with('error', 'No se puede eliminar una tarea con entregas. Ciérrala para que no reciba más.');
        }

        $this->files->delete($tarea->archivo);

        $titulo = $tarea->titulo;

        DB::transaction(function () use ($tarea) {
            $tarea->calificaciones()->delete();
            $tarea->delete();
        });

        BitacoraService::registrar("Eliminó la tarea «{$titulo}»");

        return back()->with('success', 'Tarea eliminada.');
    }

    public function changeStatus(Request $request, Tarea $tarea)
    {
        $tarea->load('leccion.modulo.curso');

        Gate::authorize('manage', $tarea);

        $estado = $request->validate([
            'estado' => ['required', 'in:borrador,publicada,cerrada'],
        ])['estado'];

        if ($estado === 'borrador' && $tarea->entregas()->exists()) {
            return back()->with('error', 'Una tarea con entregas no puede volver a borrador. Ciérrala en su lugar.');
        }

        $tarea->update(['estado' => $estado]);

        BitacoraService::registrar("Cambió la tarea «{$tarea->titulo}» a estado {$estado}");

        return back()->with('success', "Tarea {$estado}.");
    }

    /** Entregas de una tarea. */
    public function submissions(Tarea $tarea)
    {
        $tarea->load('leccion.modulo.curso');

        Gate::authorize('manage', $tarea);

        $entregas = Entrega::query()
            ->with('estudiante')
            ->where('tarea_id', $tarea->id)
            ->orderByRaw("CASE WHEN estado = 'calificada' THEN 1 ELSE 0 END")
            ->orderByDesc('fecha_entrega')
            ->get();

        return view('professor.tasks.submissions', compact('tarea', 'entregas'));
    }

    public function grade(Request $request, Tarea $tarea, Entrega $entrega)
    {
        $tarea->load('leccion.modulo.curso');

        Gate::authorize('manage', $tarea);

        abort_unless((int) $entrega->tarea_id === (int) $tarea->id, 404);

        $maximo = (float) $tarea->puntaje_maximo;

        $datos = $request->validate([
            'calificacion' => ['required', 'numeric', 'min:0', "max:{$maximo}"],
            'retroalimentacion' => ['nullable', 'string', 'max:2000'],
        ], [
            'calificacion.max' => "La calificación no puede superar {$maximo}.",
        ]);

        $this->tareas->calificar(
            $entrega,
            (float) $datos['calificacion'],
            $datos['retroalimentacion'] ?? null
        );

        return back()->with('success', 'Calificación guardada.');
    }

    /** Descarga privada del archivo que subió el estudiante. */
    public function download(Tarea $tarea, Entrega $entrega)
    {
        $tarea->load('leccion.modulo.curso');

        Gate::authorize('manage', $tarea);

        abort_unless((int) $entrega->tarea_id === (int) $tarea->id && $entrega->archivo, 404);

        $disk = $this->files->diskFor($entrega->archivo);

        abort_unless($disk, 404);

        return Storage::disk($disk)->download($entrega->archivo);
    }

    private function validar(Request $request, bool $creando): array
    {
        return $request->validate([
            'leccion_id' => [$creando ? 'required' : 'sometimes', 'integer'],
            'titulo' => ['required', 'string', 'min:3', 'max:200'],
            'instrucciones' => ['nullable', 'string', 'max:5000'],
            'fecha_limite' => ['nullable', 'date'],
            'puntaje_maximo' => ['required', 'numeric', 'min:1', 'max:999'],
            'archivo' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,jpg,jpeg,png'],
        ]);
    }

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
