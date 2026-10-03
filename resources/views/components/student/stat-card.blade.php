@props([
    'title',
    'value',
    'icon',
    'type' => 'primary',
])

@php
    $styles = match ($type) {
        'success' => [
            'background' => 'bg-green-50',
            'text' => 'text-green-600',
        ],

        'warning' => [
            'background' => 'bg-yellow-50',
            'text' => 'text-yellow-600',
        ],

        'danger' => [
            'background' => 'bg-red-50',
            'text' => 'text-red-600',
        ],

        default => [
            'background' => 'bg-blue-50',
            'text' => 'text-[#2563EB]',
        ],
    };
@endphp

<x-ui.card>

    <div
        class="
            mb-4 flex h-11 w-11
            items-center justify-center
            rounded-lg
            {{ $styles['background'] }}
            {{ $styles['text'] }}
        "
    >
        <i
            class="{{ $icon }}"
            aria-hidden="true"
        ></i>
    </div>

    <p
        class="
            text-sm font-medium
            text-slate-500
        "
    >
        {{ $title }}
    </p>

    <p
        class="
            mt-1 text-2xl
            font-bold text-slate-800
        "
    >
        {{ $value }}
    </p>

</x-ui.card>