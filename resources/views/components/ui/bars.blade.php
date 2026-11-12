@props([
    'items' => [],   // [['label' => 'Cursos', 'value' => 8, 'color' => '#2563EB', 'icon' => 'fa-solid fa-book-open'], ...]
])

@php
    $max = max(1, collect($items)->max('value') ?? 1);
@endphp

<div class="space-y-5">
    @foreach ($items as $i => $item)
        <div>
            <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                <span class="flex min-w-0 items-center gap-2 font-medium text-slate-600">
                    @if (!empty($item['icon']))
                        <i class="{{ $item['icon'] }} w-4 text-center text-slate-400" aria-hidden="true"></i>
                    @endif
                    <span class="truncate">{{ $item['label'] }}</span>
                </span>

                <span class="font-semibold text-slate-800" data-count-up="{{ $item['value'] }}">{{ $item['value'] }}</span>
            </div>

            <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                <div
                    class="el-bar-fill h-full rounded-full"
                    style="--i: {{ $i }}; width: {{ max(3, ($item['value'] / $max) * 100) }}%; background: {{ $item['color'] ?? '#2563EB' }}"
                ></div>
            </div>
        </div>
    @endforeach
</div>
