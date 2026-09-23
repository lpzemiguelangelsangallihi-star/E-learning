<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ComentarioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Comentario::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'publicacion_id' => ['sometimes', 'integer'],
            'usuario_id' => ['sometimes', 'integer'],
            'comentario' => ['sometimes', 'string'],
        ]);

        $comentario = Comentario::create($validated);

        return response()->json($comentario, 201);
    }

    public function show(Comentario $comentario): JsonResponse
    {
        return response()->json($comentario);
    }

    public function update(Request $request, Comentario $comentario): JsonResponse
    {
        $validated = $request->validate([
            'publicacion_id' => ['sometimes', 'integer'],
            'usuario_id' => ['sometimes', 'integer'],
            'comentario' => ['sometimes', 'string'],
        ]);

        $comentario->update($validated);

        return response()->json($comentario);
    }

    public function destroy(Comentario $comentario): JsonResponse
    {
        $comentario->delete();

        return response()->json(null, 204);
    }
}
