<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificacionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Notificacion::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'usuario_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'mensaje' => ['sometimes', 'string'],
            'tipo' => ['sometimes', 'string'],
            'leida' => ['sometimes', 'nullable'],
        ]);

        $notificacion = Notificacion::create($validated);

        return response()->json($notificacion, 201);
    }

    public function show(Notificacion $notificacion): JsonResponse
    {
        return response()->json($notificacion);
    }

    public function update(Request $request, Notificacion $notificacion): JsonResponse
    {
        $validated = $request->validate([
            'usuario_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'mensaje' => ['sometimes', 'string'],
            'tipo' => ['sometimes', 'string'],
            'leida' => ['sometimes', 'nullable'],
        ]);

        $notificacion->update($validated);

        return response()->json($notificacion);
    }

    public function destroy(Notificacion $notificacion): JsonResponse
    {
        $notificacion->delete();

        return response()->json(null, 204);
    }
}
