<x-student-layout
    :title="$curso->titulo"
<<<<<<< HEAD
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

=======
    :description="$curso->materia?->nombre"
>

    <div class="mb-6">
        <a href="{{ route('student.courses.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            Volver a mis cursos
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Columna principal --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Encabezado --}}
            <x-ui.card>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($progreso?->estado === 'completado')
                        <x-ui.badge type="success">Completado</x-ui.badge>
                    @endif
                </div>

                <h2 class="mt-3 text-2xl font-bold text-slate-800">{{ $curso->titulo }}</h2>

                <p class="mt-3 whitespace-pre-line text-slate-600">{{ $curso->descripcion ?: 'Este curso aún no tiene descripción.' }}</p>

                @if ($curso->objetivos)
                    <div class="mt-5 rounded-lg bg-slate-50 p-4">
                        <h3 class="text-sm font-semibold text-slate-800">Objetivos</h3>
                        <p class="mt-1 whitespace-pre-line text-sm text-slate-600">{{ $curso->objetivos }}</p>
                    </div>
                @endif

                <x-ui.progress :value="$progreso->porcentaje ?? 0" class="mt-6" />

                @if ($siguiente)
                    <x-ui.button :href="route('student.lessons.show', [$curso, $siguiente])" icon="fa-solid fa-play" class="mt-5">
                        {{ ($progreso->lecciones_completadas ?? 0) > 0 ? 'Continuar con «' . \Illuminate\Support\Str::limit($siguiente->titulo, 30) . '»' : 'Empezar curso' }}
                    </x-ui.button>
                @endif
            </x-ui.card>


            {{-- Unidades y temas --}}
            <x-ui.card>
                <h2 class="mb-1 text-base font-bold text-slate-800">Contenido del curso</h2>
                <p class="mb-6 text-sm text-slate-500">{{ $modulos->count() }} {{ $modulos->count() === 1 ? 'unidad' : 'unidades' }}</p>

                <div class="space-y-4">
                    @forelse ($modulos as $modulo)
                        <div class="overflow-hidden rounded-lg border border-slate-200">

                            <div class="flex items-center gap-3 bg-slate-50 px-4 py-3">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#1E3A8A] text-xs font-bold text-white">
                                    {{ $loop->iteration }}
                                </span>

                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-semibold text-slate-800">{{ $modulo->titulo }}</h3>

                                    @if ($modulo->descripcion)
                                        <p class="truncate text-xs text-slate-500">{{ $modulo->descripcion }}</p>
                                    @endif
                                </div>
                            </div>

                            <ul class="divide-y divide-slate-100">
                                @forelse ($modulo->lecciones as $leccion)
                                    @php($hecha = in_array($leccion->id, $completadas, true))

                                    <li>
                                        <a href="{{ route('student.lessons.show', [$curso, $leccion]) }}" class="flex items-center gap-3 px-4 py-3 transition duration-150 hover:bg-blue-50">
                                            <i @class(['fa-solid text-lg', 'fa-circle-check text-[#16A34A]' => $hecha, 'fa-circle-play text-slate-300' => !$hecha]) aria-hidden="true"></i>

                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-sm font-medium text-slate-800">{{ $leccion->titulo }}</span>

                                                <span class="text-xs text-slate-500">
                                                    {{ $leccion->videos_count }} {{ $leccion->videos_count === 1 ? 'video' : 'videos' }}
                                                    · {{ $leccion->materiales_count }} {{ $leccion->materiales_count === 1 ? 'material' : 'materiales' }}
                                                </span>
                                            </span>

                                            <i class="fa-solid fa-chevron-right text-xs text-slate-400" aria-hidden="true"></i>
                                        </a>
                                    </li>
                                @empty
                                    <li class="px-4 py-3 text-sm text-slate-500">Esta unidad aún no tiene temas publicados.</li>
                                @endforelse
                            </ul>
                        </div>
                    @empty
                        <x-ui.empty icon="fa-solid fa-layer-group" title="El profesor aún no publicó contenido" text="Vuelve pronto." />
                    @endforelse
                </div>
            </x-ui.card>
        </div>


        {{-- Columna lateral --}}
        <div class="space-y-6">

            {{-- Profesor --}}
            <x-ui.card>
                <h2 class="mb-4 text-base font-bold text-slate-800">Tu profesor</h2>

                @if ($curso->profesor)
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[#1E3A8A] text-lg font-bold text-white">
                            {{ mb_strtoupper(mb_substr($curso->profesor->nombre, 0, 1)) }}{{ mb_strtoupper(mb_substr($curso->profesor->apellido_paterno, 0, 1)) }}
                        </div>

                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800">{{ $curso->profesor->nombre_completo }}</p>
                            <p class="truncate text-sm text-slate-500">{{ $curso->profesor->email }}</p>
                        </div>
                    </div>

                    <p class="mt-4 text-xs text-slate-500">
                        <i class="fa-solid fa-book-open mr-1" aria-hidden="true"></i>
                        {{ $cursosDelProfesor }} {{ $cursosDelProfesor === 1 ? 'curso publicado' : 'cursos publicados' }}
                    </p>
                @else
                    <p class="text-sm text-slate-500">Sin profesor asignado.</p>
                @endif
            </x-ui.card>


            {{-- Resumen --}}
            <x-ui.card>
                <h2 class="mb-4 text-base font-bold text-slate-800">Tu avance</h2>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Temas completados</dt>
                        <dd class="font-semibold text-slate-800">{{ $progreso->lecciones_completadas ?? 0 }} / {{ $modulos->sum(fn ($m) => $m->lecciones->count()) }}</dd>
                    </div>

                    @if ($progreso?->fecha_inicio)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Empezaste</dt>
                            <dd class="font-semibold text-slate-800">{{ $progreso->fecha_inicio->format('d/m/Y') }}</dd>
                        </div>
                    @endif

                    @if ($progreso?->fecha_finalizacion)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Terminaste</dt>
                            <dd class="font-semibold text-slate-800">{{ $progreso->fecha_finalizacion->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                </dl>
            </x-ui.card>

        </div>
>>>>>>> bb5ded3887008384a4b1ffae92a0fd0dc2279343
    </div>

</x-student-layout>
