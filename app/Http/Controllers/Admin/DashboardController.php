<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [

            'users' =>
                User::count(),

            'students' =>
                User::whereHas(
                    'rol',
                    function ($query) {

                        $query->where(
                            'nombre',
                            'estudiante'
                        );

                    }
                )->count(),

            'teachers' =>
                User::whereHas(
                    'rol',
                    function ($query) {

                        $query->where(
                            'nombre',
                            'profesor'
                        );

                    }
                )->count(),

            'courses' =>
                DB::table('cursos')->count(),

            'subjects' =>
                DB::table('materias')->count(),

            'evaluations' =>
                DB::table('examenes')->count(),
        ];


        $recentUsers = User::with('rol')
            ->orderByDesc('creado_en')
            ->take(5)
            ->get();


        $recentCourses = DB::table('cursos as c')

            ->leftJoin(
                'users as p',
                'p.id',
                '=',
                'c.profesor_id'
            )

            ->leftJoin(
                'materias as m',
                'm.id',
                '=',
                'c.materia_id'
            )

            ->select([
                'c.id',
                'c.titulo',
                'c.estado',

                'm.nombre as materia',

                'p.nombre as profesor_nombre',

                'p.apellido_paterno as profesor_apellido',
            ])

            ->orderByDesc('c.id')
            ->limit(5)

            ->get();


        return view(
            'admin.dashboard',
            compact(
                'stats',
                'recentUsers',
                'recentCourses',
            )
        );
    }
}