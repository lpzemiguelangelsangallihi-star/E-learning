@php
    $input = 'block w-full rounded-lg border-slate-300 px-4 py-2.5 text-sm text-slate-800 focus:border-[#2563EB] focus:ring-[#2563EB]';
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
@endphp

<x-professor-layout title="Tareas" description="Crea tareas, recibe entregas y califícalas con retroalimentación.">

    <x-flash />

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Mis tareas</h2>
            <p class="mt-1 text-sm text-slate-500">Publica una tarea para que tus estudiantes puedan entregarla.</p>
        </div>

        <x-ui.button :href="route('professor.tasks.create')" icon="fa-solid fa-plus">Nueva tarea</x-ui.button>
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
                    <option value="">Todas</option>
                    @foreach (['borrador' => 'Borrador', 'publicada' => 'Publicada', 'cerrada' => 'Cerrada'] as $v => $n)
                        <option value="{{ $v }}" @selected($estado === $v)>{{ $n }}</option>
                    @endforeach
                </select>
                <x-ui.button type="submit" variant="secondary" icon="fa-solid fa-magnifying-glass">Filtrar</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if ($tareas->isEmpty())
        <x-ui.card>
            <x-ui.empty icon="fa-solid fa-clipboard-list" title="Aún no tienes tareas" text="Crea la primera tarea de tus cursos.">
                <x-ui.button :href="route('professor.tasks.create')" icon="fa-solid fa-plus">Crear tarea</x-ui.button>
            </x-ui.empty>
        </x-ui.card>
    @else
        <div class="space-y-4">
            @foreach ($tareas as $tarea)
                @php
                    $badge = match ($tarea->estado) {
                        'publicada' => 'success',
                        'cerrada' => 'danger',
                        default => 'warning',
                    };
                @endphp

                <x-ui.card>
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-ui.badge :type="$badge">{{ ucfirst($tarea->estado) }}</x-ui.badge>
                                <span class="text-xs text-slate-500">{{ $tarea->leccion?->modulo?->curso?->titulo }} · {{ $tarea->leccion?->titulo }}</span>
                            </div>

                            <h3 class="mt-1 text-base font-bold text-slate-800">{{ $tarea->titulo }}</h3>

                            <p class="mt-1 text-xs text-slate-500">
                                @if ($tarea->fecha_limite)
                                    <i class="fa-regular fa-calendar mr-1" aria-hidden="true"></i>Vence {{ $tarea->fecha_limite->format('d/m/Y H:i') }} ·
                                @endif
                                Puntaje {{ $fmt($tarea->puntaje_maximo) }} ·
                                {{ $tarea->entregas_count }} entrega(s), {{ $tarea->pendientes_count }} por calificar
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.button :href="route('professor.tasks.submissions', $tarea)" variant="secondary" icon="fa-solid fa-inbox">Entregas</x-ui.button>
                            <x-ui.button :href="route('professor.tasks.edit', $tarea)" variant="secondary" icon="fa-solid fa-pen">Editar</x-ui.button>

                            @if ($tarea->estado === 'borrador')
                                <form method="POST" action="{{ route('professor.tasks.status', $tarea) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="estado" value="publicada">
                                    <x-ui.button type="submit" variant="success" icon="fa-solid fa-upload">Publicar</x-ui.button>
                                </form>
                            @elseif ($tarea->estado === 'publicada')
                                <form method="POST" action="{{ route('professor.tasks.status', $tarea) }}" onsubmit="return confirm('¿Cerrar la tarea? Ya no se aceptarán entregas.')">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="estado" value="cerrada">
                                    <x-ui.button type="submit" variant="danger" icon="fa-solid fa-lock">Cerrar</x-ui.button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('professor.tasks.status', $tarea) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="estado" value="publicada">
                                    <x-ui.button type="submit" variant="secondary" icon="fa-solid fa-lock-open">Reabrir</x-ui.button>
                                </form>
                            @endif

                            @if ($tarea->entregas_count === 0)
                                <form method="POST" action="{{ route('professor.tasks.destroy', $tarea) }}" onsubmit="return confirm('¿Eliminar esta tarea?')">
                                    @csrf @method('DELETE')
                                    <x-ui.button type="submit" variant="danger" icon="fa-solid fa-trash" aria-label="Eliminar"></x-ui.button>
                                </form>
                            @endif
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        <div class="mt-6">{{ $tareas->links() }}</div>
    @endif

</x-professor-layout>
