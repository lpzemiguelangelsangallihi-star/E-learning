<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpcionPregunta;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OpcionPreguntaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(OpcionPregunta::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pregunta_id' => ['sometimes', 'integer'],
            'opcion' => ['sometimes', 'nullable'],
            'es_correcta' => ['sometimes', 'nullable'],
        ]);

        $opcionPregunta = OpcionPregunta::create($validated);

        return response()->json($opcionPregunta, 201);
    }

    public function show(OpcionPregunta $opcionPregunta): JsonResponse
    {
        return response()->json($opcionPregunta);
    }

    public function update(Request $request, OpcionPregunta $opcionPregunta): JsonResponse
    {
        $validated = $request->validate([
            'pregunta_id' => ['sometimes', 'integer'],
            'opcion' => ['sometimes', 'nullable'],
            'es_correcta' => ['sometimes', 'nullable'],
        ]);

        $opcionPregunta->update($validated);

        return response()->json($opcionPregunta);
    }

    public function destroy(OpcionPregunta $opcionPregunta): JsonResponse
    {
        $opcionPregunta->delete();

        return response()->json(null, 204);
    }
}
