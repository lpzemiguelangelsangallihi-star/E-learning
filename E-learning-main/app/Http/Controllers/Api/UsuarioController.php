<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UsuarioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Usuario::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rol_id' => ['sometimes', 'integer'],
            'nombre' => ['sometimes', 'string'],
            'apellido' => ['sometimes', 'nullable'],
            'correo' => ['sometimes', 'nullable'],
            'contrasena' => ['sometimes', 'nullable'],
            'foto_perfil' => ['sometimes', 'nullable'],
            'estado' => ['sometimes', 'string'],
        ]);

        $usuario = Usuario::create($validated);

        return response()->json($usuario, 201);
    }

    public function show(Usuario $usuario): JsonResponse
    {
        return response()->json($usuario);
    }

    public function update(Request $request, Usuario $usuario): JsonResponse
    {
        $validated = $request->validate([
            'rol_id' => ['sometimes', 'integer'],
            'nombre' => ['sometimes', 'string'],
            'apellido' => ['sometimes', 'nullable'],
            'correo' => ['sometimes', 'nullable'],
            'contrasena' => ['sometimes', 'nullable'],
            'foto_perfil' => ['sometimes', 'nullable'],
            'estado' => ['sometimes', 'string'],
        ]);

        $usuario->update($validated);

        return response()->json($usuario);
    }

    public function destroy(Usuario $usuario): JsonResponse
    {
        $usuario->delete();

        return response()->json(null, 204);
    }
}
