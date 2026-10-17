<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    public function index()
    {
        $calificaciones = Calificacion::query()
            ->with('curso', 'tarea', 'examen')
            ->where('estudiante_id', Auth::id())
            ->orderByDesc('fecha')
            ->get();

        // Agrupadas por curso, con su promedio.
        $porCurso = $calificaciones
            ->groupBy('curso_id')
            ->map(fn ($items) => [
                'curso' => $items->first()->curso,
                'promedio' => round($items->avg('calificacion'), 1),
                'items' => $items,
            ])
            ->sortBy(fn ($grupo) => $grupo['curso']?->titulo)
            ->values();

        $promedioGeneral = $calificaciones->isNotEmpty()
            ? round($calificaciones->avg('calificacion'), 1)
            : null;

        return view('student.grades.index', compact(
            'porCurso',
            'promedioGeneral',
            'calificaciones'
        ));
    }
}
