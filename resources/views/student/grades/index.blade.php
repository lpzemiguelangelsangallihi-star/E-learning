@php
    $aprobacion = \App\Models\Calificacion::NOTA_APROBACION;

    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');

    $tipoNota = fn ($n) => match (true) {
        $n >= 71          => 'success',
        $n >= $aprobacion => 'warning',
        default           => 'danger',
    };
@endphp

<x-student-layout
    title="Calificaciones"
    :description="'Tus notas por curso (escala 0–100, aprobación desde ' . $aprobacion . ').'"
>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-ui.stat-card title="Promedio general" :value="$promedioGeneral ?? '—'" icon="fa-solid fa-star" :type="$promedioGeneral !== null ? $tipoNota($promedioGeneral) : 'primary'" />
        <x-ui.stat-card title="Calificaciones" :value="$calificaciones->count()" icon="fa-solid fa-list-check" />
        <x-ui.stat-card title="Cursos con notas" :value="$porCurso->count()" icon="fa-solid fa-book-open" type="success" />
    </div>


    <div class="mt-6 space-y-6">
        @forelse ($porCurso as $grupo)
            <x-ui.card :padding="false">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">{{ $grupo['curso']?->titulo ?? 'Curso eliminado' }}</h2>
                        <p class="text-sm text-slate-500">{{ $grupo['items']->count() }} {{ $grupo['items']->count() === 1 ? 'calificación' : 'calificaciones' }}</p>
                    </div>

                    <div class="text-right">
                        <p class="text-xs text-slate-500">Promedio</p>
                        <x-ui.badge :type="$tipoNota($grupo['promedio'])" class="text-sm">{{ $fmt($grupo['promedio']) }}</x-ui.badge>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Evaluación</th>
                                <th class="px-6 py-3">Tipo</th>
                                <th class="px-6 py-3">Fecha</th>
                                <th class="px-6 py-3">Comentario del profesor</th>
                                <th class="px-6 py-3 text-right">Nota</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @foreach ($grupo['items'] as $nota)
                                <tr>
                                    <td class="px-6 py-4 font-semibold text-slate-800">
                                        {{ $nota->tarea?->titulo ?? $nota->examen?->titulo ?? 'Actividad' }}
                                    </td>

                                    <td class="px-6 py-4"><x-ui.badge :type="$nota->tipo === 'examen' ? 'primary' : 'default'">{{ ucfirst($nota->tipo) }}</x-ui.badge></td>

                                    <td class="whitespace-nowrap px-6 py-4 text-slate-600">{{ $nota->fecha?->format('d/m/Y') }}</td>

                                    <td class="max-w-xs px-6 py-4 text-slate-600">{{ $nota->retroalimentacion ?: '—' }}</td>

                                    <td class="px-6 py-4 text-right"><x-ui.badge :type="$tipoNota($nota->calificacion)">{{ $fmt($nota->calificacion) }}</x-ui.badge></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </x-ui.card>
        @empty
            <x-ui.card>
                <x-ui.empty
                    icon="fa-solid fa-star"
                    title="Todavía no tienes calificaciones"
                    text="Tus notas aparecerán aquí cuando tus profesores califiquen tus evaluaciones."
                />
            </x-ui.card>
        @endforelse
    </div>

</x-student-layout>
