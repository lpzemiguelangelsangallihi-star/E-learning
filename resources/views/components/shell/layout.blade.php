@props([
    'title',
    'description' => null,
    'role',            // Texto del rol: "Profesor", "Administrador", "Estudiante"
    'panel',           // Subtítulo bajo la marca: "Panel del profesor"
    'home',            // Ruta del dashboard
    'items' => [],     // [etiqueta, ruta, activo(bool), icono]
    'bell' => 0,       // null = sin campana · número = notificaciones sin leer
    'flash' => false,  // mostrar mensajes de sesión (success / error / info)
])

@php
    $user = auth()->user();
    $inicial = mb_strtoupper(mb_substr($user->nombre, 0, 1));
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

    {{-- Evita el parpadeo: decide rail / drawer antes de pintar --}}
    <script>
        (function () {
            try {
                var w = window.innerWidth, h = document.documentElement;
                var pref = localStorage.getItem('el.sidebar') === 'collapsed';
                if (w < 768) h.classList.add('sb-drawer');
                else if (w < 1024 || pref) h.classList.add('sb-rail');
                if (w >= 768 && w < 1024) h.classList.add('sb-tablet');
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="app-shell bg-[#F8FAFC] font-sans text-slate-800 antialiased">

    <div class="shell-overlay" data-shell-overlay></div>

    {{-- ============ SIDEBAR DE VIDRIO ============ --}}
    <aside class="shell-sidebar" data-shell-sidebar aria-label="Navegación principal">

        <div class="shell-brand">
            <a href="{{ route($home) }}" class="shell-brand-link" aria-label="Ir al dashboard">
                <span class="shell-logo"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></span>
                <span class="shell-text min-w-0">
                    <span class="block truncate text-base font-bold leading-tight">E-Learning</span>
                    <span class="block truncate text-xs text-blue-200">{{ $panel }}</span>
                </span>
            </a>

            <button type="button" class="shell-collapse" data-shell-collapse
                    aria-label="Contraer o expandir menú" title="Contraer / expandir (Ctrl B)">
                <i class="fa-solid fa-angles-left" aria-hidden="true"></i>
            </button>
        </div>

        <nav class="shell-nav" data-shell-nav>
            @foreach ($items as [$label, $ruta, $activo, $icono])
                <a href="{{ route($ruta) }}"
                   data-shell-link
                   data-label="{{ $label }}"
                   @if ($activo) aria-current="page" @endif
                   @class(['shell-link', 'is-active' => $activo])>
                    <span class="shell-ico"><i class="fa-solid {{ $icono }}" aria-hidden="true"></i></span>
                    <span class="shell-text truncate">{{ $label }}</span>
                </a>
            @endforeach
        </nav>

        <div class="shell-user">
            <div class="shell-avatar" aria-hidden="true">{{ $inicial }}</div>

            <div class="shell-text min-w-0 flex-1">
                <p class="truncate text-sm font-semibold">{{ $user->nombre_completo }}</p>
                <p class="truncate text-xs text-blue-200">{{ $role }}</p>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="shell-logout">
                @csrf
                <button type="submit" data-label="Cerrar sesión" title="Cerrar sesión" aria-label="Cerrar sesión">
                    <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    </aside>

    {{-- ============ CONTENIDO ============ --}}
    <div class="shell-main">

        <header class="shell-top">
            <div class="flex min-w-0 items-center gap-3">
                <button type="button" class="shell-burger" data-shell-open aria-label="Abrir menú">
                    <i class="fa-solid fa-bars" aria-hidden="true"></i>
                </button>

                <div class="min-w-0">
                    <h1 class="truncate text-lg font-bold text-slate-800 sm:text-xl">{{ $title }}</h1>

                    @if ($description)
                        <p class="hidden truncate text-xs text-slate-500 sm:block">{{ $description }}</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <button type="button" class="shell-search" data-shell-jump aria-label="Buscar una sección">
                    <i class="fa-solid fa-magnifying-glass text-slate-400" aria-hidden="true"></i>
                    <span class="hidden md:inline">Ir a una sección…</span>
                    <kbd class="hidden lg:inline">Ctrl K</kbd>
                </button>

                @if ($bell !== null)
                    <a href="{{ route('notificaciones.index') }}"
                       class="shell-icon-btn relative"
                       aria-label="Ver notificaciones" title="Notificaciones">
                        <i class="fa-regular fa-bell" aria-hidden="true"></i>

                        @if ($bell > 0)
                            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#DC2626] px-1 text-[10px] font-bold text-white ring-2 ring-white">
                                {{ $bell > 99 ? '99+' : $bell }}
                            </span>
                        @endif
                    </a>
                @endif

                <div class="shell-chip">
                    <span class="shell-chip-avatar" aria-hidden="true">{{ $inicial }}</span>
                    <span class="hidden text-left sm:block">
                        <span class="block text-sm font-semibold leading-tight text-slate-700">{{ $user->nombre }}</span>
                        <span class="block text-xs leading-tight text-slate-500">{{ $role }}</span>
                    </span>
                </div>
            </div>
        </header>

        <main class="shell-content">
            @if ($flash)
                @if (session('success'))
                    <x-ui.alert type="success" class="mb-6">{{ session('success') }}</x-ui.alert>
                @endif

                @if (session('error'))
                    <x-ui.alert type="danger" class="mb-6">{{ session('error') }}</x-ui.alert>
                @endif

                @if (session('info'))
                    <x-ui.alert type="info" class="mb-6">{{ session('info') }}</x-ui.alert>
                @endif
            @endif

            @isset($actions)
                <div class="mb-6 flex justify-end">{{ $actions }}</div>
            @endisset

            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
