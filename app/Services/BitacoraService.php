<?php

namespace App\Services;

use App\Models\bitacoras as Bitacora;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BitacoraService
{
    public static function registrar(string $accion): void
    {
        $usuario = Auth::user();

        Bitacora::create([
            'accion' => Str::limit($accion, 255, ''),
            'visible' => true,
            'usuario_id' => $usuario?->getAuthIdentifier(),
            'usuario_nombre' => $usuario?->nombre_completo,
            'rol_id' => $usuario?->rol_id,
        ]);
    }
}
