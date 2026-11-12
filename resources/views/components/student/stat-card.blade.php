@props([
    'title',
    'value',
    'icon',
    'type' => 'primary',
])

{{-- Misma tarjeta que x-ui.stat-card, para que admin, profesor y estudiante se vean igual --}}
<x-ui.stat-card
    :title="$title"
    :value="$value"
    :icon="$icon"
    :type="$type"
/>
