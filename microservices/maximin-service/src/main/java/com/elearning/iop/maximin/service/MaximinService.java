package com.elearning.iop.maximin.service;

import com.elearning.iop.maximin.dto.MaximinRequest;
import com.elearning.iop.maximin.dto.MaximinResponse;

import org.springframework.stereotype.Service;

import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

@Service
public class MaximinService {

    private static final List<String> ALTERNATIVAS =
            List.of("teorico", "practico", "visual");

    public MaximinResponse calcular(MaximinRequest request) {

        Map<String, Double> minimos = new LinkedHashMap<>();

        for (String alternativa : ALTERNATIVAS) {

            List<Double> utilidades =
                    request.matriz().get(alternativa);

            if (utilidades == null || utilidades.size() != 3) {
                throw new IllegalArgumentException(
                    "Matriz inválida para " + alternativa
                );
            }

            for (Double utilidad : utilidades) {
                if (utilidad == null || !Double.isFinite(utilidad)) {
                    throw new IllegalArgumentException(
                        "Las utilidades deben ser valores numéricos finitos."
                    );
                }
            }

            double minimo = utilidades.stream()
                    .mapToDouble(Double::doubleValue)
                    .min()
                    .orElseThrow();

            minimos.put(alternativa, minimo);
        }

        String seleccionada = null;
        double mejorValor = Double.NEGATIVE_INFINITY;

        for (String alternativa : ALTERNATIVAS) {

            double minimo = minimos.get(alternativa);

            if (minimo > mejorValor) {
                mejorValor = minimo;
                seleccionada = alternativa;
            }
        }

        return new MaximinResponse(
                seleccionada,
                mejorValor,
                minimos
        );
    }
}
