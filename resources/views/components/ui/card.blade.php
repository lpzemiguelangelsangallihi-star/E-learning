@props([
    'padding' => true,
    'hover' => false,
])

<div
    {{ $attributes->class([
        'rounded-xl border border-slate-200 bg-white shadow-sm',
        'p-5 sm:p-6' => $padding,
        'transition duration-200 hover:border-blue-200 hover:shadow-md' => $hover,
    ]) }}
>
    {{ $slot }}
</div>