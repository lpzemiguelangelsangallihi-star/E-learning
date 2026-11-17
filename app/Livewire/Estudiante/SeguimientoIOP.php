<?php

namespace App\Livewire\Estudiante;

use App\Models\IopMatrizMarkov;
use App\Models\IopSeguimiento;
use App\Services\IOP\AccesoLeccionIOPService;
use App\Services\IOP\EntregaRefuerzoService;
use App\Services\IOP\MarkovService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SeguimientoIOP extends Component
{
    #[Locked]
    public int $leccionId;

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

        app(AccesoLeccionIOPService::class)
            ->autorizar(
                $estudiante,
                $this->leccionId
            );

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

        $seguimiento = IopSeguimiento::query()
            ->where(
                'estudiante_id',
                $estudiante->id
            )
            ->where(
                'leccion_id',
                $this->leccionId
            )
            ->whereNotNull(
                'intento_diagnostico_id'
            )
            ->firstOrFail();

        if (
            !$seguimiento->activo ||
            $seguimiento->estado_actual === 'S4'
        ) {
            $this->addError(
                'respuesta',
                'Tu seguimiento ya está consolidado.'
            );

            return;
        }

        $ciclo = $seguimiento->ciclosRefuerzo()
            ->with(
                'recomendacion.asignacionRefuerzo'
            )
            ->where(
                'ciclo',
                $seguimiento->ciclo_actual
            )
            ->first();

        if (!$ciclo) {
            $this->addError(
                'respuesta',
                'No existe un ciclo de refuerzo vigente.'
            );

            return;
        }

        $recomendacion = $ciclo->recomendacion;

        abort_unless(
            $recomendacion &&
            (int) $recomendacion->estudiante_id
                === (int) $estudiante->id &&
            (int) $recomendacion->leccion_id
                === (int) $this->leccionId,
            403
        );

        $asignacion = $recomendacion
            ->asignacionRefuerzo;

        if (!$asignacion) {
            $this->addError(
                'respuesta',
                'No existe un ejercicio asignado '
                . 'para el ciclo actual.'
            );

            return;
        }

        $servicio->entregar(
            (int) $asignacion->id,
            $estudiante,
            $datos['respuesta']
        );

        $this->reset('respuesta');

        session()->flash(
            'mensaje_refuerzo',
            'Tu ejercicio fue enviado correctamente '
            . 'para revisión.'
        );
    }

    public function render()
    {
        $estudiante = Auth::user();

        abort_unless($estudiante, 401);

        app(AccesoLeccionIOPService::class)
            ->autorizar(
                $estudiante,
                $this->leccionId
            );

        $estudianteId = (int) $estudiante->id;

        $seguimiento = IopSeguimiento::query()
            ->where(
                'estudiante_id',
                $estudianteId
            )
            ->where(
                'leccion_id',
                $this->leccionId
            )
            ->whereNotNull(
                'intento_diagnostico_id'
            )
            ->with([
                'transiciones' => function ($query) {
                    $query->orderBy('ciclo');
                },
                'ciclosRefuerzo' => function ($query) {
                    $query->with([
                        'recomendacion'
                        . '.asignacionRefuerzo'
                        . '.ejercicio',
                    ]);
                },
            ])
            ->first();


        $cicloRefuerzo = null;
        $recomendacion = null;
        $asignacion = null;
        $ejercicio = null;

        if (
            $seguimiento &&
            $seguimiento->activo &&
            $seguimiento->estado_actual !== 'S4'
        ) {
            
            $cicloRefuerzo = $seguimiento
                ->ciclosRefuerzo
                ->firstWhere(
                    'ciclo',
                    (int) $seguimiento->ciclo_actual
                );

            $recomendacion = $cicloRefuerzo
                ?->recomendacion;

            
            if (
                $recomendacion &&
                (
                    (int) $recomendacion->estudiante_id
                        !== $estudianteId ||
                    (int) $recomendacion->leccion_id
                        !== (int) $this->leccionId
                )
            ) {
                $recomendacion = null;
            }

            $asignacion = $recomendacion
                ?->asignacionRefuerzo;

            $ejercicio = $asignacion
                ?->ejercicio;
        }

       
        $matrizReal = IopMatrizMarkov::query()
            ->where(
                'leccion_id',
                $this->leccionId
            )
            ->where(
                'origen',
                'evaluacion'
            )
            ->orderByDesc('version')
            ->orderByDesc('id')
            ->first();

        

        $matrizActualizada = false;

        if ($matrizReal) {
            
            $totalRegistrado = array_sum(
                $matrizReal->totales ?? []
            );

            $totalActual = DB::table(
                'iop_transiciones as t'
            )
                ->join(
                    'iop_seguimientos as s',
                    's.id',
                    '=',
                    't.seguimiento_id'
                )
                ->where(
                    's.leccion_id',
                    $this->leccionId
                )
                ->where(
                    't.origen',
                    'evaluacion'
                )
                ->count();

            
            $matrizActualizada =
                (int) $totalRegistrado
                === (int) $totalActual;
        }

        

        $proyeccion = null;

     
        $fuenteProyeccion = 'hipotetica';

        $versionMatriz = null;

        if ($seguimiento) {
            $markov = app(MarkovService::class);

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


                $matrizSeleccionada =
                    MarkovService::MATRIZ_EJEMPLO;

                if (
                    $matrizReal &&
                    $matrizReal->completa &&
                    $matrizActualizada &&
                    is_array(
                        $matrizReal->probabilidades
                    )
                ) {
                   
                    $markov->validarMatriz(
                        $matrizReal->probabilidades
                    );

                    $matrizSeleccionada =
                        $matrizReal->probabilidades;

                    $fuenteProyeccion = 'real';

                    $versionMatriz =
                        (int) $matrizReal->version;
                }


                $cantidadCiclos = max(
                    1,
                    min(10, $this->ciclos)
                );

                $proyeccion = $markov->proyectar(
                    $vectorActual,
                    $matrizSeleccionada,
                    $cantidadCiclos
                );
            }
        }

    
        $historialRefuerzos = $seguimiento
            ? $seguimiento->ciclosRefuerzo
            : collect();

  
        $evaluacionesPorCiclo = $seguimiento
            ? $seguimiento->transiciones->keyBy(
                fn ($transicion) =>
                    (int) $transicion->ciclo - 1
            )
            : collect();

  

        return view(
            'livewire.estudiante.seguimiento-iop',
            [
                'seguimiento' => $seguimiento,

                'recomendacion' => $recomendacion,

                'asignacion' => $asignacion,

                'ejercicio' => $ejercicio,

                'cicloRefuerzo' => $cicloRefuerzo,

                'historialRefuerzos' =>$historialRefuerzos,

                'evaluacionesPorCiclo' =>$evaluacionesPorCiclo,

                'matrizReal' => $matrizReal,

                'matrizActualizada' =>$matrizActualizada,

                'proyeccion' => $proyeccion,

                'fuenteProyeccion' =>$fuenteProyeccion,

                'versionMatriz' => $versionMatriz,
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
