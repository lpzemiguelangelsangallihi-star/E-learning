<x-student-layout
    :title="$curso->titulo"
    description="Unidades y temas del curso."
>

    <div class="mx-auto max-w-4xl">

        <div class="mb-6">
            <a
                href="{{ route('student.dashboard') }}"
                class="inline-flex items-center gap-2 text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Volver a mis cursos
            </a>
        </div>

        <x-flash />

        <x-ui.card class="mb-6">

            <p class="text-xs font-semibold uppercase tracking-wide text-[#2563EB]">
                {{ $curso->materia?->nombre ?? 'Sin materia' }}
            </p>

            <h2 class="mt-1 text-xl font-bold text-slate-800">{{ $curso->titulo }}</h2>

            <div class="mt-3 flex flex-wrap gap-5 text-sm text-slate-500">
                <span><i class="fa-solid fa-chalkboard-user mr-1"></i>{{ $curso->profesor?->nombre_completo }}</span>
                <span><i class="fa-solid fa-signal mr-1"></i>{{ ucfirst($curso->nivel) }}</span>
                @if ($curso->duracion)
                    <span><i class="fa-regular fa-clock mr-1"></i>{{ $curso->duracion }} h</span>
                @endif
            </div>

            @if ($curso->descripcion)
                <p class="mt-4 whitespace-pre-line text-sm text-slate-600">{{ $curso->descripcion }}</p>
            @endif

        </x-ui.card>


        <div class="space-y-4">

            @forelse ($modulos as $modulo)

                <x-ui.card :padding="false">

                    <details open>

                        <summary class="flex cursor-pointer list-none items-start gap-4 px-5 py-4 sm:px-6">

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-sm font-bold text-[#2563EB]">
                                {{ $loop->iteration }}
                            </span>

                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-800">{{ $modulo->titulo }}</h3>

                                @if ($modulo->descripcion)
                                    <p class="mt-1 text-sm text-slate-500">{{ $modulo->descripcion }}</p>
                                @endif
                            </div>

                        </summary>

                        <div class="border-t border-slate-100 px-5 py-2 sm:px-6">

                            @forelse ($modulo->lecciones as $leccion)

                                <a
                                    href="{{ route('student.lessons.show', [$curso, $leccion]) }}"
                                    class="flex items-center justify-between gap-4 rounded-lg px-3 py-3 hover:bg-slate-50 {{ ! $loop->last ? 'border-b border-slate-100' : '' }}"
                                >
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="w-10 shrink-0 text-xs font-semibold text-slate-400">
                                            {{ $loop->parent->iteration }}.{{ $loop->iteration }}
                                        </span>

                                        <span class="truncate font-semibold text-slate-700">{{ $leccion->titulo }}</span>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-3 text-xs text-slate-500">
                                        @if ($leccion->videos_count)
                                            <span><i class="fa-solid fa-circle-play mr-1"></i>{{ $leccion->videos_count }}</span>
                                        @endif
                                        @if ($leccion->materiales_count)
                                            <span><i class="fa-solid fa-paperclip mr-1"></i>{{ $leccion->materiales_count }}</span>
                                        @endif
                                        <i class="fa-solid fa-chevron-right text-slate-300"></i>
                                    </div>
                                </a>

                            @empty

                                <p class="py-3 text-center text-sm text-slate-400">
                                    Esta unidad todavía no tiene temas publicados.
                                </p>

                            @endforelse

                        </div>

                    </details>

                </x-ui.card>

            @empty

                <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center text-sm text-slate-500">
                    El profesor todavía no publicó contenido en este curso.
                </div>

            @endforelse

        </div>

    </div>

</x-student-layout>
