<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Insignia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InsigniaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Insignia::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
            'icono' => ['sometimes', 'nullable'],
            'condicion' => ['sometimes', 'nullable'],
        ]);

        $insignia = Insignia::create($validated);

        return response()->json($insignia, 201);
    }

    public function show(Insignia $insignia): JsonResponse
    {
        return response()->json($insignia);
    }

    public function update(Request $request, Insignia $insignia): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
            'icono' => ['sometimes', 'nullable'],
            'condicion' => ['sometimes', 'nullable'],
        ]);

        $insignia->update($validated);

        return response()->json($insignia);
    }

    public function destroy(Insignia $insignia): JsonResponse
    {
        $insignia->delete();

        return response()->json(null, 204);
    }
}
