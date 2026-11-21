
package com.elearning.iop.markov.service;

import com.elearning.iop.markov.dto.MarkovRequest;
import com.elearning.iop.markov.dto.MarkovResponse;

import org.junit.jupiter.api.Test;

import java.util.List;

import static org.junit.jupiter.api.Assertions.*;

class MarkovServiceTest {

    private final MarkovService service =
            new MarkovService();

    private final List<List<Double>> matriz = List.of(
        List.of(0.40, 0.45, 0.15, 0.00),
        List.of(0.15, 0.45, 0.30, 0.10),
        List.of(0.05, 0.15, 0.50, 0.30),
        List.of(0.00, 0.00, 0.00, 1.00)
    );

    @Test
    void debeProyectarTresCiclos() {

        var request = new MarkovRequest(
            matriz,
            List.of(1.0, 0.0, 0.0, 0.0),
            3
        );

        MarkovResponse resultado = service.proyectar(request);

        double[] esperado = {
            0.16825, 0.32850, 0.29175, 0.21150
        };

        for (int i = 0; i < 4; i++) {
            assertEquals(
                esperado[i],
                resultado.proyeccion().get(i),
                0.000001
            );
        }
    }

    @Test
    void debeMantenerVectorConCeroCiclos() {

        var vector = List.of(1.0, 0.0, 0.0, 0.0);

        var resultado = service.proyectar(
            new MarkovRequest(matriz, vector, 0)
        );

        assertEquals(vector, resultado.proyeccion());
    }

    @Test
    void debeMantenerEstadoAbsorbenteS4() {

        var resultado = service.proyectar(
            new MarkovRequest(
                matriz,
                List.of(0.0, 0.0, 0.0, 1.0),
                10
            )
        );

        assertEquals(
            1.0,
            resultado.proyeccion().get(3),
            0.000001
        );

        for (int i = 0; i < 3; i++) {
            assertEquals(
                0.0,
                resultado.proyeccion().get(i),
                0.000001
            );
        }
    }

    @Test
    void debeRechazarMatrizNoEstocastica() {

        var invalida = List.of(
            List.of(0.5, 0.5, 0.5, 0.0),
            List.of(0.15, 0.45, 0.30, 0.10),
            List.of(0.05, 0.15, 0.50, 0.30),
            List.of(0.0, 0.0, 0.0, 1.0)
        );

        assertThrows(
            IllegalArgumentException.class,
            () -> service.proyectar(
                new MarkovRequest(
                    invalida,
                    List.of(1.0, 0.0, 0.0, 0.0),
                    3
                )
            )
        );
    }

    @Test
    void debeRechazarCiclosNegativos() {

        assertThrows(
            IllegalArgumentException.class,
            () -> service.proyectar(
                new MarkovRequest(
                    matriz,
                    List.of(1.0, 0.0, 0.0, 0.0),
                    -1
                )
            )
        );
    }
}
