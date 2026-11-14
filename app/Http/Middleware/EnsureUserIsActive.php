<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Invalida las sesiones y tokens de usuarios desactivados.
 *
 * El login ya rechaza a los usuarios con estado distinto de "activo", pero
 * una sesión abierta antes de la desactivación seguiría siendo válida. Este
 * middleware cierra esa brecha en cada petición autenticada.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->estado === 'activo') {
            return $next($request);
        }

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson()) {
            abort(403, 'Tu cuenta está inactiva.');
        }

        return redirect()
            ->route('login')
            ->withErrors([
                'email' => 'Tu cuenta está inactiva. Contacta al administrador.',
            ]);
    }
}
