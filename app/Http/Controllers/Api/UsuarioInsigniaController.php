<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsuarioInsignia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UsuarioInsigniaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(UsuarioInsignia::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'estudiante_id' => ['sometimes', 'integer'],
            'insignia_id' => ['sometimes', 'integer'],
            'fecha_obtencion' => ['sometimes', 'date'],
        ]);

        $usuarioInsignia = UsuarioInsignia::create($validated);

        return response()->json($usuarioInsignia, 201);
    }

    public function show(UsuarioInsignia $usuarioInsignia): JsonResponse
    {
        return response()->json($usuarioInsignia);
    }

    public function update(Request $request, UsuarioInsignia $usuarioInsignia): JsonResponse
    {
        $validated = $request->validate([
            'estudiante_id' => ['sometimes', 'integer'],
            'insignia_id' => ['sometimes', 'integer'],
            'fecha_obtencion' => ['sometimes', 'date'],
        ]);

        $usuarioInsignia->update($validated);

        return response()->json($usuarioInsignia);
    }

    public function destroy(UsuarioInsignia $usuarioInsignia): JsonResponse
    {
        $usuarioInsignia->delete();

        return response()->json(null, 204);
    }
}
