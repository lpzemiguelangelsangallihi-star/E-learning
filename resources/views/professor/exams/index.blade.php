@php
    $input = 'block w-full rounded-lg border-slate-300 px-4 py-2.5 text-sm text-slate-800 focus:border-[#2563EB] focus:ring-[#2563EB]';
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
@endphp

<x-professor-layout title="Exámenes" description="Crea, publica y administra los exámenes de tus cursos.">

    <x-flash />

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Mis exámenes</h2>
            <p class="mt-1 text-sm text-slate-500">Cada examen pertenece a un tema de tus cursos.</p>
        </div>

        <div class="flex gap-2">
            <x-ui.button :href="route('professor.reports.index')" variant="secondary" icon="fa-solid fa-chart-column">Resultados</x-ui.button>
            <x-ui.button :href="route('professor.exams.create')" icon="fa-solid fa-plus">Nuevo examen</x-ui.button>
        </div>
    </div>

    <x-ui.card class="mb-6">
        <form method="GET" class="grid gap-4 md:grid-cols-4">
            <input type="search" name="q" value="{{ $buscar }}" placeholder="Buscar por título" class="{{ $input }} md:col-span-2" aria-label="Buscar">

            <select name="curso" class="{{ $input }}" aria-label="Curso">
                <option value="">Todos los cursos</option>
                @foreach ($cursos as $c)
                    <option value="{{ $c->id }}" @selected($cursoId === $c->id)>{{ $c->titulo }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <select name="estado" class="{{ $input }}" aria-label="Estado">
                    <option value="">Todos</option>
                    @foreach (['borrador' => 'Borrador', 'publicado' => 'Publicado', 'cerrado' => 'Cerrado'] as $v => $n)
                        <option value="{{ $v }}" @selected($estado === $v)>{{ $n }}</option>
                    @endforeach
                </select>
                <x-ui.button type="submit" variant="secondary" icon="fa-solid fa-magnifying-glass">Filtrar</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if ($examenes->isEmpty())
        <x-ui.card>
            <x-ui.empty icon="fa-solid fa-file-pen" title="Aún no tienes exámenes" text="Crea tu primer examen y agrégale preguntas.">
                <x-ui.button :href="route('professor.exams.create')" icon="fa-solid fa-plus">Crear examen</x-ui.button>
            </x-ui.empty>
        </x-ui.card>
    @else
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($examenes as $examen)
                @php
                    $badge = match ($examen->estado) {
                        'publicado' => 'success',
                        'cerrado' => 'danger',
                        default => 'warning',
                    };
                @endphp
                <x-ui.card hover>
                    <div class="flex h-full flex-col">
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-50 text-[#2563EB]">
                                <i class="fa-solid fa-file-pen" aria-hidden="true"></i>
                            </div>
                            <x-ui.badge :type="$badge">{{ ucfirst($examen->estado) }}</x-ui.badge>
                        </div>

                        <h3 class="text-base font-bold text-slate-800">{{ $examen->titulo }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $examen->leccion?->modulo?->curso?->titulo }} · {{ $examen->leccion?->titulo }}</p>

                        <div class="mt-3 space-y-1 text-xs text-slate-500">
                            <p><i class="fa-solid fa-circle-question mr-1" aria-hidden="true"></i>{{ $examen->preguntas_count }} pregunta(s)</p>
                            <p><i class="fa-regular fa-clock mr-1" aria-hidden="true"></i>{{ $examen->tiempo_limite ? $examen->tiempo_limite.' min' : 'Sin límite de tiempo' }}</p>
                            <p><i class="fa-solid fa-rotate mr-1" aria-hidden="true"></i>{{ $examen->intentos_permitidos }} intento(s) · {{ $examen->intentos_count }} realizados</p>
                            <p><i class="fa-solid fa-trophy mr-1" aria-hidden="true"></i>Nota máxima {{ $fmt($examen->puntaje_maximo) }}</p>
                        </div>

                        <div class="mt-5 flex gap-2">
                            <x-ui.button :href="route('professor.exams.edit', $examen)" class="flex-1" icon="fa-solid fa-pen-to-square">Editar</x-ui.button>

                            @if ($examen->intentos_count === 0)
                                <form method="POST" action="{{ route('professor.exams.destroy', $examen) }}"
                                    onsubmit="return confirm('¿Eliminar este examen y sus preguntas?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="danger" icon="fa-solid fa-trash" aria-label="Eliminar"></x-ui.button>
                                </form>
                            @endif
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        <div class="mt-6">{{ $examenes->links() }}</div>
    @endif

</x-professor-layout>
