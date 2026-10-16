<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InscripcionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Inscripcion::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'curso_id' => ['sometimes', 'integer'],
            'estudiante_id' => ['sometimes', 'integer'],
            'fecha_inscripcion' => ['sometimes', 'date'],
            'estado' => ['sometimes', 'string'],
        ]);

        $inscripcion = Inscripcion::create($validated);

        return response()->json($inscripcion, 201);
    }

    public function show(Inscripcion $inscripcion): JsonResponse
    {
        return response()->json($inscripcion);
    }

    public function update(Request $request, Inscripcion $inscripcion): JsonResponse
    {
        $validated = $request->validate([
            'curso_id' => ['sometimes', 'integer'],
            'estudiante_id' => ['sometimes', 'integer'],
            'fecha_inscripcion' => ['sometimes', 'date'],
            'estado' => ['sometimes', 'string'],
        ]);

        $inscripcion->update($validated);

        return response()->json($inscripcion);
    }

    public function destroy(Inscripcion $inscripcion): JsonResponse
    {
        $inscripcion->delete();

        return response()->json(null, 204);
    }
}
