<x-admin-layout
    title="Dashboard"
    description="Resumen general de la plataforma."
>

    @php
        $hora = now()->hour;
        $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
        $otros = max(0, $stats['users'] - $stats['students'] - $stats['teachers']);
    @endphp

    {{-- Bienvenida --}}
    <x-dashboard.hero
        class="mb-6"
        :eyebrow="now()->translatedFormat('l d \d\e F')"
        :title="$saludo . ', ' . auth()->user()->nombre"
        :subtitle="'La plataforma tiene ' . $stats['users'] . ' usuarios y ' . $stats['courses'] . ' cursos registrados.'"
        sabio="Todo bajo control. ¡Revisa lo nuevo!"
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.users.create')"
                icon="fa-solid fa-user-plus"
                class="el-shine !bg-white !text-[#1E3A8A] hover:!bg-blue-50"
            >
                Nuevo usuario
            </x-ui.button>

            <x-ui.button
                :href="route('admin.statistics.index')"
                icon="fa-solid fa-chart-pie"
                class="!bg-white/10 !text-white ring-1 ring-white/25 hover:!bg-white/20"
            >
                Estadísticas
            </x-ui.button>
        </x-slot:actions>
    </x-dashboard.hero>

    {{-- Estadísticas --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">

        <x-ui.stat-card
            title="Usuarios"
            :value="$stats['users']"
            icon="fa-solid fa-users"
        />

        <x-ui.stat-card
            title="Estudiantes"
            :value="$stats['students']"
            icon="fa-solid fa-user-graduate"
            type="success"
        />

        <x-ui.stat-card
            title="Profesores"
            :value="$stats['teachers']"
            icon="fa-solid fa-chalkboard-user"
        />

        <x-ui.stat-card
            title="Cursos"
            :value="$stats['courses']"
            icon="fa-solid fa-book-open"
        />

        <x-ui.stat-card
            title="Materias"
            :value="$stats['subjects']"
            icon="fa-solid fa-flask"
        />

        <x-ui.stat-card
            title="Evaluaciones"
            :value="$stats['evaluations']"
            icon="fa-solid fa-file-pen"
            type="warning"
        />

    </section>

    {{-- Gráficas --}}
    <section class="mt-6 grid gap-6 lg:grid-cols-3">

        <x-ui.card>
            <h2 class="text-base font-bold text-slate-800">Usuarios por rol</h2>
            <p class="mb-6 mt-1 text-sm text-slate-500">Cómo se reparte la comunidad.</p>

            <x-ui.donut
                caption="usuarios"
                :segments="[
                    ['label' => 'Estudiantes', 'value' => $stats['students'], 'color' => '#2563EB'],
                    ['label' => 'Profesores', 'value' => $stats['teachers'], 'color' => '#1E3A8A'],
                    ['label' => 'Administración', 'value' => $otros, 'color' => '#EAB308'],
                ]"
            />
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-base font-bold text-slate-800">Contenido</h2>
            <p class="mb-6 mt-1 text-sm text-slate-500">Lo que hay publicado en la plataforma.</p>

            <x-ui.bars
                :items="[
                    ['label' => 'Cursos', 'value' => $stats['courses'], 'color' => '#2563EB', 'icon' => 'fa-solid fa-book-open'],
                    ['label' => 'Materias', 'value' => $stats['subjects'], 'color' => '#1E3A8A', 'icon' => 'fa-solid fa-flask'],
                    ['label' => 'Evaluaciones', 'value' => $stats['evaluations'], 'color' => '#EAB308', 'icon' => 'fa-solid fa-file-pen'],
                ]"
            />
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-base font-bold text-slate-800">Accesos rápidos</h2>
            <p class="mb-6 mt-1 text-sm text-slate-500">Lo que más usas, a un clic.</p>

            <div class="grid grid-cols-2 gap-3">
                @foreach ([
                    ['Usuarios', 'admin.users.index', 'fa-solid fa-users'],
                    ['Cursos', 'admin.courses.index', 'fa-solid fa-book-open'],
                    ['Bitácora', 'admin.bitacora.index', 'fa-solid fa-clipboard-list'],
                    ['Ajustes', 'admin.settings.index', 'fa-solid fa-gear'],
                ] as [$label, $ruta, $icono])
                    <a
                        href="{{ route($ruta) }}"
                        class="el-quick flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-4 text-center text-sm font-semibold text-slate-700"
                    >
                        <i class="{{ $icono }} text-lg text-[#2563EB]" aria-hidden="true"></i>
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </x-ui.card>

    </section>

    {{-- Contenido reciente --}}
    <section class="mt-6 grid gap-6 xl:grid-cols-2">

        {{-- Usuarios recientes --}}
        <x-ui.card>

            <div class="mb-4">
                <h2 class="text-base font-bold text-slate-800">Usuarios recientes</h2>
                <p class="mt-1 text-sm text-slate-500">Últimas cuentas registradas.</p>
            </div>

            <div class="el-rows -mx-2 divide-y divide-slate-100">
                @forelse ($recentUsers as $user)
                    <div class="el-row flex items-center gap-3 px-2 py-3" style="--i: {{ $loop->index }}">

                        <div class="el-avatar flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#2563EB] to-[#1E3A8A] font-semibold text-white">
                            {{ mb_strtoupper(mb_substr($user->nombre, 0, 1)) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-800">
                                {{ $user->nombre_completo }}
                            </p>

                            <p class="truncate text-xs text-slate-500">
                                {{ $user->email }}
                            </p>
                        </div>

                        <x-ui.badge type="primary">
                            {{ ucfirst($user->rol?->nombre ?? 'Sin rol') }}
                        </x-ui.badge>

                    </div>
                @empty
                    <x-ui.empty
                        icon="fa-solid fa-users"
                        title="No hay usuarios registrados."
                    />
                @endforelse
            </div>

        </x-ui.card>

        {{-- Cursos recientes --}}
        <x-ui.card>

            <div class="mb-4">
                <h2 class="text-base font-bold text-slate-800">Cursos recientes</h2>
                <p class="mt-1 text-sm text-slate-500">Últimos cursos registrados.</p>
            </div>

            <div class="el-rows -mx-2 divide-y divide-slate-100">
                @forelse ($recentCourses as $course)
                    <div class="el-row px-2 py-3" style="--i: {{ $loop->index }}">

                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-800">
                                    {{ $course->titulo }}
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $course->materia ?? 'Sin materia' }}
                                </p>
                            </div>

                            <x-ui.badge :type="$course->estado === 'publicado' ? 'success' : 'warning'">
                                {{ ucfirst($course->estado) }}
                            </x-ui.badge>
                        </div>

                        <p class="mt-2 flex items-center gap-2 text-xs text-slate-500">
                            <i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i>

                            {{ trim(($course->profesor_nombre ?? '') . ' ' . ($course->profesor_apellido ?? '')) ?: 'Sin profesor' }}
                        </p>

                    </div>
                @empty
                    <x-ui.empty
                        icon="fa-solid fa-book-open"
                        title="No hay cursos registrados."
                    />
                @endforelse
            </div>

        </x-ui.card>

    </section>

</x-admin-layout>
