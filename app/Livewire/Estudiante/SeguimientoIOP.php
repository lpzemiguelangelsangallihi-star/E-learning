<?php

namespace App\Livewire\Estudiante;

use App\Models\IopMatrizMarkov;
use App\Models\IopRecomendacion;
use App\Models\IopSeguimiento;
use App\Services\IOP\MarkovService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SeguimientoIOP extends Component
{
    public int $leccionId;

    // Número de ciclos futuros seleccionados.
    public int $ciclos = 3;

    public function mount(int $leccionId): void
    {
        $this->leccionId = $leccionId;
    }

    public function updatedCiclos(): void
    {
        $this->ciclos = max(1, min(10, $this->ciclos));
    }

    public function render()
    {
        $estudianteId = Auth::id();

        // Solo consulta datos del usuario autenticado.
        $seguimiento = IopSeguimiento::query()
            ->where('estudiante_id', $estudianteId)
            ->where('leccion_id', $this->leccionId)
            ->with(['transiciones' => function ($query) {
                $query->orderBy('ciclo');
            }])
            ->first();

        $recomendacion = IopRecomendacion::query()
            ->where('estudiante_id', $estudianteId)
            ->where('leccion_id', $this->leccionId)
            ->latest('id')
            ->first();

        // Última matriz estimada con evaluaciones reales.
        $matrizReal = IopMatrizMarkov::query()
            ->where('leccion_id', $this->leccionId)
            ->where('origen', 'evaluacion')
            ->latest('id')
            ->first();

        $proyeccion = null;

        if ($seguimiento) {
            $markov = app(MarkovService::class);

            // Proyectamos desde el estado ACTUAL,
            // no desde el estado inicial.
            $vectorActual = match ($seguimiento->estado_actual) {
                'S1' => [1, 0, 0, 0],
                'S2' => [0, 1, 0, 0],
                'S3' => [0, 0, 1, 0],
                'S4' => [0, 0, 0, 1],
                default => null,
            };

            if ($vectorActual !== null) {
                // Por ahora usamos la matriz hipotética.
                // No confundimos esta proyección con
                // una estimación basada en datos reales.
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
                'matrizReal' => $matrizReal,
                'proyeccion' => $proyeccion,
            ]
        );
    }
}
