<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TareaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Tarea::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'leccion_id' => ['sometimes', 'integer'],
            'profesor_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'instrucciones' => ['sometimes', 'nullable'],
            'fecha_limite' => ['sometimes', 'date'],
            'puntaje_maximo' => ['sometimes', 'nullable'],
            'archivo' => ['sometimes', 'nullable'],
            'estado' => ['sometimes', 'string'],
        ]);

        $tarea = Tarea::create($validated);

        return response()->json($tarea, 201);
    }

    public function show(Tarea $tarea): JsonResponse
    {
        return response()->json($tarea);
    }

    public function update(Request $request, Tarea $tarea): JsonResponse
    {
        $validated = $request->validate([
            'leccion_id' => ['sometimes', 'integer'],
            'profesor_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'instrucciones' => ['sometimes', 'nullable'],
            'fecha_limite' => ['sometimes', 'date'],
            'puntaje_maximo' => ['sometimes', 'nullable'],
            'archivo' => ['sometimes', 'nullable'],
            'estado' => ['sometimes', 'string'],
        ]);

        $tarea->update($validated);

        return response()->json($tarea);
    }

    public function destroy(Tarea $tarea): JsonResponse
    {
        $tarea->delete();

        return response()->json(null, 204);
    }
}
