@props([
    'title',
    'description' => null,
])

@php
    $user = auth()->user();

    $nav = [
        ['Dashboard',               'student.dashboard',              'student.dashboard',              'fa-house'],
        ['Mis cursos',              'student.courses.index',          'student.courses.*',              'fa-book-open'],
        ['Evaluaciones',            'student.evaluations.index',      'student.evaluations.*',          'fa-file-circle-check'],
        ['Calificaciones',          'student.grades.index',           'student.grades.*',               'fa-star'],
        ['Mi progreso',             'student.progress.index',         'student.progress.*',             'fa-chart-line'],
        ['Recomendaciones',         'student.recommendations.index',  'student.recommendations.*',      'fa-lightbulb'],
        ['Mi perfil',               'student.profile.edit',           'student.profile.*',              'fa-user'],
    ];

    // "Mis cursos" no debe quedar activo en la pantalla de inscripción.
    $isActive = function (string $pattern, string $route) {
        if ($route === 'student.courses.index') {
            return request()->routeIs('student.courses.*', 'student.lessons.*')
                && !request()->routeIs('student.courses.join');
        }

        return request()->routeIs($pattern);
    };
@endphp

<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} | {{ config('app.name', 'E-Learning') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="bg-[#F8FAFC] font-sans text-slate-800 antialiased">

    <div class="min-h-screen">

        {{-- Overlay móvil (lo controla initializeAdminSidebar en app.js) --}}
        <div
            data-admin-sidebar-overlay
            class="fixed inset-0 z-40 hidden bg-slate-950/50 lg:hidden"
        ></div>


        {{-- SIDEBAR --}}
        <aside
            data-admin-sidebar
            class="
                fixed inset-y-0 left-0 z-50 flex w-64
                -translate-x-full flex-col
                bg-[#1E3A8A] text-white
                transition-transform duration-200
                lg:translate-x-0
            "
        >
            <div class="flex h-16 items-center border-b border-white/10 px-6">
                <a href="{{ route('student.dashboard') }}" class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10">
                        <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                    </span>

                    <span class="text-lg font-bold">E-Learning</span>
                </a>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-6">
                <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-blue-200">
                    Estudiante
                </p>

                @foreach ($nav as [$label, $route, $pattern, $icon])
                    <a
                        href="{{ route($route) }}"
                        @class([
                            'mb-1 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-150',
                            'bg-[#2563EB] text-white' => $isActive($pattern, $route),
                            'text-blue-50 hover:bg-white/10' => !$isActive($pattern, $route),
                        ])
                    >
                        <i class="fa-solid {{ $icon }} w-5 text-center" aria-hidden="true"></i>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-white/10 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/10 font-semibold">
                        {{ mb_strtoupper(mb_substr($user->nombre, 0, 1)) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-white">{{ $user->nombre_completo }}</p>
                        <p class="truncate text-xs text-blue-200">Estudiante</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf

                    <button
                        type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-sm font-semibold text-white transition duration-150 hover:bg-[#DC2626] active:bg-[#B91C1C]"
                    >
                        <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>


        {{-- CONTENIDO --}}
        <div class="min-h-screen lg:pl-64">

            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6 lg:px-8">

                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        data-admin-sidebar-toggle
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100 lg:hidden"
                        aria-label="Abrir menú"
                    >
                        <i class="fa-solid fa-bars" aria-hidden="true"></i>
                    </button>

                    <div class="min-w-0">
                        <h1 class="truncate text-lg font-bold text-slate-800 sm:text-xl">{{ $title }}</h1>

                        @if ($description)
                            <p class="hidden truncate text-xs text-slate-500 sm:block">{{ $description }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-slate-700">{{ $user->nombre }}</p>
                        <p class="text-xs text-slate-500">Estudiante</p>
                    </div>
                </div>
            </header>


            <main class="px-4 py-6 sm:px-6 lg:px-8">

                @if (session('success'))
                    <x-ui.alert type="success" class="mb-6">{{ session('success') }}</x-ui.alert>
                @endif

                @if (session('error'))
                    <x-ui.alert type="danger" class="mb-6">{{ session('error') }}</x-ui.alert>
                @endif

                @if (session('info'))
                    <x-ui.alert type="info" class="mb-6">{{ session('info') }}</x-ui.alert>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>

</html>
