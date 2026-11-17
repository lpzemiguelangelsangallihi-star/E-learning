<x-professor-layout title="Estadísticas" description="Seguimiento de tus cursos, estudiantes, progreso y resultados académicos.">
    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('professor.statistics.index') }}" class="grid gap-4 sm:grid-cols-4 sm:items-end">
            <div class="sm:col-span-2"><label for="curso_id" class="mb-1 block text-sm font-medium text-slate-700">Curso</label><select id="curso_id" name="curso_id" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"><option value="">Todos mis cursos</option>@foreach ($courses as $course)<option value="{{ $course->id }}" @selected((string) $cursoId === (string) $course->id)>{{ $course->titulo }}</option>@endforeach</select></div>
            <div><label for="desde" class="mb-1 block text-sm font-medium text-slate-700">Desde</label><input id="desde" name="desde" type="date" value="{{ $desde }}" class="w-full rounded-lg border-slate-300 text-sm shadow-sm"></div>
            <div><label for="hasta" class="mb-1 block text-sm font-medium text-slate-700">Hasta</label><input id="hasta" name="hasta" type="date" value="{{ $hasta }}" min="{{ $desde }}" class="w-full rounded-lg border-slate-300 text-sm shadow-sm"></div>
            <div class="flex flex-wrap gap-2 sm:col-span-4"><x-ui.button type="submit" icon="fa-solid fa-filter">Aplicar filtros</x-ui.button><a href="{{ route('professor.statistics.index') }}" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Limpiar</a></div>
        </form>
        <p class="mt-3 text-xs text-slate-500">Los filtros de fecha se aplican a inscripciones, actividad y resultados de evaluaciones. El progreso representa el estado actual registrado.</p>
    </x-ui.card>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card title="Mis cursos" :value="$stats['courses']" icon="fa-solid fa-book-open" />
        <x-ui.stat-card title="Cursos publicados" :value="$stats['published']" icon="fa-solid fa-eye" type="success" />
        <x-ui.stat-card title="Estudiantes activos" :value="$stats['students']" icon="fa-solid fa-user-graduate" />
        <x-ui.stat-card title="Inscripciones" :value="$stats['enrollments']" icon="fa-solid fa-clipboard-list" />
        <x-ui.stat-card title="Evaluaciones" :value="$stats['evaluations']" icon="fa-solid fa-file-circle-check" />
        <x-ui.stat-card title="Progreso promedio" :value="$stats['averageProgress'] . '%'" icon="fa-solid fa-chart-line" type="success" />
        <x-ui.stat-card title="Estudiantes completaron" :value="$stats['completedStudents']" icon="fa-solid fa-circle-check" type="success" />
        <x-ui.stat-card title="En progreso" :value="$stats['inProgressStudents']" icon="fa-solid fa-spinner" type="warning" />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <x-ui.card>
            <h2 class="text-base font-bold text-slate-800">Progreso y estudiantes por curso</h2><p class="mb-5 mt-1 text-sm text-slate-500">Inscripciones activas y avance académico actual.</p>
            <div class="space-y-5">
                @forelse ($studentsByCourse as $course)
                    <div><div class="mb-1 flex items-center justify-between gap-3 text-sm"><span class="truncate font-medium text-slate-700">{{ $course->titulo }}</span><span class="shrink-0 font-semibold text-slate-800">{{ $course->progreso }}%</span></div><div class="h-2.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, max(0, $course->progreso)) }}%"></div></div><p class="mt-1 text-xs text-slate-500">{{ $course->total }} estudiantes activos · {{ $course->completados }} completaron</p></div>
                @empty
                    <p class="text-sm text-slate-500">No hay cursos para mostrar.</p>
                @endforelse
            </div>
        </x-ui.card>
        <x-ui.card>
            <h2 class="text-base font-bold text-slate-800">Resultados de evaluaciones por curso</h2><p class="mb-5 mt-1 text-sm text-slate-500">Promedio porcentual calculado sobre intentos finalizados.</p>
            <div class="space-y-4">
                @forelse ($results as $result)
                    <div><div class="mb-1 flex justify-between gap-3 text-sm"><span class="font-medium text-slate-700">{{ $result->titulo }}</span><span class="font-semibold text-slate-800">{{ $result->promedio !== null ? $result->promedio . '%' : 'Sin notas' }}</span></div><div class="h-2.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-indigo-500" style="width: {{ min(100, max(0, (float) $result->promedio)) }}%"></div></div><p class="mt-1 text-xs text-slate-500">{{ $result->intentos }} intentos finalizados</p></div>
                @empty
                    <p class="text-sm text-slate-500">Todavía no hay intentos finalizados para los filtros seleccionados.</p>
                @endforelse
            </div>
            <a href="{{ route('professor.reports.index') }}" class="mt-5 inline-flex text-sm font-semibold text-blue-700 hover:text-blue-900">Ver reportes detallados <i class="fa-solid fa-arrow-right ml-2" aria-hidden="true"></i></a>
        </x-ui.card>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <x-ui.card>
            <h2 class="text-base font-bold text-slate-800">Cursos por estado</h2><div class="mt-4 space-y-3">
                @forelse ($coursesByStatus as $status => $total)
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3"><span class="text-sm font-medium text-slate-700">{{ ucfirst(str_replace('_', ' ', $status)) }}</span><span class="font-bold text-slate-900">{{ $total }}</span></div>
                @empty
                    <p class="text-sm text-slate-500">No hay estados disponibles.</p>
                @endforelse
            </div>
        </x-ui.card>
        <x-ui.card>
            <h2 class="text-base font-bold text-slate-800">Actividad reciente de estudiantes inscritos</h2><p class="mb-4 mt-1 text-sm text-slate-500">Acciones visibles de usuarios inscritos en tus cursos, según la bitácora.</p>
            <div class="divide-y divide-slate-100">
                @forelse ($activity as $event)
                    <div class="py-3 first:pt-0"><p class="text-sm font-medium text-slate-800">{{ $event->accion }}</p><div class="mt-1 flex justify-between gap-2 text-xs text-slate-500"><span>{{ trim($event->nombre . ' ' . ($event->apellido_paterno ?? '')) }}</span><time>{{ \Illuminate\Support\Carbon::parse($event->fecha)->format('d/m/Y H:i') }}</time></div></div>
                @empty
                    <p class="py-3 text-sm text-slate-500">No hay actividad disponible para los filtros seleccionados.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-professor-layout>
