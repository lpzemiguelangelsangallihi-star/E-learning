<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Nivel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NivelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Nivel::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string'],
            'experiencia_minima' => ['sometimes', 'nullable'],
            'experiencia_maxima' => ['sometimes', 'nullable'],
            'descripcion' => ['sometimes', 'string'],
        ]);

        $nivel = Nivel::create($validated);

        return response()->json($nivel, 201);
    }

    public function show(Nivel $nivel): JsonResponse
    {
        return response()->json($nivel);
    }

    public function update(Request $request, Nivel $nivel): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string'],
            'experiencia_minima' => ['sometimes', 'nullable'],
            'experiencia_maxima' => ['sometimes', 'nullable'],
            'descripcion' => ['sometimes', 'string'],
        ]);

        $nivel->update($validated);

        return response()->json($nivel);
    }

    public function destroy(Nivel $nivel): JsonResponse
    {
        $nivel->delete();

        return response()->json(null, 204);
    }
}
