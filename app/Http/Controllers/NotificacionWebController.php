<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Services\Notificaciones\RecordatorioTareasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificacionWebController extends Controller
{
    /** Bandeja exclusiva del estudiante y avisos de tareas próximas a vencer. */
    public function index(Request $request, RecordatorioTareasService $recordatorios): View
    {
        abort_unless($request->user()->rol?->nombre === 'estudiante', 403);

        $recordatorios->generar((int) $request->user()->id);

        $filtro = $request->query('filtro', 'todas');
        if (! in_array($filtro, ['todas', 'no_leidas', 'leidas'], true)) {
            $filtro = 'todas';
        }

        $consulta = Notificacion::query()
            ->with('tarea')
            ->where('usuario_id', $request->user()->getAuthIdentifier())
            ->orderByDesc('creado_en');

        if ($filtro === 'no_leidas') {
            $consulta->where('leida', false);
        } elseif ($filtro === 'leidas') {
            $consulta->where('leida', true);
        }

        return view('notificaciones.index', [
            'notificaciones' => $consulta->paginate(10)->withQueryString(),
            'filtro' => $filtro,
            'noLeidas' => Notificacion::query()
                ->where('usuario_id', $request->user()->getAuthIdentifier())
                ->where('leida', false)
                ->count(),
        ]);
    }

    /** Marca como leída solo una notificación del estudiante autenticado. */
    public function marcarLeida(Request $request, int $notificacion): RedirectResponse
    {
        abort_unless($request->user()->rol?->nombre === 'estudiante', 403);

        $registro = Notificacion::query()
            ->whereKey($notificacion)
            ->where('usuario_id', $request->user()->getAuthIdentifier())
            ->firstOrFail();

        $registro->update(['leida' => true]);

        return back()->with('success', 'Notificación marcada como leída.');
    }

}
