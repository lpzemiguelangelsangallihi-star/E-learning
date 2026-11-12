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
            'background' => 'bg-green-50',
            'text' => 'text-green-600',
            'orb' => 'bg-[#16A34A]',
        ],

        'warning' => [
            'background' => 'bg-yellow-50',
            'text' => 'text-yellow-600',
            'orb' => 'bg-[#EAB308]',
        ],

        'danger' => [
            'background' => 'bg-red-50',
            'text' => 'text-red-600',
            'orb' => 'bg-[#DC2626]',
        ],

        default => [
            'background' => 'bg-blue-50',
            'text' => 'text-[#2563EB]',
            'orb' => 'bg-[#2563EB]',
        ],
    };

    $numeric = is_numeric($value);
    $decimals = $numeric && str_contains((string) $value, '.') ? strlen(explode('.', (string) $value)[1]) : 0;
@endphp

<x-ui.card hover class="group overflow-hidden">

    {{-- Luz de color detrás del icono: crece al pasar el mouse --}}
    <span class="el-stat-orb {{ $styles['orb'] }}" aria-hidden="true"></span>

    <div class="relative flex items-start justify-between gap-4">

        <div class="min-w-0">

            <p class="text-sm font-medium text-slate-500">
                {{ $title }}
            </p>

            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-800">
                @if ($numeric)<span data-count-up="{{ $value }}" data-decimals="{{ $decimals }}">{{ $value }}</span>@else{{ $value }}@endif
            </p>

            @if (!empty($sparkline) && count($sparkline) > 1)
                @php
                    $max = max($sparkline) ?: 1;
                    $min = min($sparkline);
                    $range = ($max - $min) ?: 1;
                    $step = 100 / (count($sparkline) - 1);
                    $points = collect($sparkline)->map(fn ($v, $i) => round($i * $step, 1) . ',' . round(28 - (($v - $min) / $range) * 24, 1))->implode(' ');
                @endphp
                <svg viewBox="0 0 100 32" class="mt-3 h-8 w-full {{ $styles['text'] }}" preserveAspectRatio="none" aria-hidden="true">
                    <polyline class="el-spark" style="--len: 260" points="{{ $points }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                </svg>
            @endif

            @if ($description)
                <p class="mt-2 text-xs text-slate-500">
                    {{ $description }}
                </p>
            @endif

        </div>

        <div
            class="el-icon flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-lg ring-1 ring-black/5 {{ $styles['background'] }} {{ $styles['text'] }}"
        >
            <i class="{{ $icon }}" aria-hidden="true"></i>
        </div>

    </div>

    {{-- Línea de color que se dibuja al pasar el mouse --}}
    <span class="el-stat-line {{ $styles['orb'] }}" aria-hidden="true"></span>

</x-ui.card>
