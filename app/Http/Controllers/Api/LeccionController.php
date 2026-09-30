<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leccion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LeccionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Leccion::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'modulo_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
            'contenido' => ['sometimes', 'string'],
            'orden' => ['sometimes', 'numeric'],
            'estado' => ['sometimes', 'string'],
        ]);

        $leccion = Leccion::create($validated);

        return response()->json($leccion, 201);
    }

    public function show(Leccion $leccion): JsonResponse
    {
        return response()->json($leccion);
    }

    public function update(Request $request, Leccion $leccion): JsonResponse
    {
        $validated = $request->validate([
            'modulo_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
            'contenido' => ['sometimes', 'string'],
            'orden' => ['sometimes', 'numeric'],
            'estado' => ['sometimes', 'string'],
        ]);

        $leccion->update($validated);

        return response()->json($leccion);
    }

    public function destroy(Leccion $leccion): JsonResponse
    {
        $leccion->delete();

        return response()->json(null, 204);
    }
}
