@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'wide' => false,
    'mascot' => null,
])

@php
    $mensaje = $mascot ?? match (true) {
        request()->routeIs('register') => '¡Vamos a crear tu cuenta!',
        request()->routeIs('password.request') => 'No pasa nada, te ayudo a recuperarla.',
        request()->routeIs('password.reset') => 'Elige una contraseña segura.',
        request()->routeIs('two-factor.login') => 'Una verificación más y listo.',
        request()->routeIs('password.confirm') => 'Zona protegida, confirma tu identidad.',
        request()->routeIs('verification.notice') => 'Revisa tu correo, ya te escribí.',
        default => '¡Hola! Soy Sabio, tu guía de estudio.',
    };
@endphp

<x-guest-layout>
    <script>
        try {
            @if (request()->routeIs('login'))
                {{-- Login: siempre se reproduce la animación de apertura --}}
                document.documentElement.classList.add('auth-intro');
            @else
                if (sessionStorage.getItem('authIntro')) {
                    document.documentElement.classList.add('auth-quick');
                }
            @endif
            sessionStorage.setItem('authIntro', '1');
        } catch (e) {}
    </script>
    <div class="auth-bg flex items-center justify-center px-4 py-10 sm:px-6">

        {{-- Fondo animado: líneas fluidas + luces en la paleta azul, acento amarillo --}}
        <svg class="auth-swirl" viewBox="0 0 1200 800" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
            <defs>
                <linearGradient id="authLineA" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0" stop-color="#60A5FA" stop-opacity="0" />
                    <stop offset="0.5" stop-color="#60A5FA" stop-opacity="0.7" />
                    <stop offset="1" stop-color="#2563EB" stop-opacity="0" />
                </linearGradient>
                <linearGradient id="authLineB" x1="0" y1="1" x2="1" y2="0">
                    <stop offset="0" stop-color="#EAB308" stop-opacity="0" />
                    <stop offset="0.5" stop-color="#EAB308" stop-opacity="0.55" />
                    <stop offset="1" stop-color="#EAB308" stop-opacity="0" />
                </linearGradient>
            </defs>
            <path d="M-100 620 C 200 300, 420 820, 700 480 S 1100 120, 1320 340" stroke="url(#authLineA)" />
            <path d="M-100 700 C 240 380, 480 880, 760 560 S 1120 220, 1320 430" stroke="url(#authLineB)" />
            <path d="M-100 540 C 160 220, 380 740, 640 400 S 1060 40, 1320 250" stroke="url(#authLineA)" />
            <path d="M-100 780 C 280 460, 520 940, 820 640 S 1140 320, 1320 520" stroke="url(#authLineB)" />
        </svg>

        <div class="auth-orb -left-24 top-0 h-80 w-80 bg-[#2563EB]/40"></div>
        <div class="auth-orb -bottom-24 right-0 h-96 w-96 bg-[#1E3A8A]/70" style="animation-delay: -6s"></div>
        <div class="auth-orb left-1/2 top-1/3 h-52 w-52 bg-[#EAB308]/10" style="animation-delay: -3s"></div>

        {{-- Figuras de aprendizaje que flotan y siguen el cursor --}}
        @php
            // [contenido, icono|texto, izquierda %, arriba %, tamaño px, profundidad, retraso s, duración s, dorado, solo escritorio]
            $floaters = [
                ['i', 'fa-atom',                  8, 14, 68, 28, 0.0, 7.0, false, false],
                ['i', 'fa-calculator',           18, 62, 62, 18, 0.6, 8.0, false, false],
                ['i', 'fa-flask',                 6, 82, 58, 34, 1.1, 6.5, true,  true],
                ['t', 'π',                       27, 26, 54, 40, 0.3, 7.5, false, true],
                ['i', 'fa-book-open',            80, 12, 66, 24, 0.8, 7.2, false, false],
                ['i', 'fa-lightbulb',            88, 46, 60, 36, 0.2, 6.8, true,  false],
                ['t', 'E=mc²',                   73, 74, 78, 20, 1.4, 8.4, false, true],
                ['i', 'fa-pencil',               90, 82, 52, 30, 0.9, 7.8, false, true],
                ['t', '∑',                       70, 30, 50, 44, 1.7, 6.9, true,  true],
                ['i', 'fa-microscope',           14, 40, 52, 22, 1.9, 7.4, false, true],
            ];
        @endphp

        @foreach ($floaters as [$kind, $content, $left, $top, $size, $depth, $delay, $dur, $gold, $desktop])
            <div
                data-depth="{{ $depth }}"
                aria-hidden="true"
                @class(['auth-float', 'is-gold' => $gold, 'lg-only' => $desktop])
                style="left: {{ $left }}%; top: {{ $top }}%;"
            >
                <div style="width: {{ $size }}px; height: {{ $size }}px; --d: {{ $delay }}s; --dur: {{ $dur }}s; --r0: {{ $loop->odd ? -8 : 6 }}deg; --r1: {{ $loop->odd ? 8 : -6 }}deg; font-size: {{ $kind === 'i' ? round($size * 0.4) : round($size * 0.32) }}px;">
                    @if ($kind === 'i')
                        <i class="fa-solid {{ $content }}"></i>
                    @else
                        <span>{{ $content }}</span>
                    @endif
                </div>
            </div>
        @endforeach

        <div @class(['relative z-10 w-full max-w-md', 'lg:max-w-5xl' => $wide, 'lg:max-w-4xl' => !$wide])>

            <div class="relative">
            <div class="auth-glass overflow-hidden rounded-xl">
                <div class="auth-emblem" aria-hidden="true">
                    <span><i class="fa-solid fa-graduation-cap"></i></span>
                </div>

                <span class="auth-corner tl"></span>
                <span class="auth-corner br"></span>

                <div class="grid lg:grid-cols-[5fr_6fr]">

                <x-auth.mascot :message="$mensaje" />

                <div class="auth-stagger relative z-10 p-7 sm:p-9">

                    <a href="{{ url('/') }}" class="mb-7 inline-flex items-center gap-3 text-lg font-bold tracking-tight text-white">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#2563EB] shadow-lg shadow-blue-900/40 ring-1 ring-white/20">
                            <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                        </span>
                        E-Learning
                    </a>

                    <div class="mb-7">
                        @if ($eyebrow)
                            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[#EAB308]">
                                {{ $eyebrow }}
                            </p>
                        @endif

                        <h1 class="text-3xl font-bold tracking-tight text-white">
                            {{ $title }}
                        </h1>

                        @if ($subtitle)
                            <p class="mt-2 text-sm leading-6 text-blue-100/80">
                                {{ $subtitle }}
                            </p>
                        @endif
                    </div>

                    {{ $slot }}

                </div>
                </div>
            </div>

            {{-- Logo que brilla mientras la tarjeta está cerrada (solo en el login).
                 Va fuera del cristal para que su destello no se recorte. --}}
            <div class="auth-intro-logo" aria-hidden="true">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            </div>

            <p class="mt-6 text-center text-xs text-blue-200/70">
                Educación clara, organizada y enfocada en tu progreso.
            </p>
        </div>
    </div>
</x-guest-layout>
