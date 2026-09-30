<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entrega;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EntregaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Entrega::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tarea_id' => ['sometimes', 'integer'],
            'estudiante_id' => ['sometimes', 'integer'],
            'archivo' => ['sometimes', 'nullable'],
            'respuesta' => ['sometimes', 'string'],
            'fecha_entrega' => ['sometimes', 'date'],
            'estado' => ['sometimes', 'string'],
            'calificacion' => ['sometimes', 'numeric'],
            'retroalimentacion' => ['sometimes', 'nullable'],
        ]);

        $entrega = Entrega::create($validated);

        return response()->json($entrega, 201);
    }

    public function show(Entrega $entrega): JsonResponse
    {
        return response()->json($entrega);
    }

    public function update(Request $request, Entrega $entrega): JsonResponse
    {
        $validated = $request->validate([
            'tarea_id' => ['sometimes', 'integer'],
            'estudiante_id' => ['sometimes', 'integer'],
            'archivo' => ['sometimes', 'nullable'],
            'respuesta' => ['sometimes', 'string'],
            'fecha_entrega' => ['sometimes', 'date'],
            'estado' => ['sometimes', 'string'],
            'calificacion' => ['sometimes', 'numeric'],
            'retroalimentacion' => ['sometimes', 'nullable'],
        ]);

        $entrega->update($validated);

        return response()->json($entrega);
    }

    public function destroy(Entrega $entrega): JsonResponse
    {
        $entrega->delete();

        return response()->json(null, 204);
    }
}
