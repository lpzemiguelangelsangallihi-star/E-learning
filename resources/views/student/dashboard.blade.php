<x-student-layout
<<<<<<< HEAD
    title="Mis cursos"
    description="Únete a un curso con el código de tu profesor."
>

    <x-flash />

    {{-- Unirse con código --}}
    <x-ui.card class="mb-8">

        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

            <div>
                <h2 class="text-base font-bold text-slate-800">Unirme a un curso</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Ingresa el código de clase de 8 caracteres que te dio tu profesor.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('student.courses.join') }}"
                class="w-full lg:max-w-md"
            >
                @csrf

                <div class="flex gap-2">
                    <input
                        name="codigo"
                        type="text"
                        maxlength="20"
                        required
                        autocomplete="off"
                        value="{{ old('codigo') }}"
                        placeholder="Ej. K7M2XQ9A"
                        class="block w-full rounded-lg border-slate-300 px-4 py-2.5 font-mono text-sm uppercase tracking-widest focus:border-[#2563EB] focus:ring-[#2563EB]"
                    >

                    <button
                        type="submit"
                        class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1D4ED8]"
                    >
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Unirme
                    </button>
                </div>

                @error('codigo')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </form>

        </div>

    </x-ui.card>


    {{-- Cursos inscritos --}}
    <h2 class="mb-4 text-lg font-bold text-slate-800">Mis cursos inscritos</h2>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">

        @forelse ($inscripciones as $inscripcion)

            @php $curso = $inscripcion->curso; @endphp

            <x-ui.card hover>

                <div class="flex h-full flex-col">

                    <p class="text-xs font-semibold uppercase tracking-wide text-[#2563EB]">
                        {{ $curso->materia?->nombre ?? 'Sin materia' }}
                    </p>

                    <h3 class="mt-2 text-lg font-bold text-slate-800">{{ $curso->titulo }}</h3>

                    <p class="mt-1 text-sm text-slate-500">
                        <i class="fa-solid fa-chalkboard-user mr-1"></i>
                        {{ $curso->profesor?->nombre_completo ?? 'Profesor' }}
                    </p>

                    <div class="mt-4 flex flex-wrap gap-4 text-xs text-slate-500">
                        <span><i class="fa-solid fa-signal mr-1"></i>{{ ucfirst($curso->nivel) }}</span>
                        <span><i class="fa-solid fa-list-check mr-1"></i>{{ $temas[$curso->id] ?? 0 }} temas</span>
                    </div>

                    <div class="mt-5 border-t border-slate-100 pt-4">
                        <a
                            href="{{ route('student.courses.show', $curso) }}"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]"
                        >
                            Entrar al curso
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>

                </div>

            </x-ui.card>

        @empty

            <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-xl text-[#2563EB]">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <h3 class="mt-4 font-bold text-slate-800">Todavía no estás inscrito en ningún curso</h3>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                    Pide el código de clase a tu profesor y escríbelo arriba.
                </p>
            </div>

        @endforelse
=======
    title="Dashboard"
    description="Tu actividad y avance en un solo lugar."
>

    {{-- Bienvenida --}}
    <div class="mb-6 rounded-xl bg-[#1E3A8A] p-6 text-white sm:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold">Hola, {{ auth()->user()->nombre }} </h2>

                <p class="mt-1 text-sm text-blue-100">
                    @if (($stats['pendientes'] ?? 0) > 0)
                        Tienes {{ $stats['pendientes'] }} {{ $stats['pendientes'] === 1 ? 'evaluación pendiente' : 'evaluaciones pendientes' }}.
                    @else
                        No tienes evaluaciones pendientes. ¡Buen trabajo!
                    @endif
                </p>
            </div>

            <div class="flex gap-3">
                <div class="rounded-lg bg-white/10 px-4 py-3 text-center">
                    <p class="text-xl font-bold"><i class="fa-solid fa-fire mr-1 text-[#EAB308]" aria-hidden="true"></i>{{ $racha ?? 0 }}</p>
                    <p class="text-xs text-blue-100">{{ ($racha ??0) === 1 ? 'día seguido' : 'días seguidos' }}</p>
                </div>

                <div class="rounded-lg bg-white/10 px-4 py-3 text-center">
                    <p class="text-xl font-bold"><i class="fa-solid fa-bolt mr-1 text-[#EAB308]" aria-hidden="true"></i>{{ $experiencia ??0 }}</p>
                    <p class="text-xs text-blue-100">XP</p>
                </div>
            </div>
        </div>
    </div>


    {{-- Métricas --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card title="Cursos activos" :value="$stats['activos']" icon="fa-solid fa-book-open" />
        <x-ui.stat-card title="Cursos completados" :value="$stats['completados']" icon="fa-solid fa-circle-check" type="success" />
        <x-ui.stat-card title="Evaluaciones pendientes" :value="$stats['pendientes']" icon="fa-solid fa-file-circle-check" type="warning" />
        <x-ui.stat-card title="Promedio general" :value="$stats['promedio'] ?? '—'" icon="fa-solid fa-star" />
    </div>


    <div class="mt-6 grid gap-6 lg:grid-cols-3">

        {{-- Continuar aprendiendo --}}
        <x-ui.card class="lg:col-span-2">
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Continúa aprendiendo</h2>
                    <p class="mt-1 text-sm text-slate-500">Retoma donde lo dejaste.</p>
                </div>

                <a href="{{ route('student.courses.index') }}" class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]">
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
                                <p class="truncate font-semibold text-slate-800">{{ $curso->titulo }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $curso->materia?->nombre ?? 'Sin materia' }}</p>
                            </div>

                            <i class="fa-solid fa-chevron-right mt-1 text-slate-400" aria-hidden="true"></i>
                        </div>

                        <x-ui.progress :value="$progresos[$curso->id]->porcentaje ?? 0" class="mt-3" />
                    </a>
                @empty
                    <x-ui.empty
                        icon="fa-solid fa-book-open"
                        title="Aún no estás inscrito en ningún curso"
                        text="Pide el código a tu profesor para empezar."
                    >
                        <x-ui.button :href="route('student.courses.join')" icon="fa-solid fa-key">
                            Inscribirme con código
                        </x-ui.button>
                    </x-ui.empty>
                @endforelse
            </div>
        </x-ui.card>


        {{-- Pendientes --}}
        <x-ui.card>
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Por hacer</h2>
                    <p class="mt-1 text-sm text-slate-500">Próximas evaluaciones.</p>
                </div>

                <a href="{{ route('student.evaluations.index') }}" class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]">
                    Ver todas
                </a>
            </div>

            <div class="space-y-3">
                @forelse ($pendientes as $item)
                    <div class="rounded-lg border border-slate-200 p-3">
                        <div class="flex items-center justify-between gap-2">
                            <x-ui.badge :type="$item['tipo'] === 'examen' ? 'primary' : 'warning'">
                                {{ $item['tipo'] === 'examen' ? 'Examen' : 'Tarea' }}
                            </x-ui.badge>

                            @if ($item['fecha_limite'])
                                <span @class(['text-xs font-medium', 'text-[#DC2626]' => $item['vencida'], 'text-slate-500' => !$item['vencida']])>
                                    {{ $item['fecha_limite']->format('d/m H:i') }}
                                </span>
                            @endif
                        </div>

                        <p class="mt-2 text-sm font-semibold text-slate-800">{{ $item['titulo'] }}</p>
                        <p class="text-xs text-slate-500">{{ $item['curso']?->titulo }}</p>
                    </div>
                @empty
                    <x-ui.empty icon="fa-solid fa-circle-check" title="Todo al día" />
                @endforelse
            </div>
        </x-ui.card>
>>>>>>> bb5ded3887008384a4b1ffae92a0fd0dc2279343

    </div>

</x-student-layout>
