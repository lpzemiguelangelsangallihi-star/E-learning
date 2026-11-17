<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        $profesorId = Auth::id();
        $filters = $request->validate([
            'curso_id' => ['nullable', 'integer'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);
        $cursoId = $filters['curso_id'] ?? null;
        $desde = $filters['desde'] ?? null;
        $hasta = $filters['hasta'] ?? null;

        $courses = DB::table('cursos')->where('profesor_id', $profesorId)->orderBy('titulo')->get(['id', 'titulo']);
        abort_if($cursoId && !$courses->contains('id', (int) $cursoId), 404);

        $cursosBase = fn () => DB::table('cursos')->where('profesor_id', $profesorId)->when($cursoId, fn ($q) => $q->where('id', $cursoId));
        $inscripcionesBase = fn () => DB::table('inscripciones as i')->join('cursos as c', 'c.id', '=', 'i.curso_id')
            ->where('c.profesor_id', $profesorId)->when($cursoId, fn ($q) => $q->where('c.id', $cursoId))
            ->when($desde, fn ($q) => $q->whereDate('i.fecha_inscripcion', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('i.fecha_inscripcion', '<=', $hasta));

        $stats = [
            'courses' => $cursosBase()->count(),
            'published' => $cursosBase()->where('estado', 'publicado')->count(),
            'students' => $inscripcionesBase()->where('i.estado', 'activo')->distinct()->count('i.estudiante_id'),
            'enrollments' => $inscripcionesBase()->count(),
            'finished' => $inscripcionesBase()->where('i.estado', 'finalizado')->count(),
            'evaluations' => DB::table('examenes')->where('profesor_id', $profesorId)->count(),
        ];

        $progressBase = fn () => DB::table('progreso_cursos as p')->join('cursos as c', 'c.id', '=', 'p.curso_id')
            ->where('c.profesor_id', $profesorId)->when($cursoId, fn ($q) => $q->where('c.id', $cursoId));
        $stats['averageProgress'] = round((float) ($progressBase()->avg('p.porcentaje') ?? 0), 1);
        $stats['completedStudents'] = $progressBase()->where('p.estado', 'completado')->count();
        $stats['inProgressStudents'] = $progressBase()->where('p.estado', 'en_progreso')->count();
        $stats['notStartedStudents'] = $progressBase()->where('p.estado', 'no_iniciado')->count();

        $studentsByCourse = DB::table('cursos as c')
            ->leftJoin('inscripciones as i', function ($join) use ($desde, $hasta) {
                $join->on('i.curso_id', '=', 'c.id')->where('i.estado', '=', 'activo')
                    ->when($desde, fn ($q) => $q->whereDate('i.fecha_inscripcion', '>=', $desde))
                    ->when($hasta, fn ($q) => $q->whereDate('i.fecha_inscripcion', '<=', $hasta));
            })
            ->leftJoin('progreso_cursos as p', function ($join) {
                $join->on('p.curso_id', '=', 'c.id');
            })
            ->where('c.profesor_id', $profesorId)->when($cursoId, fn ($q) => $q->where('c.id', $cursoId))
            ->select('c.id', 'c.ulid', 'c.titulo', DB::raw('COUNT(DISTINCT i.estudiante_id) as total'), DB::raw('ROUND(COALESCE(AVG(p.porcentaje), 0), 1) as progreso'), DB::raw("COUNT(DISTINCT CASE WHEN p.estado = 'completado' THEN p.estudiante_id END) as completados"))
            ->groupBy('c.id', 'c.ulid', 'c.titulo')->orderByDesc('total')->get();

        $coursesByStatus = $cursosBase()->select('estado', DB::raw('COUNT(*) as total'))->groupBy('estado')->pluck('total', 'estado');
        $coursesBySubject = DB::table('cursos as c')->leftJoin('materias as m', 'm.id', '=', 'c.materia_id')
            ->where('c.profesor_id', $profesorId)->when($cursoId, fn ($q) => $q->where('c.id', $cursoId))
            ->select(DB::raw("COALESCE(m.nombre, 'Sin materia') as nombre"), DB::raw('COUNT(c.id) as total'))
            ->groupBy('m.id', 'm.nombre')->orderByDesc('total')->get();

        $results = DB::table('intentos_examen as i')
            ->join('examenes as e', 'e.id', '=', 'i.examen_id')
            ->join('lecciones as l', 'l.id', '=', 'e.leccion_id')
            ->join('modulos as m', 'm.id', '=', 'l.modulo_id')
            ->join('cursos as c', 'c.id', '=', 'm.curso_id')
            ->where('c.profesor_id', $profesorId)->where('i.estado', 'finalizado')
            ->when($cursoId, fn ($q) => $q->where('c.id', $cursoId))
            ->when($desde, fn ($q) => $q->whereDate('i.fecha_fin', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('i.fecha_fin', '<=', $hasta))
            ->select('c.id', 'c.titulo', DB::raw('COUNT(i.id) as intentos'), DB::raw('ROUND(AVG(i.calificacion / NULLIF(e.puntaje_maximo, 0) * 100), 1) as promedio'))
            ->groupBy('c.id', 'c.titulo')->orderBy('c.titulo')->get();

        $activity = DB::table('bitacoras as b')->join('users as u', 'u.id', '=', 'b.usuario_id')
            ->where('b.visible', true)
            ->whereExists(function ($query) use ($profesorId, $cursoId) {
                $query->selectRaw('1')->from('inscripciones as i')
                    ->join('cursos as c', 'c.id', '=', 'i.curso_id')
                    ->whereColumn('i.estudiante_id', 'u.id')
                    ->where('c.profesor_id', $profesorId)
                    ->when($cursoId, fn ($q) => $q->where('c.id', $cursoId));
            })
            ->when($desde, fn ($q) => $q->whereDate('b.fecha', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('b.fecha', '<=', $hasta))
            ->orderByDesc('b.fecha')->limit(8)->get(['b.accion', 'b.fecha', 'u.nombre', 'u.apellido_paterno']);

        return view('professor.statistics.index', compact(
            'stats', 'studentsByCourse', 'coursesByStatus', 'coursesBySubject',
            'courses', 'cursoId', 'desde', 'hasta', 'results', 'activity'
        ));
    }
}
