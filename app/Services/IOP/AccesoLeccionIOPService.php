<?php

namespace App\Services\IOP;

use App\Models\Inscripcion;
use App\Models\Leccion;
use App\Models\User;

class AccesoLeccionIOPService
{
    /**
     * Comprueba que el estudiante tenga acceso
     * a la lección correspondiente.
     */
    public function autorizar(
        User $estudiante,
        int $leccionId
    ): Leccion {
        abort_unless(
            $estudiante->rol?->nombre === 'estudiante',
            403
        );

        $leccion = Leccion::query()
            ->with('modulo.curso')
            ->findOrFail($leccionId);

        $modulo = $leccion->modulo;
        $curso = $modulo?->curso;

        abort_unless(
            $modulo !== null &&
            $curso !== null &&
            $leccion->estado === 'publicado' &&
            $modulo->estado === 'activo' &&
            $curso->estado === 'publicado',
            404
        );

        $inscrito = Inscripcion::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('curso_id', $curso->id)
            ->whereIn('estado', ['activo', 'finalizado'])
            ->exists();

        abort_unless(
            $inscrito,
            403,
            'No estás inscrito en este curso.'
        );

        return $leccion;
    }
}
