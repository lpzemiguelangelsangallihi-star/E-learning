<?php

namespace App\Services\IOP;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MarkovApiClient
{
    public function proyectar(
        array $vectorInicial,
        array $matriz,
        int $ciclos
    ): array {
        $url = rtrim(
            (string) config('services.markov.url'),
            '/'
        );

        $key = config('services.markov.key');

        if (!is_string($key) || $key === '') {
            throw new RuntimeException(
                'La API Key de Markov no está configurada.'
            );
        }

        $response = Http::acceptJson()
            ->withHeaders([
                'X-API-KEY' => $key,
            ])
            ->connectTimeout(2)
            ->timeout(5)
            ->post($url . '/api/markov/proyectar', [
                'matriz' => $matriz,
                'vectorInicial' => $vectorInicial,
                'ciclos' => $ciclos,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                'Error HTTP del microservicio Markov: '
                . $response->status()
            );
        }

        $data = $response->json();

        if (
            !is_array($data) ||
            !isset($data['proyeccion'], $data['ciclos']) ||
            !is_array($data['proyeccion']) ||
            count($data['proyeccion']) !== 4 ||
            $data['ciclos'] !== $ciclos
        ) {
            throw new RuntimeException(
                'Respuesta inválida del microservicio Markov.'
            );
        }

        $proyeccion = [];

        foreach ($data['proyeccion'] as $valor) {
            if (
                !is_numeric($valor) ||
                !is_finite((float) $valor) ||
                $valor < 0 ||
                $valor > 1
            ) {
                throw new RuntimeException(
                    'Probabilidad inválida recibida desde Markov.'
                );
            }

            $proyeccion[] = (float) $valor;
        }

        if (abs(array_sum($proyeccion) - 1.0) > 0.000001) {
            throw new RuntimeException(
                'La proyección recibida no suma 1.'
            );
        }

        return $proyeccion;
    }

    public function estimar(
        array $conteos,
        int $minimoObservaciones = 1
    ): array {
        $url = rtrim(
            (string) config('services.markov.url'),
            '/'
        );
    
        $key = config('services.markov.key');
    
        if (!is_string($key) || $key === '') {
            throw new RuntimeException(
                'La API Key de Markov no está configurada.'
            );
        }
    
        $response = Http::acceptJson()
            ->withHeaders([
                'X-API-KEY' => $key,
            ])
            ->connectTimeout(2)
            ->timeout(5)
            ->post($url . '/api/markov/estimar', [
                'conteos' => $conteos,
                'minimoObservaciones' => $minimoObservaciones,
            ]);
    
        if (!$response->successful()) {
            throw new RuntimeException(
                'Error HTTP al estimar Markov: '
                . $response->status()
            );
        }
    
        $data = $response->json();
    
        if (
            !is_array($data) ||
            !isset(
                $data['conteos'],
                $data['totales'],
                $data['probabilidades'],
                $data['completa'],
                $data['minimoObservaciones']
            ) ||
            !is_array($data['conteos']) ||
            !is_array($data['totales']) ||
            !is_array($data['probabilidades']) ||
            count($data['conteos']) !== 4 ||
            count($data['totales']) !== 4 ||
            count($data['probabilidades']) !== 4 ||
            !is_bool($data['completa']) ||
            $data['minimoObservaciones'] !== $minimoObservaciones
        ) {
            throw new RuntimeException(
                'Respuesta inválida del microservicio Markov.'
            );
        }
    
        // Comprobar que Java no ha modificado los conteos.
        if ($data['conteos'] !== $conteos) {
            throw new RuntimeException(
                'Los conteos recibidos no coinciden.'
            );
        }
    
        // Comprobar la coherencia de las probabilidades.
        $completa = true;
    
        foreach ($conteos as $i => $fila) {
            if (!is_array($fila) || count($fila) !== 4) {
                throw new RuntimeException(
                    'Matriz de conteos inválida.'
                );
            }
    
            $total = array_sum($fila);
    
            if ($data['totales'][$i] != $total) {
                throw new RuntimeException(
                    'El total de transiciones no coincide.'
                );
            }
    
            $probabilidades = $data['probabilidades'][$i];
    
            if ($i === 3) {
                if ($probabilidades !== [0.0, 0.0, 0.0, 1.0]) {
                    throw new RuntimeException(
                        'S4 debe ser absorbente.'
                    );
                }
                continue;
            }
    
            if ($total < $minimoObservaciones) {
                $completa = false;
    
                if ($probabilidades !== null) {
                    throw new RuntimeException(
                        'Se estimó una fila sin suficientes datos.'
                    );
                }
    
                continue;
            }
    
            if (!is_array($probabilidades)
                || count($probabilidades) !== 4) {
                throw new RuntimeException(
                    'Fila de probabilidades inválida.'
                );
            }
    
            foreach ($probabilidades as $j => $valor) {
                $esperado = $fila[$j] / $total;
    
                if (
                    !is_numeric($valor) ||
                    !is_finite((float) $valor) ||
                    abs((float) $valor - $esperado) > 0.000001
                ) {
                    throw new RuntimeException(
                        'Probabilidad de transición incorrecta.'
                    );
                }
            }
        }
    
        if ($data['completa'] !== $completa) {
            throw new RuntimeException(
                'El indicador de matriz completa no coincide.'
            );
        }
    
        return [
            'conteos' => $data['conteos'],
            'totales' => $data['totales'],
            'probabilidades' => $data['probabilidades'],
            'completa' => $data['completa'],
            'minimo_observaciones' => $data['minimoObservaciones'],
        ];
    }

}
