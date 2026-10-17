@php
    // Solo se enlazan URLs http(s); evita esquemas como javascript:
    $esUrlSegura = fn (?string $url) => $url && \Illuminate\Support\Str::startsWith($url, ['http://', 'https://']);
@endphp

<x-student-layout
    :title="$leccion->titulo"
    :description="$leccion->modulo->titulo"
>

    <div class="mb-6">
        <a href="{{ route('student.courses.show', $curso) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            Volver a {{ $curso->titulo }}
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        <div class="space-y-6 lg:col-span-2">

            {{-- Contenido --}}
            <x-ui.card>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#2563EB]">{{ $leccion->modulo->titulo }}</p>

                    @if ($completado)
                        <x-ui.badge type="success"><i class="fa-solid fa-check mr-1" aria-hidden="true"></i>Completado</x-ui.badge>
                    @endif
                </div>

                <h2 class="mt-2 text-2xl font-bold text-slate-800">{{ $leccion->titulo }}</h2>

                @if ($leccion->descripcion)
                    <p class="mt-2 text-slate-500">{{ $leccion->descripcion }}</p>
                @endif

                @if ($leccion->contenido)
                    <div class="mt-6 whitespace-pre-line leading-relaxed text-slate-700">{{ $leccion->contenido }}</div>
                @elseif ($leccion->videos->isEmpty() && $leccion->materiales->isEmpty())
                    <x-ui.empty icon="fa-regular fa-file-lines" title="Este tema aún no tiene contenido" />
                @endif
            </x-ui.card>


            {{-- Videos --}}
@if ($leccion->videos->isNotEmpty())
    <x-ui.card>
        <h2 class="mb-4 text-base font-bold text-slate-800">
            Videos
        </h2>

        <div class="space-y-4">
            @foreach ($leccion->videos as $video)
                <x-video-player :video="$video" />
            @endforeach
        </div>
    </x-ui.card>
@endif

            {{-- Materiales --}}
            @if ($leccion->materiales->isNotEmpty())
                <x-ui.card>
                    <h2 class="mb-4 text-base font-bold text-slate-800">Materiales</h2>

                    <ul class="space-y-3">
                        @foreach ($leccion->materiales as $material)
                            @php
                                $icon = match ($material->tipo) {
                                    'pdf'          => 'fa-solid fa-file-pdf',
                                    'imagen'       => 'fa-solid fa-image',
                                    'presentacion' => 'fa-solid fa-file-powerpoint',
                                    'documento'    => 'fa-solid fa-file-word',
                                    'enlace'       => 'fa-solid fa-link',
                                    default        => 'fa-solid fa-paperclip',
                                };

                                $enlace = $esUrlSegura($material->url)
                                    ? $material->url
                                    : ($material->archivo ? asset('storage/' . ltrim($material->archivo, '/')) : null);
                            @endphp

                            <li class="flex items-center gap-4 rounded-lg border border-slate-200 p-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-[#2563EB]">
                                    <i class="{{ $icon }}" aria-hidden="true"></i>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold text-slate-800">{{ $material->titulo }}</p>

                                    @if ($material->descripcion)
                                        <p class="truncate text-xs text-slate-500">{{ $material->descripcion }}</p>
                                    @endif
                                </div>

                                @if ($enlace)
                                    <x-ui.button :href="$enlace" target="_blank" rel="noopener noreferrer" variant="secondary" icon="fa-solid fa-download">
                                        Abrir
                                    </x-ui.button>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            @endif


            {{-- Navegación --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
                @if ($anterior)
                    <x-ui.button :href="route('student.lessons.show', [$curso, $anterior])" variant="secondary" icon="fa-solid fa-arrow-left">
                        Anterior
                    </x-ui.button>
                @else
                    <span></span>
                @endif

                @if ($siguiente)
                    <x-ui.button :href="route('student.lessons.show', [$curso, $siguiente])">
                        Siguiente <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </x-ui.button>
                @endif
            </div>
        </div>


        {{-- Lateral --}}
        <div class="space-y-6">

            <x-ui.card>
                <h2 class="mb-4 text-base font-bold text-slate-800">Tu progreso en este tema</h2>

                @if ($completado)
                    <x-ui.alert type="success">Ya completaste este tema.</x-ui.alert>
                @else
                    <form method="POST" action="{{ route('student.lessons.complete', [$curso, $leccion]) }}">
                        @csrf

                        <x-ui.button type="submit" variant="success" icon="fa-solid fa-check" class="w-full">
                            Marcar como completado
                        </x-ui.button>
                    </form>

                    <p class="mt-3 text-xs text-slate-500">Ganas 10 XP y avanzas en el curso.</p>
                @endif
            </x-ui.card>


            @if ($leccion->tareas->isNotEmpty() || $leccion->examenes->isNotEmpty())
                <x-ui.card>
                    <h2 class="mb-4 text-base font-bold text-slate-800">Evaluaciones</h2>

                    <ul class="space-y-3">
                        @foreach ($leccion->tareas as $tarea)
                            @php($entrega = $entregas[$tarea->id] ?? null)

                            <li class="rounded-lg border border-slate-200 p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <x-ui.badge type="warning">Tarea</x-ui.badge>

                                    @if ($entrega)
                                        <x-ui.badge :type="$entrega->estado === 'calificada' ? 'success' : 'primary'">
                                            {{ $entrega->estado === 'calificada' ? 'Calificada' : 'Entregada' }}
                                        </x-ui.badge>
                                    @else
                                        <x-ui.badge type="warning">Pendiente</x-ui.badge>
                                    @endif
                                </div>

                                <p class="mt-2 text-sm font-semibold text-slate-800">{{ $tarea->titulo }}</p>

                                @if ($tarea->fecha_limite)
                                    <p class="text-xs text-slate-500">Vence: {{ $tarea->fecha_limite->format('d/m/Y H:i') }}</p>
                                @endif
                            </li>
                        @endforeach

                        @foreach ($leccion->examenes as $examen)
                            @php($intento = $intentos[$examen->id] ?? null)

                            <li class="rounded-lg border border-slate-200 p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <x-ui.badge type="primary">Examen</x-ui.badge>

                                    <span class="text-xs text-slate-500">{{ $intento->total ?? 0 }} / {{ $examen->intentos_permitidos }} intentos</span>
                                </div>

                                <p class="mt-2 text-sm font-semibold text-slate-800">{{ $examen->titulo }}</p>

                                @if ($intento && $intento->mejor !== null)
                                    <p class="text-xs text-slate-500">Mejor nota: {{ rtrim(rtrim(number_format($intento->mejor, 2), '0'), '.') }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            @endif

        </div>
    </div>

</x-student-layout>
