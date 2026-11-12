<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Entrega;
use App\Models\Tarea;
use App\Services\Evaluacion\ExamException;
use App\Services\Evaluacion\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/** Tareas del estudiante: ver instrucciones y entregar. */
class TaskController extends Controller
{
    public function __construct(private TaskService $tareas)
    {
    }

    public function show(Tarea $tarea)
    {
        $tarea->load('leccion.modulo.curso');

        Gate::authorize('view', $tarea);

        $entrega = Entrega::query()
            ->where('tarea_id', $tarea->id)
            ->where('estudiante_id', Auth::id())
            ->first();

        return view('student.evaluations.task.show', [
            'tarea' => $tarea,
            'entrega' => $entrega,
            'puedeEntregar' => Gate::allows('submit', $tarea) && ($entrega === null || $entrega->estado !== 'calificada'),
        ]);
    }

    public function submit(Request $request, Tarea $tarea)
    {
        $tarea->load('leccion.modulo.curso');

        Gate::authorize('submit', $tarea);

        $datos = $request->validate([
            'respuesta' => ['nullable', 'string', 'max:5000'],
            'archivo' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,jpg,jpeg,png'],
        ]);

        $respuesta = isset($datos['respuesta']) ? trim($datos['respuesta']) : '';
        $tieneArchivo = $request->hasFile('archivo');

        $previa = Entrega::where('tarea_id', $tarea->id)->where('estudiante_id', Auth::id())->first();

        if ($respuesta === '' && ! $tieneArchivo && ! ($previa?->archivo)) {
            return back()
                ->withInput()
                ->withErrors(['respuesta' => 'Escribe una respuesta o adjunta un archivo.']);
        }

        try {
            $this->tareas->entregar(
                $tarea,
                Auth::user(),
                $respuesta !== '' ? $respuesta : null,
                $request->file('archivo')
            );
        } catch (ExamException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('student.evaluations.task.show', $tarea)
            ->with('success', 'Tu entrega fue registrada.');
    }
}
