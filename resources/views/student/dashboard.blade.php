<x-student-layout
    title="Dashboard"
    description="Tu actividad y avance en un solo lugar."
>
    @php
        $hora = now()->hour;
        $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
        $pend = $stats['pendientes'] ?? 0;
    @endphp

    <x-dashboard.hero
        class="mb-6"
        :eyebrow="now()->translatedFormat('l d \d\e F')"
        :title="$saludo . ', ' . auth()->user()->nombre"
        :subtitle="$pend > 0
            ? 'Tienes ' . $pend . ' ' . ($pend === 1 ? 'evaluación pendiente' : 'evaluaciones pendientes') . '.'
            : 'No tienes evaluaciones pendientes. ¡Buen trabajo!'"
        sabio="¡Un paso más y lo logras!"
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('student.courses.index')"
                icon="fa-solid fa-book-open"
                class="el-shine !bg-white !text-[#1E3A8A] hover:!bg-blue-50"
            >
                Mis cursos
            </x-ui.button>

            <div class="dash-chip flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold">
                <i class="fa-solid fa-fire text-[#EAB308]" aria-hidden="true"></i>
                {{ $racha ?? 0 }} {{ ($racha ?? 0) === 1 ? 'día seguido' : 'días seguidos' }}
            </div>

            <div class="dash-chip flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold">
                <i class="fa-solid fa-bolt text-[#EAB308]" aria-hidden="true"></i>
                {{ $experiencia ?? 0 }} XP
            </div>
        </x-slot:actions>
    </x-dashboard.hero>

    <x-ui.card class="mb-6">
        <h2 class="mb-3 text-base font-bold text-slate-800">
            Inscribirme con código
        </h2>

        <form
            method="POST"
            action="{{ route('student.courses.enroll') }}"
            class="flex flex-wrap gap-3"
            data-loading-form
            data-loading-text="Verificando..."
        >
            @csrf

            <label for="codigo" class="sr-only">Código del curso</label>

            <input
                id="codigo"
                name="codigo"
                value="{{ old('codigo') }}"
                maxlength="20"
                required
                autocomplete="off"
                placeholder="Código de clase"
                class="min-w-0 flex-1 rounded-lg border-slate-300 uppercase"
            >

            <x-ui.button type="submit" data-submit-button>
                <span data-submit-text>Inscribirme</span>
            </x-ui.button>
        </form>

        @error('codigo')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </x-ui.card>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card
            title="Cursos activos"
            :value="$stats['activos']"
            icon="fa-solid fa-book-open"
        />

        <x-ui.stat-card
            title="Cursos completados"
            :value="$stats['completados']"
            icon="fa-solid fa-circle-check"
            type="success"
        />

        <x-ui.stat-card
            title="Evaluaciones pendientes"
            :value="$stats['pendientes']"
            icon="fa-solid fa-file-circle-check"
            type="warning"
        />

        <x-ui.stat-card
            title="Promedio general"
            :value="$stats['promedio'] ?? '—'"
            icon="fa-solid fa-star"
        />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-student.calendar :eventos="$eventos" />
        </div>

        <x-ui.card>
            <div class="mb-4">
                <h2 class="text-base font-bold text-slate-800">Esta semana</h2>
                <p class="mt-1 text-sm text-slate-500">Lo que vence en los próximos 7 días.</p>
            </div>

            <div class="space-y-3">
                @forelse ($semana as $item)
                    @php
                        $f = $item['fecha_limite'];
                        $etiqueta = $item['vencida']
                            ? 'Vencida'
                            : ($f->isToday() ? 'Hoy' : ($f->isTomorrow() ? 'Mañana' : $f->translatedFormat('D d M')));
                    @endphp

                    <a
                        href="{{ route('student.evaluations.task.show', $item['ulid']) }}"
                        @class([
                            'block rounded-lg border p-3 transition hover:shadow-sm',
                            'border-red-200 bg-red-50' => $item['vencida'],
                            'border-amber-200 bg-amber-50' => !$item['vencida'] && $f->isToday(),
                            'border-slate-200' => !$item['vencida'] && !$f->isToday(),
                        ])
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span @class([
                                'text-xs font-bold uppercase tracking-wide',
                                'text-red-700' => $item['vencida'],
                                'text-amber-700' => !$item['vencida'] && $f->isToday(),
                                'text-slate-500' => !$item['vencida'] && !$f->isToday(),
                            ])>{{ $etiqueta }}</span>

                            <span class="text-xs text-slate-500">{{ $f->format('H:i') }}</span>
                        </div>

                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $item['titulo'] }}</p>
                        <p class="text-xs text-slate-500">{{ $item['curso']?->titulo }}</p>
                    </a>
                @empty
                    <x-ui.empty
                        icon="fa-solid fa-calendar-check"
                        title="Semana libre"
                        text="No tienes entregas para los próximos 7 días."
                    />
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2">
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-800">
                        Continúa aprendiendo
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Retoma donde lo dejaste.
                    </p>
                </div>

                <a
                    href="{{ route('student.courses.index') }}"
                    class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]"
                >
                    Ver todos
                </a>
            </div>

            <div class="space-y-3">
                @forelse ($continuar as $inscripcion)
                    @php($curso = $inscripcion->curso)

                    <a
                        href="{{ route('student.courses.show', $curso) }}"
                        class="block rounded-lg border border-slate-200 p-4 transition duration-150 hover:border-[#2563EB] hover:shadow-sm"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-800">
                                    {{ $curso->titulo }}
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $curso->materia?->nombre ?? 'Sin materia' }}
                                </p>
                            </div>

                            <i
                                class="fa-solid fa-chevron-right mt-1 text-slate-400"
                                aria-hidden="true"
                            ></i>
                        </div>

                        <x-ui.progress
                            :value="$progresos[$curso->id]->porcentaje ?? 0"
                            class="mt-3"
                        />
                    </a>
                @empty
                    <x-ui.empty
                        icon="fa-solid fa-book-open"
                        title="Aún no estás inscrito en ningún curso"
                        text="Pide el código a tu profesor para empezar."
                    >
                        <x-ui.button
                            :href="route('student.courses.join')"
                            icon="fa-solid fa-key"
                        >
                            Inscribirme con código
                        </x-ui.button>
                    </x-ui.empty>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-800">
                        Por hacer
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Próximas evaluaciones.
                    </p>
                </div>

                <a
                    href="{{ route('student.evaluations.index') }}"
                    class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]"
                >
                    Ver todas
                </a>
            </div>

            <div class="space-y-3">
                @forelse ($pendientes as $item)
                    <div class="rounded-lg border border-slate-200 p-3">
                        <div class="flex items-center justify-between gap-2">
                            <x-ui.badge
                                :type="$item['tipo'] === 'examen' ? 'primary' : 'warning'"
                            >
                                {{ $item['tipo'] === 'examen' ? 'Examen' : 'Tarea' }}
                            </x-ui.badge>

                            @if ($item['fecha_limite'])
                                <span @class([
                                    'text-xs font-medium',
                                    'text-[#DC2626]' => $item['vencida'],
                                    'text-slate-500' => !$item['vencida'],
                                ])>
                                    {{ $item['fecha_limite']->format('d/m H:i') }}
                                </span>
                            @endif
                        </div>

                        <p class="mt-2 text-sm font-semibold text-slate-800">
                            {{ $item['titulo'] }}
                        </p>

                        <p class="text-xs text-slate-500">
                            {{ $item['curso']?->titulo }}
                        </p>
                    </div>
                @empty
                    <x-ui.empty
                        icon="fa-solid fa-circle-check"
                        title="Todo al día"
                    />
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-student-layout>