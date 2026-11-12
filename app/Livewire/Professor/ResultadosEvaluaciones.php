<?php

namespace App\Livewire\Professor;

use App\Models\IntentoExamen;
use App\Models\RespuestaEstudiante;
use App\Services\BitacoraService;
use App\Services\Evaluacion\AcademicReportService;
use App\Services\Evaluacion\ExamGradingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Reportes académicos del profesor (por examen, estudiante, tema y tiempo)
 * y revisión / corrección manual de intentos.
 */
class ResultadosEvaluaciones extends Component
{
    use WithPagination;

    #[Url(as: 'curso')]
    public $cursoId = '';

    #[Url(as: 'examen')]
    public $examenId = '';

    #[Url(as: 'vista')]
    public string $vista = 'examenes';

    #[Url(as: 'q')]
    public string $buscar = '';

    public ?int $detalleId = null;

    /** [respuesta_id => puntaje manual] */
    public array $ajustes = [];

    public string $mensaje = '';
    public string $error = '';

    public function updatedCursoId(): void
    {
        $this->examenId = '';
        $this->resetPage();
        $this->cerrarDetalle();
    }

    public function updatedExamenId(): void
    {
        $this->resetPage();
        $this->cerrarDetalle();
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedVista(): void
    {
        $this->resetPage();
        $this->cerrarDetalle();
    }

    /* ---------------------------------------------------------------
     | Revisión de un intento y corrección manual
     * ------------------------------------------------------------- */

    public function verDetalle(int $intentoId): void
    {
        $intento = IntentoExamen::with('examen.leccion.modulo.curso')->findOrFail($intentoId);
        Gate::authorize('review', $intento);

        $this->mensaje = '';
        $this->error = '';
        $this->resetErrorBag();
        $this->detalleId = $intento->id;

        $this->ajustes = RespuestaEstudiante::where('intento_id', $intento->id)
            ->get()
            ->mapWithKeys(fn ($r) => [$r->id => rtrim(rtrim((string) $r->puntaje_obtenido, '0'), '.') ?: '0'])
            ->all();
    }

    public function cerrarDetalle(): void
    {
        $this->detalleId = null;
        $this->ajustes = [];
        $this->resetErrorBag();
    }

    public function guardarAjustes(ExamGradingService $calificador): void
    {
        $this->mensaje = '';
        $this->error = '';
        $this->resetErrorBag();

        if (! $this->detalleId) {
            return;
        }

        $intento = IntentoExamen::with('examen.leccion.modulo.curso', 'estudiante')->findOrFail($this->detalleId);
        Gate::authorize('review', $intento);

        $respuestas = RespuestaEstudiante::with('pregunta')
            ->where('intento_id', $intento->id)
            ->get()
            ->keyBy('id');

        // Máximo permitido por pregunta (varias filas de una misma pregunta comparten el tope).
        $totalesPorPregunta = [];

        foreach ($respuestas as $id => $respuesta) {
            $valor = $this->ajustes[$id] ?? $respuesta->puntaje_obtenido;

            if (! is_numeric($valor) || (float) $valor < 0) {
                $this->addError("ajustes.$id", 'Puntaje inválido.');

                return;
            }

            $totalesPorPregunta[$respuesta->pregunta_id] = ($totalesPorPregunta[$respuesta->pregunta_id] ?? 0) + (float) $valor;
        }

        foreach ($respuestas as $id => $respuesta) {
            $tope = (float) $respuesta->pregunta->puntaje;

            if (($totalesPorPregunta[$respuesta->pregunta_id] ?? 0) > $tope + 0.0001) {
                $this->addError("ajustes.$id", 'No puede superar '.rtrim(rtrim(number_format($tope, 2), '0'), '.').' pts.');

                return;
            }
        }

        $notaAntes = (float) $intento->calificacion;

        DB::transaction(function () use ($respuestas, $intento, $calificador) {
            foreach ($respuestas as $id => $respuesta) {
                $nuevo = round((float) ($this->ajustes[$id] ?? $respuesta->puntaje_obtenido), 2);

                if (abs($nuevo - (float) $respuesta->puntaje_obtenido) > 0.0001) {
                    $respuesta->puntaje_obtenido = $nuevo;
                    $respuesta->es_correcta = $nuevo >= (float) $respuesta->pregunta->puntaje - 0.0001;
                    $respuesta->save();
                }
            }

            $calificador->recalculate($intento);
        });

        $intento->refresh();

        BitacoraService::registrar(
            "Corrigió manualmente el intento #{$intento->id} de {$intento->estudiante?->nombre_completo} "
            ."en «{$intento->examen->titulo}»: ".rtrim(rtrim(number_format($notaAntes, 2), '0'), '.')
            .' → '.rtrim(rtrim(number_format((float) $intento->calificacion, 2), '0'), '.')
        );

        $this->mensaje = 'Corrección guardada. La nota del estudiante se actualizó.';
    }

    /* ---------------------------------------------------------------
     | Render
     * ------------------------------------------------------------- */

    public function render(AcademicReportService $reportes)
    {
        $profesor = Auth::user();
        $curso = $this->cursoId !== '' ? (int) $this->cursoId : null;
        $examen = $this->examenId !== '' ? (int) $this->examenId : null;

        $datos = [
            'cursos' => $reportes->cursos($profesor),
            'examenes' => $reportes->examenes($profesor, $curso),
            'resumen' => $reportes->resumen($profesor, $curso, $examen),
            'vista' => $this->vista,
        ];

        switch ($this->vista) {
            case 'estudiantes':
                $datos['filas'] = $reportes->porEstudiante($profesor, $curso, $examen, trim($this->buscar));
                break;
            case 'temas':
                $datos['filas'] = $reportes->porTema($profesor, $curso, $examen);
                break;
            case 'tiempo':
                $datos['filas'] = $reportes->evolucion($profesor, $curso, $examen);
                break;
            case 'intentos':
                $datos['filas'] = $reportes->intentos($profesor, $curso, $examen, trim($this->buscar))->paginate(10);
                break;
            default:
                $datos['filas'] = $reportes->porExamen($profesor, $curso, $examen);
        }

        $datos['detalle'] = null;

        if ($this->detalleId) {
            $intento = IntentoExamen::with([
                'examen.leccion.modulo.curso',
                'estudiante',
                'respuestas.pregunta.opciones',
                'respuestas.opcion',
            ])->find($this->detalleId);

            if ($intento && Gate::allows('review', $intento)) {
                $datos['detalle'] = $intento;
            }
        }

        return view('livewire.professor.resultados-evaluaciones', $datos);
    }
}
