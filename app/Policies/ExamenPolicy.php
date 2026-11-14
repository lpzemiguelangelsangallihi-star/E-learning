<?php

namespace App\Policies;

use App\Models\Examen;
use App\Models\Inscripcion;
use App\Models\User;

class ExamenPolicy
{
    /** El profesor dueño del curso al que pertenece el examen. */
    public function manage(User $user, Examen $examen): bool
    {
        $curso = $examen->leccion?->modulo?->curso;

        return $user->rol?->nombre === 'profesor'
            && $curso !== null
            && (int) $curso->profesor_id === (int) $user->id;
    }

    /** Estudiante inscrito en el curso y examen publicado. */
    public function take(User $user, Examen $examen): bool
    {
        if ($user->rol?->nombre !== 'estudiante' || $examen->estado !== 'publicado') {
            return false;
        }

        $leccion = $examen->leccion;
        $curso = $leccion?->modulo?->curso;

        if (
            ! $curso
            || $curso->estado !== 'publicado'
            || $leccion->estado !== 'publicado'
            || $leccion->modulo?->estado !== 'activo'
        ) {
            return false;
        }

        return in_array((int) $curso->id, array_map('intval', Inscripcion::cursoIdsDe($user->id)), true);
    }

    /**
     * Ver la ficha del examen: publicado (como "take") o cerrado cuando el
     * estudiante ya tiene intentos y necesita volver a consultar sus resultados.
     */
    public function view(User $user, Examen $examen): bool
    {
        if ($this->take($user, $examen)) {
            return true;
        }

        if ($user->rol?->nombre !== 'estudiante' || $examen->estado !== 'cerrado') {
            return false;
        }

        $curso = $examen->leccion?->modulo?->curso;

        if (! $curso || ! in_array((int) $curso->id, array_map('intval', Inscripcion::cursoIdsDe($user->id)), true)) {
            return false;
        }

        return $examen->intentos()->where('estudiante_id', $user->id)->exists();
    }
}
