<?php

namespace App\Livewire\Estudiante;

use App\Models\IopMatrizMarkov;
use App\Models\IopRecomendacion;
use App\Models\IopSeguimiento;
use App\Services\IOP\MarkovService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;
use App\Services\IOP\EntregaRefuerzoService;
use App\Services\IOP\AccesoLeccionIOPService;

class SeguimientoIOP extends Component
{
    #[Locked]
    public int $leccionId;

    // Cantidad de ciclos futuros.
    public int $ciclos = 3;
    public string $respuesta = '';

    
    public function mount(
        int $leccionId,
        AccesoLeccionIOPService $acceso
    ): void {
        $estudiante = Auth::user();

        abort_unless($estudiante, 401);

        $acceso->autorizar(
            $estudiante,
            $leccionId
        );

        $this->leccionId = $leccionId;
    }


    public function updatedCiclos(): void
    {
        $this->ciclos = max(
            1,
            min(10, $this->ciclos)
        );
    }

    public function entregarRefuerzo(
        EntregaRefuerzoService $servicio
    ): void {
        $estudiante = Auth::user();
    
        abort_unless($estudiante, 401);
    
        $datos = $this->validate([
            'respuesta' => [
                'required',
                'string',
                'min:10',
                'max:10000',
            ],
        ], [
            'respuesta.required' =>
                'Debes escribir tu respuesta.',
            'respuesta.min' =>
                'La respuesta debe tener al menos 10 caracteres.',
            'respuesta.max' =>
                'La respuesta no debe superar los 10000 caracteres.',
        ]);
    
        // Buscar exclusivamente el seguimiento
        // del estudiante autenticado.
        $seguimiento = IopSeguimiento::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('leccion_id', $this->leccionId)
            ->firstOrFail();
    
        // Obtener la recomendación inicial
        // y la asignación correspondiente.
        $recomendacion = IopRecomendacion::query()
            ->whereKey(
                $seguimiento->recomendacion_inicial_id
            )
            ->where('estudiante_id', $estudiante->id)
            ->where('leccion_id', $this->leccionId)
            ->with('asignacionRefuerzo')
            ->firstOrFail();
    
        $asignacion = $recomendacion->asignacionRefuerzo;
    
        if (!$asignacion) {
            $this->addError(
                'respuesta',
                'No existe un ejercicio asignado.'
            );
    
            return;
        }
    
        // El servicio valida nuevamente el acceso,
        // la inscripción y el estado de la asignación.
        $servicio->entregar(
            (int) $asignacion->id,
            $estudiante,
            $datos['respuesta']
        );
    
        $this->reset('respuesta');
    
        session()->flash(
            'mensaje_refuerzo',
            'Tu ejercicio fue enviado correctamente para revisión.'
        );
    }


    public function render()
    {
        $estudianteId = Auth::id();

        // Seguimiento del estudiante autenticado.
        $seguimiento = IopSeguimiento::query()
            ->where('estudiante_id', $estudianteId)
            ->where('leccion_id', $this->leccionId)
            ->with([
                'transiciones' => function ($query) {
                    $query->orderBy('ciclo');
                }
            ])
            ->first();

        // Recomendación correspondiente al seguimiento.
        // La relación inicial evita mostrar una asignación
        // que pertenezca a otra recomendación histórica.
        $recomendacion = $seguimiento
            ? IopRecomendacion::query()
                ->whereKey(
                    $seguimiento->recomendacion_inicial_id
                )
                ->where('estudiante_id', $estudianteId)
                ->where('leccion_id', $this->leccionId)
                ->with('asignacionRefuerzo.ejercicio')
                ->first()
            : null;

        // Asignación ya registrada en MySQL.
        // No creamos nuevas asignaciones desde render().
        $asignacion = $recomendacion?->asignacionRefuerzo;

        // Ejercicio correspondiente a la asignación.
        $ejercicio = $asignacion?->ejercicio;

        // Última matriz basada en evaluaciones reales.
        $matrizReal = IopMatrizMarkov::query()
            ->where('leccion_id', $this->leccionId)
            ->where('origen', 'evaluacion')
            ->latest('id')
            ->first();

        $proyeccion = null;

        if ($seguimiento) {
            $markov = app(MarkovService::class);

            // Proyectar desde el estado actual.
            $vectorActual = match (
                $seguimiento->estado_actual
            ) {
                'S1' => [1, 0, 0, 0],
                'S2' => [0, 1, 0, 0],
                'S3' => [0, 0, 1, 0],
                'S4' => [0, 0, 0, 1],
                default => null,
            };

            if ($vectorActual !== null) {
                // Proyección ilustrativa con matriz hipotética.
                $proyeccion = $markov->proyectar(
                    $vectorActual,
                    MarkovService::MATRIZ_EJEMPLO,
                    $this->ciclos
                );
            }
        }

        return view(
            'livewire.estudiante.seguimiento-iop',
            [
                'seguimiento' => $seguimiento,
                'recomendacion' => $recomendacion,
                'asignacion' => $asignacion,
                'ejercicio' => $ejercicio,
                'matrizReal' => $matrizReal,
                'proyeccion' => $proyeccion,
            ]
        );
    }

    public function hydrate(): void
    {
        $estudiante = Auth::user();
    
        abort_unless($estudiante, 401);
    
        app(AccesoLeccionIOPService::class)
            ->autorizar(
                $estudiante,
                $this->leccionId
            );
    }

}
