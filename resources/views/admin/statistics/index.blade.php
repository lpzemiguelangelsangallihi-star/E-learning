<x-admin-layout
    title="Estadísticas"
    description="Resumen general de actividad y uso de la plataforma."
>

    {{-- Estadísticas generales --}}
    <div
        class="
            grid gap-4
            sm:grid-cols-2
            xl:grid-cols-3
            2xl:grid-cols-6
        "
    >

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
            type="warning"
        />

        <x-ui.stat-card
            title="Inscripciones"
            :value="$stats['activeEnrollments']"
            icon="fa-solid fa-clipboard-list"
            type="success"
            description="Inscripciones activas"
        />

        <x-ui.stat-card
            title="Evaluaciones"
            :value="$stats['evaluations']"
            icon="fa-solid fa-file-circle-check"
        />

    </div>


    <div
        class="
            mt-6
            grid gap-6
            xl:grid-cols-2
        "
    >

        {{-- Usuarios por rol --}}
        <x-ui.card>

            <div class="mb-6">

                <h2
                    class="
                        text-base
                        font-bold
                        text-slate-800
                    "
                >
                    Usuarios por rol
                </h2>

                <p
                    class="
                        mt-1
                        text-sm
                        text-slate-500
                    "
                >
                    Distribución de usuarios registrados.
                </p>

            </div>


            <div class="space-y-5">

                @foreach ($usersByRole as $role)

                    @php
                        $percentage =
                            round(
                                ($role->total / $totalUsers)
                                * 100
                            );
                    @endphp


                    <div>

                        <div
                            class="
                                mb-2 flex
                                items-center
                                justify-between
                                gap-4
                            "
                        >

                            <span
                                class="
                                    text-sm
                                    font-medium
                                    text-slate-700
                                "
                            >
                                {{ ucfirst($role->nombre) }}
                            </span>

                            <span
                                class="
                                    text-sm
                                    font-semibold
                                    text-slate-800
                                "
                            >
                                {{ $role->total }}
                            </span>

                        </div>


                        <div
                            class="
                                h-2.5
                                overflow-hidden
                                rounded-full
                                bg-slate-100
                            "
                        >

                            <div
                                class="
                                    h-full
                                    rounded-full
                                    bg-[#2563EB]
                                    transition-all
                                "
                                style="
                                    width:
                                    {{ $percentage }}%;
                                "
                            ></div>

                        </div>


                        <p
                            class="
                                mt-1.5
                                text-right
                                text-xs
                                text-slate-400
                            "
                        >
                            {{ $percentage }}%
                        </p>

                    </div>

                @endforeach

            </div>

        </x-ui.card>


        {{-- Cursos por materia --}}
        <x-ui.card>

            <div class="mb-6">

                <h2
                    class="
                        text-base
                        font-bold
                        text-slate-800
                    "
                >
                    Cursos por materia
                </h2>

                <p
                    class="
                        mt-1
                        text-sm
                        text-slate-500
                    "
                >
                    Distribución de cursos entre las tres materias.
                </p>

            </div>


            <div class="space-y-5">

                @foreach ($coursesBySubject as $subject)

                    @php
                        $percentage =
                            round(
                                ($subject->total / $totalCourses)
                                * 100
                            );

                        $subjectName =
                            mb_strtolower(
                                $subject->nombre
                            );

                        $icon = match ($subjectName) {
                            'matemáticas',
                            'matematicas'
                                => 'fa-solid fa-calculator',

                            'física',
                            'fisica'
                                => 'fa-solid fa-atom',

                            'química',
                            'quimica'
                                => 'fa-solid fa-flask',

                            default
                                => 'fa-solid fa-book',
                        };
                    @endphp


                    <div>

                        <div
                            class="
                                mb-2 flex
                                items-center
                                justify-between
                                gap-4
                            "
                        >

                            <div
                                class="
                                    flex items-center
                                    gap-2
                                "
                            >

                                <i
                                    class="
                                        {{ $icon }}
                                        w-5
                                        text-[#2563EB]
                                    "
                                ></i>

                                <span
                                    class="
                                        text-sm
                                        font-medium
                                        text-slate-700
                                    "
                                >
                                    {{ $subject->nombre }}
                                </span>

                            </div>


                            <span
                                class="
                                    text-sm
                                    font-semibold
                                    text-slate-800
                                "
                            >
                                {{ $subject->total }}
                            </span>

                        </div>


                        <div
                            class="
                                h-2.5
                                overflow-hidden
                                rounded-full
                                bg-slate-100
                            "
                        >

                            <div
                                class="
                                    h-full
                                    rounded-full
                                    bg-[#2563EB]
                                "
                                style="
                                    width:
                                    {{ $percentage }}%;
                                "
                            ></div>

                        </div>


                        <p
                            class="
                                mt-1.5
                                text-right
                                text-xs
                                text-slate-400
                            "
                        >
                            {{ $percentage }}%
                        </p>

                    </div>

                @endforeach

            </div>

        </x-ui.card>

    </div>


    {{-- Estado de cursos --}}
    <x-ui.card class="mt-6">

        <div class="mb-6">

            <h2
                class="
                    text-base
                    font-bold
                    text-slate-800
                "
            >
                Estado de los cursos
            </h2>

            <p
                class="
                    mt-1
                    text-sm
                    text-slate-500
                "
            >
                Situación actual de los cursos registrados.
            </p>

        </div>


        <div
            class="
                grid gap-4
                sm:grid-cols-3
            "
        >

            @php
                $statusMap = [
                    'publicado' => [
                        'label' => 'Publicados',
                        'icon' => 'fa-solid fa-eye',
                        'classes' =>
                            'bg-green-50 text-green-700 border-green-200',
                    ],

                    'borrador' => [
                        'label' => 'Borradores',
                        'icon' => 'fa-solid fa-pen-ruler',
                        'classes' =>
                            'bg-yellow-50 text-yellow-700 border-yellow-200',
                    ],

                    'oculto' => [
                        'label' => 'Ocultos',
                        'icon' => 'fa-solid fa-eye-slash',
                        'classes' =>
                            'bg-red-50 text-red-700 border-red-200',
                    ],
                ];
            @endphp


            @foreach ($statusMap as $status => $config)

                @php
                    $record =
                        $coursesByStatus
                            ->firstWhere(
                                'estado',
                                $status
                            );

                    $total =
                        $record?->total ?? 0;
                @endphp


                <div
                    class="
                        rounded-xl
                        border
                        p-5
                        {{ $config['classes'] }}
                    "
                >

                    <div
                        class="
                            flex items-center
                            justify-between
                            gap-4
                        "
                    >

                        <div>

                            <p
                                class="
                                    text-sm
                                    font-semibold
                                "
                            >
                                {{ $config['label'] }}
                            </p>

                            <p
                                class="
                                    mt-2
                                    text-3xl
                                    font-bold
                                "
                            >
                                {{ $total }}
                            </p>

                        </div>


                        <i
                            class="
                                {{ $config['icon'] }}
                                text-2xl
                            "
                        ></i>

                    </div>

                </div>

            @endforeach

        </div>

    </x-ui.card>

</x-admin-layout>