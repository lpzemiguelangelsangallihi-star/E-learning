
package com.elearning.iop.maximin.service;

import com.elearning.iop.maximin.dto.MaximinRequest;
import com.elearning.iop.maximin.dto.MaximinResponse;

import org.junit.jupiter.api.Test;

import java.util.List;
import java.util.Map;

import static org.junit.jupiter.api.Assertions.*;

class MaximinServiceTest {

    private final MaximinService service =
            new MaximinService();

    @Test
    void debeRecomendarPractico() {

        var matriz = Map.of(
            "teorico", List.of(8.0, 7.0, 5.0),
            "practico", List.of(7.0, 9.0, 8.0),
            "visual", List.of(9.0, 8.0, 6.0)
        );

        MaximinResponse resultado = service.calcular(
            new MaximinRequest(matriz)
        );

        assertEquals(
            "practico",
            resultado.alternativaRecomendada()
        );

        assertEquals(7.0, resultado.valorMaximin());
    }

    @Test
    void debePriorizarTeoricoEnEmpate() {

        var matriz = Map.of(
            "teorico", List.of(9.0, 8.0, 7.0),
            "practico", List.of(7.0, 10.0, 9.0),
            "visual", List.of(6.0, 8.0, 9.0)
        );

        var resultado = service.calcular(
            new MaximinRequest(matriz)
        );

        assertEquals(
            "teorico",
            resultado.alternativaRecomendada()
        );
    }

    @Test
    void debeAceptarValoresDecimales() {

        var matriz = Map.of(
            "teorico", List.of(-2.5, 4.0, 6.0),
            "practico", List.of(0.5, 1.5, 2.5),
            "visual", List.of(-1.0, 3.5, 7.0)
        );

        var resultado = service.calcular(
            new MaximinRequest(matriz)
        );

        assertEquals(
            "practico",
            resultado.alternativaRecomendada()
        );

        assertEquals(0.5, resultado.valorMaximin());
    }

    @Test
    void debeRechazarMatricesIncompletas() {

        var matriz = Map.of(
            "teorico", List.of(8.0, 7.0, 5.0),
            "practico", List.of(7.0, 9.0, 8.0)
        );

        assertThrows(
            IllegalArgumentException.class,
            () -> service.calcular(new MaximinRequest(matriz))
        );
    }
}

