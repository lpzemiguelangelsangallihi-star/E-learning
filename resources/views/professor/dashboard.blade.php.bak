<x-professor-layout
    title="Dashboard"
    description="Resumen general de tus cursos y estudiantes."
>

    <div
        class="
            grid gap-4
            sm:grid-cols-2
            xl:grid-cols-4
        "
    >

        <x-ui.stat-card
            title="Mis cursos"
            :value="$stats['courses']"
            icon="fa-solid fa-book-open"
        />

        <x-ui.stat-card
            title="Publicados"
            :value="$stats['published']"
            icon="fa-solid fa-eye"
            type="success"
        />

        <x-ui.stat-card
            title="Estudiantes"
            :value="$stats['students']"
            icon="fa-solid fa-user-graduate"
        />

        <x-ui.stat-card
            title="Evaluaciones"
            :value="$stats['evaluations']"
            icon="fa-solid fa-file-circle-check"
            type="warning"
        />

    </div>


    <x-ui.card class="mt-6">

        <div
            class="
                mb-6
                flex items-center
                justify-between
                gap-4
            "
        >

            <div>

                <h2
                    class="
                        text-base
                        font-bold
                        text-slate-800
                    "
                >
                    Cursos recientes
                </h2>

                <p
                    class="
                        mt-1
                        text-sm
                        text-slate-500
                    "
                >
                    Últimos cursos creados.
                </p>

            </div>


            <a
                href="{{ route('professor.courses.index') }}"
                class="
                    text-sm
                    font-semibold
                    text-[#2563EB]
                    hover:text-[#1D4ED8]
                "
            >
                Ver todos
            </a>

        </div>


        <div class="space-y-3">

            @forelse ($recentCourses as $curso)

                <div
                    class="
                        flex items-center
                        justify-between
                        gap-4
                        rounded-lg
                        border border-slate-200
                        p-4
                    "
                >

                    <div>

                        <p
                            class="
                                font-semibold
                                text-slate-800
                            "
                        >
                            {{ $curso->titulo }}
                        </p>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-slate-500
                            "
                        >
                            {{
                                $curso->materia?->nombre
                                ?? 'Sin materia'
                            }}
                        </p>

                    </div>


                    @php
                        $type = match ($curso->estado) {
                            'publicado' => 'success',
                            'borrador' => 'warning',
                            'oculto' => 'danger',
                            default => 'primary',
                        };
                    @endphp


                    <x-ui.badge :type="$type">
                        {{ ucfirst($curso->estado) }}
                    </x-ui.badge>

                </div>

            @empty

                <div
                    class="
                        py-10
                        text-center
                    "
                >

                    <i
                        class="
                            fa-solid
                            fa-book-open
                            text-3xl
                            text-slate-300
                        "
                    ></i>

                    <p
                        class="
                            mt-3
                            text-sm
                            text-slate-500
                        "
                    >
                        Todavía no tienes cursos.
                    </p>

                </div>

            @endforelse

        </div>

    </x-ui.card>

</x-professor-layout>