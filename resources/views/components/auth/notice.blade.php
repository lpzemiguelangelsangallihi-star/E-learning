@props(['type' => 'success'])

<div
    {{ $attributes->class([
        'mb-5 flex items-start gap-3 rounded-lg border px-4 py-3 text-sm',
        'border-green-400/40 bg-green-500/15 text-green-100' => $type === 'success',
        'border-red-400/40 bg-red-500/15 text-red-100' => $type === 'danger',
        'border-blue-300/40 bg-blue-500/15 text-blue-50' => $type === 'info',
    ]) }}
    role="{{ $type === 'danger' ? 'alert' : 'status' }}"
>
    <i class="fa-solid {{ $type === 'danger' ? 'fa-circle-exclamation' : 'fa-circle-check' }} mt-0.5" aria-hidden="true"></i>
    <div>{{ $slot }}</div>
</div>
