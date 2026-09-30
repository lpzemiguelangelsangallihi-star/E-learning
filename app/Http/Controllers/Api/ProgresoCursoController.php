<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProgresoCurso;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProgresoCursoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(ProgresoCurso::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'curso_id' => ['sometimes', 'integer'],
            'estudiante_id' => ['sometimes', 'integer'],
            'porcentaje' => ['sometimes', 'numeric'],
            'lecciones_completadas' => ['sometimes', 'nullable'],
            'estado' => ['sometimes', 'string'],
            'fecha_inicio' => ['sometimes', 'date'],
            'fecha_finalizacion' => ['sometimes', 'date'],
        ]);

        $progresoCurso = ProgresoCurso::create($validated);

        return response()->json($progresoCurso, 201);
    }

    public function show(ProgresoCurso $progresoCurso): JsonResponse
    {
        return response()->json($progresoCurso);
    }

    public function update(Request $request, ProgresoCurso $progresoCurso): JsonResponse
    {
        $validated = $request->validate([
            'curso_id' => ['sometimes', 'integer'],
            'estudiante_id' => ['sometimes', 'integer'],
            'porcentaje' => ['sometimes', 'numeric'],
            'lecciones_completadas' => ['sometimes', 'nullable'],
            'estado' => ['sometimes', 'string'],
            'fecha_inicio' => ['sometimes', 'date'],
            'fecha_finalizacion' => ['sometimes', 'date'],
        ]);

        $progresoCurso->update($validated);

        return response()->json($progresoCurso);
    }

    public function destroy(ProgresoCurso $progresoCurso): JsonResponse
    {
        $progresoCurso->delete();

        return response()->json(null, 204);
    }
}
