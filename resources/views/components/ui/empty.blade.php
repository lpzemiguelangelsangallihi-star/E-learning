@props([
    'icon' => 'fa-solid fa-inbox',
    'title',
    'text' => null,
])

<div {{ $attributes->class(['py-12 text-center']) }}>
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
        <i class="{{ $icon }} text-xl" aria-hidden="true"></i>
    </div>

    <h3 class="mt-4 text-base font-semibold text-slate-800">{{ $title }}</h3>

    @if ($text)
        <p class="mx-auto mt-1 max-w-sm text-sm text-slate-500">{{ $text }}</p>
    @endif

    @if (trim((string) $slot) !== '')
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
