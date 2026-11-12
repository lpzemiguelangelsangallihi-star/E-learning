@php
    $input = 'block w-full rounded-lg border-slate-300 px-4 py-2.5 text-sm text-slate-800 focus:border-[#2563EB] focus:ring-[#2563EB]';
    $label = 'mb-1 block text-sm font-medium text-slate-700';
    $editando = $tarea !== null;
@endphp

<x-professor-layout :title="$editando ? 'Editar tarea' : 'Nueva tarea'" description="Define las instrucciones, la fecha límite y el puntaje.">

    <x-flash />

    @if ($errors->any())
        <x-ui.alert type="danger" class="mb-6">Revisa los campos marcados.</x-ui.alert>
    @endif

    @if ($lecciones->isEmpty())
        <x-ui.card>
            <x-ui.empty icon="fa-solid fa-layer-group" title="Primero crea un tema"
                text="Las tareas se asocian a un tema de tus cursos.">
                <x-ui.button :href="route('professor.courses.index')" icon="fa-solid fa-book-open">Ir a mis cursos</x-ui.button>
            </x-ui.empty>
        </x-ui.card>
    @else
        <form method="POST"
            action="{{ $editando ? route('professor.tasks.update', $tarea) : route('professor.tasks.store') }}"
            enctype="multipart/form-data" data-loading-form data-loading-text="Guardando...">
            @csrf
            @if ($editando) @method('PUT') @endif

            <x-ui.card>
                <div class="grid gap-5 md:grid-cols-2">
                    @if ($editando)
                        <div class="md:col-span-2">
                            <p class="{{ $label }}">Tema</p>
                            <p class="text-sm text-slate-600">{{ $tarea->leccion->modulo->curso->titulo }} › {{ $tarea->leccion->titulo }}</p>
                        </div>
                    @else
                        <div class="md:col-span-2">
                            <label for="leccion_id" class="{{ $label }}">Tema</label>
                            <select id="leccion_id" name="leccion_id" required class="{{ $input }}">
                                <option value="">Selecciona un tema…</option>
                                @foreach ($lecciones->groupBy(fn ($l) => $l->modulo->curso->titulo) as $curso => $items)
                                    <optgroup label="{{ $curso }}">
                                        @foreach ($items as $l)
                                            <option value="{{ $l->id }}" @selected((int) old('leccion_id', $leccionSeleccionada) === $l->id)>
                                                {{ $l->modulo->titulo }} › {{ $l->titulo }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('leccion_id') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="md:col-span-2">
                        <label for="titulo" class="{{ $label }}">Título</label>
                        <input id="titulo" type="text" name="titulo" value="{{ old('titulo', $tarea?->titulo) }}" required maxlength="200" class="{{ $input }}">
                        @error('titulo') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="instrucciones" class="{{ $label }}">Instrucciones</label>
                        <textarea id="instrucciones" name="instrucciones" rows="5" maxlength="5000" class="{{ $input }}">{{ old('instrucciones', $tarea?->instrucciones) }}</textarea>
                        @error('instrucciones') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="fecha_limite" class="{{ $label }}">Fecha límite (opcional)</label>
                        <input id="fecha_limite" type="datetime-local" name="fecha_limite"
                            value="{{ old('fecha_limite', $tarea?->fecha_limite?->format('Y-m-d\TH:i')) }}" class="{{ $input }}">
                        @error('fecha_limite') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="puntaje_maximo" class="{{ $label }}">Puntaje máximo</label>
                        <input id="puntaje_maximo" type="number" name="puntaje_maximo" min="1" max="999" step="0.01"
                            value="{{ old('puntaje_maximo', $tarea ? rtrim(rtrim((string) $tarea->puntaje_maximo, '0'), '.') : 100) }}" required class="{{ $input }}">
                        @error('puntaje_maximo') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="archivo" class="{{ $label }}">Archivo de apoyo (opcional, máx. 10 MB)</label>
                        <input id="archivo" type="file" name="archivo" class="{{ $input }}">
                        @error('archivo') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror

                        @if ($editando && $tarea->archivo)
                            <label class="mt-2 flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="quitar_archivo" value="1" class="rounded border-slate-300 text-[#2563EB] focus:ring-[#2563EB]">
                                Quitar el archivo actual ({{ basename($tarea->archivo) }})
                            </label>
                        @endif
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <x-ui.button type="submit" icon="fa-solid fa-floppy-disk">Guardar tarea</x-ui.button>
                    <x-ui.button :href="route('professor.tasks.index')" variant="secondary">Cancelar</x-ui.button>
                </div>
            </x-ui.card>
        </form>
    @endif

</x-professor-layout>
