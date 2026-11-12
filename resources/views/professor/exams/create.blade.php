@php
    $input = 'block w-full rounded-lg border-slate-300 px-4 py-2.5 text-sm text-slate-800 focus:border-[#2563EB] focus:ring-[#2563EB]';
    $label = 'mb-1 block text-sm font-medium text-slate-700';
@endphp

<x-professor-layout title="Nuevo examen" description="Elige el tema y define las reglas básicas del examen.">

    <x-flash />

    @if ($errors->any())
        <x-ui.alert type="danger" class="mb-6">Revisa los campos marcados.</x-ui.alert>
    @endif

    @if ($lecciones->isEmpty())
        <x-ui.card>
            <x-ui.empty icon="fa-solid fa-layer-group" title="Primero crea un tema"
                text="Los exámenes se asocian a un tema de tus cursos. Crea unidades y temas en la estructura del curso.">
                <x-ui.button :href="route('professor.courses.index')" icon="fa-solid fa-book-open">Ir a mis cursos</x-ui.button>
            </x-ui.empty>
        </x-ui.card>
    @else
        <form method="POST" action="{{ route('professor.exams.store') }}" data-loading-form data-loading-text="Creando...">
            @csrf

            <x-ui.card>
                <div class="grid gap-5 md:grid-cols-2">
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

                    <div class="md:col-span-2">
                        <label for="titulo" class="{{ $label }}">Título</label>
                        <input id="titulo" type="text" name="titulo" value="{{ old('titulo') }}" required maxlength="200" class="{{ $input }}">
                        @error('titulo') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="descripcion" class="{{ $label }}">Descripción / instrucciones</label>
                        <textarea id="descripcion" name="descripcion" rows="3" maxlength="2000" class="{{ $input }}">{{ old('descripcion') }}</textarea>
                        @error('descripcion') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="tiempo_limite" class="{{ $label }}">Tiempo límite (minutos)</label>
                        <input id="tiempo_limite" type="number" name="tiempo_limite" min="1" max="600" value="{{ old('tiempo_limite') }}" placeholder="Sin límite" class="{{ $input }}">
                        @error('tiempo_limite') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="intentos_permitidos" class="{{ $label }}">Intentos permitidos</label>
                        <input id="intentos_permitidos" type="number" name="intentos_permitidos" min="1" max="20" value="{{ old('intentos_permitidos', 1) }}" required class="{{ $input }}">
                        @error('intentos_permitidos') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="puntaje_maximo" class="{{ $label }}">Nota máxima</label>
                        <input id="puntaje_maximo" type="number" name="puntaje_maximo" min="1" max="999" step="0.01" value="{{ old('puntaje_maximo', 100) }}" required class="{{ $input }}">
                        @error('puntaje_maximo') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <x-ui.button type="submit" icon="fa-solid fa-arrow-right">Crear y agregar preguntas</x-ui.button>
                    <x-ui.button :href="route('professor.exams.index')" variant="secondary">Cancelar</x-ui.button>
                </div>
            </x-ui.card>
        </form>
    @endif

</x-professor-layout>
