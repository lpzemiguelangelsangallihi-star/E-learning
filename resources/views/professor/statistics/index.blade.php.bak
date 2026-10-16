<x-professor-layout
    title="Estadísticas"
    description="Resumen de la actividad de tus cursos y estudiantes."
>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-ui.stat-card title="Mis cursos" :value="$stats['courses']" icon="fa-solid fa-book-open" />
        <x-ui.stat-card title="Publicados" :value="$stats['published']" icon="fa-solid fa-eye" type="success" />
        <x-ui.stat-card title="Estudiantes activos" :value="$stats['students']" icon="fa-solid fa-user-graduate" type="success" description="Estudiantes únicos en tus cursos" />
        <x-ui.stat-card title="Inscripciones" :value="$stats['enrollments']" icon="fa-solid fa-clipboard-list" />
        <x-ui.stat-card title="Finalizadas" :value="$stats['finished']" icon="fa-solid fa-flag-checkered" type="warning" />
        <x-ui.stat-card title="Evaluaciones" :value="$stats['evaluations']" icon="fa-solid fa-file-circle-check" />
    </div>


    <div class="mt-6 grid gap-6 xl:grid-cols-2">

        {{-- Estudiantes por curso --}}
        <x-ui.card>
            <div class="mb-6">
                <h2 class="text-base font-bold text-slate-800">Estudiantes por curso</h2>
                <p class="mt-1 text-sm text-slate-500">Estudiantes activos en tus cursos con más inscritos.</p>
            </div>

            @php $maxStudents = max($studentsByCourse->max('total') ?? 0, 1); @endphp

            <div class="space-y-5">
                @forelse ($studentsByCourse as $row)
                    <div>
                        <div class="mb-2 flex items-center justify-between gap-4">
                            <a href="{{ route('professor.courses.students', $row->id) }}"
                               class="truncate text-sm font-medium text-slate-700 hover:text-[#2563EB]">
                                {{ $row->titulo }}
                            </a>
                            <span class="text-sm font-semibold text-slate-800">{{ $row->total }}</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-[#2563EB]"
                                 style="width: {{ round(($row->total / $maxStudents) * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Aún no tienes cursos.</p>
                @endforelse
            </div>
        </x-ui.card>


        {{-- Cursos por materia --}}
        <x-ui.card>
            <div class="mb-6">
                <h2 class="text-base font-bold text-slate-800">Cursos por materia</h2>
                <p class="mt-1 text-sm text-slate-500">Distribución de tus cursos entre materias.</p>
            </div>

            @php $totalCourses = max($stats['courses'], 1); @endphp

            <div class="space-y-5">
                @forelse ($coursesBySubject as $row)
                    @php $pct = round(($row->total / $totalCourses) * 100); @endphp
                    <div>
                        <div class="mb-2 flex items-center justify-between gap-4">
                            <span class="text-sm font-medium text-slate-700">{{ $row->nombre }}</span>
                            <span class="text-sm font-semibold text-slate-800">{{ $row->total }}</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-[#2563EB]" style="width: {{ $pct }}%"></div>
                        </div>
                        <p class="mt-1.5 text-right text-xs text-slate-400">{{ $pct }}%</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Sin datos todavía.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>


    {{-- Estado y tipo --}}
    <x-ui.card class="mt-6">
        <div class="mb-6">
            <h2 class="text-base font-bold text-slate-800">Estado y tipo de cursos</h2>
            <p class="mt-1 text-sm text-slate-500">Visibilidad y modalidad de tus cursos.</p>
        </div>

        @php
            $cards = [
                ['Publicados', $coursesByStatus['publicado'] ?? 0, 'fa-solid fa-eye', 'bg-green-50 text-green-700 border-green-200'],
                ['Borradores', $coursesByStatus['borrador'] ?? 0, 'fa-solid fa-pen-ruler', 'bg-yellow-50 text-yellow-700 border-yellow-200'],
                ['Ocultos', $coursesByStatus['oculto'] ?? 0, 'fa-solid fa-eye-slash', 'bg-red-50 text-red-700 border-red-200'],
                ['Gratuitos', $coursesByType['gratuito'] ?? 0, 'fa-solid fa-gift', 'bg-blue-50 text-blue-700 border-blue-200'],
                ['De pago', $coursesByType['pago'] ?? 0, 'fa-solid fa-tag', 'bg-slate-50 text-slate-700 border-slate-200'],
            ];
        @endphp

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($cards as [$label, $total, $icon, $classes])
                <div class="rounded-xl border p-5 {{ $classes }}">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold">{{ $label }}</p>
                            <p class="mt-2 text-3xl font-bold">{{ $total }}</p>
                        </div>
                        <i class="{{ $icon }} text-2xl"></i>
                    </div>
                </div>
            @endforeach
        </div>
    </x-ui.card>

</x-professor-layout>
