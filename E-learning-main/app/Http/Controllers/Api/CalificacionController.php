<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CalificacionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Calificacion::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'estudiante_id' => ['sometimes', 'integer'],
            'profesor_id' => ['sometimes', 'integer'],
            'curso_id' => ['sometimes', 'integer'],
            'tarea_id' => ['sometimes', 'integer'],
            'examen_id' => ['sometimes', 'integer'],
            'tipo' => ['sometimes', 'string'],
            'calificacion' => ['sometimes', 'numeric'],
            'retroalimentacion' => ['sometimes', 'nullable'],
            'fecha' => ['sometimes', 'date'],
        ]);

        $calificacion = Calificacion::create($validated);

        return response()->json($calificacion, 201);
    }

    public function show(Calificacion $calificacion): JsonResponse
    {
        return response()->json($calificacion);
    }

    public function update(Request $request, Calificacion $calificacion): JsonResponse
    {
        $validated = $request->validate([
            'estudiante_id' => ['sometimes', 'integer'],
            'profesor_id' => ['sometimes', 'integer'],
            'curso_id' => ['sometimes', 'integer'],
            'tarea_id' => ['sometimes', 'integer'],
            'examen_id' => ['sometimes', 'integer'],
            'tipo' => ['sometimes', 'string'],
            'calificacion' => ['sometimes', 'numeric'],
            'retroalimentacion' => ['sometimes', 'nullable'],
            'fecha' => ['sometimes', 'date'],
        ]);

        $calificacion->update($validated);

        return response()->json($calificacion);
    }

    public function destroy(Calificacion $calificacion): JsonResponse
    {
        $calificacion->delete();

        return response()->json(null, 204);
    }
}
