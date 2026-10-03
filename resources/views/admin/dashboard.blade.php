<x-admin-layout
    title="Dashboard"
    description="Resumen general de la plataforma."
>

    {{-- Estadísticas --}}
    <section
        class="
            grid gap-4
            sm:grid-cols-2
            xl:grid-cols-3
        "
    >

        <x-student.stat-card
            title="Usuarios"
            :value="$stats['users']"
            icon="fa-solid fa-users"
        />

        <x-student.stat-card
            title="Estudiantes"
            :value="$stats['students']"
            icon="fa-solid fa-user-graduate"
            type="success"
        />

        <x-student.stat-card
            title="Profesores"
            :value="$stats['teachers']"
            icon="fa-solid fa-chalkboard-user"
        />

        <x-student.stat-card
            title="Cursos"
            :value="$stats['courses']"
            icon="fa-solid fa-book-open"
        />

        <x-student.stat-card
            title="Materias"
            :value="$stats['subjects']"
            icon="fa-solid fa-flask"
        />

        <x-student.stat-card
            title="Evaluaciones"
            :value="$stats['evaluations']"
            icon="fa-solid fa-file-pen"
            type="warning"
        />

    </section>


    {{-- Contenido --}}
    <section
        class="
            mt-8 grid gap-6
            xl:grid-cols-2
        "
    >

        {{-- Usuarios recientes --}}
        <div>

            <div class="mb-4">

                <h2
                    class="
                        text-xl font-bold
                        text-slate-800
                    "
                >
                    Usuarios recientes
                </h2>

                <p
                    class="
                        mt-1 text-sm
                        text-slate-500
                    "
                >
                    Últimas cuentas registradas.
                </p>

            </div>


            <x-ui.card>

                <div
                    class="
                        divide-y
                        divide-slate-100
                    "
                >

                    @forelse (
                        $recentUsers
                        as $user
                    )

                        <div
                            class="
                                flex items-center
                                gap-3 py-4
                                first:pt-0
                                last:pb-0
                            "
                        >

                            <div
                                class="
                                    flex h-10 w-10
                                    shrink-0
                                    items-center
                                    justify-center
                                    rounded-full
                                    bg-blue-50
                                    font-semibold
                                    text-[#2563EB]
                                "
                            >
                                {{
                                    mb_strtoupper(
                                        mb_substr(
                                            $user->nombre,
                                            0,
                                            1
                                        )
                                    )
                                }}
                            </div>


                            <div
                                class="
                                    min-w-0
                                    flex-1
                                "
                            >

                                <p
                                    class="
                                        truncate
                                        text-sm
                                        font-semibold
                                        text-slate-800
                                    "
                                >
                                    {{
                                        $user
                                            ->nombre_completo
                                    }}
                                </p>

                                <p
                                    class="
                                        truncate
                                        text-xs
                                        text-slate-500
                                    "
                                >
                                    {{ $user->email }}
                                </p>

                            </div>


                            <x-ui.badge type="primary">

                                {{
                                    ucfirst(
                                        $user->rol?->nombre
                                        ?? 'Sin rol'
                                    )
                                }}

                            </x-ui.badge>

                        </div>

                    @empty

                        <p
                            class="
                                text-sm
                                text-slate-500
                            "
                        >
                            No hay usuarios registrados.
                        </p>

                    @endforelse

                </div>

            </x-ui.card>

        </div>


        {{-- Cursos recientes --}}
        <div>

            <div class="mb-4">

                <h2
                    class="
                        text-xl font-bold
                        text-slate-800
                    "
                >
                    Cursos recientes
                </h2>

                <p
                    class="
                        mt-1 text-sm
                        text-slate-500
                    "
                >
                    Últimos cursos registrados.
                </p>

            </div>


            <x-ui.card>

                <div
                    class="
                        divide-y
                        divide-slate-100
                    "
                >

                    @forelse (
                        $recentCourses
                        as $course
                    )

                        <div
                            class="
                                py-4
                                first:pt-0
                                last:pb-0
                            "
                        >

                            <div
                                class="
                                    flex items-start
                                    justify-between
                                    gap-3
                                "
                            >

                                <div>

                                    <p
                                        class="
                                            font-semibold
                                            text-slate-800
                                        "
                                    >
                                        {{ $course->titulo }}
                                    </p>

                                    <p
                                        class="
                                            mt-1 text-sm
                                            text-slate-500
                                        "
                                    >
                                        {{
                                            $course->materia
                                            ?? 'Sin materia'
                                        }}
                                    </p>

                                </div>


                                <x-ui.badge
                                    :type="
                                        $course->estado
                                            === 'publicado'
                                        ? 'success'
                                        : 'warning'
                                    "
                                >
                                    {{
                                        ucfirst(
                                            $course->estado
                                        )
                                    }}
                                </x-ui.badge>

                            </div>


                            <p
                                class="
                                    mt-3
                                    flex items-center
                                    gap-2
                                    text-xs
                                    text-slate-500
                                "
                            >

                                <i
                                    class="
                                        fa-solid
                                        fa-chalkboard-user
                                    "
                                ></i>

                                {{
                                    trim(
                                        (
                                            $course
                                            ->profesor_nombre
                                            ?? ''
                                        )
                                        .' '
                                        .(
                                            $course
                                            ->profesor_apellido
                                            ?? ''
                                        )
                                    )
                                    ?: 'Sin profesor'
                                }}

                            </p>

                        </div>

                    @empty

                        <p
                            class="
                                text-sm
                                text-slate-500
                            "
                        >
                            No hay cursos registrados.
                        </p>

                    @endforelse

                </div>

            </x-ui.card>

        </div>

    </section>

</x-admin-layout>