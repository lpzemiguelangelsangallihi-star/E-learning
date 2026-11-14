<?php

namespace App\Services;

use App\Models\Calificacion;
use App\Models\Curso;
use App\Models\Examen;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Reglas de eliminación de usuarios.
 *
 * Las llaves foráneas impiden borrar a un profesor que todavía es dueño de
 * cursos, tareas, exámenes o calificaciones. En lugar de devolver un error de
 * base de datos, se informa al administrador y se conserva el contenido.
 */
class UserDeletionService
{
    public function __construct(private CourseFileService $files) {}

    /** @return array<int, string> */
    public function academicDependencies(User $user): array
    {
        if ($user->rol?->nombre !== 'profesor') {
            return [];
        }

        $dependencias = [];

        if (Curso::query()->where('profesor_id', $user->id)->exists()) {
            $dependencias[] = 'cursos';
        }

        if (Tarea::query()->where('profesor_id', $user->id)->exists()) {
            $dependencias[] = 'tareas';
        }

        if (Examen::query()->where('profesor_id', $user->id)->exists()) {
            $dependencias[] = 'exámenes';
        }

        if (Calificacion::query()->where('profesor_id', $user->id)->exists()) {
            $dependencias[] = 'calificaciones';
        }

        return $dependencias;
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Los archivos deben borrarse antes de la cascada de la base de datos.
            $this->files->deleteForUser($user);

            $user->deleteProfilePhoto();
            $user->tokens->each->delete();
            $user->delete();
        });
    }
}
