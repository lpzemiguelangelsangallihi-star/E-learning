<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\bitacoras as Bitacora;
use Illuminate\Http\Request;

class BitacoraController extends Controller
{
    public function index(Request $request)
    {
        $search = trim(
            $request->string('search')->toString()
        );

        $registros = Bitacora::query()
            ->with(['usuario', 'rol'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('accion', 'like', "%{$search}%")
                        ->orWhere('usuario_nombre', 'like', "%{$search}%")
                        ->orWhereHas('usuario', function ($usuarioQuery) use ($search) {
                            $usuarioQuery
                                ->where('nombre', 'like', "%{$search}%")
                                ->orWhere('apellido_paterno', 'like', "%{$search}%")
                                ->orWhere('apellido_materno', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view(
            'admin.bitacora.index',
            compact('registros', 'search')
        );
    }
}
