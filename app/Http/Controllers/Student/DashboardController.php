<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use App\Models\Inscripcion;
use App\Models\ProgresoCurso;
use App\Services\StudentEvaluationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(StudentEvaluationService $evaluaciones)
    {
        $estudianteId = Auth::id();

        $inscripciones = Inscripcion::query()
            ->with('curso.materia', 'curso.profesor')
            ->where('estudiante_id', $estudianteId)
            ->whereIn('estado', ['activo', 'finalizado'])
            ->get();

        $progresos = ProgresoCurso::query()
            ->where('estudiante_id', $estudianteId)
            ->get()
            ->keyBy('curso_id');

        $pendientes = $evaluaciones->pending($estudianteId);

        $promedio = Calificacion::query()
            ->where('estudiante_id', $estudianteId)
            ->avg('calificacion');

        $stats = [
            'activos' => $inscripciones->where('estado', 'activo')->count(),
            'completados' => $progresos->where('estado', 'completado')->count(),
            'pendientes' => $pendientes->count(),
            'promedio' => $promedio !== null ? round($promedio, 1) : null,
        ];

        $continuar = $inscripciones
            ->where('estado', 'activo')
            ->filter(
                fn ($inscripcion) => $inscripcion->curso?->estado === 'publicado'
            )
            ->sortByDesc(
                fn ($inscripcion) =>
                    (float) ($progresos[$inscripcion->curso_id]->porcentaje ?? 0)
            )
            ->take(3)
            ->values();

        $racha = DB::table('rachas')
            ->where('estudiante_id', $estudianteId)
            ->first();

        $experiencia = (int) DB::table('usuario_experiencia')
            ->where('estudiante_id', $estudianteId)
            ->value('experiencia_total');

        // Tareas con fecha límite: alimentan el calendario y "Esta semana".
        $conFecha = $pendientes
            ->filter(fn (array $item) => $item['tipo'] === 'tarea' && $item['fecha_limite'] !== null)
            ->values();

        $eventos = $conFecha->map(fn (array $item) => [
            'fecha' => $item['fecha_limite']->format('Y-m-d'),
            'hora' => $item['fecha_limite']->format('H:i'),
            'titulo' => $item['titulo'],
            'curso' => $item['curso']?->titulo,
            'vencida' => $item['vencida'],
            'url' => route('student.evaluations.task.show', $item['ulid']),
        ])->all();

        $semana = $conFecha
            ->filter(fn (array $item) => $item['fecha_limite']->lte(now()->addDays(7)->endOfDay()))
            ->values();

        return view('student.dashboard', [
            'eventos' => $eventos,
            'semana' => $semana,
            'stats' => $stats,
            'continuar' => $continuar,
            'progresos' => $progresos,
            'pendientes' => $pendientes->take(5),
            'racha' => (int) ($racha->racha_actual ?? 0),
            'experiencia' => $experiencia,
        ]);
    }
}