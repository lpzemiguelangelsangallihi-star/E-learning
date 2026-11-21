
package com.elearning.iop.markov.dto;

import java.util.List;

public record MarkovResponse(

    List<Double> proyeccion,

    Integer ciclos

) {}

