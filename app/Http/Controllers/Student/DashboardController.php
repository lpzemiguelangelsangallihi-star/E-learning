<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $inscripciones = Inscripcion::query()
            ->with(['curso.materia', 'curso.profesor'])
            ->where('estudiante_id', Auth::id())
            ->where('estado', 'activo')
            ->orderByDesc('fecha_inscripcion')
            ->get()
            ->filter(fn ($inscripcion) => $inscripcion->curso !== null)
            ->values();

        // Cantidad de temas visibles (unidad activa + tema publicado) por curso.
        $temas = [];

        foreach ($inscripciones as $inscripcion) {
            $temas[$inscripcion->curso_id] = $inscripcion->curso
                ->lecciones()
                ->where('modulos.estado', 'activo')
                ->where('lecciones.estado', 'publicado')
                ->count();
        }

        return view('student.dashboard', compact('inscripciones', 'temas'));
    }
}
