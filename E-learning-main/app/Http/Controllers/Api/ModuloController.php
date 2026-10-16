<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ModuloController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Modulo::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'curso_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
            'orden' => ['sometimes', 'numeric'],
            'estado' => ['sometimes', 'string'],
        ]);

        $modulo = Modulo::create($validated);

        return response()->json($modulo, 201);
    }

    public function show(Modulo $modulo): JsonResponse
    {
        return response()->json($modulo);
    }

    public function update(Request $request, Modulo $modulo): JsonResponse
    {
        $validated = $request->validate([
            'curso_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
            'orden' => ['sometimes', 'numeric'],
            'estado' => ['sometimes', 'string'],
        ]);

        $modulo->update($validated);

        return response()->json($modulo);
    }

    public function destroy(Modulo $modulo): JsonResponse
    {
        $modulo->delete();

        return response()->json(null, 204);
    }
}
