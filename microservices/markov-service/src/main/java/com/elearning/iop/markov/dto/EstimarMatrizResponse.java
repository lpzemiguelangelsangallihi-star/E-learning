package com.elearning.iop.markov.dto;

import java.util.List;

public record EstimarMatrizResponse(

    List<List<Long>> conteos,

    List<Long> totales,

    List<List<Double>> probabilidades,

    boolean completa,

    int minimoObservaciones

) {}
