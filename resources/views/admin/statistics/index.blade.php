<x-admin-layout title="Estadísticas" description="Indicadores administrativos, uso de la plataforma y rendimiento académico.">
    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('admin.statistics.index') }}" class="grid gap-4 sm:grid-cols-3 sm:items-end">
            <div>
                <label for="desde" class="mb-1 block text-sm font-medium text-slate-700">Desde</label>
                <input id="desde" name="desde" type="date" value="{{ $desde }}" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="hasta" class="mb-1 block text-sm font-medium text-slate-700">Hasta</label>
                <input id="hasta" name="hasta" type="date" value="{{ $hasta }}" min="{{ $desde }}" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex gap-2">
                <x-ui.button type="submit" icon="fa-solid fa-filter">Aplicar filtros</x-ui.button>
                <a href="{{ route('admin.statistics.index') }}" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Limpiar</a>
            </div>
        </form>
        <p class="mt-3 text-xs text-slate-500">El rango afecta inscripciones, evaluaciones, nuevos usuarios y actividad. Los totales generales muestran el estado actual del sistema.</p>
    </x-ui.card>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card title="Usuarios" :value="$stats['users']" icon="fa-solid fa-users" />
        <x-ui.stat-card title="Estudiantes" :value="$stats['students']" icon="fa-solid fa-user-graduate" type="success" />
        <x-ui.stat-card title="Docentes" :value="$stats['teachers']" icon="fa-solid fa-chalkboard-user" />
        <x-ui.stat-card title="Cursos" :value="$stats['courses']" icon="fa-solid fa-book-open" type="warning" />
        <x-ui.stat-card title="Inscripciones activas" :value="$stats['activeEnrollments']" icon="fa-solid fa-clipboard-list" type="success" />
        <x-ui.stat-card title="Evaluaciones" :value="$stats['evaluations']" icon="fa-solid fa-file-circle-check" />
        <x-ui.stat-card title="Nuevos usuarios" :value="$stats['newUsers']" icon="fa-solid fa-user-plus" description="En el periodo seleccionado" />
        <x-ui.stat-card title="Eventos de actividad" :value="$stats['activityEvents']" icon="fa-solid fa-clock-rotate-left" description="Registros visibles de bitácora" />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <x-ui.card>
            <h2 class="text-base font-bold text-slate-800">Usuarios por rol</h2>
            <p class="mb-5 mt-1 text-sm text-slate-500">Distribución actual de las cuentas registradas.</p>
            <div class="space-y-4">
                @forelse ($usersByRole as $role)
                    @php($percentage = round(($role->total / $totalUsers) * 100))
                    <div>
                        <div class="mb-1 flex justify-between text-sm"><span class="font-medium text-slate-700">{{ ucfirst($role->nombre) }}</span><span class="font-semibold text-slate-800">{{ $role->total }} ({{ $percentage }}%)</span></div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-blue-600" style="width: {{ $percentage }}%"></div></div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No hay usuarios registrados.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-base font-bold text-slate-800">Cursos por materia</h2>
            <p class="mb-5 mt-1 text-sm text-slate-500">Cantidad de cursos asociados a cada materia.</p>
            <div class="space-y-4">
                @forelse ($coursesBySubject as $subject)
                    @php($percentage = round(($subject->total / $totalCourses) * 100))
                    <div>
                        <div class="mb-1 flex justify-between text-sm"><span class="font-medium text-slate-700">{{ $subject->nombre }}</span><span class="font-semibold text-slate-800">{{ $subject->total }} ({{ $percentage }}%)</span></div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-indigo-500" style="width: {{ $percentage }}%"></div></div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No hay materias registradas.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <x-ui.card>
            <div class="mb-5 flex items-start justify-between gap-3"><div><h2 class="text-base font-bold text-slate-800">Progreso académico por curso</h2><p class="mt-1 text-sm text-slate-500">Promedio de avance registrado por estudiante.</p></div><span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ $stats['averageProgress'] }}% promedio general</span></div>
            <div class="space-y-4">
                @forelse ($progressByCourse as $course)
                    <div>
                        <div class="mb-1 flex items-center justify-between gap-3 text-sm"><span class="truncate font-medium text-slate-700">{{ $course->titulo }}</span><span class="shrink-0 font-semibold text-slate-800">{{ $course->promedio }}%</span></div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, max(0, $course->promedio)) }}%"></div></div>
                        <p class="mt-1 text-xs text-slate-500">{{ $course->estudiantes }} estudiantes con progreso · {{ $course->completados }} completaron</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Todavía no hay registros de progreso académico.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-base font-bold text-slate-800">Actividad reciente de la plataforma</h2>
            <p class="mb-4 mt-1 text-sm text-slate-500">Eventos registrados en la bitácora dentro del periodo.</p>
            <div class="divide-y divide-slate-100">
                @forelse ($recentActivity as $event)
                    <div class="py-3 first:pt-0"><p class="text-sm font-medium text-slate-800">{{ $event->accion }}</p><div class="mt-1 flex flex-wrap justify-between gap-2 text-xs text-slate-500"><span>{{ trim(($event->nombre ?? 'Usuario eliminado') . ' ' . ($event->apellido_paterno ?? '')) }}</span><time>{{ \Illuminate\Support\Carbon::parse($event->fecha)->format('d/m/Y H:i') }}</time></div></div>
                @empty
                    <p class="py-3 text-sm text-slate-500">No hay actividad registrada para este periodo.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-admin-layout>
