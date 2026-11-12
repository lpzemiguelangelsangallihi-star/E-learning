@props(['message' => '¡Hola! Soy Sabio, tu guía de estudio.', 'compact' => false])

<div
    @class([
        'auth-scene relative flex items-center justify-center overflow-hidden',
        'h-64 pb-12 lg:h-auto lg:pb-0 lg:min-h-[480px]' => !$compact,
        'sabio-mini h-52 w-44 shrink-0' => $compact,
    ])
    data-mascot-scene
>

    <svg viewBox="0 0 320 400" class="relative z-10 h-full w-full max-w-[320px]" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
        <defs>
            <linearGradient id="mCone" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#93C5FD" stop-opacity="0.55" />
                <stop offset="1" stop-color="#2563EB" stop-opacity="0" />
            </linearGradient>
            <linearGradient id="mShade" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#2563EB" />
                <stop offset="1" stop-color="#1E3A8A" />
            </linearGradient>
            <linearGradient id="mHead" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#60A5FA" />
                <stop offset="1" stop-color="#2563EB" />
            </linearGradient>
            <filter id="mGlow" x="-100%" y="-100%" width="300%" height="300%">
                <feGaussianBlur stdDeviation="6" />
            </filter>
        </defs>

        {{-- Lámpara --}}
        <g class="mascot-lamp">
            <line x1="160" y1="0" x2="160" y2="38" stroke="#64748B" stroke-width="2" />
            <polygon class="mascot-cone" points="140,92 180,92 292,400 28,400" fill="url(#mCone)" />
            <path d="M108 86 L134 40 L186 40 L212 86 Z" fill="url(#mShade)" />
            <ellipse class="mascot-bulb-glow" cx="160" cy="90" rx="22" ry="12" fill="#EAB308" opacity="0.7" filter="url(#mGlow)" />
            <ellipse class="mascot-bulb" cx="160" cy="88" rx="12" ry="6" fill="#FDE68A" />
            {{-- Cordón: al hacer clic se apaga o enciende --}}
            <g data-lamp-toggle style="cursor: pointer; pointer-events: auto;">
                <line x1="204" y1="68" x2="204" y2="112" stroke="#94A3B8" stroke-width="2" />
                <circle cx="204" cy="116" r="6" fill="#EAB308" />
                <rect x="194" y="60" width="20" height="64" fill="transparent" />
            </g>
        </g>

        {{-- Búho Sabio con birrete --}}
        <g class="mascot-bot">
            {{-- Rama --}}
            <rect x="70" y="370" width="180" height="11" rx="5.5" fill="#475569" />
            <rect x="70" y="370" width="180" height="4" rx="2" fill="rgba(255,255,255,.18)" />

            {{-- Patas --}}
            <path d="M138 364 l-5 9 M138 364 l0 10 M138 364 l5 9" stroke="#EAB308" stroke-width="3.5" stroke-linecap="round" />
            <path d="M182 364 l-5 9 M182 364 l0 10 M182 364 l5 9" stroke="#EAB308" stroke-width="3.5" stroke-linecap="round" />

            {{-- Cuerpo y pecho --}}
            <ellipse cx="160" cy="305" rx="64" ry="66" fill="url(#mShade)" />
            <ellipse cx="160" cy="318" rx="40" ry="46" fill="#93C5FD" opacity="0.92" />
            <path d="M138 300 q8 8 16 0 M166 300 q8 8 16 0 M146 322 q8 8 16 0 M158 340 q8 8 16 0 M134 336 q8 8 16 0" fill="none" stroke="#2563EB" stroke-width="2.2" stroke-linecap="round" opacity="0.7" />

            {{-- Cabeza y orejas --}}
            <path d="M104 226 L98 190 L128 208 Z" fill="#1E3A8A" />
            <path d="M216 226 L222 190 L192 208 Z" fill="#1E3A8A" />
            <ellipse cx="160" cy="248" rx="68" ry="58" fill="url(#mHead)" />
            <ellipse cx="160" cy="248" rx="68" ry="58" fill="none" stroke="rgba(255,255,255,.35)" stroke-width="1.5" />

            {{-- Ojos --}}
            <g data-eye="l" transform="translate(135 252)">
                <g class="mascot-blink">
                    <g class="eye-open">
                        <circle r="25" fill="#DBEAFE" stroke="#1E3A8A" stroke-width="3" />
                        <circle data-eye-ball r="17" fill="#fff" />
                        <circle data-pupil r="9" fill="#0F172A" />
                        <circle data-pupil-shine r="2.6" cx="2.5" cy="-2.5" fill="#fff" />
                    </g>
                </g>
                <path class="eye-closed" d="M-14 3 Q0 -11 14 3" fill="none" stroke="#1E3A8A" stroke-width="4" stroke-linecap="round" />
            </g>
            <g data-eye="r" transform="translate(185 252)">
                <g class="mascot-blink">
                    <g class="eye-open">
                        <circle r="25" fill="#DBEAFE" stroke="#1E3A8A" stroke-width="3" />
                        <circle data-eye-ball r="17" fill="#fff" />
                        <circle data-pupil r="9" fill="#0F172A" />
                        <circle data-pupil-shine r="2.6" cx="2.5" cy="-2.5" fill="#fff" />
                    </g>
                </g>
                <path class="eye-closed" d="M-14 3 Q0 -11 14 3" fill="none" stroke="#1E3A8A" stroke-width="4" stroke-linecap="round" />
            </g>

            {{-- Pico --}}
            <path d="M152 266 L168 266 L160 282 Z" fill="#EAB308" />

            {{-- Birrete --}}
            <path d="M132 206 L132 224 Q160 236 188 224 L188 206 Z" fill="#172554" />
            <polygon points="96,200 160,174 224,200 160,224" fill="#1E293B" stroke="#475569" stroke-width="1.5" stroke-linejoin="round" />
            <circle cx="160" cy="199" r="3.5" fill="#EAB308" />
            <g class="mascot-tassel">
                <path d="M160 199 L212 204 L212 232" fill="none" stroke="#EAB308" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                <rect x="207" y="230" width="10" height="16" rx="3" fill="#EAB308" />
            </g>

            {{-- Alas: se levantan para taparse los ojos con la contraseña --}}
            <ellipse class="mascot-hand hand-l" cx="98" cy="306" rx="20" ry="38" fill="#1E3A8A" stroke="#60A5FA" stroke-width="2" />
            <ellipse class="mascot-hand hand-r" cx="222" cy="306" rx="20" ry="38" fill="#1E3A8A" stroke="#60A5FA" stroke-width="2" />
        </g>
    </svg>

    @unless ($compact)
        <p class="absolute inset-x-4 bottom-3 z-20 text-center text-xs font-medium text-blue-100/90 lg:bottom-6 lg:text-sm" data-mascot-message>
            <span class="inline-block rounded-full border border-white/15 bg-white/10 px-4 py-1.5 backdrop-blur">
                {{ $message }}
            </span>
        </p>
    @endunless
</div>
