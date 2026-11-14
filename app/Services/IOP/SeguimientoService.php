<?php

namespace App\Services\IOP;

use App\Models\IopRecomendacion;
use App\Models\IopSeguimiento;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use App\Models\IopTransicion;
use Illuminate\Validation\ValidationException;

class SeguimientoService
{    
    /**
     * Clasifica una calificación normalizada
     * entre 0 y 100, incluyendo decimales.
     *
     * S4 se alcanza mediante el servicio de
     * consolidación, no por esta clasificación.
     */
    public function clasificarEstado(float $nota): string
    {
        if (
            !is_finite($nota) ||
            $nota < 0 ||
            $nota > 100
        ) {
            throw new InvalidArgumentException(
                'La calificación debe estar entre 0 y 100.'
            );
        }

        return match (true) {
            $nota <= 50 => 'S1',
            $nota < 70 => 'S2',
            default => 'S3',
        };
    }

    /**
     * Construye el vector inicial a0.
     */
    public function generarVectorInicial(string $estado): array
    {
        return match ($estado) {
            'S1' => [1, 0, 0, 0],
            'S2' => [0, 1, 0, 0],
            'S3' => [0, 0, 1, 0],
            'S4' => [0, 0, 0, 1],
            default => throw new InvalidArgumentException(
                'Estado de Markov inválido.'
            ),
        };
    }

    /**
     * Inicia un seguimiento a partir de una
     * recomendación Maximin ya registrada.
     */
    public function iniciar(int $recomendacionId): IopSeguimiento
    {
        return DB::transaction(function () use ($recomendacionId) {

            $recomendacion = IopRecomendacion::findOrFail(
                $recomendacionId
            );

            $nota = (float) $recomendacion->calificacion_inicial;

            $estado = $this->clasificarEstado($nota);

            $vector = $this->generarVectorInicial($estado);

            // Evita abrir un segundo seguimiento
            // para la misma pareja estudiante/lección.
            return IopSeguimiento::firstOrCreate(
                [
                    'estudiante_id' => $recomendacion->estudiante_id,
                    'leccion_id' => $recomendacion->leccion_id,
                ],
                [
                    'recomendacion_inicial_id' => $recomendacion->id,
                    'calificacion_inicial' => $nota,
                    'estado_inicial' => $estado,
                    'estado_actual' => $estado,
                    'vector_inicial' => $vector,
                    'ciclo_actual' => 0,
                    'ultima_calificacion' => $nota,
                    'activo' => true,
                ]
            );
        });
    }


    /**
     * Registra una nueva evaluación y actualiza
     * el estado del estudiante.
     */
    public function registrarEvaluacion(
        int $seguimientoId,
        float $nuevaCalificacion,
        string $origen = 'simulacion',
        ?string $referenciaExterna = null
    ): IopTransicion {
    
        // Validamos antes de modificar la base de datos.
        $nuevoEstado = $this->clasificarEstado(
            $nuevaCalificacion
        );
    
        if (!in_array($origen, ['simulacion', 'evaluacion'], true)) {
            throw new InvalidArgumentException(
                'Origen de evaluación inválido.'
            );
        }
    
        if (
            $origen === 'evaluacion' &&
            ($referenciaExterna === null || $referenciaExterna === '')
        ) {
            throw new InvalidArgumentException(
                'Una evaluación real requiere referencia externa.'
            );
        }
    
        return DB::transaction(function () use (
            $seguimientoId,
            $nuevaCalificacion,
            $nuevoEstado,
            $origen,
            $referenciaExterna
        ) {
    
            // Bloqueamos el seguimiento durante su actualización.
            $seguimiento = IopSeguimiento::query()
                ->lockForUpdate()
                ->findOrFail($seguimientoId);
    
    
            // Una evaluación real no debe registrarse dos veces.
            if ($referenciaExterna !== null) {
                $existente = IopTransicion::where(
                    'referencia_externa',
                    $referenciaExterna
                )->first();
    
                if ($existente) {
                    if ( (int) $existente->seguimiento_id !== (int) $seguimiento->id) {
                        throw ValidationException::withMessages([
                            'referencia_externa' =>
                                'La evaluación pertenece a otro seguimiento.',
                        ]);
                    }
    
                    return $existente;
                }
            }

            if (!$seguimiento->activo) {
                throw ValidationException::withMessages([
                    'seguimiento' => 'El seguimiento está cerrado.',
                ]);
            }
    
            // Evita alterar un seguimiento ya consolidado.
            if ($seguimiento->estado_actual === 'S4') {
                throw ValidationException::withMessages([
                    'estado' => 'El estudiante ya alcanzó S4.',
                ]);
            }
    
            $estadoAnterior = $seguimiento->estado_actual;
            $calificacionAnterior = $seguimiento->ultima_calificacion;
            $nuevoCiclo = $seguimiento->ciclo_actual + 1;
    
            $transicion = IopTransicion::create([
                'seguimiento_id' => $seguimiento->id,
                'ciclo' => $nuevoCiclo,
                'estado_anterior' => $estadoAnterior,
                'estado_siguiente' => $nuevoEstado,
                'calificacion_anterior' => $calificacionAnterior,
                'calificacion_nueva' => $nuevaCalificacion,
                'origen' => $origen,
                'referencia_externa' => $referenciaExterna,
            ]);
    
            $seguimiento->update([
                'estado_actual' => $nuevoEstado,
                'ultima_calificacion' => $nuevaCalificacion,
                'ciclo_actual' => $nuevoCiclo,
            ]);
    
            return $transicion;
        });
    }

}
