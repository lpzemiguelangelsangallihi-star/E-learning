<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $profesorId = Auth::id();


        $stats = [
    'courses' => Curso::where(
        'profesor_id',
        $profesorId
    )->count(),

    'published' => Curso::where(
        'profesor_id',
        $profesorId
    )
        ->where('estado', 'publicado')
        ->count(),

    'students' => DB::table('inscripciones as i')
        ->join(
            'cursos as c',
            'c.id',
            '=',
            'i.curso_id'
        )
        ->where(
            'c.profesor_id',
            $profesorId
        )
        ->where(
            'i.estado',
            'activo'
        )
        ->distinct()
        ->count('i.estudiante_id'),

    'evaluations' => 0,
];


        $recentCourses = Curso::query()
            ->with('materia')
            ->where(
                'profesor_id',
                $profesorId
            )
            ->orderByDesc('creado_en')
            ->take(5)
            ->get();


        return view(
            'professor.dashboard',
            compact(
                'stats',
                'recentCourses'
            )
        );
    }
}