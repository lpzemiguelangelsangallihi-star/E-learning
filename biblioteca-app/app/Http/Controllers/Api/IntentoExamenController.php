<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IntentoExamen;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class IntentoExamenController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(IntentoExamen::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'examen_id' => ['sometimes', 'integer'],
            'estudiante_id' => ['sometimes', 'integer'],
            'numero_intento' => ['sometimes', 'nullable'],
            'fecha_inicio' => ['sometimes', 'date'],
            'fecha_fin' => ['sometimes', 'date'],
            'calificacion' => ['sometimes', 'numeric'],
            'estado' => ['sometimes', 'string'],
        ]);

        $intentoExamen = IntentoExamen::create($validated);

        return response()->json($intentoExamen, 201);
    }

    public function show(IntentoExamen $intentoExamen): JsonResponse
    {
        return response()->json($intentoExamen);
    }

    public function update(Request $request, IntentoExamen $intentoExamen): JsonResponse
    {
        $validated = $request->validate([
            'examen_id' => ['sometimes', 'integer'],
            'estudiante_id' => ['sometimes', 'integer'],
            'numero_intento' => ['sometimes', 'nullable'],
            'fecha_inicio' => ['sometimes', 'date'],
            'fecha_fin' => ['sometimes', 'date'],
            'calificacion' => ['sometimes', 'numeric'],
            'estado' => ['sometimes', 'string'],
        ]);

        $intentoExamen->update($validated);

        return response()->json($intentoExamen);
    }

    public function destroy(IntentoExamen $intentoExamen): JsonResponse
    {
        $intentoExamen->delete();

        return response()->json(null, 204);
    }
}
