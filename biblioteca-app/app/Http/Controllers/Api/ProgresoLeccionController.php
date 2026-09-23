<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProgresoLeccion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProgresoLeccionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(ProgresoLeccion::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'leccion_id' => ['sometimes', 'integer'],
            'estudiante_id' => ['sometimes', 'integer'],
            'porcentaje' => ['sometimes', 'numeric'],
            'completado' => ['sometimes', 'nullable'],
            'fecha_inicio' => ['sometimes', 'date'],
            'fecha_completado' => ['sometimes', 'date'],
        ]);

        $progresoLeccion = ProgresoLeccion::create($validated);

        return response()->json($progresoLeccion, 201);
    }

    public function show(ProgresoLeccion $progresoLeccion): JsonResponse
    {
        return response()->json($progresoLeccion);
    }

    public function update(Request $request, ProgresoLeccion $progresoLeccion): JsonResponse
    {
        $validated = $request->validate([
            'leccion_id' => ['sometimes', 'integer'],
            'estudiante_id' => ['sometimes', 'integer'],
            'porcentaje' => ['sometimes', 'numeric'],
            'completado' => ['sometimes', 'nullable'],
            'fecha_inicio' => ['sometimes', 'date'],
            'fecha_completado' => ['sometimes', 'date'],
        ]);

        $progresoLeccion->update($validated);

        return response()->json($progresoLeccion);
    }

    public function destroy(ProgresoLeccion $progresoLeccion): JsonResponse
    {
        $progresoLeccion->delete();

        return response()->json(null, 204);
    }
}
