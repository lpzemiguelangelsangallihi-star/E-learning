<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Racha;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RachaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Racha::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'estudiante_id' => ['sometimes', 'integer'],
            'racha_actual' => ['sometimes', 'nullable'],
            'mejor_racha' => ['sometimes', 'nullable'],
            'ultima_fecha' => ['sometimes', 'nullable'],
        ]);

        $racha = Racha::create($validated);

        return response()->json($racha, 201);
    }

    public function show(Racha $racha): JsonResponse
    {
        return response()->json($racha);
    }

    public function update(Request $request, Racha $racha): JsonResponse
    {
        $validated = $request->validate([
            'estudiante_id' => ['sometimes', 'integer'],
            'racha_actual' => ['sometimes', 'nullable'],
            'mejor_racha' => ['sometimes', 'nullable'],
            'ultima_fecha' => ['sometimes', 'nullable'],
        ]);

        $racha->update($validated);

        return response()->json($racha);
    }

    public function destroy(Racha $racha): JsonResponse
    {
        $racha->delete();

        return response()->json(null, 204);
    }
}
