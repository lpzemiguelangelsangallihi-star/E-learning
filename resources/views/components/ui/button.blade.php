@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
    'icon' => null,
])

@php
    $variants = match ($variant) {
        'secondary' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 active:bg-slate-100',
        'success'   => 'bg-[#16A34A] text-white hover:bg-[#15803D] active:bg-[#166534]',
        'danger'    => 'bg-[#DC2626] text-white hover:bg-[#B91C1C] active:bg-[#991B1B]',
        default     => 'bg-[#2563EB] text-white hover:bg-[#1D4ED8] active:bg-[#1E40AF]',
    };

    $classes = "inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition duration-150 focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 {$variants}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<i class="{{ $icon }}" aria-hidden="true"></i>@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<i class="{{ $icon }}" aria-hidden="true"></i>@endif
        {{ $slot }}
    </button>
@endif
