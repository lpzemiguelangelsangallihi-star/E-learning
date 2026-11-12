@php
    $input = 'block w-full rounded-lg border-slate-300 px-4 py-2.5 text-sm text-slate-800 focus:border-[#2563EB] focus:ring-[#2563EB]';
    $total = $preguntas->count();
    $respondida = function ($p) use ($respuestas) {
        $v = $respuestas[$p->id] ?? null;
        return is_array($v) ? count($v) > 0 : ($v !== null && trim((string) $v) !== '');
    };
    $sinResponder = $preguntas->filter(fn ($p) => ! $respondida($p))->count();
@endphp

<div class="space-y-6">

    {{-- Barra superior: título, progreso y tiempo --}}
    <x-ui.card>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#2563EB]">Intento {{ $intento->numero_intento }} de {{ $examen->intentos_permitidos }}</p>
                <h2 class="truncate text-lg font-bold text-slate-800">{{ $examen->titulo }}</h2>
                <p class="text-sm text-slate-500">Pregunta {{ $actual + 1 }} de {{ $total }} · {{ $total - $sinResponder }} respondida(s)</p>
            </div>

            @if ($restante !== null)
                <div
                    x-data="{
                        s: {{ (int) $restante }},
                        timer: null,
                        fmt() {
                            const m = Math.floor(this.s / 60), r = this.s % 60;
                            return String(m).padStart(2, '0') + ':' + String(r).padStart(2, '0');
                        },
                        init() {
                            this.timer = setInterval(() => {
                                if (this.s <= 0) { clearInterval(this.timer); $wire.finalizar(); return; }
                                this.s--;
                            }, 1000);
                        },
                        destroy() { clearInterval(this.timer); }
                    }"
                    :class="s <= 60 ? 'border-red-200 bg-red-50 text-[#DC2626]' : 'border-slate-200 bg-slate-50 text-slate-700'"
                    class="flex items-center gap-2 rounded-lg border px-4 py-2 text-lg font-bold tabular-nums"
                    role="timer" aria-live="off"
                >
                    <i class="fa-regular fa-clock" aria-hidden="true"></i>
                    <span x-text="fmt()">--:--</span>
                </div>
            @endif
        </div>

        <div class="mt-4">
            <x-ui.progress :value="$total ? (($total - $sinResponder) / $total) * 100 : 0" />
        </div>
    </x-ui.card>

    @if ($aviso)
        <x-ui.alert type="warning">{{ $aviso }}</x-ui.alert>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_16rem]">

        {{-- Pregunta actual --}}
        <x-ui.card>
            @if ($pregunta)
                <div wire:key="pregunta-{{ $pregunta->id }}">
                    <div class="mb-3 flex items-center gap-2">
                        <x-ui.badge type="primary">
                            {{ match ($pregunta->tipo) {
                                'varias_respuestas' => 'Selecciona todas las correctas',
                                'verdadero_falso' => 'Verdadero o falso',
                                'respuesta_corta' => 'Respuesta corta',
                                default => 'Una respuesta correcta',
                            } }}
                        </x-ui.badge>
                        <span class="text-xs text-slate-500">{{ rtrim(rtrim(number_format((float) $pregunta->puntaje, 2), '0'), '.') }} pts</span>
                    </div>

                    <p class="mb-5 whitespace-pre-line text-base font-semibold text-slate-800">{{ $pregunta->pregunta }}</p>

                    @if ($pregunta->tipo === 'respuesta_corta')
                        <label for="resp-{{ $pregunta->id }}" class="sr-only">Tu respuesta</label>
                        <input id="resp-{{ $pregunta->id }}" type="text" maxlength="500" autocomplete="off"
                            wire:model.blur="respuestas.{{ $pregunta->id }}" placeholder="Escribe tu respuesta" class="{{ $input }}">
                    @else
                        <div class="space-y-2">
                            @foreach ($pregunta->opciones as $opcion)
                                <label wire:key="op-{{ $opcion->id }}"
                                    class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-3 text-sm text-slate-700 transition hover:border-[#2563EB] hover:bg-blue-50/40">
                                    @if ($pregunta->tipo === 'varias_respuestas')
                                        <input type="checkbox" value="{{ $opcion->id }}"
                                            wire:model.live="respuestas.{{ $pregunta->id }}"
                                            class="mt-0.5 rounded border-slate-300 text-[#2563EB] focus:ring-[#2563EB]">
                                    @else
                                        <input type="radio" name="p-{{ $pregunta->id }}" value="{{ $opcion->id }}"
                                            wire:model.live="respuestas.{{ $pregunta->id }}"
                                            class="mt-0.5 text-[#2563EB] focus:ring-[#2563EB]">
                                    @endif
                                    <span>{{ $opcion->opcion }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5">
                    <button type="button" wire:click="ir({{ $actual - 1 }})" @disabled($actual === 0)
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Anterior
                    </button>

                    @if ($actual < $total - 1)
                        <button type="button" wire:click="ir({{ $actual + 1 }})"
                            class="inline-flex items-center gap-2 rounded-lg bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1D4ED8]">
                            Siguiente <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </button>
                    @else
                        <button type="button" wire:click="finalizar" wire:loading.attr="disabled"
                            wire:confirm="{{ $sinResponder > 0 ? "Tienes {$sinResponder} pregunta(s) sin responder. ¿Entregar de todos modos?" : '¿Entregar el examen? No podrás cambiar tus respuestas.' }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-[#16A34A] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#15803D] disabled:opacity-60">
                            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Entregar examen
                        </button>
                    @endif
                </div>
            @endif
        </x-ui.card>

        {{-- Navegación --}}
        <div class="space-y-4">
            <x-ui.card>
                <p class="mb-3 text-sm font-bold text-slate-800">Preguntas</p>

                <div class="grid grid-cols-5 gap-2">
                    @foreach ($preguntas as $i => $p)
                        <button type="button" wire:click="ir({{ $i }})" wire:key="nav-{{ $p->id }}"
                            @class([
                                'flex h-9 items-center justify-center rounded-lg text-sm font-semibold transition',
                                'bg-[#2563EB] text-white' => $i === $actual,
                                'bg-green-50 text-green-700 ring-1 ring-green-600/20 hover:bg-green-100' => $i !== $actual && $respondida($p),
                                'bg-slate-100 text-slate-600 hover:bg-slate-200' => $i !== $actual && ! $respondida($p),
                            ])
                            aria-label="Ir a la pregunta {{ $i + 1 }}">
                            {{ $i + 1 }}
                        </button>
                    @endforeach
                </div>

                <p class="mt-3 text-xs text-slate-500">Tus respuestas se guardan automáticamente.</p>
            </x-ui.card>

            <button type="button" wire:click="finalizar" wire:loading.attr="disabled"
                wire:confirm="{{ $sinResponder > 0 ? "Tienes {$sinResponder} pregunta(s) sin responder. ¿Entregar de todos modos?" : '¿Entregar el examen? No podrás cambiar tus respuestas.' }}"
                class="w-full rounded-lg bg-[#16A34A] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#15803D] disabled:opacity-60">
                <i class="fa-solid fa-paper-plane mr-1" aria-hidden="true"></i> Entregar examen
            </button>
        </div>
    </div>
</div>
