<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Estadísticas generales
        |--------------------------------------------------------------------------
        */

        $stats = [
            'users' => User::count(),

            'students' => User::whereHas(
                'rol',
                fn ($query) =>
                    $query->where('nombre', 'estudiante')
            )->count(),

            'teachers' => User::whereHas(
                'rol',
                fn ($query) =>
                    $query->where('nombre', 'profesor')
            )->count(),

            'courses' => DB::table('cursos')->count(),

            'activeEnrollments' => DB::table('inscripciones')
                ->where('estado', 'activo')
                ->count(),

            'evaluations' => DB::table('examenes')->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | Usuarios por rol
        |--------------------------------------------------------------------------
        */

        $usersByRole = DB::table('roles as r')
            ->leftJoin(
                'users as u',
                'u.rol_id',
                '=',
                'r.id'
            )
            ->select(
                'r.nombre',
                DB::raw('COUNT(u.id) as total')
            )
            ->groupBy(
                'r.id',
                'r.nombre'
            )
            ->orderBy('r.nombre')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Cursos por materia
        |--------------------------------------------------------------------------
        */

        $coursesBySubject = DB::table('materias as m')
            ->leftJoin(
                'cursos as c',
                'c.materia_id',
                '=',
                'm.id'
            )
            ->select(
                'm.nombre',
                DB::raw('COUNT(c.id) as total')
            )
            ->groupBy(
                'm.id',
                'm.nombre'
            )
            ->orderBy('m.nombre')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Cursos por estado
        |--------------------------------------------------------------------------
        */

        $coursesByStatus = DB::table('cursos')
            ->select(
                'estado',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('estado')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Totales necesarios para calcular porcentajes
        |--------------------------------------------------------------------------
        */

        $totalUsers = max(
            $usersByRole->sum('total'),
            1
        );

        $totalCourses = max(
            $coursesBySubject->sum('total'),
            1
        );


        return view(
            'admin.statistics.index',
            compact(
                'stats',
                'usersByRole',
                'coursesBySubject',
                'coursesByStatus',
                'totalUsers',
                'totalCourses',
            )
        );
    }
}

