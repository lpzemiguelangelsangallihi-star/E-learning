<?php

namespace App\Livewire\Student;

use App\Models\IntentoExamen;
use App\Models\Pregunta;
use App\Services\Evaluacion\ExamAttemptService;
use App\Services\Evaluacion\ExamException;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Sesión de resolución de un examen: navegación entre preguntas,
 * guardado automático, control de tiempo y entrega.
 */
class ResolverExamen extends Component
{
    #[Locked]
    public int $intentoId;

    public int $actual = 0;

    /** [pregunta_id => id de opción | [ids] | texto] */
    public array $respuestas = [];

    public string $aviso = '';

    public function mount(int $intentoId, ExamAttemptService $servicio): void
    {
        $this->intentoId = $intentoId;

        $intento = $this->intento();

        if ($intento->estado === 'finalizado') {
            $this->redirect(route('student.evaluations.exam.result', $intento->id), navigate: false);

            return;
        }

        $guardadas = $servicio->respuestasGuardadas($intento);

        // Valor inicial por tipo (los checkboxes necesitan un arreglo).
        foreach (Pregunta::where('examen_id', $intento->examen_id)->get(['id', 'tipo']) as $pregunta) {
            $this->respuestas[$pregunta->id] = $guardadas[$pregunta->id] ?? match ($pregunta->tipo) {
                'varias_respuestas' => [],
                'respuesta_corta' => '',
                default => null,
            };
        }
    }

    private function intento(): IntentoExamen
    {
        $intento = IntentoExamen::with('examen.leccion.modulo.curso')->findOrFail($this->intentoId);

        Gate::authorize('own', $intento);

        return $intento;
    }

    /** Se ejecuta al cambiar cualquier respuesta: guarda esa pregunta. */
    public function updatedRespuestas($valor, $clave): void
    {
        $preguntaId = (int) explode('.', (string) $clave)[0];

        $this->guardarPregunta($preguntaId);
    }

    private function guardarPregunta(int $preguntaId): bool
    {
        $servicio = app(ExamAttemptService::class);
        $intento = $this->intento();

        if ($intento->estado !== 'en_proceso') {
            return false;
        }

        $pregunta = Pregunta::where('examen_id', $intento->examen_id)->find($preguntaId);

        if (! $pregunta) {
            return false;
        }

        try {
            $servicio->guardarRespuesta($intento, $pregunta, $this->respuestas[$preguntaId] ?? null);
            $this->aviso = '';

            return true;
        } catch (ExamException $e) {
            $this->aviso = $e->getMessage();

            return false;
        }
    }

    public function ir(int $indice): void
    {
        $this->guardarTodo();

        $total = Pregunta::where('examen_id', $this->intento()->examen_id)->count();

        $this->actual = max(0, min($indice, $total - 1));
    }

    private function guardarTodo(): void
    {
        foreach (array_keys($this->respuestas) as $preguntaId) {
            $this->guardarPregunta((int) $preguntaId);
        }
    }

    /** Entrega el examen (también la llama el temporizador al llegar a cero). */
    public function finalizar(ExamAttemptService $servicio)
    {
        $intento = $this->intento();

        if ($intento->estado === 'en_proceso' && ! $servicio->expirado($intento)) {
            $this->guardarTodo();
        }

        $servicio->finalizar($intento);

        return $this->redirect(route('student.evaluations.exam.result', $intento->id));
    }

    public function render(ExamAttemptService $servicio)
    {
        $intento = $this->intento();

        $preguntas = Pregunta::with('opciones')
            ->where('examen_id', $intento->examen_id)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        $limite = $servicio->limite($intento);

        return view('livewire.student.resolver-examen', [
            'intento' => $intento,
            'examen' => $intento->examen,
            'preguntas' => $preguntas,
            'pregunta' => $preguntas->get($this->actual),
            'restante' => $limite ? max(0, $limite->timestamp - now()->timestamp) : null,
        ]);
    }
}
