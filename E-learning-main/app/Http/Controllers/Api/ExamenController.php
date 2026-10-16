<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Examen;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExamenController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Examen::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'leccion_id' => ['sometimes', 'integer'],
            'profesor_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
            'tiempo_limite' => ['sometimes', 'nullable'],
            'intentos_permitidos' => ['sometimes', 'nullable'],
            'puntaje_maximo' => ['sometimes', 'nullable'],
            'estado' => ['sometimes', 'string'],
        ]);

        $examen = Examen::create($validated);

        return response()->json($examen, 201);
    }

    public function show(Examen $examen): JsonResponse
    {
        return response()->json($examen);
    }

    public function update(Request $request, Examen $examen): JsonResponse
    {
        $validated = $request->validate([
            'leccion_id' => ['sometimes', 'integer'],
            'profesor_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
            'tiempo_limite' => ['sometimes', 'nullable'],
            'intentos_permitidos' => ['sometimes', 'nullable'],
            'puntaje_maximo' => ['sometimes', 'nullable'],
            'estado' => ['sometimes', 'string'],
        ]);

        $examen->update($validated);

        return response()->json($examen);
    }

    public function destroy(Examen $examen): JsonResponse
    {
        $examen->delete();

        return response()->json(null, 204);
    }
}
