<?php

namespace App\Services\IOP;

use App\Models\IntentoExamen;
use App\Models\IopRecomendacion;
use App\Models\IopSeguimiento;
use App\Models\IopTransicion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IntegracionEvaluacionIOPService
{ 
        public function __construct(
            private NormalizadorCalificacionService $normalizador,
            private RecomendacionService $recomendaciones,
            private SeguimientoService $seguimientos,
            private ConsolidacionSeguimientoIOPService $consolidacion,
            private AsignacionRefuerzoService $asignaciones,
            private CicloRefuerzoService $ciclosRefuerzo
        ) {}
    /**
     * Obtiene un intento finalizado y su nota en escala 0-100.
     */
    private function obtenerIntento(int $intentoId): array
    {
        $intento = IntentoExamen::with('examen')
            ->findOrFail($intentoId);

        if (
            $intento->estado !== 'finalizado' ||
            $intento->calificacion === null ||
            $intento->fecha_fin === null ||
            $intento->examen === null
        ) {
            throw ValidationException::withMessages([
                'intento' => 'El examen todavía no está finalizado.',
            ]);
        }

        $nota = $this->normalizador->normalizar(
            (float) $intento->calificacion,
            (float) $intento->examen->puntaje_maximo
        );

        return [$intento, $nota];
    }

    /**
     * Primera evaluación diagnóstica.
     *
     * Crea una recomendación Maximin e inicia a0.
     */
    public function procesarDiagnostico(
        int $intentoId
    ): IopSeguimiento {
        return DB::transaction(function () use ($intentoId) {
            [$intento, $nota] = $this->obtenerIntento($intentoId);

            // Serializa el procesamiento para el estudiante.
            User::whereKey($intento->estudiante_id)
                ->lockForUpdate()
                ->firstOrFail();

            $leccionId = (int) $intento->examen->leccion_id;

            $existente = IopSeguimiento::query()
                ->where('estudiante_id', $intento->estudiante_id)
                ->where('leccion_id', $leccionId)
                ->first();

            if ($existente) {
                // Repetir el mismo diagnóstico es seguro.
                if (
                    (int) $existente->intento_diagnostico_id
                    === (int) $intento->id
                ) {
                    $this->ciclosRefuerzo->registrarInicial(
                        (int) $existente->id);
                    return $existente;
                }

                // Nunca sobrescribir un seguimiento previo.
                throw ValidationException::withMessages([
                    'seguimiento' =>
                        'Ya existe un seguimiento para esta lección. '
                        .'No se reemplazará el historial anterior.',
                ]);
            }

            
            $recomendacion = $this->recomendaciones->generar(
                (int) $intento->estudiante_id,
                $leccionId,
                (float) $nota
            );             
            // Asignar automáticamente el ejercicio que
            // corresponde a la alternativa Maximin.
            $this->asignaciones->asignar(
                (int) $recomendacion->id
            );             
            // Iniciar el seguimiento académico.
            $seguimiento = $this->seguimientos->iniciar(
                (int) $recomendacion->id
            );

            $seguimiento->update([
                'intento_diagnostico_id' => $intento->id,
            ]);
            $this->ciclosRefuerzo->registrarInicial(
                (int) $seguimiento->id
            );
            return $seguimiento->refresh();
        });
    }

    /**
     * Evaluación posterior a un ciclo de refuerzo.
     */
    public function procesarSeguimiento(
        int $intentoId
    ): IopTransicion {
        return DB::transaction(function () use ($intentoId) {
            [$intento, $nota] = $this->obtenerIntento($intentoId);

            User::whereKey($intento->estudiante_id)
                ->lockForUpdate()
                ->firstOrFail();

            $leccionId = (int) $intento->examen->leccion_id;

            $seguimiento = IopSeguimiento::query()
                ->where('estudiante_id', $intento->estudiante_id)
                ->where('leccion_id', $leccionId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($seguimiento->intento_diagnostico_id === null) {
                throw ValidationException::withMessages([
                    'seguimiento' =>
                        'Este seguimiento fue creado manualmente. '
                        .'No se mezclarán datos simulados con datos reales.',
                ]);
            }

            $referencia = 'intento_examen:' . $intento->id;

            // Idempotencia: no repetir una transición registrada.
            $existente = IopTransicion::query()
                ->where('referencia_externa', $referencia)
                ->first();

            if ($existente) {
                if (
                    (int) $existente->seguimiento_id
                    !== (int) $seguimiento->id
                ) {
                    throw ValidationException::withMessages([
                        'intento' =>
                            'El intento ya pertenece a otro seguimiento.',
                    ]);
                }

                return $existente;
            }

            if (
                (int) $seguimiento->intento_diagnostico_id
                === (int) $intento->id
            ) {
                throw ValidationException::withMessages([
                    'intento' =>
                        'El diagnóstico no puede generar una transición.',
                ]);
            }

            // No mezclar una secuencia manual con evaluaciones.
            if ($seguimiento->transiciones()
                ->where('origen', 'simulacion')
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'seguimiento' =>
                        'El historial contiene simulaciones.',
                ]);
            }

            $diagnostico = IntentoExamen::findOrFail(
                $seguimiento->intento_diagnostico_id
            );

            if (
                !$diagnostico->fecha_fin ||
                !$intento->fecha_fin->greaterThan(
                    $diagnostico->fecha_fin
                )
            ) {
                throw ValidationException::withMessages([
                    'intento' =>
                        'El seguimiento debe ser posterior al diagnóstico.',
                ]);
            }

            // Impide ingresar exámenes antiguos fuera de orden.
            $ultimaTransicion = $seguimiento->transiciones()
                ->orderByDesc('ciclo')
                ->first();

            if ($ultimaTransicion) {
                $ultimaReferencia =
                    $ultimaTransicion->referencia_externa;

                if (
                    !$ultimaReferencia ||
                    !str_starts_with(
                        $ultimaReferencia,
                        'intento_examen:'
                    )
                ) {
                    throw ValidationException::withMessages([
                        'historial' =>
                            'El historial no tiene referencias verificables.',
                    ]);
                }

                $ultimoId = (int) substr(
                    $ultimaReferencia,
                    strlen('intento_examen:')
                );

                $ultimoIntento = IntentoExamen::findOrFail(
                    $ultimoId
                );

                if (
                    !$ultimoIntento->fecha_fin ||
                    !$intento->fecha_fin->greaterThan(
                        $ultimoIntento->fecha_fin
                    )
                ) {
                    throw ValidationException::withMessages([
                        'intento' =>
                            'Las evaluaciones deben procesarse en orden.',
                    ]);
                }
            }

            
             //VERIFICACIÓN DEL REFUERZO ACADÉMICo

            $recomendacion = $seguimiento
                ->recomendacionInicial()
                ->with('asignacionRefuerzo')
                ->first();

            $asignacion = $recomendacion
                ?->asignacionRefuerzo;

            if (
                !$asignacion ||
                $asignacion->estado !== 'completado' ||
                !$asignacion->completado_en ||
                !$asignacion->revisado_por
            ) {
                throw ValidationException::withMessages([
                    'refuerzo' =>
                        'Debes completar y obtener la aprobación '
                        .'del ejercicio de refuerzo antes de '
                        .'realizar una evaluación de seguimiento.',
                ]);
            }

            // Impide utilizar una evaluación realizada
            // antes de que el profesor aprobara el refuerzo.
            if (
                !$intento->fecha_fin->greaterThan(
                    $asignacion->completado_en
                )
            ) {
                throw ValidationException::withMessages([
                    'intento' =>
                        'La evaluación debe haberse realizado '
                        .'después de la aprobación del refuerzo.',
                ]);
            }


            
            $transicion = $this->seguimientos->registrarEvaluacion(
                (int) $seguimiento->id,
                (float) $nota,
                'evaluacion',
                $referencia
            );

            // Comprobar si la nueva evaluación permite
            // consolidar el aprendizaje en S4.
            $this->consolidacion->consolidarSiCorresponde(
                (int) $seguimiento->id
            );

            // Devolver el estado definitivo de la transición.
            return $transicion->refresh();
        });
    }
}
