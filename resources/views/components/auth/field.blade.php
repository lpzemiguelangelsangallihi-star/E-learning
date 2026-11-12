@props([
    'name',
    'label',
    'type' => 'text',
    'icon' => null,
    'id' => null,
])

@php
    $id ??= $name;
    $isPassword = $type === 'password';
@endphp

<div>
    <label for="{{ $id }}" class="mb-2 block text-sm font-semibold text-blue-50">
        {{ $label }}
    </label>

    <div class="relative">
        @if ($icon)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-blue-200/70">
                <i class="{{ $icon }}" aria-hidden="true"></i>
            </span>
        @endif

        <input
            id="{{ $id }}"
            type="{{ $type }}"
            name="{{ $name }}"
            {{ $attributes->class([
                'auth-input block w-full rounded-lg border bg-white/10 py-3 text-sm text-white placeholder:text-blue-200/50',
                'focus:border-[#2563EB] focus:ring-0',
                'border-red-400' => $errors->has($name),
                'border-white/20' => !$errors->has($name),
                'pl-10' => $icon,
                'pl-4' => !$icon,
                'pr-12' => $isPassword,
                'pr-4' => !$isPassword,
            ]) }}
        >

        @if ($isPassword)
            <button
                type="button"
                data-password-toggle="{{ $id }}"
                aria-label="Mostrar contraseña"
                aria-pressed="false"
                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-lg text-blue-200/70 transition hover:text-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-300"
            >
                <i class="fa-regular fa-eye" data-password-icon aria-hidden="true"></i>
            </button>
        @endif
    </div>

    @error($name)
        <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
    @enderror
</div>
