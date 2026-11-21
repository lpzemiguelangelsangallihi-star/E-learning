
package com.elearning.iop.markov.security;

import jakarta.servlet.FilterChain;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Component;
import org.springframework.web.filter.OncePerRequestFilter;

import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;

@Component
public class ApiKeyFilter extends OncePerRequestFilter {

    private final String apiKey;

    public ApiKeyFilter(
            @Value("${iop.api.key}") String apiKey
    ) {
        if (apiKey == null || apiKey.isBlank()) {
            throw new IllegalStateException(
                "La variable IOP_API_KEY es obligatoria"
            );
        }

        this.apiKey = apiKey;
    }

    @Override
    protected boolean shouldNotFilter(
            HttpServletRequest request
    ) {
        return !request.getRequestURI()
                .startsWith("/api/markov/");
    }

    @Override
    protected void doFilterInternal(
            HttpServletRequest request,
            HttpServletResponse response,
            FilterChain chain
    ) throws ServletException, IOException {

        String receivedKey = request.getHeader("X-API-KEY");

        boolean valid = receivedKey != null
                && MessageDigest.isEqual(
                    apiKey.getBytes(StandardCharsets.UTF_8),
                    receivedKey.getBytes(StandardCharsets.UTF_8)
                );

        if (!valid) {
            response.sendError(
                HttpServletResponse.SC_UNAUTHORIZED,
                "API Key inválida"
            );
            return;
        }

        chain.doFilter(request, response);
    }
}
