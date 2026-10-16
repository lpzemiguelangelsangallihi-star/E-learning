<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mensaje;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MensajeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Mensaje::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'remitente_id' => ['sometimes', 'integer'],
            'destinatario_id' => ['sometimes', 'integer'],
            'asunto' => ['sometimes', 'nullable'],
            'mensaje' => ['sometimes', 'string'],
            'leido' => ['sometimes', 'nullable'],
        ]);

        $mensaje = Mensaje::create($validated);

        return response()->json($mensaje, 201);
    }

    public function show(Mensaje $mensaje): JsonResponse
    {
        return response()->json($mensaje);
    }

    public function update(Request $request, Mensaje $mensaje): JsonResponse
    {
        $validated = $request->validate([
            'remitente_id' => ['sometimes', 'integer'],
            'destinatario_id' => ['sometimes', 'integer'],
            'asunto' => ['sometimes', 'nullable'],
            'mensaje' => ['sometimes', 'string'],
            'leido' => ['sometimes', 'nullable'],
        ]);

        $mensaje->update($validated);

        return response()->json($mensaje);
    }

    public function destroy(Mensaje $mensaje): JsonResponse
    {
        $mensaje->delete();

        return response()->json(null, 204);
    }
}
