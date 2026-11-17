@php
    $layout = match (auth()->user()->rol?->nombre) {
        'administrador' => 'admin-layout',
        'profesor' => 'professor-layout',
        'estudiante' => 'student-layout',
        default => 'student-layout',
    };
@endphp

<x-dynamic-component :component="$layout" title="Notificaciones" description="Avisos y novedades de tu cuenta">
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Tu bandeja de notificaciones</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Tienes {{ $noLeidas }} {{ $noLeidas === 1 ? 'aviso sin leer' : 'avisos sin leer' }}.
                </p>
            </div>

            <form method="GET" action="{{ route('notificaciones.index') }}" class="flex items-center gap-2">
                <label for="filtro" class="text-sm font-medium text-slate-600">Mostrar:</label>
                <select
                    id="filtro"
                    name="filtro"
                    onchange="this.form.submit()"
                    class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="todas" @selected($filtro === 'todas')>Todas</option>
                    <option value="no_leidas" @selected($filtro === 'no_leidas')>No leídas</option>
                    <option value="leidas" @selected($filtro === 'leidas')>Leídas</option>
                </select>
            </form>
        </div>

        @if (session('success'))
            <div role="status" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @forelse ($notificaciones as $notificacion)
                <article class="flex flex-col gap-4 border-b border-slate-100 p-5 last:border-b-0 sm:flex-row sm:items-start {{ $notificacion->leida ? 'bg-white' : 'bg-blue-50/50' }}">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full {{ $notificacion->leida ? 'bg-slate-100 text-slate-500' : 'bg-blue-100 text-blue-700' }}">
                        <i class="fa-solid fa-bell" aria-hidden="true"></i>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-semibold text-slate-800">{{ $notificacion->titulo }}</h3>
                            @if (! $notificacion->leida)
                                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">Nueva</span>
                            @endif
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                                {{ ucfirst($notificacion->tipo) }}
                            </span>
                        </div>

                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $notificacion->mensaje }}</p>

                        @if ($notificacion->tarea && $notificacion->tarea->estado === 'publicada')
                            <a href="{{ route('student.evaluations.task.show', $notificacion->tarea) }}" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:text-blue-900">
                                Ver tarea <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </a>
                        @endif

                        <p class="mt-3 text-xs text-slate-400">
                            {{ $notificacion->creado_en?->format('d/m/Y H:i') ?? 'Fecha no disponible' }}
                        </p>
                    </div>

                    @if (! $notificacion->leida)
                        <form method="POST" action="{{ route('notificaciones.leida', $notificacion->id) }}">
                            @csrf
                            @method('PATCH')
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-lg border border-blue-200 px-3 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100"
                            >
                                <i class="fa-solid fa-check" aria-hidden="true"></i>
                                Marcar como leída
                            </button>
                        </form>
                    @else
                        <span class="inline-flex items-center gap-2 text-sm text-slate-400">
                            <i class="fa-solid fa-check-double" aria-hidden="true"></i>
                            Leída
                        </span>
                    @endif
                </article>
            @empty
                <div class="px-6 py-14 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <i class="fa-regular fa-bell-slash text-xl" aria-hidden="true"></i>
                    </div>
                    <h3 class="mt-4 font-semibold text-slate-800">No hay notificaciones</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        @if ($filtro === 'no_leidas')
                            No tienes avisos pendientes por leer.
                        @elseif ($filtro === 'leidas')
                            Todavía no tienes avisos leídos.
                        @else
                            Cuando recibas un aviso, aparecerá aquí.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        @if ($notificaciones->hasPages())
            <div>{{ $notificaciones->links() }}</div>
        @endif
    </div>
</x-dynamic-component>
