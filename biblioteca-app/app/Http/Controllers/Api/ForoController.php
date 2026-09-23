<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Foro;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ForoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Foro::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'curso_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
        ]);

        $foro = Foro::create($validated);

        return response()->json($foro, 201);
    }

    public function show(Foro $foro): JsonResponse
    {
        return response()->json($foro);
    }

    public function update(Request $request, Foro $foro): JsonResponse
    {
        $validated = $request->validate([
            'curso_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
        ]);

        $foro->update($validated);

        return response()->json($foro);
    }

    public function destroy(Foro $foro): JsonResponse
    {
        $foro->delete();

        return response()->json(null, 204);
    }
}
