<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $desde = $filters['desde'] ?? null;
        $hasta = $filters['hasta'] ?? null;
        $applyDates = static function ($query, string $column) use ($desde, $hasta) {
            return $query
                ->when($desde, fn ($q) => $q->whereDate($column, '>=', $desde))
                ->when($hasta, fn ($q) => $q->whereDate($column, '<=', $hasta));
        };

        $stats = [
            'users' => DB::table('users')->count(),
            'students' => DB::table('users as u')->join('roles as r', 'r.id', '=', 'u.rol_id')->where('r.nombre', 'estudiante')->count(),
            'teachers' => DB::table('users as u')->join('roles as r', 'r.id', '=', 'u.rol_id')->where('r.nombre', 'profesor')->count(),
            'courses' => DB::table('cursos')->count(),
            'activeEnrollments' => $applyDates(DB::table('inscripciones')->where('estado', 'activo'), 'fecha_inscripcion')->count(),
            'evaluations' => $applyDates(DB::table('examenes'), 'creado_en')->count(),
            'newUsers' => $applyDates(DB::table('users'), 'creado_en')->count(),
            'activityEvents' => $applyDates(DB::table('bitacoras')->where('visible', true), 'fecha')->count(),
            'completedProgress' => DB::table('progreso_cursos')->where('estado', 'completado')->count(),
            'averageProgress' => round((float) (DB::table('progreso_cursos')->avg('porcentaje') ?? 0), 1),
        ];

        $usersByRole = DB::table('roles as r')
            ->leftJoin('users as u', 'u.rol_id', '=', 'r.id')
            ->select('r.nombre', DB::raw('COUNT(u.id) as total'))
            ->groupBy('r.id', 'r.nombre')->orderBy('r.nombre')->get();

        $coursesBySubject = DB::table('materias as m')
            ->leftJoin('cursos as c', 'c.materia_id', '=', 'm.id')
            ->select('m.nombre', DB::raw('COUNT(c.id) as total'))
            ->groupBy('m.id', 'm.nombre')->orderBy('m.nombre')->get();

        $coursesByStatus = DB::table('cursos')->select('estado', DB::raw('COUNT(*) as total'))->groupBy('estado')->get();

        $progressByCourse = DB::table('cursos as c')
            ->leftJoin('progreso_cursos as p', 'p.curso_id', '=', 'c.id')
            ->select('c.id', 'c.titulo', DB::raw('COUNT(p.id) as estudiantes'), DB::raw('ROUND(COALESCE(AVG(p.porcentaje), 0), 1) as promedio'), DB::raw("SUM(CASE WHEN p.estado = 'completado' THEN 1 ELSE 0 END) as completados"))
            ->groupBy('c.id', 'c.titulo')->orderByDesc('promedio')->limit(10)->get();

        $recentActivity = $applyDates(DB::table('bitacoras as b')->leftJoin('users as u', 'u.id', '=', 'b.usuario_id'), 'b.fecha')
            ->where('b.visible', true)
            ->orderByDesc('b.fecha')->limit(10)
            ->get(['b.accion', 'b.fecha', 'u.nombre', 'u.apellido_paterno']);

        $totalUsers = max((int) $usersByRole->sum('total'), 1);
        $totalCourses = max((int) $coursesBySubject->sum('total'), 1);

        return view('admin.statistics.index', compact(
            'stats', 'usersByRole', 'coursesBySubject', 'coursesByStatus',
            'progressByCourse', 'recentActivity', 'totalUsers', 'totalCourses', 'desde', 'hasta'
        ));
    }
}
