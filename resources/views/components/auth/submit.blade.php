@props([
    'icon' => 'fa-solid fa-right-to-bracket',
])

<button
    type="submit"
    data-submit-button
    {{ $attributes->class([
        'auth-btn el-shine inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#2563EB] px-4 py-3',
        'text-sm font-semibold text-white shadow-lg shadow-blue-900/40 transition duration-200',
        'hover:bg-[#1D4ED8] active:bg-[#1E40AF] focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-offset-2 focus:ring-offset-[#172554]',
        'disabled:cursor-not-allowed disabled:opacity-60',
    ]) }}
>
    <i class="{{ $icon }}" data-submit-icon aria-hidden="true"></i>
    <span data-submit-text>{{ $slot }}</span>
</button>
