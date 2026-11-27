@props([
    'title',
    'value',
    'icon',
    'type' => 'primary',
    'description' => null,
    'sparkline' => null,
])

@php
    $styles = match ($type) {
        'success' => [
            'tile' => 'from-[#16A34A] to-[#166534]',
            'glow' => '#16A34A',
            'text' => 'text-green-600',
            'orb' => 'bg-[#16A34A]',
        ],

        'warning' => [
            'tile' => 'from-[#EAB308] to-[#A16207]',
            'glow' => '#EAB308',
            'text' => 'text-yellow-600',
            'orb' => 'bg-[#EAB308]',
        ],

        'danger' => [
            'tile' => 'from-[#DC2626] to-[#991B1B]',
            'glow' => '#DC2626',
            'text' => 'text-red-600',
            'orb' => 'bg-[#DC2626]',
        ],

        default => [
            'tile' => 'from-[#2563EB] to-[#1E3A8A]',
            'glow' => '#2563EB',
            'text' => 'text-[#2563EB]',
            'orb' => 'bg-[#2563EB]',
        ],
    };

    $numeric = is_numeric($value);
    $decimals = $numeric && str_contains((string) $value, '.') ? strlen(explode('.', (string) $value)[1]) : 0;
@endphp

<x-ui.card hover class="group overflow-hidden">

    {{-- Luz de color en la esquina: crece al pasar el mouse --}}
    <span class="el-stat-orb {{ $styles['orb'] }}" aria-hidden="true"></span>

    <div class="relative flex items-center gap-4">

        <div
            class="stat-tile el-icon bg-gradient-to-br {{ $styles['tile'] }}"
            style="--tile: {{ $styles['glow'] }}"
        >
            <i class="{{ $icon }}" aria-hidden="true"></i>
        </div>

        <div class="min-w-0">
            <p class="text-sm font-medium leading-tight text-slate-500">
                {{ $title }}
            </p>

            <p class="mt-0.5 text-3xl font-bold leading-none tracking-tight text-slate-800">
                @if ($numeric)<span data-count-up="{{ $value }}" data-decimals="{{ $decimals }}">{{ $value }}</span>@else{{ $value }}@endif
            </p>
        </div>

    </div>

    @if (!empty($sparkline) && count($sparkline) > 1)
        @php
            $max = max($sparkline) ?: 1;
            $min = min($sparkline);
            $range = ($max - $min) ?: 1;
            $step = 100 / (count($sparkline) - 1);
            $points = collect($sparkline)->map(fn ($v, $i) => round($i * $step, 1) . ',' . round(28 - (($v - $min) / $range) * 24, 1))->implode(' ');
        @endphp
        <svg viewBox="0 0 100 32" class="relative mt-4 h-8 w-full {{ $styles['text'] }}" preserveAspectRatio="none" aria-hidden="true">
            <polyline class="el-spark" style="--len: 260" points="{{ $points }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
        </svg>
    @endif

    @if ($description)
        <p class="relative mt-3 text-xs text-slate-500">
            {{ $description }}
        </p>
    @endif

    {{-- Línea de color que se dibuja al pasar el mouse --}}
    <span class="el-stat-line {{ $styles['orb'] }}" aria-hidden="true"></span>

</x-ui.card>
