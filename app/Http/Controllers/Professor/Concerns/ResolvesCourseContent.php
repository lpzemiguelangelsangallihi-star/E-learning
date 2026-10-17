<?php

namespace App\Http\Controllers\Professor\Concerns;

use App\Models\Curso;
use App\Models\Leccion;
use App\Models\Modulo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Utilidades compartidas por los controladores de contenido del profesor:
 * autorización por curso, pertenencia unidad/tema y ordenamiento.
 */
trait ResolvesCourseContent
{
    /** El curso debe pertenecer al profesor autenticado. */
    protected function ownCourse(Curso $curso): Curso
    {
        if ((int) $curso->profesor_id !== (int) Auth::id()) {
            abort(403);
        }

        return $curso;
    }

    /** La unidad debe pertenecer al curso de la URL. */
    protected function moduleOf(Curso $curso, Modulo $modulo): Modulo
    {
        if ((int) $modulo->curso_id !== (int) $curso->id) {
            abort(404);
        }

        return $modulo;
    }

    /** El tema debe pertenecer a la unidad de la URL. */
    protected function lessonOf(Modulo $modulo, Leccion $leccion): Leccion
    {
        if ((int) $leccion->modulo_id !== (int) $modulo->id) {
            abort(404);
        }

        return $leccion;
    }

    /** Valida toda la cadena curso → unidad → tema de una vez. */
    protected function resolveLesson(Curso $curso, Modulo $modulo, Leccion $leccion): void
    {
        $this->ownCourse($curso);
        $this->moduleOf($curso, $modulo);
        $this->lessonOf($modulo, $leccion);
    }

    /**
     * Reasigna orden = 1..n respetando el orden actual.
     * Sirve para cerrar huecos después de eliminar.
     */
    protected function normalizeOrder(Collection $items): void
    {
        foreach ($items->values() as $index => $item) {
            if ((int) $item->orden !== $index + 1) {
                $item->orden = $index + 1;
                $item->save();
            }
        }
    }

    /**
     * Sube o baja un elemento intercambiándolo con su vecino.
     *
     * @param  Collection  $items  hermanos ya ordenados (orden, id)
     */
    protected function moveItem(Collection $items, int $id, string $direction): void
    {
        $items = $items->values();

        $index = $items->search(fn ($item) => (int) $item->id === $id);

        if ($index === false) {
            return;
        }

        $target = $direction === 'up' ? $index - 1 : $index + 1;

        if ($target < 0 || $target >= $items->count()) {
            return;
        }

        $list = $items->all();

        [$list[$index], $list[$target]] = [$list[$target], $list[$index]];

        $this->normalizeOrder(collect($list));
    }

    /** Un tema con tareas o exámenes no se elimina desde aquí. */
    protected function lessonHasBlockers(Leccion $leccion): bool
    {
        return $leccion->tareas()->exists() || $leccion->examenes()->exists();
    }

    /** Elimina un tema con sus videos, materiales y progreso (y archivos). */
    protected function deleteLessonCascade(Leccion $leccion): void
    {
        foreach ($leccion->materiales as $material) {
            if ($material->archivo) {
                Storage::disk('public')->delete($material->archivo);
            }
        }

        foreach ($leccion->videos as $video) {
            if ($video->es_local) {
                Storage::disk('public')->delete($video->url);
            }
        }

        $leccion->materiales()->delete();
        $leccion->videos()->delete();
        $leccion->progresos()->delete();
        $leccion->delete();
    }
}
