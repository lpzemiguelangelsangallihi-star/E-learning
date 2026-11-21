
package com.elearning.iop.markov.service;

import com.elearning.iop.markov.dto.EstimarMatrizRequest;
import com.elearning.iop.markov.dto.EstimarMatrizResponse;

import org.junit.jupiter.api.Test;

import java.util.Arrays;
import java.util.List;

import static org.junit.jupiter.api.Assertions.*;

class EstimarMatrizServiceTest {

    private final EstimarMatrizService service =
            new EstimarMatrizService();

    private List<List<Long>> conteosEjemplo() {
        return List.of(
            List.of(4L, 5L, 1L, 0L),
            List.of(2L, 4L, 3L, 1L),
            List.of(0L, 2L, 5L, 3L),
            List.of(0L, 0L, 0L, 0L)
        );
    }

    @Test
    void debeEstimarMatrizCompleta() {

        var resultado = service.estimar(
            new EstimarMatrizRequest(conteosEjemplo(), 1)
        );

        assertTrue(resultado.completa());

        assertEquals(
            List.of(10L, 10L, 10L, 0L),
            resultado.totales()
        );

        assertEquals(
            List.of(0.4, 0.5, 0.1, 0.0),
            resultado.probabilidades().get(0)
        );

        assertEquals(
            List.of(0.2, 0.4, 0.3, 0.1),
            resultado.probabilidades().get(1)
        );
    }

    @Test
    void debeDejarFilaSinDatosComoNull() {

        var conteos = List.of(
            List.of(4L, 5L, 1L, 0L),
            List.of(2L, 4L, 3L, 1L),
            List.of(0L, 1L, 1L, 0L),
            List.of(0L, 0L, 0L, 0L)
        );

        var resultado = service.estimar(
            new EstimarMatrizRequest(conteos, 5)
        );

        assertFalse(resultado.completa());
        assertNull(resultado.probabilidades().get(2));
        assertEquals(2L, resultado.totales().get(2));
    }

    @Test
    void debeMantenerS4Absorbente() {

        var resultado = service.estimar(
            new EstimarMatrizRequest(conteosEjemplo(), 1)
        );

        assertEquals(
            List.of(0.0, 0.0, 0.0, 1.0),
            resultado.probabilidades().get(3)
        );
    }

    @Test
    void debeRechazarConteosNegativos() {

        var conteos = List.of(
            List.of(-1L, 5L, 1L, 0L),
            List.of(2L, 4L, 3L, 1L),
            List.of(0L, 2L, 5L, 3L),
            List.of(0L, 0L, 0L, 0L)
        );

        assertThrows(
            IllegalArgumentException.class,
            () -> service.estimar(
                new EstimarMatrizRequest(conteos, 1)
            )
        );
    }

    @Test
    void debeRechazarMinimoInvalido() {

        assertThrows(
            IllegalArgumentException.class,
            () -> service.estimar(
                new EstimarMatrizRequest(conteosEjemplo(), 0)
            )
        );
    }

    @Test
    void debeRechazarMatrizIncompletaEnDimensiones() {

        var conteos = List.of(
            List.of(4L, 5L, 1L, 0L),
            List.of(2L, 4L, 3L, 1L)
        );

        assertThrows(
            IllegalArgumentException.class,
            () -> service.estimar(
                new EstimarMatrizRequest(conteos, 1)
            )
        );
    }
}
