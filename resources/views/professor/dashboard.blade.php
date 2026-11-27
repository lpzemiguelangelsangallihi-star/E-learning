<x-professor-layout
    title="Dashboard"
    description="Resumen general de tus cursos y estudiantes."
>

    @php
        $hora = now()->hour;
        $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
        $borradores = max(0, $stats['courses'] - $stats['published']);
    @endphp

    {{-- Bienvenida --}}
    <x-dashboard.hero
        class="mb-6"
        :eyebrow="now()->translatedFormat('l d \d\e F')"
        :title="$saludo . ', ' . auth()->user()->nombre"
        :subtitle="$stats['students'] > 0
            ? 'Tienes ' . $stats['students'] . ' ' . ($stats['students'] === 1 ? 'estudiante activo' : 'estudiantes activos') . ' en tus cursos.'
            : 'Crea tu primer curso y comparte el código de clase con tus estudiantes.'"
        sabio="¡Tus estudiantes te esperan, profe!"
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('professor.courses.create')"
                icon="fa-solid fa-plus"
                class="el-shine !bg-white !text-[#1E3A8A] hover:!bg-blue-50"
            >
                Nuevo curso
            </x-ui.button>

            <x-ui.button
                :href="route('professor.statistics.index')"
                icon="fa-solid fa-chart-column"
                class="!bg-white/10 !text-white ring-1 ring-white/25 hover:!bg-white/20"
            >
                Estadísticas
            </x-ui.button>
        </x-slot:actions>
    </x-dashboard.hero>

    {{-- 6 columnas: 3 + 2 tarjetas anchas, sin huecos en ningún ancho --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">

        <div class="lg:col-span-2">
            <x-ui.stat-card
                title="Mis cursos"
                :value="$stats['courses']"
                icon="fa-solid fa-book-open"
            />
        </div>

        <div class="lg:col-span-2">
            <x-ui.stat-card
                title="Publicados"
                :value="$stats['published']"
                icon="fa-solid fa-eye"
                type="success"
            />
        </div>

        <div class="lg:col-span-2">
            <x-ui.stat-card
                title="Estudiantes"
                :value="$stats['students']"
                icon="fa-solid fa-user-graduate"
            />
        </div>

        <div class="lg:col-span-3">
            <x-ui.stat-card
                title="Evaluaciones"
                :value="$stats['evaluations']"
                icon="fa-solid fa-file-circle-check"
                type="warning"
            />
        </div>

        <div class="sm:col-span-2 lg:col-span-3">
            <x-ui.stat-card
                title="Estudiantes que completaron"
                :value="$stats['completedStudents']"
                icon="fa-solid fa-circle-check"
                type="success"
            />
        </div>

    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">

        {{-- Entregas por calificar --}}
        <x-ui.card>
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Entregas por calificar</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $totalPorCalificar > 0
                            ? $totalPorCalificar . ($totalPorCalificar === 1 ? ' entrega espera' : ' entregas esperan') . ' tu revisión.'
                            : 'Estás al día con las revisiones.' }}
                    </p>
                </div>

                @if ($totalPorCalificar > 0)
                    <x-ui.badge type="warning">{{ $totalPorCalificar }}</x-ui.badge>
                @endif
            </div>

            <div class="space-y-3">
                @forelse ($porCalificar as $tarea)
                    <a
                        href="{{ route('professor.tasks.submissions', $tarea) }}"
                        class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 p-3 transition hover:border-[#2563EB] hover:shadow-sm"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ $tarea->titulo }}</p>
                            <p class="truncate text-xs text-slate-500">
                                {{ $tarea->leccion?->modulo?->curso?->titulo }}
                                @if ($tarea->primera_entrega)
                                    · desde {{ \Illuminate\Support\Carbon::parse($tarea->primera_entrega)->diffForHumans() }}
                                @endif
                            </p>
                        </div>

                        <span class="shrink-0 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">
                            {{ $tarea->por_calificar_count }}
                            {{ $tarea->por_calificar_count === 1 ? 'entrega' : 'entregas' }}
                        </span>
                    </a>
                @empty
                    <x-ui.empty
                        icon="fa-solid fa-circle-check"
                        title="Nada por calificar"
                        text="Cuando tus estudiantes entreguen tareas, aparecerán aquí."
                    />
                @endforelse
            </div>
        </x-ui.card>

        {{-- Próximos vencimientos --}}
        <x-ui.card>
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Próximos vencimientos</h2>
                    <p class="mt-1 text-sm text-slate-500">Tareas publicadas que vencen pronto.</p>
                </div>

                <a
                    href="{{ route('professor.tasks.index') }}"
                    class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]"
                >
                    Ver tareas
                </a>
            </div>

            <div class="space-y-3">
                @forelse ($proximas as $tarea)
                    @php
                        $curso = $tarea->leccion?->modulo?->curso;
                        $total = (int) ($matriculados[$curso?->id] ?? 0);
                        $hechas = (int) $tarea->entregadas_count;
                        $pct = $total > 0 ? min(100, (int) round($hechas / $total * 100)) : 0;
                        $f = $tarea->fecha_limite;
                        $cuando = $f->isToday() ? 'Hoy' : ($f->isTomorrow() ? 'Mañana' : $f->translatedFormat('D d M'));
                    @endphp

                    <a
                        href="{{ route('professor.tasks.submissions', $tarea) }}"
                        class="block rounded-lg border border-slate-200 p-3 transition hover:border-[#2563EB] hover:shadow-sm"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ $tarea->titulo }}</p>
                            <span class="shrink-0 text-xs font-medium {{ $f->isToday() ? 'text-amber-700' : 'text-slate-500' }}">
                                {{ $cuando }} · {{ $f->format('H:i') }}
                            </span>
                        </div>

                        <p class="truncate text-xs text-slate-500">{{ $curso?->titulo }}</p>

                        <div class="mt-2 flex items-center gap-3">
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-[#2563EB]" style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="text-xs text-slate-500">{{ $hechas }} de {{ $total }} entregaron</span>
                        </div>
                    </a>
                @empty
                    <x-ui.empty
                        icon="fa-solid fa-calendar-check"
                        title="Sin vencimientos cercanos"
                        text="Las tareas publicadas con fecha límite aparecerán aquí."
                    />
                @endforelse
            </div>
        </x-ui.card>

    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">

        {{-- Cursos recientes --}}
        <x-ui.card class="lg:col-span-2">

            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Cursos recientes</h2>
                    <p class="mt-1 text-sm text-slate-500">Últimos cursos creados.</p>
                </div>

                <a
                    href="{{ route('professor.courses.index') }}"
                    class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]"
                >
                    Ver todos
                </a>
            </div>

            <div class="el-rows space-y-3">
                @forelse ($recentCourses as $curso)
                    @php
                        $type = match ($curso->estado) {
                            'publicado' => 'success',
                            'borrador' => 'warning',
                            'oculto' => 'danger',
                            default => 'primary',
                        };
                    @endphp

                    <a
                        href="{{ route('professor.courses.edit', ['curso' => $curso->ulid]) }}"
                        class="el-card el-card-lift flex items-center justify-between gap-4 rounded-lg border border-slate-200 p-4"
                        style="--i: {{ $loop->index }}"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="el-icon flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-[#2563EB]">
                                <i class="fa-solid fa-book-open" aria-hidden="true"></i>
                            </span>

                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-800">
                                    {{ $curso->titulo }}
                                </p>

                                <p class="mt-0.5 text-sm text-slate-500">
                                    {{ $curso->materia?->nombre ?? 'Sin materia' }}
                                </p>
                            </div>
                        </div>

                        <x-ui.badge :type="$type">
                            {{ ucfirst($curso->estado) }}
                        </x-ui.badge>
                    </a>
                @empty
                    <x-ui.empty
                        icon="fa-solid fa-book-open"
                        title="Todavía no tienes cursos."
                        text="Crea el primero y comparte su código de clase."
                    >
                        <x-ui.button
                            :href="route('professor.courses.create')"
                            icon="fa-solid fa-plus"
                        >
                            Crear curso
                        </x-ui.button>
                    </x-ui.empty>
                @endforelse
            </div>

        </x-ui.card>

        <div class="space-y-6">

            {{-- Estado de los cursos --}}
            <x-ui.card>
                <h2 class="text-base font-bold text-slate-800">Estado de tus cursos</h2>
                <p class="mb-6 mt-1 text-sm text-slate-500">Publicados frente a pendientes.</p>

                <x-ui.donut
                    caption="cursos"
                    :size="148"
                    :segments="[
                        ['label' => 'Publicados', 'value' => $stats['published'], 'color' => '#16A34A'],
                        ['label' => 'Sin publicar', 'value' => $borradores, 'color' => '#EAB308'],
                    ]"
                />
            </x-ui.card>

            {{-- Accesos rápidos --}}
            <x-ui.card>
                <h2 class="mb-4 text-base font-bold text-slate-800">Accesos rápidos</h2>

                <div class="grid grid-cols-3 gap-3">
                    @foreach ([
                        ['Nuevo curso', 'professor.courses.create', 'fa-solid fa-plus'],
                        ['Mis cursos', 'professor.courses.index', 'fa-solid fa-book-open'],
                        ['Estadísticas', 'professor.statistics.index', 'fa-solid fa-chart-column'],
                    ] as [$label, $ruta, $icono])
                        <a
                            href="{{ route($ruta) }}"
                            class="el-quick flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-2 py-4 text-center text-xs font-semibold text-slate-700"
                        >
                            <i class="{{ $icono }} text-lg text-[#2563EB]" aria-hidden="true"></i>
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </x-ui.card>

        </div>

    </div>

</x-professor-layout>

