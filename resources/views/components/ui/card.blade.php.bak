@props([
    'padding' => true,
    'hover' => false,
])

<div
    {{ $attributes->class([
        'rounded-xl border border-slate-200 bg-white shadow-sm',
        'p-5 sm:p-6' => $padding,
        'transition duration-200 hover:border-[#2563EB] hover:shadow-md' => $hover,
    ]) }}
>
    {{ $slot }}
</div>