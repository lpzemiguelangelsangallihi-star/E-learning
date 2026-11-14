<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\Leccion;
use App\Models\Material;
use App\Models\Tarea;
use App\Models\Video;
use App\Services\CourseFileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

/**
 * Entrega archivos académicos solo a quienes tienen acceso al curso.
 *
 * Los archivos nuevos viven en el disco privado, así que no se pueden abrir
 * con /storage/... sin pasar por estas rutas autorizadas.
 */
class CourseFileController extends Controller
{
    public function __construct(private CourseFileService $files) {}

    public function material(Request $request, Material $material)
    {
        $material->loadMissing('leccion.modulo.curso');

        $this->authorizeLesson($request, $material->leccion);

        abort_unless($material->archivo, 404);

        return $this->serve($material->archivo);
    }

    public function video(Request $request, Video $video)
    {
        $video->loadMissing('leccion.modulo.curso');

        $this->authorizeLesson($request, $video->leccion);

        abort_unless($video->es_local, 404);

        return $this->serve($video->url);
    }

    public function tarea(Request $request, Tarea $tarea)
    {
        $tarea->loadMissing('leccion.modulo.curso');

        abort_unless(
            Gate::forUser($request->user())->any(['view', 'manage'], $tarea),
            403
        );

        abort_unless($tarea->archivo, 404);

        return $this->serve($tarea->archivo);
    }

    /** Profesor dueño del curso o estudiante inscrito en un curso publicado. */
    private function authorizeLesson(Request $request, ?Leccion $leccion): void
    {
        abort_unless($leccion, 404);

        $curso = $leccion->modulo?->curso;

        abort_unless($curso, 404);

        $user = $request->user();
        $rol = $user?->rol?->nombre;

        $esProfesorDueno = $rol === 'profesor'
            && (int) $curso->profesor_id === (int) $user->id;

        $esEstudianteInscrito = $rol === 'estudiante'
            && $curso->estado === 'publicado'
            && $leccion->estado === 'publicado'
            && in_array(
                (int) $curso->id,
                array_map('intval', Inscripcion::cursoIdsDe($user->id)),
                true
            );

        abort_unless($esProfesorDueno || $esEstudianteInscrito, 403);
    }

    private function serve(string $path)
    {
        $disk = $this->files->diskFor($path);

        abort_unless($disk, 404);

        return Storage::disk($disk)->response($path);
    }
}
