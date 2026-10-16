<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CursoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Curso::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'categoria_id' => ['sometimes', 'integer'],
            'materia_id' => ['sometimes', 'integer'],
            'profesor_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
            'objetivos' => ['sometimes', 'string'],
            'imagen' => ['sometimes', 'string'],
            'nivel' => ['sometimes', 'nullable'],
            'duracion' => ['sometimes', 'numeric'],
            'tipo' => ['sometimes', 'string'],
            'precio' => ['sometimes', 'numeric'],
            'estado' => ['sometimes', 'string'],
        ]);

        $curso = Curso::create($validated);

        return response()->json($curso, 201);
    }

    public function show(Curso $curso): JsonResponse
    {
        return response()->json($curso);
    }

    public function update(Request $request, Curso $curso): JsonResponse
    {
        $validated = $request->validate([
            'categoria_id' => ['sometimes', 'integer'],
            'materia_id' => ['sometimes', 'integer'],
            'profesor_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
            'objetivos' => ['sometimes', 'string'],
            'imagen' => ['sometimes', 'string'],
            'nivel' => ['sometimes', 'nullable'],
            'duracion' => ['sometimes', 'numeric'],
            'tipo' => ['sometimes', 'string'],
            'precio' => ['sometimes', 'numeric'],
            'estado' => ['sometimes', 'string'],
        ]);

        $curso->update($validated);

        return response()->json($curso);
    }

    public function destroy(Curso $curso): JsonResponse
    {
        $curso->delete();

        return response()->json(null, 204);
    }
}
