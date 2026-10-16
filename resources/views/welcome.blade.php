<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>E-Learning</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >
</head>

<body class="bg-slate-50 text-slate-800">

    
    <header class="fixed top-0 z-50 w-full border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">

            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>

                <span class="text-xl font-bold text-slate-900">
                    E-Learning
                </span>
            </a>

            <nav class="hidden items-center gap-8 md:flex">
                <a href="#inicio" class="text-sm font-medium text-slate-600 hover:text-blue-600">
                    Inicio
                </a>

                <a href="#caracteristicas" class="text-sm font-medium text-slate-600 hover:text-blue-600">
                    Características
                </a>

                <a href="#materias" class="text-sm font-medium text-slate-600 hover:text-blue-600">
                    Materias
                </a>

                <a href="#roles" class="text-sm font-medium text-slate-600 hover:text-blue-600">
                    Usuarios
                </a>
            </nav>

            <div class="flex items-center gap-3">

                @auth

                    <a
                        href="{{ route('dashboard') }}"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                    >
                        Ir al panel
                    </a>

                @else

                    <a
                        href="{{ route('login') }}"
                        class="hidden rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 sm:inline-flex"
                    >
                        Iniciar sesión
                    </a>

                    <a
                        href="{{ route('register') }}"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                    >
                        Registrarse
                    </a>

                @endauth

            </div>
        </div>
    </header>


    
    <section
        id="inicio"
        class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-blue-950 to-blue-700 pt-32"
    >
        <div class="absolute inset-0 opacity-20">
            <div class="absolute -left-20 top-10 h-72 w-72 rounded-full bg-blue-400 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-cyan-300 blur-3xl"></div>
        </div>

        <div class="relative mx-auto grid max-w-7xl items-center gap-14 px-6 py-24 lg:grid-cols-2">

            <div>

                <span class="inline-flex rounded-full border border-blue-300/30 bg-white/10 px-4 py-2 text-sm font-medium text-blue-100">
                    Plataforma educativa inteligente
                </span>

                <h1 class="mt-6 text-4xl font-bold leading-tight text-white sm:text-5xl lg:text-6xl">
                    Aprende, enseña y mejora tu rendimiento
                    <span class="text-blue-300">
                        en un solo lugar
                    </span>
                </h1>

                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
                    Nuestra plataforma E-Learning permite gestionar cursos,
                    aulas virtuales, materiales, evaluaciones y progreso académico,
                    ofreciendo una experiencia organizada para estudiantes y profesores.
                </p>

                <div class="mt-8 flex flex-wrap gap-4">

                    @guest

                        <a
                            href="{{ route('register') }}"
                            class="rounded-xl bg-white px-6 py-3 font-semibold text-blue-700 transition hover:bg-blue-50"
                        >
                            Comenzar ahora
                        </a>

                        <a
                            href="{{ route('login') }}"
                            class="rounded-xl border border-white/30 px-6 py-3 font-semibold text-white transition hover:bg-white/10"
                        >
                            Iniciar sesión
                        </a>

                    @else

                        <a
                            href="{{ route('dashboard') }}"
                            class="rounded-xl bg-white px-6 py-3 font-semibold text-blue-700 transition hover:bg-blue-50"
                        >
                            Ir a mi panel
                        </a>

                    @endguest

                </div>

            </div>


            <div class="relative">

                <div class="rounded-3xl border border-white/20 bg-white/10 p-6 shadow-2xl backdrop-blur">

                    <div class="rounded-2xl bg-white p-6">

                        <div class="mb-6 flex items-center justify-between">

                            <div>
                                <p class="text-sm text-slate-500">
                                    Tu aprendizaje
                                </p>

                                <h3 class="text-xl font-bold text-slate-900">
                                    Panel académico
                                </h3>
                            </div>

                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                        </div>


                        <div class="grid gap-4 sm:grid-cols-2">

                            <div class="rounded-xl bg-blue-50 p-4">
                                <i class="fa-solid fa-book-open text-blue-600"></i>

                                <p class="mt-3 text-2xl font-bold">
                                    Cursos
                                </p>

                                <p class="text-sm text-slate-500">
                                    Organiza tu aprendizaje
                                </p>
                            </div>


                            <div class="rounded-xl bg-emerald-50 p-4">
                                <i class="fa-solid fa-chart-pie text-emerald-600"></i>

                                <p class="mt-3 text-2xl font-bold">
                                    Progreso
                                </p>

                                <p class="text-sm text-slate-500">
                                    Revisa tu avance
                                </p>
                            </div>


                            <div class="rounded-xl bg-violet-50 p-4">
                                <i class="fa-solid fa-file-circle-check text-violet-600"></i>

                                <p class="mt-3 text-2xl font-bold">
                                    Evaluaciones
                                </p>

                                <p class="text-sm text-slate-500">
                                    Pon a prueba tus conocimientos
                                </p>
                            </div>


                            <div class="rounded-xl bg-amber-50 p-4">
                                <i class="fa-solid fa-lightbulb text-amber-600"></i>

                                <p class="mt-3 text-2xl font-bold">
                                    Recomendaciones
                                </p>

                                <p class="text-sm text-slate-500">
                                    Refuerza tus dificultades
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section id="caracteristicas" class="py-24">
        <div class="mx-auto max-w-7xl px-6">

            <div class="mx-auto max-w-2xl text-center">

                <p class="font-semibold text-blue-600">
                    Funcionalidades
                </p>

                <h2 class="mt-2 text-3xl font-bold text-slate-900 sm:text-4xl">
                    Todo lo necesario para el aprendizaje virtual
                </h2>

                <p class="mt-4 text-slate-600">
                    La plataforma integra herramientas académicas para facilitar
                    la enseñanza, evaluación y seguimiento del estudiante.
                </p>

            </div>


            <div class="mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-3">

                @php
                    $features = [
                        [
                            'icon' => 'fa-book',
                            'title' => 'Cursos virtuales',
                            'text' => 'Los profesores pueden crear y administrar cursos asociados a las diferentes materias.'
                        ],
                        [
                            'icon' => 'fa-users',
                            'title' => 'Aulas virtuales',
                            'text' => 'Los estudiantes pueden incorporarse a sus aulas mediante códigos de acceso.'
                        ],
                        [
                            'icon' => 'fa-folder-open',
                            'title' => 'Contenido educativo',
                            'text' => 'Unidades, temas y materiales organizados para facilitar el aprendizaje.'
                        ],
                        [
                            'icon' => 'fa-clipboard-question',
                            'title' => 'Evaluaciones',
                            'text' => 'Evaluaciones en línea con distintos tipos de preguntas y resultados académicos.'
                        ],
                        [
                            'icon' => 'fa-user-check',
                            'title' => 'Asistencia',
                            'text' => 'Registro y control de asistencia de los estudiantes de cada aula.'
                        ],
                        [
                            'icon' => 'fa-brain',
                            'title' => 'Aprendizaje personalizado',
                            'text' => 'Análisis del rendimiento y recomendaciones para reforzar áreas de dificultad.'
                        ],
                    ];
                @endphp


                @foreach ($features as $feature)

                    <article class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">

                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <i class="fa-solid {{ $feature['icon'] }}"></i>
                        </div>

                        <h3 class="mt-5 text-lg font-bold text-slate-900">
                            {{ $feature['title'] }}
                        </h3>

                        <p class="mt-2 leading-7 text-slate-600">
                            {{ $feature['text'] }}
                        </p>

                    </article>

                @endforeach

            </div>
        </div>
    </section>



    <section id="materias" class="bg-slate-100 py-24">
        <div class="mx-auto max-w-7xl px-6">

            <div class="mb-12 text-center">
                <p class="font-semibold text-blue-600">
                    Áreas académicas
                </p>

                <h2 class="mt-2 text-3xl font-bold text-slate-900">
                    Materias disponibles
                </h2>
            </div>


            <div class="grid gap-6 md:grid-cols-3">

                <div class="rounded-2xl bg-white p-8 shadow-sm">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-blue-100 text-xl text-blue-600">
                        <i class="fa-solid fa-calculator"></i>
                    </div>

                    <h3 class="mt-5 text-xl font-bold">
                        Matemáticas
                    </h3>

                    <p class="mt-3 text-slate-600">
                        Fortalece el razonamiento lógico, cálculo y resolución de problemas.
                    </p>
                </div>


                <div class="rounded-2xl bg-white p-8 shadow-sm">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-violet-100 text-xl text-violet-600">
                        <i class="fa-solid fa-atom"></i>
                    </div>

                    <h3 class="mt-5 text-xl font-bold">
                        Física
                    </h3>

                    <p class="mt-3 text-slate-600">
                        Comprende los fenómenos físicos mediante conceptos, ejercicios y evaluaciones.
                    </p>
                </div>


                <div class="rounded-2xl bg-white p-8 shadow-sm">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-100 text-xl text-emerald-600">
                        <i class="fa-solid fa-flask"></i>
                    </div>

                    <h3 class="mt-5 text-xl font-bold">
                        Química
                    </h3>

                    <p class="mt-3 text-slate-600">
                        Aprende principios químicos mediante contenido organizado y actividades.
                    </p>
                </div>

            </div>
        </div>
    </section>


    {{-- ROLES --}}
    <section id="roles" class="py-24">
        <div class="mx-auto max-w-7xl px-6">

            <div class="text-center">
                <p class="font-semibold text-blue-600">
                    Para todos
                </p>

                <h2 class="mt-2 text-3xl font-bold text-slate-900">
                    Una plataforma adaptada a cada usuario
                </h2>
            </div>


            <div class="mt-14 grid gap-8 lg:grid-cols-2">

                <div class="rounded-3xl border border-slate-200 bg-white p-8">

                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-blue-600 text-xl text-white">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>

                    <h3 class="mt-5 text-2xl font-bold">
                        Profesores
                    </h3>

                    <p class="mt-3 text-slate-600">
                        Gestiona cursos, aulas, contenidos, evaluaciones
                        y resultados de tus estudiantes.
                    </p>
                </div>


                <div class="rounded-3xl border border-slate-200 bg-white p-8">

                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-600 text-xl text-white">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>

                    <h3 class="mt-5 text-2xl font-bold">
                        Estudiantes
                    </h3>

                    <p class="mt-3 text-slate-600">
                        Inscríbete en cursos, revisa contenidos, realiza evaluaciones,
                        consulta tu progreso y recibe recomendaciones.
                    </p>
                </div>

            </div>
        </div>
    </section>


    <section class="bg-blue-600 py-20">
        <div class="mx-auto max-w-4xl px-6 text-center">

            <h2 class="text-3xl font-bold text-white sm:text-4xl">
                Empieza a aprender hoy
            </h2>

            <p class="mx-auto mt-4 max-w-2xl text-blue-100">
                Accede a una plataforma creada para organizar y mejorar
                tu experiencia académica.
            </p>

            @guest

                <div class="mt-8 flex justify-center gap-4">
                    <a
                        href="{{ route('register') }}"
                        class="rounded-xl bg-white px-6 py-3 font-semibold text-blue-700 hover:bg-blue-50"
                    >
                        Crear cuenta
                    </a>

                    <a
                        href="{{ route('login') }}"
                        class="rounded-xl border border-white/40 px-6 py-3 font-semibold text-white hover:bg-white/10"
                    >
                        Iniciar sesión
                    </a>
                </div>

            @endguest

        </div>
    </section>


    <footer class="bg-slate-950 py-10 text-slate-400">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 md:flex-row">

            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-white">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>

                <span class="font-semibold text-white">
                    E-Learning
                </span>
            </div>

            <p class="text-sm"> 
                 {{ date('Y') }} Plataforma E-Learning.
            </p>

        </div>
    </footer>

</body>
</html>