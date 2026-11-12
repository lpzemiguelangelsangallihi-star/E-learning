<?php

namespace App\Services\Evaluacion;

use App\Models\Calificacion;
use App\Models\Curso;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reportes académicos del profesor: por examen, estudiante, tema y tiempo.
 * Todas las notas se expresan en escala 0-100.
 */
class AcademicReportService
{
    public function cursos(User $profesor): Collection
    {
        return Curso::query()
            ->where('profesor_id', $profesor->id)
            ->orderBy('titulo')
            ->get(['id', 'titulo']);
    }

    public function examenes(User $profesor, ?int $cursoId = null): Collection
    {
        return DB::table('examenes as e')
            ->join('lecciones as l', 'l.id', '=', 'e.leccion_id')
            ->join('modulos as m', 'm.id', '=', 'l.modulo_id')
            ->join('cursos as c', 'c.id', '=', 'm.curso_id')
            ->where('c.profesor_id', $profesor->id)
            ->when($cursoId, fn ($q) => $q->where('c.id', $cursoId))
            ->orderBy('e.titulo')
            ->get(['e.id', 'e.titulo']);
    }

    /** Intentos finalizados de los cursos del profesor (base de los reportes). */
    private function base(User $profesor, ?int $cursoId, ?int $examenId): Builder
    {
        return DB::table('intentos_examen as i')
            ->join('examenes as e', 'e.id', '=', 'i.examen_id')
            ->join('lecciones as l', 'l.id', '=', 'e.leccion_id')
            ->join('modulos as m', 'm.id', '=', 'l.modulo_id')
            ->join('cursos as c', 'c.id', '=', 'm.curso_id')
            ->where('c.profesor_id', $profesor->id)
            ->where('i.estado', 'finalizado')
            ->when($cursoId, fn ($q) => $q->where('c.id', $cursoId))
            ->when($examenId, fn ($q) => $q->where('e.id', $examenId));
    }

    private const PORCENTAJE = 'i.calificacion / NULLIF(e.puntaje_maximo, 0) * 100';

    /** Resumen general. */
    public function resumen(User $profesor, ?int $cursoId, ?int $examenId): array
    {
        $fila = $this->base($profesor, $cursoId, $examenId)
            ->selectRaw('COUNT(*) as intentos')
            ->selectRaw('COUNT(DISTINCT i.estudiante_id) as estudiantes')
            ->selectRaw('AVG('.self::PORCENTAJE.') as promedio')
            ->selectRaw('SUM(CASE WHEN '.self::PORCENTAJE.' >= ? THEN 1 ELSE 0 END) as aprobados', [Calificacion::NOTA_APROBACION])
            ->first();

        $intentos = (int) ($fila->intentos ?? 0);

        return [
            'intentos' => $intentos,
            'estudiantes' => (int) ($fila->estudiantes ?? 0),
            'promedio' => $intentos ? round((float) $fila->promedio, 1) : null,
            'aprobacion' => $intentos ? round(((int) $fila->aprobados / $intentos) * 100, 1) : null,
        ];
    }

    /** Rendimiento por examen (y su curso). */
    public function porExamen(User $profesor, ?int $cursoId, ?int $examenId): Collection
    {
        return $this->base($profesor, $cursoId, $examenId)
            ->groupBy('e.id', 'e.titulo', 'c.titulo')
            ->orderBy('c.titulo')
            ->orderBy('e.titulo')
            ->selectRaw('e.id, e.titulo as examen, c.titulo as curso')
            ->selectRaw('COUNT(*) as intentos')
            ->selectRaw('COUNT(DISTINCT i.estudiante_id) as estudiantes')
            ->selectRaw('AVG('.self::PORCENTAJE.') as promedio')
            ->selectRaw('MAX('.self::PORCENTAJE.') as mejor')
            ->selectRaw('SUM(CASE WHEN '.self::PORCENTAJE.' >= ? THEN 1 ELSE 0 END) as aprobados', [Calificacion::NOTA_APROBACION])
            ->get();
    }

    /** Rendimiento por estudiante. */
    public function porEstudiante(User $profesor, ?int $cursoId, ?int $examenId, string $buscar = ''): Collection
    {
        return $this->base($profesor, $cursoId, $examenId)
            ->join('users as u', 'u.id', '=', 'i.estudiante_id')
            ->when($buscar !== '', function ($q) use ($buscar) {
                $like = '%'.$buscar.'%';
                $q->where(function ($q) use ($like) {
                    $q->where('u.nombre', 'like', $like)
                        ->orWhere('u.apellido_paterno', 'like', $like)
                        ->orWhere('u.apellido_materno', 'like', $like)
                        ->orWhere('u.email', 'like', $like);
                });
            })
            ->groupBy('u.id', 'u.nombre', 'u.apellido_paterno', 'u.apellido_materno', 'u.email')
            ->orderBy('u.apellido_paterno')
            ->orderBy('u.nombre')
            ->selectRaw("u.id, u.nombre, u.apellido_paterno, u.apellido_materno, u.email")
            ->selectRaw('COUNT(*) as intentos')
            ->selectRaw('AVG('.self::PORCENTAJE.') as promedio')
            ->selectRaw('MAX('.self::PORCENTAJE.') as mejor')
            ->selectRaw('MAX(i.fecha_fin) as ultimo')
            ->get();
    }

    /**
     * Dificultad por tema (lección): porcentaje de puntaje conseguido
     * sobre el puntaje posible, y la pregunta más difícil de cada tema.
     */
    public function porTema(User $profesor, ?int $cursoId, ?int $examenId): Collection
    {
        $filas = DB::table('respuestas_estudiante as r')
            ->join('intentos_examen as i', 'i.id', '=', 'r.intento_id')
            ->join('preguntas as p', 'p.id', '=', 'r.pregunta_id')
            ->join('examenes as e', 'e.id', '=', 'i.examen_id')
            ->join('lecciones as l', 'l.id', '=', 'e.leccion_id')
            ->join('modulos as m', 'm.id', '=', 'l.modulo_id')
            ->join('cursos as c', 'c.id', '=', 'm.curso_id')
            ->where('c.profesor_id', $profesor->id)
            ->where('i.estado', 'finalizado')
            ->when($cursoId, fn ($q) => $q->where('c.id', $cursoId))
            ->when($examenId, fn ($q) => $q->where('e.id', $examenId))
            ->groupBy('p.id', 'p.pregunta', 'p.puntaje', 'l.id', 'l.titulo', 'c.titulo')
            ->selectRaw('p.id as pregunta_id, p.pregunta, p.puntaje, l.id as leccion_id, l.titulo as leccion, c.titulo as curso')
            ->selectRaw('SUM(r.puntaje_obtenido) as obtenido')
            ->selectRaw('COUNT(DISTINCT r.intento_id) as respondieron')
            ->get();

        return $filas
            ->map(function ($f) {
                $posible = (float) $f->puntaje * (int) $f->respondieron;
                $f->logro = $posible > 0 ? round(((float) $f->obtenido / $posible) * 100, 1) : null;
                $f->posible = $posible;

                return $f;
            })
            ->groupBy('leccion_id')
            ->map(function (Collection $preguntas) {
                $primera = $preguntas->first();
                $posible = $preguntas->sum('posible');
                $obtenido = $preguntas->sum(fn ($p) => (float) $p->obtenido);
                $dificil = $preguntas->filter(fn ($p) => $p->logro !== null)->sortBy('logro')->first();

                return (object) [
                    'leccion' => $primera->leccion,
                    'curso' => $primera->curso,
                    'preguntas' => $preguntas->count(),
                    'logro' => $posible > 0 ? round(($obtenido / $posible) * 100, 1) : null,
                    'mas_dificil' => $dificil?->pregunta,
                    'mas_dificil_logro' => $dificil?->logro,
                ];
            })
            ->sortBy('logro')
            ->values();
    }

    /** Evolución mensual (últimos 12 meses) del promedio y la cantidad de intentos. */
    public function evolucion(User $profesor, ?int $cursoId, ?int $examenId): Collection
    {
        $desde = now()->startOfMonth()->subMonths(11);

        $intentos = $this->base($profesor, $cursoId, $examenId)
            ->where('i.fecha_fin', '>=', $desde)
            ->selectRaw('i.fecha_fin, '.self::PORCENTAJE.' as nota')
            ->get();

        $meses = collect();

        for ($k = 0; $k < 12; $k++) {
            $mes = $desde->copy()->addMonths($k);
            $clave = $mes->format('Y-m');

            $delMes = $intentos->filter(
                fn ($f) => substr((string) $f->fecha_fin, 0, 7) === $clave
            );

            $meses->push((object) [
                'mes' => $mes->translatedFormat('M Y'),
                'intentos' => $delMes->count(),
                'promedio' => $delMes->isNotEmpty() ? round((float) $delMes->avg('nota'), 1) : null,
            ]);
        }

        return $meses;
    }

    /** Intentos finalizados para el listado (con paginación en el componente). */
    public function intentos(User $profesor, ?int $cursoId, ?int $examenId, string $buscar = '')
    {
        return DB::table('intentos_examen as i')
            ->join('examenes as e', 'e.id', '=', 'i.examen_id')
            ->join('lecciones as l', 'l.id', '=', 'e.leccion_id')
            ->join('modulos as m', 'm.id', '=', 'l.modulo_id')
            ->join('cursos as c', 'c.id', '=', 'm.curso_id')
            ->join('users as u', 'u.id', '=', 'i.estudiante_id')
            ->where('c.profesor_id', $profesor->id)
            ->where('i.estado', 'finalizado')
            ->when($cursoId, fn ($q) => $q->where('c.id', $cursoId))
            ->when($examenId, fn ($q) => $q->where('e.id', $examenId))
            ->when($buscar !== '', function ($q) use ($buscar) {
                $like = '%'.$buscar.'%';
                $q->where(function ($q) use ($like) {
                    $q->where('u.nombre', 'like', $like)
                        ->orWhere('u.apellido_paterno', 'like', $like)
                        ->orWhere('u.apellido_materno', 'like', $like);
                });
            })
            ->orderByDesc('i.fecha_fin')
            ->selectRaw('i.id, i.numero_intento, i.fecha_fin, i.calificacion, e.titulo as examen, e.puntaje_maximo, c.titulo as curso')
            ->selectRaw("TRIM(CONCAT(u.nombre, ' ', COALESCE(u.apellido_paterno, ''), ' ', COALESCE(u.apellido_materno, ''))) as estudiante");
    }
}
