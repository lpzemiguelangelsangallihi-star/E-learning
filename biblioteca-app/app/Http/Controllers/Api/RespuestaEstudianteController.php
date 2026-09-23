<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RespuestaEstudiante;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RespuestaEstudianteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(RespuestaEstudiante::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'intento_id' => ['sometimes', 'integer'],
            'pregunta_id' => ['sometimes', 'integer'],
            'opcion_id' => ['sometimes', 'integer'],
            'respuesta_texto' => ['sometimes', 'nullable'],
            'es_correcta' => ['sometimes', 'nullable'],
            'puntaje_obtenido' => ['sometimes', 'nullable'],
        ]);

        $respuestaEstudiante = RespuestaEstudiante::create($validated);

        return response()->json($respuestaEstudiante, 201);
    }

    public function show(RespuestaEstudiante $respuestaEstudiante): JsonResponse
    {
        return response()->json($respuestaEstudiante);
    }

    public function update(Request $request, RespuestaEstudiante $respuestaEstudiante): JsonResponse
    {
        $validated = $request->validate([
            'intento_id' => ['sometimes', 'integer'],
            'pregunta_id' => ['sometimes', 'integer'],
            'opcion_id' => ['sometimes', 'integer'],
            'respuesta_texto' => ['sometimes', 'nullable'],
            'es_correcta' => ['sometimes', 'nullable'],
            'puntaje_obtenido' => ['sometimes', 'nullable'],
        ]);

        $respuestaEstudiante->update($validated);

        return response()->json($respuestaEstudiante);
    }

    public function destroy(RespuestaEstudiante $respuestaEstudiante): JsonResponse
    {
        $respuestaEstudiante->delete();

        return response()->json(null, 204);
    }
}
