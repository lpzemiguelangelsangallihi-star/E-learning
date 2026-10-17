@props([
    'type' => 'info',
])

@php
    $styles = match ($type) {
        'success' => ['box' => 'border-green-200 bg-green-50 text-green-800', 'icon' => 'fa-circle-check text-[#16A34A]'],
        'warning' => ['box' => 'border-yellow-200 bg-yellow-50 text-yellow-800', 'icon' => 'fa-triangle-exclamation text-[#EAB308]'],
        'danger'  => ['box' => 'border-red-200 bg-red-50 text-red-800', 'icon' => 'fa-circle-xmark text-[#DC2626]'],
        default   => ['box' => 'border-blue-200 bg-blue-50 text-blue-800', 'icon' => 'fa-circle-info text-[#2563EB]'],
    };
@endphp

<div
    role="alert"
    {{ $attributes->class(['flex items-start gap-3 rounded-lg border p-4 text-sm', $styles['box']]) }}
>
    <i class="fa-solid {{ $styles['icon'] }} mt-0.5" aria-hidden="true"></i>

    <div class="flex-1">{{ $slot }}</div>
</div>
