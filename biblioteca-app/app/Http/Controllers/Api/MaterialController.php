<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MaterialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Material::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'leccion_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'tipo' => ['sometimes', 'string'],
            'archivo' => ['sometimes', 'nullable'],
            'url' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
        ]);

        $material = Material::create($validated);

        return response()->json($material, 201);
    }

    public function show(Material $material): JsonResponse
    {
        return response()->json($material);
    }

    public function update(Request $request, Material $material): JsonResponse
    {
        $validated = $request->validate([
            'leccion_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'tipo' => ['sometimes', 'string'],
            'archivo' => ['sometimes', 'nullable'],
            'url' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
        ]);

        $material->update($validated);

        return response()->json($material);
    }

    public function destroy(Material $material): JsonResponse
    {
        $material->delete();

        return response()->json(null, 204);
    }
}
