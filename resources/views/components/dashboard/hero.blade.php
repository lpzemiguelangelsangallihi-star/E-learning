@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'sabio' => null,   // frase de Sabio; si es null no aparece el búho
])

<section {{ $attributes->class('dash-hero text-white shadow-lg shadow-blue-900/20') }}>

    {{-- Esquinas doradas, igual que el login --}}
    <span class="auth-corner tl" aria-hidden="true"></span>
    <span class="auth-corner br" aria-hidden="true"></span>

    {{-- Luces animadas de fondo --}}
    <div class="auth-orb -right-10 -top-16 h-64 w-64 bg-sky-300/25" aria-hidden="true"></div>
    <div class="auth-orb -bottom-24 left-1/3 h-56 w-56 bg-[#2563EB]/60" style="animation-delay: -5s" aria-hidden="true"></div>

    <div class="relative z-10 flex flex-col gap-4 p-6 sm:p-8 lg:flex-row lg:items-center lg:justify-between">

        <div class="max-w-xl">

            @if ($eyebrow)
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/20">
                    <span class="dash-live h-1.5 w-1.5 rounded-full bg-[#EAB308]"></span>
                    {{ $eyebrow }}
                </span>
            @endif

            <h2 class="mt-4 text-3xl font-bold leading-tight sm:text-4xl">
                {{ $title }}
            </h2>

            @if ($subtitle)
                <p class="mt-2 text-sm text-blue-100 sm:text-base">
                    {{ $subtitle }}
                </p>
            @endif

            @isset($actions)
                <div class="mt-5 flex flex-wrap gap-3">
                    {{ $actions }}
                </div>
            @endisset

        </div>

        @if ($sabio)
            <div class="hidden items-end gap-1 lg:flex">
                <p class="sabio-bubble">{{ $sabio }}</p>

                <x-auth.mascot compact message="" />
            </div>
        @endif

    </div>

    @isset($footer)
        <div class="relative z-10 border-t border-white/10 bg-white/5 px-6 py-4 sm:px-8">
            {{ $footer }}
        </div>
    @endisset

</section>
