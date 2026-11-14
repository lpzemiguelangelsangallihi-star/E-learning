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

            // Historial de ejercicios recibidos por el estudiante
            // en esta lección, excluyendo la recomendación actual.
            $ejerciciosPrevios = IopAsignacionRefuerzo::query()
                ->where('recomendacion_id', '!=', $recomendacion->id)
                ->whereHas(
                    'recomendacion',
                    fn ($query) => $query
                        ->where('estudiante_id', $recomendacion->estudiante_id)
                        ->where('leccion_id', $recomendacion->leccion_id)
                )
                ->pluck('ejercicio_id')
                ->map(fn ($id) => (int) $id)
                ->all();
            
            // Cantidad de veces que recibió cada ejercicio.
            $frecuencias = array_count_values($ejerciciosPrevios);
            
            // Preferimos el ejercicio menos utilizado.
            // En caso de empate conservamos el orden del catálogo.
            $ejercicio = $disponibles
                ->sortBy(
                    fn ($item) => $frecuencias[(int) $item->id] ?? 0
                )
                ->first();
            
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
