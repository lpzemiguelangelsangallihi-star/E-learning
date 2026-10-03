@props([
    'type' => 'info',
])

@php
    $classes = match ($type) {
        'success' =>
            'bg-green-50 text-green-700 ring-green-600/20',

        'warning' =>
            'bg-yellow-50 text-yellow-700 ring-yellow-600/20',

        'danger' =>
            'bg-red-50 text-red-700 ring-red-600/20',

        'primary' =>
            'bg-blue-50 text-blue-700 ring-blue-600/20',

        default =>
            'bg-slate-100 text-slate-700 ring-slate-600/20',
    };
@endphp

<span
    {{ $attributes->class([
        'inline-flex items-center rounded-full px-2.5 py-1',
        'text-xs font-semibold ring-1 ring-inset',
        $classes,
    ]) }}
>
    {{ $slot }}
</span>