package com.elearning.iop.maximin.dto;

import jakarta.validation.constraints.NotEmpty;
import jakarta.validation.constraints.NotNull;

import java.util.List;
import java.util.Map;

public record MaximinRequest(

    @NotEmpty
    Map<String, @NotEmpty List<@NotNull Double>> matriz

) {}
