@props(['video'])

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

    @if ($video->embed_url)

        <div class="aspect-video w-full bg-black">
            <iframe
                src="{{ $video->embed_url }}"
                title="{{ $video->titulo }}"
                class="h-full w-full"
                loading="lazy"
                allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                allowfullscreen
            ></iframe>
        </div>

    @elseif ($video->es_local || $video->es_archivo_directo)

        <video
            controls
            preload="metadata"
            class="aspect-video w-full bg-black"
            src="{{ $video->es_local ? $video->archivo_url : $video->url }}"
        >
            Tu navegador no puede reproducir este video.
        </video>

    @else

        <a
            href="{{ $video->url }}"
            target="_blank"
            rel="noopener noreferrer"
            class="flex aspect-video w-full items-center justify-center gap-2 bg-slate-100 text-sm font-semibold text-[#2563EB] hover:bg-slate-200"
        >
            <i class="fa-solid fa-circle-play text-lg"></i>
            Abrir video en una pestaña nueva
        </a>

    @endif

    <div class="px-4 py-3">
        <p class="font-semibold text-slate-800">{{ $video->titulo }}</p>

        @if ($video->descripcion)
            <p class="mt-1 text-sm text-slate-500">{{ $video->descripcion }}</p>
        @endif

        @if ($video->duracion)
            <p class="mt-1 text-xs text-slate-400">
                <i class="fa-regular fa-clock mr-1"></i>{{ $video->duracion }} min
            </p>
        @endif
    </div>

</div>
