<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MateriaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Materia::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
            'estado' => ['sometimes', 'string'],
        ]);

        $materia = Materia::create($validated);

        return response()->json($materia, 201);
    }

    public function show(Materia $materia): JsonResponse
    {
        return response()->json($materia);
    }

    public function update(Request $request, Materia $materia): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
            'estado' => ['sometimes', 'string'],
        ]);

        $materia->update($validated);

        return response()->json($materia);
    }

    public function destroy(Materia $materia): JsonResponse
    {
        $materia->delete();

        return response()->json(null, 204);
    }
}
