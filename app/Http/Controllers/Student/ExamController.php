<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Examen;
use App\Models\IntentoExamen;
use App\Services\Evaluacion\ExamAttemptService;
use App\Services\Evaluacion\ExamException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/** Flujo del estudiante: ver examen, iniciar, resolver y ver resultado. */
class ExamController extends Controller
{
    public function __construct(private ExamAttemptService $intentos)
    {
    }

    public function show(Examen $examen)
    {
        $examen->load('leccion.modulo.curso');

        // "view" también permite entrar a un examen cerrado si el estudiante ya tiene intentos.
        Gate::authorize('view', $examen);

        $estudianteId = Auth::id();

        $historial = IntentoExamen::query()
            ->where('examen_id', $examen->id)
            ->where('estudiante_id', $estudianteId)
            ->orderBy('numero_intento')
            ->get();

        $abierto = $this->intentos->abierto($examen, $estudianteId);

        // Si el abierto expiró se finalizó dentro de abierto(); recarga el historial.
        if ($historial->contains(fn ($i) => $i->estado === 'en_proceso') && ! $abierto) {
            $historial = IntentoExamen::query()
                ->where('examen_id', $examen->id)
                ->where('estudiante_id', $estudianteId)
                ->orderBy('numero_intento')
                ->get();
        }

        $usados = $historial->count();
        $puedeIniciar = $examen->estado === 'publicado'
            && ($abierto !== null || $usados < (int) $examen->intentos_permitidos);

        return view('student.exams.show', [
            'examen' => $examen,
            'historial' => $historial,
            'abierto' => $abierto,
            'usados' => $usados,
            'puedeIniciar' => $puedeIniciar,
            'totalPreguntas' => $examen->preguntas()->count(),
        ]);
    }

    public function start(Examen $examen)
    {
        $examen->load('leccion.modulo.curso');

        Gate::authorize('take', $examen);

        try {
            $intento = $this->intentos->iniciar($examen, Auth::user());
        } catch (ExamException $e) {
            return redirect()
                ->route('student.evaluations.exam.show', $examen)
                ->with('error', $e->getMessage());
        }

        return redirect()->route('student.evaluations.exam.attempt', $intento);
    }

    public function attempt(IntentoExamen $intento)
    {
        $intento->load('examen.leccion.modulo.curso');

        Gate::authorize('own', $intento);

        if ($intento->estado === 'en_proceso' && $this->intentos->expirado($intento)) {
            $this->intentos->finalizar($intento);
        }

        $intento->refresh();

        if ($intento->estado === 'finalizado') {
            return redirect()->route('student.evaluations.exam.result', $intento);
        }

        return view('student.exams.attempt', ['intento' => $intento, 'examen' => $intento->examen]);
    }

    public function result(IntentoExamen $intento)
    {
        $intento->load([
            'examen.leccion.modulo.curso',
            'respuestas.pregunta.opciones',
            'respuestas.opcion',
        ]);

        Gate::authorize('own', $intento);

        if ($intento->estado !== 'finalizado') {
            return redirect()->route('student.evaluations.exam.attempt', $intento);
        }

        $examen = $intento->examen;

        $intentosRestantes = max(0, (int) $examen->intentos_permitidos - $this->intentos->usados($examen, Auth::id()));

        return view('student.exams.result', [
            'intento' => $intento,
            'examen' => $examen,
            'mostrarCorrectas' => (bool) ($examen->mostrar_correctas ?? true),
            'intentosRestantes' => $intentosRestantes,
            'porPregunta' => $intento->respuestas->groupBy('pregunta_id'),
        ]);
    }
}
