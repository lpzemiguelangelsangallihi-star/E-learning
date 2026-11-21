<?php

namespace App\Services\IOP;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MaximinApiClient
{
    public function recomendar(array $matriz): array
    {
        $url = rtrim(
            config('services.maximin.url'),
            '/'
        );

        $key = config('services.maximin.key');
        if (!is_string($key) || $key === '') {
            throw new RuntimeException(
                'La API Key de Maximin no está configurada.'
            );
        }
        
        $response = Http::acceptJson()
            ->withHeaders([
                'X-API-KEY' => $key,
            ])
            ->connectTimeout(2)
            ->timeout(5)
            ->post($url . '/api/maximin/calcular', [
                'matriz' => $matriz,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                'Error HTTP del microservicio Maximin: '
                . $response->status()
            );
        }

        $data = $response->json();

        if (
            !is_array($data) ||
            !isset(
                $data['alternativaRecomendada'],
                $data['valorMaximin'],
                $data['minimosPorAlternativa']
            )
        ) {
            throw new RuntimeException(
                'Respuesta inválida del microservicio Maximin.'
            );
        }

        $alternativa = $data['alternativaRecomendada'];
        $minimos = $data['minimosPorAlternativa'];

        if (
            !in_array($alternativa, [
                'teorico', 'practico', 'visual'
            ], true) ||
            !is_numeric($data['valorMaximin']) ||
            !is_finite((float) $data['valorMaximin']) ||
            !is_array($minimos)
        ) {
            throw new RuntimeException(
                'El microservicio devolvió datos inválidos.'
            );
        }

        foreach (['teorico', 'practico', 'visual'] as $tipo) {
            if (
                !isset($minimos[$tipo]) ||
                !is_numeric($minimos[$tipo]) ||
                !is_finite((float) $minimos[$tipo])
            ) {
                throw new RuntimeException(
                    'Los mínimos recibidos son inválidos.'
                );
            }
        }

        return [
            'alternativa' => $alternativa,
            'valor_maximin' => $data['valorMaximin'],
            'minimos' => $minimos,
        ];
    }
}
