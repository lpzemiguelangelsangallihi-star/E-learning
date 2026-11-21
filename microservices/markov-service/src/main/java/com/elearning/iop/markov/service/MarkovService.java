
package com.elearning.iop.markov.service;

import com.elearning.iop.markov.dto.MarkovRequest;
import com.elearning.iop.markov.dto.MarkovResponse;
import org.springframework.stereotype.Service;

import java.util.ArrayList;
import java.util.List;

@Service
public class MarkovService {

    private static final int ESTADOS = 4;
    private static final double TOLERANCIA = 0.000001;

    public void validarMatriz(List<List<Double>> matriz) {

        if (matriz == null || matriz.size() != ESTADOS) {
            throw new IllegalArgumentException(
                "La matriz debe tener 4 filas."
            );
        }

        for (List<Double> fila : matriz) {

            if (fila == null || fila.size() != ESTADOS) {
                throw new IllegalArgumentException(
                    "Cada fila debe tener 4 columnas."
                );
            }

            double suma = 0.0;

            for (Double probabilidad : fila) {

                if (probabilidad == null
                        || !Double.isFinite(probabilidad)
                        || probabilidad < 0
                        || probabilidad > 1) {

                    throw new IllegalArgumentException(
                        "Probabilidad inválida."
                    );
                }

                suma += probabilidad;
            }

            if (Math.abs(suma - 1.0) > TOLERANCIA) {
                throw new IllegalArgumentException(
                    "Cada fila debe sumar 1."
                );
            }
        }

        // El estado S4 es absorbente.
        double[] absorbente = {0.0, 0.0, 0.0, 1.0};

        for (int j = 0; j < ESTADOS; j++) {

            if (Math.abs(
                    matriz.get(3).get(j) - absorbente[j]
            ) > TOLERANCIA) {

                throw new IllegalArgumentException(
                    "S4 debe ser un estado absorbente."
                );
            }
        }
    }

    public void validarVector(List<Double> vector) {

        if (vector == null || vector.size() != ESTADOS) {
            throw new IllegalArgumentException(
                "El vector inicial debe tener 4 valores."
            );
        }

        double suma = 0.0;

        for (Double probabilidad : vector) {

            if (probabilidad == null
                    || !Double.isFinite(probabilidad)
                    || probabilidad < 0
                    || probabilidad > 1) {

                throw new IllegalArgumentException(
                    "Vector de probabilidades inválido."
                );
            }

            suma += probabilidad;
        }

        if (Math.abs(suma - 1.0) > TOLERANCIA) {
            throw new IllegalArgumentException(
                "El vector debe sumar 1."
            );
        }
    }

    private double[][] multiplicarMatrices(
            double[][] a,
            double[][] b
    ) {

        double[][] resultado = new double[ESTADOS][ESTADOS];

        for (int i = 0; i < ESTADOS; i++) {
            for (int j = 0; j < ESTADOS; j++) {
                for (int k = 0; k < ESTADOS; k++) {

                    resultado[i][j] +=
                        a[i][k] * b[k][j];
                }
            }
        }

        return resultado;
    }

    public double[][] potenciaMatriz(
            List<List<Double>> matriz,
            int ciclos
    ) {

        validarMatriz(matriz);

        if (ciclos < 0) {
            throw new IllegalArgumentException(
                "El número de ciclos no puede ser negativo."
            );
        }

        // Matriz identidad I4.
        double[][] resultado = new double[ESTADOS][ESTADOS];

        for (int i = 0; i < ESTADOS; i++) {
            resultado[i][i] = 1.0;
        }

        double[][] base = new double[ESTADOS][ESTADOS];

        for (int i = 0; i < ESTADOS; i++) {
            for (int j = 0; j < ESTADOS; j++) {
                base[i][j] = matriz.get(i).get(j);
            }
        }

        // Exponenciación binaria.
        while (ciclos > 0) {

            if (ciclos % 2 == 1) {
                resultado = multiplicarMatrices(resultado, base);
            }

            base = multiplicarMatrices(base, base);
            ciclos /= 2;
        }

        return resultado;
    }

    public MarkovResponse proyectar(MarkovRequest request) {

        validarVector(request.vectorInicial());

        if (request.ciclos() == null) {
            throw new IllegalArgumentException(
                "El número de ciclos es obligatorio."
            );
        }

        double[][] potencia = potenciaMatriz(
            request.matriz(),
            request.ciclos()
        );

        List<Double> proyeccion = new ArrayList<>();

        for (int j = 0; j < ESTADOS; j++) {

            double valor = 0.0;

            for (int i = 0; i < ESTADOS; i++) {
                valor += request.vectorInicial().get(i)
                        * potencia[i][j];
            }

            proyeccion.add(valor);
        }

        return new MarkovResponse(
            proyeccion,
            request.ciclos()
        );
    }
}
