
package com.elearning.iop.markov.dto;

import jakarta.validation.constraints.NotEmpty;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Min;

import java.util.List;

public record MarkovRequest(

    @NotEmpty
    List<@NotEmpty List<@NotNull Double>> matriz,

    @NotEmpty
    List<@NotNull Double> vectorInicial,

    @NotNull
    @Min(0)
    Integer ciclos

) {}
