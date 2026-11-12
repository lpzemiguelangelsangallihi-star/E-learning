<?php

namespace App\Policies;

use App\Models\Inscripcion;
use App\Models\Tarea;
use App\Models\User;

class TareaPolicy
{
    public function manage(User $user, Tarea $tarea): bool
    {
        $curso = $tarea->leccion?->modulo?->curso;

        return $user->rol?->nombre === 'profesor'
            && $curso !== null
            && (int) $curso->profesor_id === (int) $user->id;
    }

    /** Estudiante inscrito; la tarea debe estar publicada o cerrada (visible). */
    public function view(User $user, Tarea $tarea): bool
    {
        if ($user->rol?->nombre !== 'estudiante' || $tarea->estado === 'borrador') {
            return false;
        }

        $curso = $tarea->leccion?->modulo?->curso;

        if (! $curso || $curso->estado !== 'publicado') {
            return false;
        }

        return in_array((int) $curso->id, array_map('intval', Inscripcion::cursoIdsDe($user->id)), true);
    }

    /** Solo se entrega mientras la tarea esté publicada. */
    public function submit(User $user, Tarea $tarea): bool
    {
        return $tarea->estado === 'publicada' && $this->view($user, $tarea);
    }
}
