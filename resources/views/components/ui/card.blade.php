@props([
    'padding' => true,
    'hover' => false,
])

<div
    {{ $attributes->class([
        'el-card rounded-xl border border-slate-200 bg-white shadow-sm',
        'p-5 sm:p-6' => $padding,
        'el-card-lift' => $hover,
    ]) }}
>
    {{ $slot }}
</div>
