package com.elearning.iop.maximin.controller;

import com.elearning.iop.maximin.dto.MaximinRequest;
import com.elearning.iop.maximin.dto.MaximinResponse;
import com.elearning.iop.maximin.service.MaximinService;

import jakarta.validation.Valid;

import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/maximin")
public class MaximinController {

    private final MaximinService maximinService;

    public MaximinController(MaximinService maximinService) {
        this.maximinService = maximinService;
    }

    @PostMapping("/calcular")
    public ResponseEntity<MaximinResponse> calcular(
            @Valid @RequestBody MaximinRequest request) {

        MaximinResponse resultado = maximinService.calcular(request);

        return ResponseEntity.ok(resultado);
    }
}
