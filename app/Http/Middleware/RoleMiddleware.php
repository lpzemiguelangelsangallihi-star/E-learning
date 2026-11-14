<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /** Panel al que pertenece cada rol. */
    private const PANELES = [
        'administrador' => 'admin.dashboard',
        'profesor' => 'professor.dashboard',
        'estudiante' => 'student.dashboard',
    ];

    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {

        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $rol = $user->rol?->nombre;

        if (! $rol || ! in_array($rol, $roles, true)) {

            $panel = self::PANELES[$rol] ?? null;

            /*
             * En navegación normal devolvemos al panel del usuario con un
             * aviso, en lugar de mostrar una pantalla 403 sin contexto.
             * Las peticiones JSON/API sí reciben 403.
             */
            if ($panel && $request->isMethod('GET') && ! $request->expectsJson()) {
                return redirect()
                    ->route($panel)
                    ->with('error', 'No tienes permiso para acceder a esa sección.');
            }

            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
