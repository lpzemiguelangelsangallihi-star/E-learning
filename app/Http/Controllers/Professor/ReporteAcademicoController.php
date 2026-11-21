<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Services\Evaluacion\AcademicReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Descarga en CSV (abre en Excel) de los reportes académicos del profesor.
 */
class ReporteAcademicoController extends Controller
{
    public const TIPOS = [
        'examenes', 'estudiantes', 'temas', 'tiempo', 'intentos',
        'progreso', 'tareas', 'ranking',
    ];

    public function exportar(Request $request, string $tipo, AcademicReportService $reportes): StreamedResponse
    {
        abort_unless(in_array($tipo, self::TIPOS, true), 404);

        $profesor = Auth::user();
        $filtros = $request->validate([
            'curso' => ['nullable', 'integer'],
            'examen' => ['nullable', 'integer'],
        ]);
        $curso = $filtros['curso'] ?? null;
        $examen = $filtros['examen'] ?? null;

        // Solo cursos / exámenes propios.
        abort_if($curso && ! Curso::where('id', $curso)->where('profesor_id', $profesor->id)->exists(), 404);
        abort_if($examen && ! $reportes->examenes($profesor)->contains('id', $examen), 404);

        [$cabecera, $filas] = $this->datos($tipo, $reportes, $profesor, $curso, $examen);

        $nombre = 'reporte_'.$tipo.'_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($cabecera, $filas) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF"); // UTF-8 para Excel

            fputcsv($salida, $cabecera);

            foreach ($filas as $fila) {
                fputcsv($salida, array_map([$this, 'seguro'], $fila));
            }

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{0: array<int,string>, 1: iterable<array>} */
    private function datos(string $tipo, AcademicReportService $r, $profesor, ?int $curso, ?int $examen): array
    {
        $n = fn ($v) => $v === null ? '' : round((float) $v, 1);

        return match ($tipo) {
            'examenes' => [
                ['Examen', 'Curso', 'Intentos', 'Estudiantes', 'Promedio', 'Mejor nota', 'Aprobación %'],
                $r->porExamen($profesor, $curso, $examen)->map(fn ($f) => [
                    $f->examen, $f->curso, $f->intentos, $f->estudiantes, $n($f->promedio), $n($f->mejor),
                    $f->intentos ? round($f->aprobados / $f->intentos * 100, 1) : '',
                ]),
            ],
            'estudiantes' => [
                ['Estudiante', 'Correo', 'Intentos', 'Promedio', 'Mejor nota', 'Último intento'],
                $r->porEstudiante($profesor, $curso, $examen)->map(fn ($f) => [
                    trim("{$f->nombre} {$f->apellido_paterno} {$f->apellido_materno}"), $f->email,
                    $f->intentos, $n($f->promedio), $n($f->mejor), $f->ultimo,
                ]),
            ],
            'temas' => [
                ['Tema (lección)', 'Curso', 'Preguntas', 'Logro %', 'Pregunta más difícil', 'Logro de esa pregunta %'],
                $r->porTema($profesor, $curso, $examen)->map(fn ($f) => [
                    $f->leccion, $f->curso, $f->preguntas, $f->logro, $f->mas_dificil, $f->mas_dificil_logro,
                ]),
            ],
            'tiempo' => [
                ['Mes', 'Intentos', 'Promedio'],
                $r->evolucion($profesor, $curso, $examen)->map(fn ($f) => [$f->mes, $f->intentos, $f->promedio]),
            ],
            'intentos' => [
                ['Estudiante', 'Examen', 'Curso', 'N° intento', 'Fecha', 'Calificación', 'Puntaje máximo'],
                $r->intentos($profesor, $curso, $examen)->get()->map(fn ($f) => [
                    $f->estudiante, $f->examen, $f->curso, $f->numero_intento, $f->fecha_fin, $f->calificacion, $f->puntaje_maximo,
                ]),
            ],
            'progreso' => [
                ['Curso', 'Estudiante', 'Correo', 'Inscripción', 'Fecha inscripción', 'Progreso %', 'Lecciones completadas', 'Estado', 'Finalizó'],
                $r->progresoEstudiantes($profesor, $curso)->map(fn ($f) => [
                    $f->curso, $f->estudiante, $f->email, $f->inscripcion, $f->fecha_inscripcion,
                    $f->porcentaje, $f->lecciones_completadas, $f->progreso, $f->fecha_finalizacion,
                ]),
            ],
            'tareas' => [
                ['Tarea', 'Curso', 'Estado', 'Fecha límite', 'Inscritos', 'Entregas', 'Pendientes', 'Tarde', 'Calificadas', 'Promedio %'],
                $r->tareas($profesor, $curso)->map(fn ($f) => [
                    $f->tarea, $f->curso, $f->estado, $f->fecha_limite, $f->inscritos, $f->entregas,
                    max(0, (int) $f->inscritos - (int) $f->entregas), $f->tarde, $f->calificadas, $n($f->promedio),
                ]),
            ],
            'ranking' => [
                ['Posición', 'Estudiante', 'Correo', 'Promedio', 'Mejor nota', 'Intentos'],
                $r->ranking($profesor, $curso, $examen)->map(fn ($f) => [
                    $f->posicion, trim("{$f->nombre} {$f->apellido_paterno} {$f->apellido_materno}"),
                    $f->email, $n($f->promedio), $n($f->mejor), $f->intentos,
                ]),
            ],
        };
    }

    /** Evita que Excel interprete texto del usuario como fórmula. */
    private function seguro($valor)
    {
        if (! is_string($valor)) {
            return $valor;
        }

        return preg_match('/^[\s\x00-\x1F]*[=+\-@]/u', $valor) ? "'".$valor : $valor;
    }
}
