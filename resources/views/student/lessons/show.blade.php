<x-student-layout
    :title="$leccion->titulo"
    :description="$leccion->modulo->titulo"
>

    <div class="mx-auto max-w-4xl">

        <div class="mb-6">
            <a
                href="{{ route('student.courses.show', $curso) }}"
                class="inline-flex items-center gap-2 text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Volver al curso
            </a>
        </div>

        <x-ui.card class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#2563EB]">
                {{ $curso->titulo }} › {{ $leccion->modulo->titulo }}
            </p>

            <h2 class="mt-1 text-xl font-bold text-slate-800">{{ $leccion->titulo }}</h2>

            @if ($leccion->descripcion)
                <p class="mt-2 text-sm text-slate-500">{{ $leccion->descripcion }}</p>
            @endif
        </x-ui.card>


        @foreach ($leccion->videos as $video)
            <div class="mb-6">
                <x-video-player :video="$video" />
            </div>
        @endforeach


        @if ($leccion->contenido)
            <x-ui.card class="mb-6">
                <div class="whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $leccion->contenido }}</div>
            </x-ui.card>
        @endif


        @if ($leccion->materiales->isNotEmpty())

            <h3 class="mb-3 text-base font-bold text-slate-800">Materiales de apoyo</h3>

            <div class="mb-6 space-y-3">
                @foreach ($leccion->materiales as $material)
                    <a
                        href="{{ $material->enlace }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm transition hover:border-blue-200 hover:shadow-md"
                    >
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-[#2563EB]">
                                <i class="{{ $material->icono }}"></i>
                            </div>

                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-800">{{ $material->titulo }}</p>
                                @if ($material->descripcion)
                                    <p class="truncate text-xs text-slate-500">{{ $material->descripcion }}</p>
                                @endif
                            </div>
                        </div>

                        <i class="fa-solid fa-arrow-up-right-from-square text-slate-400"></i>
                    </a>
                @endforeach
            </div>

        @endif


        @if ($leccion->videos->isEmpty() && ! $leccion->contenido && $leccion->materiales->isEmpty())
            <div class="mb-6 rounded-xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-500">
                Este tema todavía no tiene contenido.
            </div>
        @endif


        <div class="flex items-center justify-between gap-4">

            @if ($anterior)
                <a
                    href="{{ route('student.lessons.show', [$curso, $anterior]) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Anterior
                </a>
            @else
                <span></span>
            @endif

            @if ($siguiente)
                <a
                    href="{{ route('student.lessons.show', [$curso, $siguiente]) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1D4ED8]"
                >
                    Siguiente
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            @endif

        </div>

    </div>

</x-student-layout>
