<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Professor\Concerns\ResolvesCourseContent;
use App\Models\Curso;
use App\Models\Leccion;
use App\Models\Material;
use App\Models\Modulo;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Materiales de un tema: archivos, enlaces y videos.
 *
 * - archivo / enlace  -> tabla `materiales`
 * - video             -> tabla `videos` (enlace YouTube/Vimeo o archivo subido)
 */
class MaterialController extends Controller
{
    use ResolvesCourseContent;

    public function index(Curso $curso, Modulo $modulo, Leccion $leccion)
    {
        $this->resolveLesson($curso, $modulo, $leccion);

        $leccion->load(['materiales', 'videos']);

        return view('professor.materials.index', compact('curso', 'modulo', 'leccion'));
    }

    public function store(Request $request, Curso $curso, Modulo $modulo, Leccion $leccion)
    {
        $this->resolveLesson($curso, $modulo, $leccion);

        $base = $request->validate([
            'recurso' => ['required', Rule::in(['archivo', 'enlace', 'video'])],
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ]);

        $titulo = trim($base['titulo']);
        $descripcion = ! empty($base['descripcion']) ? trim($base['descripcion']) : null;

        switch ($base['recurso']) {

            case 'archivo':
                $request->validate([
                    'archivo' => [
                        'required',
                        'file',
                        'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,ppt,pptx,xls,xlsx,txt,zip',
                        'max:10240', // 10 MB
                    ],
                ]);

                $file = $request->file('archivo');

                Material::create([
                    'leccion_id' => $leccion->id,
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'tipo' => $this->tipoPorExtension($file->getClientOriginalExtension()),
                    'archivo' => $file->store("materiales/lecciones/{$leccion->id}", 'public'),
                ]);

                $mensaje = 'Archivo agregado correctamente.';
                break;

            case 'enlace':
                $request->validate([
                    'enlace' => ['required', 'url', 'max:500'],
                ]);

                Material::create([
                    'leccion_id' => $leccion->id,
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'tipo' => 'enlace',
                    'url' => $request->input('enlace'),
                ]);

                $mensaje = 'Enlace agregado correctamente.';
                break;

            default: // video
                $request->validate([
                    'video_url' => ['nullable', 'url', 'max:500'],
                    'video_archivo' => [
                        'nullable',
                        'file',
                        'mimetypes:video/mp4,video/webm,video/quicktime',
                        'max:102400', // 100 MB
                    ],
                    'duracion' => ['nullable', 'integer', 'min:0', 'max:10000'],
                ]);

                $tieneUrl = $request->filled('video_url');
                $tieneArchivo = $request->hasFile('video_archivo');

                if ($tieneUrl === $tieneArchivo) {
                    throw ValidationException::withMessages([
                        'video_url' => $tieneUrl
                            ? 'Indica un enlace o sube un archivo, no ambos.'
                            : 'Indica el enlace del video o sube un archivo.',
                    ]);
                }

                Video::create([
                    'leccion_id' => $leccion->id,
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'url' => $tieneArchivo
                        ? $request->file('video_archivo')->store("videos/lecciones/{$leccion->id}", 'public')
                        : $request->input('video_url'),
                    'duracion' => (int) $request->input('duracion', 0),
                    'orden' => ((int) $leccion->videos()->max('orden')) + 1,
                ]);

                $mensaje = 'Video publicado correctamente.';
                break;
        }

        return back()->with('success', $mensaje);
    }

    public function destroyMaterial(Curso $curso, Modulo $modulo, Leccion $leccion, Material $material)
    {
        $this->resolveLesson($curso, $modulo, $leccion);

        if ((int) $material->leccion_id !== (int) $leccion->id) {
            abort(404);
        }

        if ($material->archivo) {
            Storage::disk('public')->delete($material->archivo);
        }

        $material->delete();

        return back()->with('success', 'Material eliminado.');
    }

    public function destroyVideo(Curso $curso, Modulo $modulo, Leccion $leccion, Video $video)
    {
        $this->resolveLesson($curso, $modulo, $leccion);

        if ((int) $video->leccion_id !== (int) $leccion->id) {
            abort(404);
        }

        if ($video->es_local) {
            Storage::disk('public')->delete($video->url);
        }

        $video->delete();

        return back()->with('success', 'Video eliminado.');
    }

    private function tipoPorExtension(string $extension): string
    {
        return match (strtolower($extension)) {
            'pdf' => 'pdf',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'imagen',
            'ppt', 'pptx' => 'presentacion',
            'doc', 'docx', 'txt', 'xls', 'xlsx' => 'documento',
            default => 'otro',
        };
    }
}
