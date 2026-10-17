@php
    $totalTemas = $curso->modulos->sum(fn ($m) => $m->lecciones->count());

    $estadoTipo = match ($curso->estado) {
        'publicado' => 'success',
        'borrador' => 'warning',
        default => 'danger',
    };

    $input = 'block w-full rounded-lg border-slate-300 px-4 py-2.5 text-sm text-slate-800 focus:border-[#2563EB] focus:ring-[#2563EB]';
    $btnPrimary = 'inline-flex items-center justify-center gap-2 rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1D4ED8]';
    $btnIcon = 'flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent';
@endphp

<x-professor-layout
    title="Contenido del curso"
    description="Organiza las unidades y temas, y comparte el código de clase."
>

    <div class="mx-auto max-w-5xl">

        <div class="mb-6">
            <a
                href="{{ route('professor.courses.index') }}"
                class="inline-flex items-center gap-2 text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Volver a mis cursos
            </a>
        </div>

        <x-flash />

        {{-- Resumen del curso + código de clase --}}
        <x-ui.card class="mb-6">

            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">

                <div class="min-w-0">

                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[#2563EB]">
                            {{ $curso->materia?->nombre ?? 'Sin materia' }}
                        </p>

                        <x-ui.badge :type="$estadoTipo">{{ ucfirst($curso->estado) }}</x-ui.badge>
                    </div>

                    <h2 class="mt-2 text-xl font-bold text-slate-800">{{ $curso->titulo }}</h2>

                    <div class="mt-4 flex flex-wrap gap-5 text-sm text-slate-500">
                        <span><i class="fa-solid fa-layer-group mr-1"></i>{{ $curso->modulos->count() }} unidades</span>
                        <span><i class="fa-solid fa-list-check mr-1"></i>{{ $totalTemas }} temas</span>
                        <span><i class="fa-solid fa-user-graduate mr-1"></i>{{ $estudiantes }} estudiantes</span>
                    </div>

                    @if ($curso->estado !== 'publicado')
                        <p class="mt-4 text-sm text-yellow-700">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                            Los estudiantes solo pueden unirse con el código cuando el curso está
                            <strong>publicado</strong>. Publícalo desde «Mis cursos».
                        </p>
                    @endif
                </div>


                <div class="w-full shrink-0 rounded-xl border border-blue-200 bg-blue-50 p-4 lg:w-72">

                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">
                        Código de clase
                    </p>

                    <p
                        id="codigo-clase"
                        class="mt-2 select-all font-mono text-3xl font-bold tracking-[0.25em] text-[#1E3A8A]"
                    >{{ $curso->codigo_clase }}</p>

                    <p class="mt-2 text-xs text-blue-700">
                        Compártelo con tus estudiantes para que se inscriban.
                    </p>

                    <div class="mt-3 flex items-center gap-2">

                        <button
                            type="button"
                            id="copiar-codigo"
                            class="inline-flex items-center gap-2 rounded-lg bg-[#2563EB] px-3 py-2 text-xs font-semibold text-white hover:bg-[#1D4ED8]"
                        >
                            <i class="fa-regular fa-copy"></i>
                            <span>Copiar</span>
                        </button>

                        <form
                            method="POST"
                            action="{{ route('professor.courses.regenerate-code', $curso) }}"
                            onsubmit="return confirm('Se generará un código nuevo y el anterior dejará de funcionar. Los estudiantes ya inscritos no se verán afectados. ¿Continuar?')"
                        >
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-lg border border-blue-300 bg-white px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                            >
                                <i class="fa-solid fa-rotate"></i>
                                Generar otro
                            </button>
                        </form>

                    </div>
                </div>

            </div>

        </x-ui.card>


        {{-- Nueva unidad --}}
        <x-ui.card class="mb-6">

            <h3 class="text-base font-bold text-slate-800">Nueva unidad</h3>

            <form
                method="POST"
                action="{{ route('professor.modules.store', $curso) }}"
                class="mt-4 grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-start"
            >
                @csrf

                <div>
                    <label for="unidad_titulo" class="mb-2 block text-sm font-semibold text-slate-700">Título</label>
                    <input
                        id="unidad_titulo"
                        name="titulo"
                        type="text"
                        maxlength="200"
                        required
                        value="{{ $errors->unidad->any() ? old('titulo') : '' }}"
                        placeholder="Ej. Unidad 1: Funciones"
                        class="{{ $input }}"
                    >
                    @if ($errors->unidad->has('titulo'))
                        <p class="mt-2 text-sm text-red-600">{{ $errors->unidad->first('titulo') }}</p>
                    @endif
                </div>

                <div>
                    <label for="unidad_descripcion" class="mb-2 block text-sm font-semibold text-slate-700">
                        Descripción <span class="font-normal text-slate-400">(opcional)</span>
                    </label>
                    <input
                        id="unidad_descripcion"
                        name="descripcion"
                        type="text"
                        maxlength="1000"
                        value="{{ $errors->unidad->any() ? old('descripcion') : '' }}"
                        class="{{ $input }}"
                    >
                    @if ($errors->unidad->has('descripcion'))
                        <p class="mt-2 text-sm text-red-600">{{ $errors->unidad->first('descripcion') }}</p>
                    @endif
                </div>

                <div class="md:pt-7">
                    <button type="submit" class="{{ $btnPrimary }} w-full md:w-auto">
                        <i class="fa-solid fa-plus"></i>
                        Agregar unidad
                    </button>
                </div>
            </form>

        </x-ui.card>


        {{-- Unidades --}}
        <div class="space-y-5">

            @forelse ($curso->modulos as $modulo)

                @php
                    $bag = $errors->getBag('unidad_'.$modulo->id);
                    $modulos = $curso->modulos;
                @endphp

                <x-ui.card :padding="false">

                    {{-- Cabecera de la unidad --}}
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">

                        <div class="flex min-w-0 items-start gap-4">

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-sm font-bold text-[#2563EB]">
                                {{ $loop->iteration }}
                            </span>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-bold text-slate-800">{{ $modulo->titulo }}</h3>

                                    @if ($modulo->estado === 'inactivo')
                                        <x-ui.badge type="danger">Inactiva</x-ui.badge>
                                    @endif
                                </div>

                                @if ($modulo->descripcion)
                                    <p class="mt-1 text-sm text-slate-500">{{ $modulo->descripcion }}</p>
                                @endif
                            </div>
                        </div>


                        <div class="flex shrink-0 items-center gap-1">

                            <form method="POST" action="{{ route('professor.modules.move', [$curso, $modulo]) }}">
                                @csrf
                                <input type="hidden" name="direccion" value="up">
                                <button type="submit" class="{{ $btnIcon }}" title="Subir unidad" @disabled($loop->first)>
                                    <i class="fa-solid fa-arrow-up"></i>
                                </button>
                            </form>

                            <form method="POST" action="{{ route('professor.modules.move', [$curso, $modulo]) }}">
                                @csrf
                                <input type="hidden" name="direccion" value="down">
                                <button type="submit" class="{{ $btnIcon }}" title="Bajar unidad" @disabled($loop->last)>
                                    <i class="fa-solid fa-arrow-down"></i>
                                </button>
                            </form>

                            <form
                                method="POST"
                                action="{{ route('professor.modules.destroy', [$curso, $modulo]) }}"
                                onsubmit="return confirm('¿Eliminar esta unidad con todos sus temas, videos y materiales?')"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="{{ $btnIcon }} hover:!bg-red-50 hover:!text-red-600" title="Eliminar unidad">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>

                        </div>
                    </div>


                    {{-- Editar unidad --}}
                    <details class="group border-b border-slate-100" @if ($bag->any()) open @endif>

                        <summary class="flex cursor-pointer list-none items-center gap-2 px-5 py-2.5 text-xs font-semibold text-slate-500 hover:text-[#2563EB] sm:px-6">
                            <i class="fa-solid fa-pen"></i>
                            Editar unidad
                        </summary>

                        <form
                            method="POST"
                            action="{{ route('professor.modules.update', [$curso, $modulo]) }}"
                            class="grid gap-4 px-5 pb-5 sm:px-6 md:grid-cols-2"
                        >
                            @csrf
                            @method('PUT')

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-700">Título</label>
                                <input
                                    name="titulo"
                                    type="text"
                                    maxlength="200"
                                    required
                                    value="{{ $bag->any() ? old('titulo') : $modulo->titulo }}"
                                    class="{{ $input }}"
                                >
                                @if ($bag->has('titulo'))
                                    <p class="mt-2 text-sm text-red-600">{{ $bag->first('titulo') }}</p>
                                @endif
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-700">Estado</label>
                                <select name="estado" class="{{ $input }}">
                                    @php $estadoActual = $bag->any() ? old('estado') : $modulo->estado; @endphp
                                    <option value="activo" @selected($estadoActual === 'activo')>Activa (visible para estudiantes)</option>
                                    <option value="inactivo" @selected($estadoActual === 'inactivo')>Inactiva (oculta)</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-semibold text-slate-700">Descripción</label>
                                <textarea name="descripcion" rows="2" maxlength="1000" class="{{ $input }}">{{ $bag->any() ? old('descripcion') : $modulo->descripcion }}</textarea>
                            </div>

                            <div class="md:col-span-2 flex justify-end">
                                <button type="submit" class="{{ $btnPrimary }}">
                                    <i class="fa-solid fa-floppy-disk"></i>
                                    Guardar unidad
                                </button>
                            </div>
                        </form>
                    </details>


                    {{-- Temas --}}
                    <div class="px-5 py-4 sm:px-6">

                        @forelse ($modulo->lecciones as $leccion)

                            @php
                                $tipo = match ($leccion->estado) {
                                    'publicado' => 'success',
                                    'borrador' => 'warning',
                                    default => 'danger',
                                };
                            @endphp

                            <div class="flex flex-col gap-3 rounded-lg px-3 py-3 hover:bg-slate-50 sm:flex-row sm:items-center sm:justify-between {{ ! $loop->last ? 'border-b border-slate-100' : '' }}">

                                <div class="flex min-w-0 items-center gap-3">

                                    <span class="w-10 shrink-0 text-xs font-semibold text-slate-400">
                                        {{ $loop->parent->iteration }}.{{ $loop->iteration }}
                                    </span>

                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-800">{{ $leccion->titulo }}</p>

                                        <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                            <x-ui.badge :type="$tipo">{{ ucfirst($leccion->estado) }}</x-ui.badge>
                                            <span><i class="fa-solid fa-circle-play mr-1"></i>{{ $leccion->videos_count }} videos</span>
                                            <span><i class="fa-solid fa-paperclip mr-1"></i>{{ $leccion->materiales_count }} materiales</span>
                                        </div>
                                    </div>
                                </div>


                                <div class="flex shrink-0 flex-wrap items-center gap-1 sm:justify-end">

                                    <a
                                        href="{{ route('professor.materials.index', [$curso, $modulo, $leccion]) }}"
                                        class="mr-1 inline-flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-[#2563EB] hover:bg-blue-50"
                                    >
                                        <i class="fa-solid fa-photo-film"></i>
                                        Videos y materiales
                                    </a>

                                    <form method="POST" action="{{ route('professor.lessons.move', [$curso, $modulo, $leccion]) }}">
                                        @csrf
                                        <input type="hidden" name="direccion" value="up">
                                        <button type="submit" class="{{ $btnIcon }}" title="Subir tema" @disabled($loop->first)>
                                            <i class="fa-solid fa-arrow-up"></i>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('professor.lessons.move', [$curso, $modulo, $leccion]) }}">
                                        @csrf
                                        <input type="hidden" name="direccion" value="down">
                                        <button type="submit" class="{{ $btnIcon }}" title="Bajar tema" @disabled($loop->last)>
                                            <i class="fa-solid fa-arrow-down"></i>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('professor.lessons.toggle-status', [$curso, $modulo, $leccion]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            class="{{ $btnIcon }}"
                                            title="{{ $leccion->estado === 'publicado' ? 'Ocultar tema' : 'Publicar tema' }}"
                                        >
                                            <i class="fa-solid {{ $leccion->estado === 'publicado' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        </button>
                                    </form>

                                    <a href="{{ route('professor.lessons.edit', [$curso, $modulo, $leccion]) }}" class="{{ $btnIcon }}" title="Editar tema">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('professor.lessons.destroy', [$curso, $modulo, $leccion]) }}"
                                        onsubmit="return confirm('¿Eliminar este tema con sus videos y materiales?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="{{ $btnIcon }} hover:!bg-red-50 hover:!text-red-600" title="Eliminar tema">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>

                                </div>
                            </div>

                        @empty

                            <p class="py-3 text-center text-sm text-slate-400">
                                Esta unidad todavía no tiene temas.
                            </p>

                        @endforelse


                        <div class="mt-3">
                            <a
                                href="{{ route('professor.lessons.create', [$curso, $modulo]) }}"
                                class="inline-flex items-center gap-2 text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]"
                            >
                                <i class="fa-solid fa-plus"></i>
                                Agregar tema
                            </a>
                        </div>

                    </div>

                </x-ui.card>

            @empty

                <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-xl text-[#2563EB]">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <h3 class="mt-4 font-bold text-slate-800">Este curso todavía no tiene unidades</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                        Crea la primera unidad arriba y luego agrégale temas.
                    </p>
                </div>

            @endforelse

        </div>

    </div>


    <script>
        document.getElementById('copiar-codigo')?.addEventListener('click', async (event) => {
            const button = event.currentTarget;
            const label = button.querySelector('span');
            const codigo = document.getElementById('codigo-clase').textContent.trim();

            try {
                await navigator.clipboard.writeText(codigo);
            } catch (e) {
                const range = document.createRange();
                range.selectNodeContents(document.getElementById('codigo-clase'));
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
                document.execCommand('copy');
            }

            label.textContent = '¡Copiado!';
            setTimeout(() => (label.textContent = 'Copiar'), 1800);
        });
    </script>

</x-professor-layout>
