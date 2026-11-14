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

    // Número de ciclos futuros para la proyección Markov.
    public int $ciclos = 3;

    // Respuesta que escribe el estudiante.
    public string $respuesta = '';

    /**
     * Autoriza el acceso a la lección al montar
     * el componente.
     */
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

    /**
     * Limita la cantidad de ciclos de proyección.
     */
    public function updatedCiclos(): void
    {
        $this->ciclos = max(
            1,
            min(10, $this->ciclos)
        );
    }

    /**
     * Entrega el ejercicio de refuerzo correspondiente
     * al ciclo académico vigente.
     */
    public function entregarRefuerzo(
        EntregaRefuerzoService $servicio
    ): void {
        $estudiante = Auth::user();

        abort_unless($estudiante, 401);

        // Volver a comprobar la autorización.
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

        // Buscar exclusivamente el seguimiento
        // del estudiante autenticado.
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

        // No admitir nuevas entregas después de S4.
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

        // Identificar únicamente el ciclo vigente.
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

        // Validar que la recomendación pertenece
        // realmente a este estudiante y lección.
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

        // El servicio verifica nuevamente:
        // - Propiedad de la asignación.
        // - Inscripción del estudiante.
        // - Estado del curso.
        // - Ciclo académico vigente.
        // - Estado permitido de la actividad.
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

    /**
     * Renderiza el seguimiento académico.
     *
     * Las consultas son de solo lectura.
     * No se generan nuevas matrices desde Livewire.
     */
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

        // Recuperar el seguimiento real del estudiante.
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

        /*
        |--------------------------------------------------------------------------
        | CICLO VIGENTE
        |--------------------------------------------------------------------------
        */

        $cicloRefuerzo = null;
        $recomendacion = null;
        $asignacion = null;
        $ejercicio = null;

        if (
            $seguimiento &&
            $seguimiento->activo &&
            $seguimiento->estado_actual !== 'S4'
        ) {
            // No usar el último ciclo histórico:
            // debe coincidir con ciclo_actual.
            $cicloRefuerzo = $seguimiento
                ->ciclosRefuerzo
                ->firstWhere(
                    'ciclo',
                    (int) $seguimiento->ciclo_actual
                );

            $recomendacion = $cicloRefuerzo
                ?->recomendacion;

            // Solo mostrar recomendaciones vinculadas
            // al seguimiento actual.
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

        /*
        |--------------------------------------------------------------------------
        | MATRIZ MARKOV REAL
        |--------------------------------------------------------------------------
        */

        // Recuperar la versión real más reciente
        // almacenada para esta lección.
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

        /*
        |--------------------------------------------------------------------------
        | VERIFICAR VIGENCIA
        |--------------------------------------------------------------------------
        */

        $matrizActualizada = false;

        if ($matrizReal) {
            // Cantidad de transiciones que se utilizaron
            // al generar la versión almacenada.
            $totalRegistrado = array_sum(
                $matrizReal->totales ?? []
            );

            // Cantidad actual de transiciones reales
            // correspondientes a esta lección.
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

            // Se considera actualizada si no se han
            // agregado nuevas observaciones.
            $matrizActualizada =
                (int) $totalRegistrado
                === (int) $totalActual;
        }

        /*
        |--------------------------------------------------------------------------
        | PROYECCIÓN MARKOV
        |--------------------------------------------------------------------------
        */

        $proyeccion = null;

        // Identifica qué matriz se utiliza.
        $fuenteProyeccion = 'hipotetica';

        // Versión del modelo real, si está disponible.
        $versionMatriz = null;

        if ($seguimiento) {
            $markov = app(MarkovService::class);

            // Construir el vector a partir
            // del estado académico actual.
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

                // La matriz hipotética es la alternativa
                // para fines exclusivamente ilustrativos.
                $matrizSeleccionada =
                    MarkovService::MATRIZ_EJEMPLO;

                // Emplear una matriz real únicamente
                // cuando sea completa y esté actualizada.
                if (
                    $matrizReal &&
                    $matrizReal->completa &&
                    $matrizActualizada &&
                    is_array(
                        $matrizReal->probabilidades
                    )
                ) {
                    // Verificar que sea estocástica 4x4
                    // y que S4 sea un estado absorbente.
                    $markov->validarMatriz(
                        $matrizReal->probabilidades
                    );

                    $matrizSeleccionada =
                        $matrizReal->probabilidades;

                    $fuenteProyeccion = 'real';

                    $versionMatriz =
                        (int) $matrizReal->version;
                }

                // Proyectar de 1 a 10 ciclos futuros.
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

        /*
        |--------------------------------------------------------------------------
        | HISTORIAL ACADÉMICO
        |--------------------------------------------------------------------------
        */

        // Conservar todos los ciclos históricos,
        // incluso después de la consolidación S4.
        $historialRefuerzos = $seguimiento
            ? $seguimiento->ciclosRefuerzo
            : collect();

        // La evaluación del ciclo N+1 corresponde
        // al refuerzo realizado en el ciclo N.
        $evaluacionesPorCiclo = $seguimiento
            ? $seguimiento->transiciones->keyBy(
                fn ($transicion) =>
                    (int) $transicion->ciclo - 1
            )
            : collect();

        /*
        |--------------------------------------------------------------------------
        | VISTA
        |--------------------------------------------------------------------------
        */

        return view(
            'livewire.estudiante.seguimiento-iop',
            [
                'seguimiento' => $seguimiento,

                'recomendacion' => $recomendacion,

                'asignacion' => $asignacion,

                'ejercicio' => $ejercicio,

                'cicloRefuerzo' => $cicloRefuerzo,

                'historialRefuerzos' =>
                    $historialRefuerzos,

                'evaluacionesPorCiclo' =>
                    $evaluacionesPorCiclo,

                'matrizReal' => $matrizReal,

                'matrizActualizada' =>
                    $matrizActualizada,

                'proyeccion' => $proyeccion,

                'fuenteProyeccion' =>
                    $fuenteProyeccion,

                'versionMatriz' => $versionMatriz,
            ]
        );
    }

    /**
     * Autoriza nuevamente al estudiante en cada
     * petición posterior de Livewire.
     */
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
