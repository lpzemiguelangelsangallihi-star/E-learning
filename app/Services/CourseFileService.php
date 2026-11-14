<?php

namespace App\Services;

use App\Models\Curso;
use App\Models\Entrega;
use App\Models\Leccion;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Localiza y elimina los archivos académicos.
 *
 * Las subidas nuevas viven en el disco privado "local"; los archivos creados
 * por versiones anteriores pueden seguir en el disco "public". El servicio
 * revisa ambos discos para no dejar archivos huérfanos al eliminar contenido.
 */
class CourseFileService
{
    /** Discos donde puede vivir un archivo académico, en orden de preferencia. */
    public const DISKS = ['local', 'public'];

    /** Devuelve el disco que contiene la ruta, o null si no existe. */
    public function diskFor(?string $path): ?string
    {
        $path = $this->normalize($path);

        if ($path === null) {
            return null;
        }

        foreach (self::DISKS as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return $disk;
            }
        }

        return null;
    }

    /** Elimina la ruta de todos los discos donde exista. */
    public function delete(?string $path): void
    {
        $path = $this->normalize($path);

        if ($path === null) {
            return;
        }

        foreach (self::DISKS as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }

    /** Archivos de los materiales, videos, tareas y entregas de un tema. */
    public function deleteForLesson(Leccion $leccion): void
    {
        foreach ($leccion->materiales as $material) {
            $this->delete($material->archivo);
        }

        foreach ($leccion->videos as $video) {
            if ($video->es_local) {
                $this->delete($video->url);
            }
        }

        foreach ($leccion->tareas as $tarea) {
            $this->delete($tarea->archivo);

            foreach ($tarea->entregas as $entrega) {
                $this->delete($entrega->archivo);
            }
        }
    }

    /** Archivos de todas las lecciones de un curso. */
    public function deleteForCourse(Curso $curso): void
    {
        $lecciones = Leccion::query()
            ->whereHas('modulo', fn ($query) => $query->where('curso_id', $curso->id))
            ->with(['materiales', 'videos', 'tareas.entregas'])
            ->get();

        foreach ($lecciones as $leccion) {
            $this->deleteForLesson($leccion);
        }

        $this->delete($curso->imagen);
    }

    /**
     * Archivos personales de un usuario: entregas como estudiante y material
     * de las tareas/cursos que imparte como profesor.
     */
    public function deleteForUser(User $user): void
    {
        Entrega::query()
            ->where('estudiante_id', $user->id)
            ->get()
            ->each(fn (Entrega $entrega) => $this->delete($entrega->archivo));

        Tarea::query()
            ->where('profesor_id', $user->id)
            ->with('entregas')
            ->get()
            ->each(function (Tarea $tarea) {
                $this->delete($tarea->archivo);

                foreach ($tarea->entregas as $entrega) {
                    $this->delete($entrega->archivo);
                }
            });

        Curso::query()
            ->where('profesor_id', $user->id)
            ->get()
            ->each(fn (Curso $curso) => $this->deleteForCourse($curso));
    }

    private function normalize(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $path = ltrim(trim($path), '/');

        if ($path === '' || preg_match('#^https?://#i', $path) === 1) {
            return null;
        }

        return $path;
    }
}
