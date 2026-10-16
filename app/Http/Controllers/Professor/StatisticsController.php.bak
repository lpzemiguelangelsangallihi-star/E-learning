<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index()
    {
        $profesorId = Auth::id();

        $cursosBase = fn () => DB::table('cursos')
            ->where('profesor_id', $profesorId);

        $inscripcionesBase = fn () => DB::table('inscripciones as i')
            ->join('cursos as c', 'c.id', '=', 'i.curso_id')
            ->where('c.profesor_id', $profesorId);

        $stats = [
            'courses' => $cursosBase()->count(),

            'published' => $cursosBase()
                ->where('estado', 'publicado')
                ->count(),

            'students' => $inscripcionesBase()
                ->where('i.estado', 'activo')
                ->distinct()
                ->count('i.estudiante_id'),

            'enrollments' => $inscripcionesBase()->count(),

            'finished' => $inscripcionesBase()
                ->where('i.estado', 'finalizado')
                ->count(),

            'evaluations' => DB::table('examenes')
                ->where('profesor_id', $profesorId)
                ->count(),
        ];

        // Estudiantes activos por curso.
        $studentsByCourse = DB::table('cursos as c')
            ->leftJoin('inscripciones as i', function ($join) {
                $join->on('i.curso_id', '=', 'c.id')
                    ->where('i.estado', '=', 'activo');
            })
            ->where('c.profesor_id', $profesorId)
            ->select('c.id', 'c.titulo', DB::raw('COUNT(i.id) as total'))
            ->groupBy('c.id', 'c.titulo')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // Cursos por estado.
        $coursesByStatus = $cursosBase()
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        // Cursos por materia.
        $coursesBySubject = DB::table('cursos as c')
            ->leftJoin('materias as m', 'm.id', '=', 'c.materia_id')
            ->where('c.profesor_id', $profesorId)
            ->select(
                DB::raw("COALESCE(m.nombre, 'Sin materia') as nombre"),
                DB::raw('COUNT(c.id) as total')
            )
            ->groupBy('m.id', 'm.nombre')
            ->orderByDesc('total')
            ->get();

        // Cursos por tipo (gratuito / pago).
        $coursesByType = $cursosBase()
            ->select('tipo', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        return view(
            'professor.statistics.index',
            compact(
                'stats',
                'studentsByCourse',
                'coursesByStatus',
                'coursesBySubject',
                'coursesByType'
            )
        );
    }
}
