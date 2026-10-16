<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Categoria::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
            'estado' => ['sometimes', 'string'],
        ]);

        $categoria = Categoria::create($validated);

        return response()->json($categoria, 201);
    }

    public function show(Categoria $categoria): JsonResponse
    {
        return response()->json($categoria);
    }

    public function update(Request $request, Categoria $categoria): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
            'estado' => ['sometimes', 'string'],
        ]);

        $categoria->update($validated);

        return response()->json($categoria);
    }

    public function destroy(Categoria $categoria): JsonResponse
    {
        $categoria->delete();

        return response()->json(null, 204);
    }
}
