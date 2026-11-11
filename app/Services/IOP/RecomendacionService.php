<?php

namespace App\Services\IOP;

use App\Models\IopModeloDecision;
use App\Models\IopRecomendacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RecomendacionService
{
    public function __construct(
        private MaximinService $maximin
    ) {}

    public function generar(
        int $estudianteId,
        int $leccionId,
        float $calificacion
    ): IopRecomendacion {

        // Validar los datos antes de registrar una recomendación.
        Validator::make([
            'estudiante_id' => $estudianteId,
            'leccion_id' => $leccionId,
            'calificacion' => $calificacion,
        ], [
            'estudiante_id' => 'required|integer|exists:users,id',
            'leccion_id' => 'required|integer|exists:lecciones,id',
            'calificacion' => 'required|numeric|between:0,100',
        ])->validate();

        return DB::transaction(function () use (
            $estudianteId,
            $leccionId,
            $calificacion
        ) {
            $modelo = IopModeloDecision::where('activo', true)
                ->where('metodo', 'maximin')
                ->orderByDesc('version')
                ->first();

            if (!$modelo) {
                throw ValidationException::withMessages([
                    'modelo' => 'No existe una matriz Maximin activa.',
                ]);
            }

            $resultado = $this->maximin->recomendar(
                $modelo->matriz_utilidades
            );

            return IopRecomendacion::create([
                'estudiante_id' => $estudianteId,
                'leccion_id' => $leccionId,
                'modelo_decision_id' => $modelo->id,
                'calificacion_inicial' => $calificacion,
                'alternativa_recomendada' =>
                    $resultado['alternativa'],
                'valor_maximin' =>
                    $resultado['valor_maximin'],
                'minimos' => $resultado['minimos'],
                'matriz_utilizada' =>
                    $modelo->matriz_utilidades,
            ]);
        });
    }
}
