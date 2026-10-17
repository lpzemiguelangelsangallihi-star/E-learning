@props([
    'leccion' => null,
    'action',
    'method' => 'POST',
    'cancelUrl',
    'submitText' => 'Guardar tema',
])

@php
    $input = 'block w-full rounded-lg border-slate-300 px-4 py-3 text-sm text-slate-800 focus:border-[#2563EB] focus:ring-[#2563EB]';
    $label = 'mb-2 block text-sm font-semibold text-slate-700';
@endphp

<form
    method="POST"
    action="{{ $action }}"
    class="space-y-6"
    data-loading-form
    data-loading-text="Guardando..."
>
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>

        <div class="grid gap-5">

            <div>
                <label for="titulo" class="{{ $label }}">Título del tema</label>
                <input
                    id="titulo"
                    name="titulo"
                    type="text"
                    maxlength="200"
                    required
                    value="{{ old('titulo', $leccion?->titulo) }}"
                    class="{{ $input }}"
                >
                @error('titulo')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="descripcion" class="{{ $label }}">
                    Descripción breve <span class="font-normal text-slate-400">(opcional)</span>
                </label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="2"
                    maxlength="1000"
                    class="{{ $input }}"
                >{{ old('descripcion', $leccion?->descripcion) }}</textarea>
                @error('descripcion')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="contenido" class="{{ $label }}">
                    Contenido <span class="font-normal text-slate-400">(opcional)</span>
                </label>
                <textarea
                    id="contenido"
                    name="contenido"
                    rows="8"
                    class="{{ $input }}"
                >{{ old('contenido', $leccion?->contenido) }}</textarea>
                <p class="mt-2 text-xs text-slate-500">
                    Texto de la clase. Los videos y archivos se agregan después, en «Videos y materiales».
                </p>
                @error('contenido')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:max-w-xs">
                <label for="estado" class="{{ $label }}">Estado</label>
                @php $estadoActual = old('estado', $leccion?->estado ?? 'borrador'); @endphp
                <select id="estado" name="estado" required class="{{ $input }}">
                    <option value="borrador" @selected($estadoActual === 'borrador')>Borrador (no visible)</option>
                    <option value="publicado" @selected($estadoActual === 'publicado')>Publicado (visible)</option>
                    <option value="oculto" @selected($estadoActual === 'oculto')>Oculto</option>
                </select>
                @error('estado')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

    </x-ui.card>


    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

        <a
            href="{{ $cancelUrl }}"
            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
        >
            Cancelar
        </a>

        <button
            type="submit"
            data-submit-button
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1D4ED8] disabled:cursor-not-allowed disabled:bg-slate-300"
        >
            <i class="fa-solid fa-floppy-disk" data-submit-icon></i>
            <span data-submit-text>{{ $submitText }}</span>
        </button>

    </div>

</form>
