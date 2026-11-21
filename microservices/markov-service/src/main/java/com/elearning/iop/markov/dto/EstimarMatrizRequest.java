package com.elearning.iop.markov.dto;

import jakarta.validation.constraints.Min;
import jakarta.validation.constraints.NotNull;

import java.util.List;

public record EstimarMatrizRequest(

    @NotNull
    List<List<Long>> conteos,

    @NotNull
    @Min(1)
    Integer minimoObservaciones

) {}
