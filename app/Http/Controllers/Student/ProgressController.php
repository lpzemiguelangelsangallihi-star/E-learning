<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use App\Models\ProgresoCurso;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    public function index()
    {
        $estudianteId = Auth::id();

        $inscripciones = Inscripcion::query()
            ->with('curso.materia')
            ->where('estudiante_id', $estudianteId)
            ->whereIn('estado', ['activo', 'finalizado'])
            ->get();

        $progresos = ProgresoCurso::query()
            ->where('estudiante_id', $estudianteId)
            ->get()
            ->keyBy('curso_id');

        $promedioProgreso = $inscripciones->isNotEmpty()
            ? round(
                $inscripciones->avg(
                    fn ($inscripcion) =>
                        (float) ($progresos[$inscripcion->curso_id]->porcentaje ?? 0)
                ),
                1
            )
            : 0;

        $lecciones = $progresos->sum('lecciones_completadas');

        $experiencia = (int) DB::table('usuario_experiencia')
            ->where('estudiante_id', $estudianteId)
            ->value('experiencia_total');

        $nivel = DB::table('niveles')
            ->where('experiencia_minima', '<=', $experiencia)
            ->orderByDesc('experiencia_minima')
            ->first();

        $siguienteNivel = DB::table('niveles')
            ->where('experiencia_minima', '>', $experiencia)
            ->orderBy('experiencia_minima')
            ->first();

        $racha = DB::table('rachas')
            ->where('estudiante_id', $estudianteId)
            ->first();

        $insignias = DB::table('usuario_insignias as ui')
            ->join('insignias as i', 'i.id', '=', 'ui.insignia_id')
            ->where('ui.estudiante_id', $estudianteId)
            ->orderByDesc('ui.fecha_obtencion')
            ->select('i.nombre', 'i.descripcion', 'i.icono', 'ui.fecha_obtencion')
            ->get();

        return view('student.progress.index', compact(
            'inscripciones',
            'progresos',
            'promedioProgreso',
            'lecciones',
            'experiencia',
            'nivel',
            'siguienteNivel',
            'racha',
            'insignias'
        ));
    }
}
