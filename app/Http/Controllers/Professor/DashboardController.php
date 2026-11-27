<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Tarea;
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

    'evaluations' => DB::table('examenes')
        ->where('profesor_id', $profesorId)
        ->count(),
];

        
$progressBase = fn () => DB::table('progreso_cursos as p')
    ->join('cursos as c', 'c.id', '=', 'p.curso_id')
    ->where('c.profesor_id', $profesorId);

$stats['averageProgress'] = round(
    (float) ($progressBase()->avg('p.porcentaje') ?? 0),
    1
);

$stats['completedStudents'] = $progressBase()
    ->where('p.estado', 'completado')
    ->count();



        $recentCourses = Curso::query()
            ->with('materia')
            ->where(
                'profesor_id',
                $profesorId
            )
            ->orderByDesc('creado_en')
            ->take(5)
            ->get();


        // Tareas con entregas que todavía no tienen nota (la más antigua primero).
        $porCalificar = Tarea::query()
            ->with('leccion.modulo.curso')
            ->where('profesor_id', $profesorId)
            ->withCount([
                'entregas as por_calificar_count' => fn ($query) => $query
                    ->whereIn('estado', ['entregada', 'tarde']),
            ])
            ->withMin([
                'entregas as primera_entrega' => fn ($query) => $query
                    ->whereIn('estado', ['entregada', 'tarde']),
            ], 'fecha_entrega')
            ->having('por_calificar_count', '>', 0)
            ->orderBy('primera_entrega')
            ->take(5)
            ->get();

        $totalPorCalificar = DB::table('entregas as e')
            ->join('tareas as t', 't.id', '=', 'e.tarea_id')
            ->where('t.profesor_id', $profesorId)
            ->whereIn('e.estado', ['entregada', 'tarde'])
            ->count();

        // Próximos vencimientos de las tareas publicadas.
        $proximas = Tarea::query()
            ->with('leccion.modulo.curso')
            ->where('profesor_id', $profesorId)
            ->where('estado', 'publicada')
            ->whereNotNull('fecha_limite')
            ->where('fecha_limite', '>=', now())
            ->withCount('entregas as entregadas_count')
            ->orderBy('fecha_limite')
            ->take(5)
            ->get();

        $matriculados = DB::table('inscripciones')
            ->where('estado', 'activo')
            ->whereIn('curso_id', $proximas->map(fn ($t) => $t->leccion?->modulo?->curso_id)->filter()->unique())
            ->selectRaw('curso_id, COUNT(*) as total')
            ->groupBy('curso_id')
            ->pluck('total', 'curso_id');

        return view(
            'professor.dashboard',
            compact(
                'stats',
                'recentCourses',
                'porCalificar',
                'totalPorCalificar',
                'proximas',
                'matriculados'
            )
        );
    }
}