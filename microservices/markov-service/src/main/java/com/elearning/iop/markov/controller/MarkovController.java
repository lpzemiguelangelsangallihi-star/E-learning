package com.elearning.iop.markov.controller;

import com.elearning.iop.markov.dto.MarkovRequest;
import com.elearning.iop.markov.dto.MarkovResponse;
import com.elearning.iop.markov.dto.EstimarMatrizRequest;
import com.elearning.iop.markov.dto.EstimarMatrizResponse;

import com.elearning.iop.markov.service.MarkovService;
import com.elearning.iop.markov.service.EstimarMatrizService;

import jakarta.validation.Valid;

import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/markov")
public class MarkovController {

    private final MarkovService markovService;
    private final EstimarMatrizService estimarMatrizService;

    public MarkovController(
            MarkovService markovService,
            EstimarMatrizService estimarMatrizService
    ) {
        this.markovService = markovService;
        this.estimarMatrizService = estimarMatrizService;
    }

    @PostMapping("/proyectar")
    public ResponseEntity<MarkovResponse> proyectar(
            @Valid @RequestBody MarkovRequest request
    ) {
        return ResponseEntity.ok(
            markovService.proyectar(request)
        );
    }

    @PostMapping("/estimar")
    public ResponseEntity<EstimarMatrizResponse> estimar(
            @Valid @RequestBody EstimarMatrizRequest request
    ) {
        return ResponseEntity.ok(
            estimarMatrizService.estimar(request)
        );
    }
}
