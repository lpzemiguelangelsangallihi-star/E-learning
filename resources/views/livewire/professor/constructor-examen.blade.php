@php
    $input = 'block w-full rounded-lg border-slate-300 px-4 py-2.5 text-sm text-slate-800 focus:border-[#2563EB] focus:ring-[#2563EB]';
    $label = 'mb-1 block text-sm font-medium text-slate-700';
    $btnIcon = 'flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-30';
    $estadoBadge = match ($examen->estado) {
        'publicado' => 'success',
        'cerrado' => 'danger',
        default => 'warning',
    };
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
@endphp

<div class="space-y-6">

    {{-- Mensajes --}}
    @if ($mensaje)
        <div class="flex items-start gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700" role="status">
            <i class="fa-solid fa-circle-check mt-0.5" aria-hidden="true"></i>
            {{ $mensaje }}
        </div>
    @endif

    @if ($error)
        <div class="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
            <i class="fa-solid fa-circle-exclamation mt-0.5" aria-hidden="true"></i>
            {{ $error }}
        </div>
    @endif

    {{-- Cabecera --}}
    <x-ui.card>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge :type="$estadoBadge">{{ ucfirst($examen->estado) }}</x-ui.badge>
                    <span class="text-xs text-slate-500">
                        {{ $examen->leccion?->modulo?->curso?->titulo }} · {{ $examen->leccion?->titulo }}
                    </span>
                </div>

                <p class="mt-2 text-sm text-slate-500">
                    {{ $preguntas->count() }} pregunta(s) · {{ $fmt($totalPuntos) }} puntos en preguntas ·
                    nota máxima {{ $fmt($examen->puntaje_maximo) }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($examen->estado !== 'publicado')
                    <button type="button" wire:click="cambiarEstado('publicado')" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-[#16A34A] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#15803D] disabled:opacity-60">
                        <i class="fa-solid fa-upload" aria-hidden="true"></i>
                        {{ $examen->estado === 'cerrado' ? 'Reabrir' : 'Publicar' }}
                    </button>
                @else
                    <button type="button" wire:click="cambiarEstado('cerrado')" wire:loading.attr="disabled"
                        wire:confirm="¿Cerrar el examen? Los estudiantes ya no podrán iniciar nuevos intentos."
                        class="inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#B91C1C] disabled:opacity-60">
                        <i class="fa-solid fa-lock" aria-hidden="true"></i>
                        Cerrar examen
                    </button>
                @endif

                @if ($examen->estado === 'publicado' && ! $bloqueado)
                    <button type="button" wire:click="cambiarEstado('borrador')" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Volver a borrador
                    </button>
                @endif
            </div>
        </div>

        @if ($bloqueado)
            <x-ui.alert type="warning" class="mt-4">
                Este examen ya tiene intentos de estudiantes. Las preguntas quedaron protegidas para no alterar el historial;
                puedes seguir editando el título, la descripción y cerrar el examen.
            </x-ui.alert>
        @endif
    </x-ui.card>

    {{-- Datos del examen --}}
    <x-ui.card>
        <h2 class="mb-4 text-base font-bold text-slate-800">Datos del examen</h2>

        <form wire:submit="guardarExamen" class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="titulo" class="{{ $label }}">Título</label>
                <input id="titulo" type="text" wire:model="titulo" maxlength="200" class="{{ $input }}">
                @error('titulo') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="descripcion" class="{{ $label }}">Descripción / instrucciones</label>
                <textarea id="descripcion" rows="2" wire:model="descripcion" maxlength="2000" class="{{ $input }}"></textarea>
                @error('descripcion') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="tiempo" class="{{ $label }}">Tiempo límite (minutos)</label>
                <input id="tiempo" type="number" min="1" max="600" wire:model="tiempo_limite" placeholder="Sin límite" class="{{ $input }}">
                @error('tiempo_limite') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="intentos" class="{{ $label }}">Intentos permitidos</label>
                <input id="intentos" type="number" min="1" max="20" wire:model="intentos_permitidos" class="{{ $input }}">
                @error('intentos_permitidos') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="maximo" class="{{ $label }}">Nota máxima</label>
                <input id="maximo" type="number" min="1" max="999" step="0.01" wire:model="puntaje_maximo" class="{{ $input }}">
                @error('puntaje_maximo') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de evaluación IOP</label>
                <select wire:model="tipo_iop" @disabled($bloqueado) class="w-full rounded-lg border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB] disabled:bg-slate-100">
                    @foreach ($tiposIop as $valor => $etiqueta)
                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                    @endforeach
                </select>
                @error('tipo_iop') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                @if ($bloqueado)
                    <p class="mt-1 text-xs text-slate-500">Bloqueado: el examen ya tiene intentos.</p>
                @endif
            </div>

            <div class="flex items-end">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" wire:model="mostrar_correctas" class="rounded border-slate-300 text-[#2563EB] focus:ring-[#2563EB]">
                    Mostrar las respuestas correctas al terminar
                </label>
            </div>

            <div class="md:col-span-2">
                <button type="submit" wire:loading.attr="disabled" wire:target="guardarExamen"
                    class="inline-flex items-center gap-2 rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1D4ED8] disabled:opacity-60">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    Guardar datos
                </button>
            </div>
        </form>
    </x-ui.card>

    {{-- Preguntas --}}
    <x-ui.card>
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-base font-bold text-slate-800">Preguntas</h2>

            @if (! $bloqueado && ! $formAbierto)
                <button type="button" wire:click="nuevaPregunta"
                    class="inline-flex items-center gap-2 rounded-lg bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1D4ED8]">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    Nueva pregunta
                </button>
            @endif
        </div>

        {{-- Formulario de pregunta --}}
        @if ($formAbierto)
            <form wire:submit="guardarPregunta" class="mb-6 space-y-5 rounded-xl border border-blue-200 bg-blue-50/40 p-5">
                <h3 class="text-sm font-bold text-slate-800">
                    {{ $editandoId ? 'Editar pregunta' : 'Nueva pregunta' }}
                </h3>

                <div class="grid gap-5 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <label for="q_tipo" class="{{ $label }}">Tipo de pregunta</label>
                        <select id="q_tipo" wire:model.live="q_tipo" class="{{ $input }}">
                            @foreach ($tipos as $valor => $nombre)
                                <option value="{{ $valor }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="q_puntaje" class="{{ $label }}">Puntaje</label>
                        <input id="q_puntaje" type="number" min="0.01" max="100" step="0.01" wire:model="q_puntaje" class="{{ $input }}">
                        @error('q_puntaje') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="q_texto" class="{{ $label }}">Enunciado</label>
                    <textarea id="q_texto" rows="3" wire:model="q_texto" maxlength="2000" class="{{ $input }}"></textarea>
                    @error('q_texto') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
                </div>

                {{-- Opciones según el tipo --}}
                @if ($q_tipo === 'verdadero_falso')
                    <div>
                        <p class="{{ $label }}">Respuesta correcta</p>
                        <div class="flex gap-6 text-sm text-slate-700">
                            <label class="flex items-center gap-2">
                                <input type="radio" value="verdadero" wire:model="q_vf" class="text-[#2563EB] focus:ring-[#2563EB]"> Verdadero
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" value="falso" wire:model="q_vf" class="text-[#2563EB] focus:ring-[#2563EB]"> Falso
                            </label>
                        </div>
                    </div>
                @else
                    <div>
                        <p class="{{ $label }}">
                            @if ($q_tipo === 'respuesta_corta')
                                Respuestas aceptadas (no distingue mayúsculas ni tildes)
                            @elseif ($q_tipo === 'varias_respuestas')
                                Opciones (marca todas las correctas)
                            @else
                                Opciones (marca la correcta)
                            @endif
                        </p>

                        <div class="space-y-2">
                            @foreach ($q_opciones as $i => $opcion)
                                <div class="flex items-center gap-2" wire:key="opcion-{{ $i }}">
                                    @if ($q_tipo === 'opcion_multiple')
                                        <input type="radio" name="correcta" @checked($opcion['correcta'] ?? false)
                                            wire:click="marcarCorrecta({{ $i }})"
                                            class="text-[#16A34A] focus:ring-[#16A34A]" title="Marcar como correcta">
                                    @elseif ($q_tipo === 'varias_respuestas')
                                        <input type="checkbox" wire:model="q_opciones.{{ $i }}.correcta"
                                            class="rounded border-slate-300 text-[#16A34A] focus:ring-[#16A34A]" title="Marcar como correcta">
                                    @else
                                        <i class="fa-solid fa-check w-4 text-center text-[#16A34A]" aria-hidden="true"></i>
                                    @endif

                                    <input type="text" wire:model="q_opciones.{{ $i }}.texto" maxlength="500"
                                        placeholder="{{ $q_tipo === 'respuesta_corta' ? 'Respuesta aceptada' : 'Texto de la opción' }}"
                                        class="{{ $input }}">

                                    <button type="button" wire:click="quitarOpcion({{ $i }})" class="{{ $btnIcon }}" title="Quitar">
                                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        @if (count($q_opciones) < 8)
                            <button type="button" wire:click="agregarOpcion"
                                class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-[#2563EB] hover:underline">
                                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                {{ $q_tipo === 'respuesta_corta' ? 'Agregar otra respuesta aceptada' : 'Agregar opción' }}
                            </button>
                        @endif
                    </div>
                @endif

                @error('q_opciones') <p class="text-sm text-[#DC2626]">{{ $message }}</p> @enderror

                <div class="flex gap-3">
                    <button type="submit" wire:loading.attr="disabled" wire:target="guardarPregunta"
                        class="inline-flex items-center gap-2 rounded-lg bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1D4ED8] disabled:opacity-60">
                        <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                        Guardar pregunta
                    </button>

                    <button type="button" wire:click="cancelarPregunta"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Cancelar
                    </button>
                </div>
            </form>
        @endif

        {{-- Lista --}}
        @if ($preguntas->isEmpty())
            <x-ui.empty
                icon="fa-solid fa-circle-question"
                title="Aún no hay preguntas"
                text="Agrega la primera pregunta o impórtala desde el banco." />
        @else
            <ol class="space-y-3">
                @foreach ($preguntas as $n => $pregunta)
                    <li wire:key="pregunta-{{ $pregunta->id }}" class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="mb-1 flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-bold text-slate-400">#{{ $n + 1 }}</span>
                                    <x-ui.badge type="primary">{{ $tipos[$pregunta->tipo] ?? $pregunta->tipo }}</x-ui.badge>
                                    <span class="text-xs text-slate-500">{{ $fmt($pregunta->puntaje) }} pts</span>
                                </div>

                                <p class="text-sm font-semibold text-slate-800">{{ $pregunta->pregunta }}</p>

                                <ul class="mt-2 space-y-1 text-sm">
                                    @foreach ($pregunta->opciones as $opcion)
                                        <li @class(['flex items-center gap-2', 'font-semibold text-[#16A34A]' => $opcion->es_correcta, 'text-slate-600' => ! $opcion->es_correcta])>
                                            <i class="fa-solid {{ $opcion->es_correcta ? 'fa-circle-check' : 'fa-circle' }} text-xs" aria-hidden="true"></i>
                                            {{ $opcion->opcion }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            @if (! $bloqueado)
                                <div class="flex shrink-0 gap-1">
                                    <button type="button" wire:click="mover({{ $pregunta->id }}, 'up')" class="{{ $btnIcon }}" title="Subir" @disabled($loop->first)>
                                        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" wire:click="mover({{ $pregunta->id }}, 'down')" class="{{ $btnIcon }}" title="Bajar" @disabled($loop->last)>
                                        <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" wire:click="editarPregunta({{ $pregunta->id }})" class="{{ $btnIcon }}" title="Editar">
                                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" wire:click="duplicarPregunta({{ $pregunta->id }})" class="{{ $btnIcon }}" title="Duplicar">
                                        <i class="fa-regular fa-copy" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" wire:click="eliminarPregunta({{ $pregunta->id }})"
                                        wire:confirm="¿Eliminar esta pregunta?" class="{{ $btnIcon }} hover:!text-[#DC2626]" title="Eliminar">
                                        <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </x-ui.card>

    {{-- Banco de preguntas --}}
    @if (! $bloqueado)
        <x-ui.card>
            <h2 class="text-base font-bold text-slate-800">Banco de preguntas</h2>
            <p class="mb-4 mt-1 text-sm text-slate-500">Reutiliza preguntas de tus otros exámenes.</p>

            @if ($otrosExamenes->isEmpty())
                <p class="text-sm text-slate-500">Todavía no tienes otros exámenes con preguntas.</p>
            @else
                <div class="max-w-md">
                    <label for="banco" class="{{ $label }}">Examen de origen</label>
                    <select id="banco" wire:model.live="bancoExamenId" class="{{ $input }}">
                        <option value="">Selecciona un examen…</option>
                        @foreach ($otrosExamenes as $otro)
                            <option value="{{ $otro->id }}">{{ $otro->titulo }} ({{ $otro->preguntas_count }})</option>
                        @endforeach
                    </select>
                </div>

                @if ($bancoPreguntas->isNotEmpty())
                    <ul class="mt-4 space-y-2">
                        @foreach ($bancoPreguntas as $bp)
                            <li wire:key="banco-{{ $bp->id }}">
                                <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-3 text-sm text-slate-700">
                                    <input type="checkbox" value="{{ $bp->id }}" wire:model="bancoSeleccion"
                                        class="mt-0.5 rounded border-slate-300 text-[#2563EB] focus:ring-[#2563EB]">
                                    <span>
                                        <span class="text-xs text-slate-400">{{ $tipos[$bp->tipo] ?? $bp->tipo }}</span><br>
                                        {{ $bp->pregunta }}
                                    </span>
                                </label>
                            </li>
                        @endforeach
                    </ul>

                    <button type="button" wire:click="importarDelBanco" wire:loading.attr="disabled"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1D4ED8] disabled:opacity-60">
                        <i class="fa-solid fa-file-import" aria-hidden="true"></i>
                        Importar seleccionadas
                    </button>
                @endif
            @endif
        </x-ui.card>
    @endif

</div>
