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


    
    <section id="inicio" class="lp-hero">
        <div class="lp-sky"></div>
        <div class="lp-inner">
            <span class="lp-badge">Plataforma educativa inteligente</span>
            <h1>Aprende, enseña y mejora tu rendimiento <span>en un solo lugar</span></h1>
            <p class="lp-lead">Nuestra plataforma E-Learning permite gestionar cursos, aulas virtuales, materiales, evaluaciones y progreso académico, ofreciendo una experiencia organizada para estudiantes y profesores.</p>
            <div class="lp-cta">
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
        <div class="lp-m lp-m1"><svg viewBox="0 0 1440 400" preserveAspectRatio="none" aria-hidden="true"><path d="M0 400 L0 169 L30 178 L57 141 L100 145 L144 143 L186 161 L214 183 L242 165 L285 183 L329 156 L362 201 L406 227 L450 197 L477 226 L504 194 L534 175 L564 193 L608 178 L655 171 L699 194 L731 183 L774 203 L818 162 L850 190 L893 185 L929 187 L969 179 L1002 214 L1050 214 L1078 196 L1120 190 L1156 212 L1191 201 L1219 152 L1258 155 L1294 153 L1335 181 L1382 142 L1425 199 L1440 400 Z" fill="rgba(37,99,235,.28)"/></svg></div>
        <div class="lp-m lp-m2"><svg viewBox="0 0 1440 400" preserveAspectRatio="none" aria-hidden="true"><path d="M0 400 L0 231 L37 261 L81 283 L109 287 L143 247 L190 205 L239 270 L285 257 L332 279 L367 269 L414 237 L454 239 L499 222 L526 230 L561 225 L594 243 L635 224 L675 244 L709 271 L748 271 L782 262 L819 262 L857 280 L887 221 L917 231 L950 213 L994 225 L1029 209 L1068 253 L1113 256 L1143 268 L1185 294 L1231 268 L1258 246 L1308 299 L1355 283 L1393 238 L1431 203 L1440 400 Z" fill="#13204a"/></svg></div>
        <div class="lp-m lp-m3"><svg viewBox="0 0 1440 400" preserveAspectRatio="none" aria-hidden="true"><path d="M0 400 L0 308 L32 274 L64 312 L93 303 L120 280 L164 286 L193 358 L238 278 L270 327 L300 329 L337 326 L378 290 L419 353 L459 317 L494 291 L523 334 L572 304 L620 299 L646 302 L688 311 L736 320 L762 331 L797 343 L825 329 L859 319 L890 309 L923 320 L973 318 L1019 298 L1069 344 L1102 343 L1151 343 L1183 319 L1220 338 L1246 344 L1287 296 L1335 328 L1372 313 L1421 368 L1440 400 Z" fill="#0B1226"/><path d="M0 308 L32 274 L64 312 L93 303 L120 280 L164 286 L193 358 L238 278 L270 327 L300 329 L337 326 L378 290 L419 353 L459 317 L494 291 L523 334 L572 304 L620 299 L646 302 L688 311 L736 320 L762 331 L797 343 L825 329 L859 319 L890 309 L923 320 L973 318 L1019 298 L1069 344 L1102 343 L1151 343 L1183 319 L1220 338 L1246 344 L1287 296 L1335 328 L1372 313 L1421 368" fill="none" stroke="#60A5FA" stroke-opacity=".55" stroke-width="1.5"/></svg></div>
        <x-landing.sabio />
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