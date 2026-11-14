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

            if (
                $intento->examen->tipo_iop
                    !== \App\Models\Examen::IOP_DIAGNOSTICO
            ) {
                throw ValidationException::withMessages([
                    'intento' =>
                        'El examen no está configurado como diagnóstico IOP.',
                ]);
            }
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
     * Procesa una evaluación real de refuerzo.
     *
     * Verifica el ciclo actual, registra Markov,
     * comprueba S4 y genera el siguiente refuerzo.
     */
    public function procesarSeguimiento(
        int $intentoId
    ): IopTransicion {
        return DB::transaction(function () use ($intentoId) {

            [$intento, $nota] = $this->obtenerIntento(
                $intentoId
            );

            if (
                $intento->examen->tipo_iop
                    !== \App\Models\Examen::IOP_REFUERZO
            ) {
                throw ValidationException::withMessages([
                    'intento' =>
                        'El examen no es una evaluación IOP de refuerzo.',
                ]);
            }

            // Serializar las operaciones del estudiante.
            User::query()
                ->whereKey($intento->estudiante_id)
                ->lockForUpdate()
                ->firstOrFail();

            $leccionId = (int) $intento->examen->leccion_id;

            $seguimiento = IopSeguimiento::query()
                ->where('estudiante_id', $intento->estudiante_id)
                ->where('leccion_id', $leccionId)
                ->lockForUpdate()
                ->firstOrFail();

            $referencia = 'intento_examen:' . $intento->id;

            // Repetir una evaluación procesada
            // nunca genera otro ciclo.
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
                            'El intento pertenece a otro seguimiento.',
                    ]);
                }

                return $existente;
            }

            if (
                !$seguimiento->activo ||
                $seguimiento->estado_actual === 'S4'
            ) {
                throw ValidationException::withMessages([
                    'seguimiento' =>
                        'El seguimiento ya está cerrado.',
                ]);
            }

            if ($seguimiento->intento_diagnostico_id === null) {
                throw ValidationException::withMessages([
                    'seguimiento' =>
                        'No existe un diagnóstico real vinculado.',
                ]);
            }

            if (
                (int) $seguimiento->intento_diagnostico_id
                    === (int) $intento->id
            ) {
                throw ValidationException::withMessages([
                    'intento' =>
                        'El diagnóstico no puede utilizarse '
                        .'como evaluación de seguimiento.',
                ]);
            }

            // Impedir mezclar simulaciones con datos reales.
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
                        'La evaluación debe ser posterior al diagnóstico.',
                ]);
            }

            // Consultar el ciclo que el estudiante
            // debe completar antes de esta evaluación.
            $cicloActual = $seguimiento->ciclosRefuerzo()
                ->where('ciclo', $seguimiento->ciclo_actual)
                ->with('recomendacion.asignacionRefuerzo')
                ->first();

            if (!$cicloActual) {
                throw ValidationException::withMessages([
                    'ciclo' =>
                        'No existe un refuerzo registrado '
                        .'para el ciclo actual.',
                ]);
            }

            $recomendacion = $cicloActual->recomendacion;
            $asignacion = $recomendacion
                ?->asignacionRefuerzo;

            if (
                !$recomendacion ||
                (int) $recomendacion->estudiante_id
                    !== (int) $seguimiento->estudiante_id ||
                (int) $recomendacion->leccion_id
                    !== (int) $seguimiento->leccion_id ||
                !$asignacion ||
                $asignacion->estado !== 'completado' ||
                !$asignacion->completado_en ||
                !$asignacion->revisado_por
            ) {
                throw ValidationException::withMessages([
                    'refuerzo' =>
                        'Debes completar y obtener la aprobación '
                        .'del refuerzo correspondiente al ciclo actual.',
                ]);
            }

            // La evaluación debe ocurrir después
            // de aprobar el ejercicio.
            if (
                !$intento->fecha_fin->greaterThan(
                    $asignacion->completado_en
                )
            ) {
                throw ValidationException::withMessages([
                    'intento' =>
                        'La evaluación se realizó antes '
                        .'de aprobar el refuerzo actual.',
                ]);
            }

            // Mantener el orden de las evaluaciones.
            $ultimaTransicion = $seguimiento->transiciones()
                ->orderByDesc('ciclo')
                ->first();

            if ($ultimaTransicion) {

                $ultimaReferencia = (string)
                    $ultimaTransicion->referencia_externa;

                if (!preg_match(
                    '/^intento_examen:[1-9][0-9]*$/',
                    $ultimaReferencia
                )) {
                    throw ValidationException::withMessages([
                        'historial' =>
                            'El historial contiene una referencia inválida.',
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
                            'Las evaluaciones deben procesarse '
                            .'en orden cronológico.',
                    ]);
                }

                if (
                    !$asignacion->completado_en->greaterThan(
                        $ultimoIntento->fecha_fin
                    )
                ) {
                    throw ValidationException::withMessages([
                        'refuerzo' =>
                            'El refuerzo actual debe aprobarse '
                            .'después de la evaluación anterior.',
                    ]);
                }
            }

            // 1. Registrar la transición académica real.
            $transicion = $this->seguimientos
                ->registrarEvaluacion(
                    (int) $seguimiento->id,
                    (float) $nota,
                    'evaluacion',
                    $referencia
                );

            // 2. Comprobar la consolidación del aprendizaje.
            $consolidado = $this->consolidacion
                ->consolidarSiCorresponde(
                    (int) $seguimiento->id
                );

            // 3. Si no alcanzó S4, preparar
            //    automáticamente la siguiente actividad.
            if (!$consolidado) {
                $this->ciclosRefuerzo->generarSiguiente(
                    (int) $transicion->id
                );
            }

            return $transicion->refresh();
        });
    }
}