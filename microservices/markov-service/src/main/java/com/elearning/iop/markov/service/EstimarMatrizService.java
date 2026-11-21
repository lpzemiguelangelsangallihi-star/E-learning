package com.elearning.iop.markov.service;

import com.elearning.iop.markov.dto.EstimarMatrizRequest;
import com.elearning.iop.markov.dto.EstimarMatrizResponse;
import org.springframework.stereotype.Service;

import java.util.ArrayList;
import java.util.List;

@Service
public class EstimarMatrizService {

    private static final int ESTADOS = 4;

    public EstimarMatrizResponse estimar(
            EstimarMatrizRequest request
    ) {
        if (request == null
                || request.conteos() == null
                || request.conteos().size() != ESTADOS) {
            throw new IllegalArgumentException(
                "La matriz de conteos debe tener 4 filas."
            );
        }

        Integer minimo = request.minimoObservaciones();

        if (minimo == null || minimo < 1) {
            throw new IllegalArgumentException(
                "El mínimo de observaciones debe ser positivo."
            );
        }

        List<List<Long>> conteos = request.conteos();

        // Validación de los 16 conteos.
        for (List<Long> fila : conteos) {

            if (fila == null || fila.size() != ESTADOS) {
                throw new IllegalArgumentException(
                    "Cada fila debe tener 4 columnas."
                );
            }

            for (Long cantidad : fila) {
                if (cantidad == null || cantidad < 0) {
                    throw new IllegalArgumentException(
                        "Los conteos deben ser enteros no negativos."
                    );
                }
            }
        }

        List<Long> totales = new ArrayList<>();
        List<List<Double>> probabilidades = new ArrayList<>();

        boolean completa = true;

        for (int i = 0; i < ESTADOS; i++) {

            long total = 0;

            for (Long cantidad : conteos.get(i)) {
                total = Math.addExact(total, cantidad);
            }

            totales.add(total);

            // S4 siempre es absorbente.
            if (i == 3) {
                probabilidades.add(
                    List.of(0.0, 0.0, 0.0, 1.0)
                );
                continue;
            }

            // No inventar probabilidades sin datos suficientes.
            if (total < minimo) {
                probabilidades.add(null);
                completa = false;
                continue;
            }

            List<Double> filaProbabilidades = new ArrayList<>();

            for (Long cantidad : conteos.get(i)) {
                filaProbabilidades.add(
                    (double) cantidad / total
                );
            }

            probabilidades.add(filaProbabilidades);
        }

        return new EstimarMatrizResponse(
            conteos,
            totales,
            probabilidades,
            completa,
            minimo
        );
    }
}
