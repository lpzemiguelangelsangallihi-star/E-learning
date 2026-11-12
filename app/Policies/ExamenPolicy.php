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

        if (! $curso || $curso->estado !== 'publicado' || $leccion->estado !== 'publicado') {
            return false;
        }

        return in_array((int) $curso->id, array_map('intval', Inscripcion::cursoIdsDe($user->id)), true);
    }
}
