<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pregunta;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PreguntaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Pregunta::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'examen_id' => ['sometimes', 'integer'],
            'pregunta' => ['sometimes', 'nullable'],
            'tipo' => ['sometimes', 'string'],
            'puntaje' => ['sometimes', 'numeric'],
            'orden' => ['sometimes', 'numeric'],
        ]);

        $pregunta = Pregunta::create($validated);

        return response()->json($pregunta, 201);
    }

    public function show(Pregunta $pregunta): JsonResponse
    {
        return response()->json($pregunta);
    }

    public function update(Request $request, Pregunta $pregunta): JsonResponse
    {
        $validated = $request->validate([
            'examen_id' => ['sometimes', 'integer'],
            'pregunta' => ['sometimes', 'nullable'],
            'tipo' => ['sometimes', 'string'],
            'puntaje' => ['sometimes', 'numeric'],
            'orden' => ['sometimes', 'numeric'],
        ]);

        $pregunta->update($validated);

        return response()->json($pregunta);
    }

    public function destroy(Pregunta $pregunta): JsonResponse
    {
        $pregunta->delete();

        return response()->json(null, 204);
    }
}
