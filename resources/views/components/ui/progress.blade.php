@props([
    'value' => 0,
    'showLabel' => true,
])

@php
    $value = max(0, min(100, (float) $value));

    $color = match (true) {
        $value >= 100 => 'bg-[#16A34A]',
        default       => 'bg-[#2563EB]',
    };
@endphp

<div {{ $attributes }}>
    @if ($showLabel)
        <div class="mb-1 flex justify-between text-xs font-medium text-slate-500">
            <span>Progreso</span>
            <span>{{ rtrim(rtrim(number_format($value, 1), '0'), '.') }}%</span>
        </div>
    @endif

    <div
        class="h-2 w-full overflow-hidden rounded-full bg-slate-100"
        role="progressbar"
        aria-valuenow="{{ $value }}"
        aria-valuemin="0"
        aria-valuemax="100"
    >
        <div class="h-full rounded-full {{ $color }} transition-all duration-200" style="width: {{ $value }}%"></div>
    </div>
</div>
