<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Models\IopSeguimiento;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteIOPController extends Controller
{
    public function exportar(): StreamedResponse
    {
        abort_unless(
            Auth::user()?->rol?->nombre === 'profesor',
            403
        );

        $profesorId = Auth::id();

        $nombre = 'reporte_iop_' .
            now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(
            function () use ($profesorId) {
                $salida = fopen('php://output', 'w');

                // Facilitar apertura con UTF-8.
                fwrite($salida, "\xEF\xBB\xBF");

                fputcsv($salida, [
                    'Estudiante',
                    'Curso',
                    'Leccion',
                    'Ciclo',
                    'Estado actual',
                    'Nota de referencia',
                    'Alternativa Maximin',
                    'Valor Maximin',
                    'Ejercicio',
                    'Estado del ejercicio',
                    'Aprobado el',
                    'Evaluacion posterior',
                    'Estado anterior',
                    'Estado siguiente',
                ]);

                // Evita interpretar contenido del usuario
                // como fórmulas en hojas de cálculo.
                $seguro = static function ($valor): string {
                    $texto = (string) ($valor ?? '');

                    if (
                        preg_match(
                            '/^[\s\x00-\x1F]*[=+\-@]/u',
                            $texto
                        )
                    ) {
                        return "'" . $texto;
                    }

                    return $texto;
                };

                IopSeguimiento::query()
                    ->whereNotNull('intento_diagnostico_id')
                    ->whereHas(
                        'leccion.modulo.curso',
                        fn ($q) => $q->where(
                            'profesor_id',
                            $profesorId
                        )
                    )
                    ->with([
                        'estudiante',
                        'leccion.modulo.curso',
                        'transiciones',
                        'ciclosRefuerzo.recomendacion.asignacionRefuerzo.ejercicio',
                    ])
                    ->lazyById(100)
                    ->each(function ($seguimiento) use (
                        $salida,
                        $seguro
                    ) {
                        $estudiante = $seguimiento
                            ->estudiante?->nombre_completo;

                        $curso = $seguimiento
                            ->leccion?->modulo?->curso?->titulo;

                        $leccion = $seguimiento
                            ->leccion?->titulo;

                        foreach (
                            $seguimiento->ciclosRefuerzo
                            as $ciclo
                        ) {
                            $rec = $ciclo->recomendacion;

                            $asignacion = $rec
                                ?->asignacionRefuerzo;

                            $evaluacion = $seguimiento
                                ->transiciones
                                ->firstWhere(
                                    'ciclo',
                                    (int) $ciclo->ciclo + 1
                                );

                            fputcsv($salida, [
                                $seguro($estudiante),
                                $seguro($curso),
                                $seguro($leccion),
                                $ciclo->ciclo,
                                $seguimiento->estado_actual,
                                $rec?->calificacion_inicial,
                                $seguro(
                                    $rec?->alternativa_recomendada
                                ),
                                $rec?->valor_maximin,
                                $seguro(
                                    $asignacion
                                        ?->ejercicio?->titulo
                                ),
                                $asignacion?->estado,
                                $asignacion
                                    ?->completado_en
                                    ?->format('Y-m-d H:i:s'),
                                $evaluacion
                                    ?->calificacion_nueva,
                                $evaluacion
                                    ?->estado_anterior,
                                $evaluacion
                                    ?->estado_siguiente,
                            ]);
                        }
                    });

                fclose($salida);
            },
            $nombre,
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',
            ]
        );
    }
}
