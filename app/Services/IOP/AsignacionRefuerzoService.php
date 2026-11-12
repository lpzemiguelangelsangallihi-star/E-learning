<?php

namespace App\Services\IOP;

use App\Models\IopAsignacionRefuerzo;
use App\Models\IopRecomendacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AsignacionRefuerzoService
{
    public function __construct(
        private EjercicioRefuerzoService $ejercicios
    ) {}

    /**
     * Asigna un ejercicio a una recomendación Maximin.
     *
     * En esta primera versión:
     * - Se asigna un ejercicio por recomendación.
     * - Se selecciona el primer ejercicio activo
     *   según el orden del catálogo.
     * - Una asignación existente no se reemplaza.
     */
    public function asignar(
        int $recomendacionId
    ): IopAsignacionRefuerzo {

        return DB::transaction(function () use ($recomendacionId) {

            // Bloqueamos la recomendación para evitar
            // asignaciones simultáneas duplicadas.
            $recomendacion = IopRecomendacion::query()
                ->whereKey($recomendacionId)
                ->lockForUpdate()
                ->firstOrFail();

            $existente = IopAsignacionRefuerzo::query()
                ->where('recomendacion_id', $recomendacion->id)
                ->first();

            if ($existente) {
                return $existente;
            }

            $disponibles = $this->ejercicios->buscar(
                (int) $recomendacion->leccion_id,
                $recomendacion->alternativa_recomendada
            );

            $ejercicio = $disponibles->first();

            if (!$ejercicio) {
                throw ValidationException::withMessages([
                    'ejercicio' =>
                        'No existen ejercicios activos '
                        .'para la categoría recomendada '
                        .'en esta lección.',
                ]);
            }

            return IopAsignacionRefuerzo::create([
                'recomendacion_id' => $recomendacion->id,
                'ejercicio_id' => $ejercicio->id,
                'estado' => 'asignado',
                'asignado_en' => now(),
                'completado_en' => null,
            ]);
        });
    }
}
