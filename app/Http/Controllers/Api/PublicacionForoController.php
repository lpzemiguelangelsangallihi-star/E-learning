<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicacionForo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PublicacionForoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(PublicacionForo::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'foro_id' => ['sometimes', 'integer'],
            'usuario_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'contenido' => ['sometimes', 'string'],
        ]);

        $publicacionForo = PublicacionForo::create($validated);

        return response()->json($publicacionForo, 201);
    }

    public function show(PublicacionForo $publicacionForo): JsonResponse
    {
        return response()->json($publicacionForo);
    }

    public function update(Request $request, PublicacionForo $publicacionForo): JsonResponse
    {
        $validated = $request->validate([
            'foro_id' => ['sometimes', 'integer'],
            'usuario_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'contenido' => ['sometimes', 'string'],
        ]);

        $publicacionForo->update($validated);

        return response()->json($publicacionForo);
    }

    public function destroy(PublicacionForo $publicacionForo): JsonResponse
    {
        $publicacionForo->delete();

        return response()->json(null, 204);
    }
}
