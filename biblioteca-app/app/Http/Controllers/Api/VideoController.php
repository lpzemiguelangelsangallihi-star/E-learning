<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VideoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        return response()->json(Video::query()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'leccion_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
            'url' => ['sometimes', 'string'],
            'duracion' => ['sometimes', 'numeric'],
            'orden' => ['sometimes', 'numeric'],
        ]);

        $video = Video::create($validated);

        return response()->json($video, 201);
    }

    public function show(Video $video): JsonResponse
    {
        return response()->json($video);
    }

    public function update(Request $request, Video $video): JsonResponse
    {
        $validated = $request->validate([
            'leccion_id' => ['sometimes', 'integer'],
            'titulo' => ['sometimes', 'string'],
            'descripcion' => ['sometimes', 'string'],
            'url' => ['sometimes', 'string'],
            'duracion' => ['sometimes', 'numeric'],
            'orden' => ['sometimes', 'numeric'],
        ]);

        $video->update($validated);

        return response()->json($video);
    }

    public function destroy(Video $video): JsonResponse
    {
        $video->delete();

        return response()->json(null, 204);
    }
}
