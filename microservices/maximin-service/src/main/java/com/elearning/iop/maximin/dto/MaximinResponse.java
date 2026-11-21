package com.elearning.iop.maximin.dto;

import java.util.Map;

public record MaximinResponse(

    String alternativaRecomendada,

    double valorMaximin,

    Map<String, Double> minimosPorAlternativa

) {}
