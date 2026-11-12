<?php

namespace App\Services\Evaluacion;

use App\Models\Calificacion;
use App\Models\Examen;
use App\Models\IntentoExamen;
use App\Models\Pregunta;
use App\Models\RespuestaEstudiante;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Corrección automática de exámenes.
 *
 * Reglas:
 *  - opción múltiple / verdadero-falso: todo o nada.
 *  - varias respuestas: crédito parcial = (aciertos - errores) / correctas, mínimo 0.
 *  - respuesta corta: coincide con alguna respuesta aceptada (sin tildes ni mayúsculas).
 *  - La nota del intento se escala a puntaje_maximo del examen.
 *  - En "calificaciones" se guarda la MEJOR nota del estudiante, en escala 0-100.
 */
class ExamGradingService
{
    /** Corrige el intento, lo cierra y sincroniza la calificación. */
    public function grade(IntentoExamen $intento): IntentoExamen
    {
        $examen = $intento->examen()->with('preguntas.opciones')->first();

        $respuestas = RespuestaEstudiante::query()
            ->where('intento_id', $intento->id)
            ->get()
            ->groupBy('pregunta_id');

        foreach ($examen->preguntas as $pregunta) {
            $filas = $respuestas->get($pregunta->id, collect());

            if ($filas->isEmpty()) {
                // Pregunta sin responder: se deja constancia con una fila vacía.
                RespuestaEstudiante::create([
                    'intento_id' => $intento->id,
                    'pregunta_id' => $pregunta->id,
                    'opcion_id' => null,
                    'respuesta_texto' => null,
                    'es_correcta' => false,
                    'puntaje_obtenido' => 0,
                ]);

                continue;
            }

            $this->gradeQuestion($pregunta, $filas);
        }

        $fin = now();
        $limite = $examen->tiempo_limite
            ? $intento->fecha_inicio->copy()->addMinutes((int) $examen->tiempo_limite)
            : null;

        if ($limite && $fin->greaterThan($limite)) {
            $fin = $limite;
        }

        $intento->fecha_fin = $fin;
        $intento->estado = 'finalizado';
        $intento->calificacion = $this->computeScore($intento, $examen);
        $intento->save();

        $this->syncCalificacion($examen, (int) $intento->estudiante_id);

        return $intento;
    }

    /**
     * Recalcula la nota de un intento a partir de los puntajes de sus respuestas
     * (se usa tras una corrección manual del profesor).
     */
    public function recalculate(IntentoExamen $intento): IntentoExamen
    {
        $examen = $intento->examen()->with('preguntas')->first();

        $intento->calificacion = $this->computeScore($intento, $examen);
        $intento->save();

        $this->syncCalificacion($examen, (int) $intento->estudiante_id);

        return $intento;
    }

    /** Guarda en "calificaciones" la mejor nota del estudiante (escala 0-100). */
    public function syncCalificacion(Examen $examen, int $estudianteId): void
    {
        $mejor = IntentoExamen::query()
            ->where('examen_id', $examen->id)
            ->where('estudiante_id', $estudianteId)
            ->where('estado', 'finalizado')
            ->max('calificacion');

        $examen->loadMissing('leccion.modulo');
        $cursoId = $examen->leccion?->modulo?->curso_id;

        if ($mejor === null || $cursoId === null) {
            return;
        }

        $maximo = (float) $examen->puntaje_maximo;
        $sobreCien = $maximo > 0 ? round(((float) $mejor / $maximo) * 100, 2) : 0;

        Calificacion::updateOrCreate(
            [
                'estudiante_id' => $estudianteId,
                'examen_id' => $examen->id,
                'tipo' => 'examen',
            ],
            [
                'profesor_id' => $examen->profesor_id,
                'curso_id' => $cursoId,
                'calificacion' => $sobreCien,
                'fecha' => now(),
            ]
        );
    }

    /** Normaliza un texto para comparar respuestas cortas. */
    public static function normalize(?string $texto): string
    {
        $texto = Str::lower(trim((string) $texto));
        $texto = Str::ascii($texto);
        $texto = preg_replace('/\s+/', ' ', $texto) ?? $texto;

        return $texto;
    }

    private function computeScore(IntentoExamen $intento, Examen $examen): float
    {
        $total = (float) $examen->preguntas->sum(fn (Pregunta $p) => (float) $p->puntaje);

        if ($total <= 0) {
            return 0.0;
        }

        $obtenido = (float) RespuestaEstudiante::query()
            ->where('intento_id', $intento->id)
            ->sum('puntaje_obtenido');

        $obtenido = min($obtenido, $total);

        return round(($obtenido / $total) * (float) $examen->puntaje_maximo, 2);
    }

    /** Califica las filas de respuesta de una pregunta y las persiste. */
    private function gradeQuestion(Pregunta $pregunta, Collection $filas): void
    {
        $maximo = (float) $pregunta->puntaje;
        $opciones = $pregunta->opciones;

        switch ($pregunta->tipo) {
            case 'varias_respuestas':
                $correctas = $opciones->where('es_correcta', true)->pluck('id')->map(fn ($id) => (int) $id);
                $elegidas = $filas->pluck('opcion_id')->filter()->map(fn ($id) => (int) $id)->unique();

                $aciertos = $elegidas->intersect($correctas)->count();
                $errores = $elegidas->diff($correctas)->count();

                $fraccion = $correctas->isEmpty()
                    ? 0.0
                    : max(0.0, ($aciertos - $errores) / $correctas->count());

                $primera = true;

                foreach ($filas as $fila) {
                    $fila->es_correcta = $correctas->contains((int) $fila->opcion_id);
                    $fila->puntaje_obtenido = $primera ? round($fraccion * $maximo, 2) : 0;
                    $fila->save();
                    $primera = false;
                }

                break;

            case 'respuesta_corta':
                $fila = $filas->first();
                $aceptadas = $opciones
                    ->where('es_correcta', true)
                    ->map(fn ($op) => self::normalize($op->opcion))
                    ->filter()
                    ->all();

                $texto = self::normalize($fila->respuesta_texto);
                $ok = $texto !== '' && in_array($texto, $aceptadas, true);

                $fila->es_correcta = $ok;
                $fila->puntaje_obtenido = $ok ? $maximo : 0;
                $fila->save();

                break;

            default: // opcion_multiple, verdadero_falso
                $fila = $filas->first();
                $opcion = $opciones->firstWhere('id', (int) $fila->opcion_id);
                $ok = $opcion !== null && $opcion->es_correcta === true;

                $fila->es_correcta = $ok;
                $fila->puntaje_obtenido = $ok ? $maximo : 0;
                $fila->save();
        }
    }
}
