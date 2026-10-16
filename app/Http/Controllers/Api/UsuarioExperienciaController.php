<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsuarioExperiencia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UsuarioExperienciaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(UsuarioExperiencia::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'estudiante_id' => ['sometimes', 'integer'],
            'experiencia_total' => ['sometimes', 'nullable'],
        ]);

        $usuarioExperiencia = UsuarioExperiencia::create($validated);

        return response()->json($usuarioExperiencia, 201);
    }

    public function show(UsuarioExperiencia $usuarioExperiencia): JsonResponse
    {
        return response()->json($usuarioExperiencia);
    }

    public function update(Request $request, UsuarioExperiencia $usuarioExperiencia): JsonResponse
    {
        $validated = $request->validate([
            'estudiante_id' => ['sometimes', 'integer'],
            'experiencia_total' => ['sometimes', 'nullable'],
        ]);

        $usuarioExperiencia->update($validated);

        return response()->json($usuarioExperiencia);
    }

    public function destroy(UsuarioExperiencia $usuarioExperiencia): JsonResponse
    {
        $usuarioExperiencia->delete();

        return response()->json(null, 204);
    }
}
