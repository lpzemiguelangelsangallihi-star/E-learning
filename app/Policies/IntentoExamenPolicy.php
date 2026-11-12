<?php

namespace App\Policies;

use App\Models\IntentoExamen;
use App\Models\User;

class IntentoExamenPolicy
{
    /** El intento pertenece al estudiante. */
    public function own(User $user, IntentoExamen $intento): bool
    {
        return (int) $intento->estudiante_id === (int) $user->id;
    }

    /** El profesor dueño del curso puede revisar y corregir el intento. */
    public function review(User $user, IntentoExamen $intento): bool
    {
        $curso = $intento->examen?->leccion?->modulo?->curso;

        return $user->rol?->nombre === 'profesor'
            && $curso !== null
            && (int) $curso->profesor_id === (int) $user->id;
    }
}
