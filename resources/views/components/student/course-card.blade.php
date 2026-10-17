@props([
    'curso',
    'progreso' => null,
    'inscripcion' => null,
    'motivo' => null,
])

@php
    $materia = mb_strtolower($curso->materia?->nombre ?? '');

    $icon = match ($materia) {
        'matemáticas', 'matematicas' => 'fa-solid fa-calculator',
        'física', 'fisica'           => 'fa-solid fa-atom',
        'química', 'quimica'         => 'fa-solid fa-flask',
        default                      => 'fa-solid fa-book-open',
    };

    $porcentaje = (float) ($progreso->porcentaje ?? 0);
    $finalizado = $inscripcion?->estado === 'finalizado';
@endphp

<x-ui.card hover class="h-full">
    <div class="flex h-full flex-col">

        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-50 text-[#2563EB]">
                <i class="{{ $icon }}" aria-hidden="true"></i>
            </div>

            @if ($finalizado)
                <x-ui.badge type="success">Completado</x-ui.badge>
            @elseif ($inscripcion)
                <x-ui.badge type="primary">En curso</x-ui.badge>
            @endif
        </div>

        <div class="flex-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#2563EB]">
                {{ $curso->materia?->nombre ?? 'Sin materia' }}
            </p>

            <h3 class="mt-2 text-lg font-bold text-slate-800">{{ $curso->titulo }}</h3>

            @if ($curso->profesor)
                <p class="mt-1 text-sm text-slate-500">
                    <i class="fa-solid fa-chalkboard-user mr-1" aria-hidden="true"></i>
                    {{ $curso->profesor->nombre_completo }}
                </p>
            @endif

            <p class="mt-2 line-clamp-3 text-sm text-slate-500">
                {{ $curso->descripcion ?: 'Sin descripción.' }}
            </p>

            <div class="mt-4 flex flex-wrap gap-4 text-xs text-slate-500">

                @isset($curso->estudiantes_count)
                    <span><i class="fa-solid fa-user-graduate mr-1" aria-hidden="true"></i>{{ $curso->estudiantes_count }}</span>
                @endisset
            </div>

            @if ($motivo)
                <p class="mt-4 rounded-lg bg-blue-50 px-3 py-2 text-xs font-medium text-blue-700">
                    <i class="fa-solid fa-lightbulb mr-1" aria-hidden="true"></i>{{ $motivo }}
                </p>
            @endif
        </div>

        @if ($inscripcion)
            <x-ui.progress :value="$porcentaje" class="mt-5" />

            <x-ui.button :href="route('student.courses.show', $curso)" class="mt-4 w-full"
                :variant="$finalizado ? 'secondary' : 'primary'">
                {{ $finalizado ? 'Repasar curso' : ($porcentaje > 0 ? 'Continuar' : 'Empezar') }}
            </x-ui.button>
        @else
            <x-ui.button :href="route('student.courses.join')" variant="secondary" icon="fa-solid fa-key" class="mt-5 w-full">
                Inscribirme con código
            </x-ui.button>
        @endif

    </div>
</x-ui.card>
