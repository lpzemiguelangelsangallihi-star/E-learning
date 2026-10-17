<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Inscripcion;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    public function index()
    {
        $estudianteId = Auth::id();

        $inscritos = Inscripcion::cursoIdsDe($estudianteId);

        $misCursos = Curso::query()
            ->with('materia')
            ->whereIn('id', $inscritos)
            ->get();

        $materiaIds = $misCursos->pluck('materia_id')->filter()->unique()->all();

        $cursos = Curso::query()
            ->with('materia', 'profesor')
            ->withCount([
                'inscripciones as estudiantes_count' =>
                    fn ($query) => $query->where('estado', 'activo'),
            ])
            ->where('estado', 'publicado')
            ->whereNotIn('id', $inscritos)
            ->get()
            ->map(function (Curso $curso) use ($materiaIds) {

                $mismaMateria = in_array($curso->materia_id, $materiaIds, true);

                $curso->puntaje_recomendacion = $mismaMateria ? 1 : 0;

                $curso->motivo = match (true) {
                    $mismaMateria => 'Porque estudias '
                        . ($curso->materia?->nombre ?? 'esta materia'),
                    default => 'Popular entre los estudiantes',
                };

                return $curso;
            })
            ->sortBy([
                ['puntaje_recomendacion', 'desc'],
                ['estudiantes_count', 'desc'],
            ])
            ->take(9)
            ->values();

        return view('student.recommendations.index', compact('cursos'));
    }
}
