@props([
    'segments' => [],   // [['label' => 'Estudiantes', 'value' => 12, 'color' => '#2563EB'], ...]
    'size' => 160,
    'caption' => 'total',
])

@php
    $total = collect($segments)->sum('value');
    $gap = count(array_filter($segments, fn ($s) => $s['value'] > 0)) > 1 ? 1.2 : 0;
    $offset = 0;
@endphp

<div class="flex flex-col items-center gap-6">

    <div class="el-donut-wrap relative shrink-0" style="width: {{ $size }}px; height: {{ $size }}px">

        {{-- r = 15.9155 → circunferencia = 100, así los porcentajes se usan directo --}}
        <svg viewBox="0 0 42 42" class="h-full w-full -rotate-90" role="img" aria-label="Gráfica de distribución">
            <circle cx="21" cy="21" r="15.9155" fill="none" stroke="#E2E8F0" stroke-width="5" />

            @foreach ($segments as $i => $segment)
                @php
                    $len = $total > 0 ? ($segment['value'] / $total) * 100 : 0;
                @endphp

                @if ($len > 0)
                    <circle
                        class="el-donut"
                        cx="21" cy="21" r="15.9155"
                        fill="none"
                        stroke="{{ $segment['color'] }}"
                        stroke-width="5"
                        style="--i: {{ $i }}; stroke-dasharray: {{ max($len - $gap, 0.1) }} {{ 100 - max($len - $gap, 0.1) }}; stroke-dashoffset: -{{ $offset }};"
                    >
                        <title>{{ $segment['label'] }}: {{ $segment['value'] }}</title>
                    </circle>
                @endif

                @php $offset += $len; @endphp
            @endforeach
        </svg>

        <div class="absolute inset-0 flex flex-col items-center justify-center">
            <span class="text-3xl font-bold text-slate-800" data-count-up="{{ $total }}">{{ $total }}</span>
            <span class="text-xs font-medium text-slate-500">{{ $caption }}</span>
        </div>
    </div>

    <ul class="w-full min-w-0 flex-1 space-y-2.5">
        @foreach ($segments as $segment)
            <li class="flex items-center justify-between gap-3 text-sm">
                <span class="flex min-w-0 items-center gap-2 text-slate-600">
                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $segment['color'] }}"></span>
                    <span class="truncate">{{ $segment['label'] }}</span>
                </span>

                <span class="shrink-0 font-semibold text-slate-800">
                    {{ $segment['value'] }}
                    <span class="ml-1 text-xs font-medium text-slate-400">
                        {{ $total > 0 ? round($segment['value'] / $total * 100) : 0 }}%
                    </span>
                </span>
            </li>
        @endforeach
    </ul>

</div>
