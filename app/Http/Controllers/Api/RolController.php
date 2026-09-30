<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RolController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Rol::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
        ]);

        $rol = Rol::create($validated);

        return response()->json($rol, 201);
    }

    public function show(Rol $rol): JsonResponse
    {
        return response()->json($rol);
    }

    public function update(Request $request, Rol $rol): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
        ]);

        $rol->update($validated);

        return response()->json($rol);
    }

    public function destroy(Rol $rol): JsonResponse
    {
        $rol->delete();

        return response()->json(null, 204);
    }
}
