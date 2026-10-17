@php
    $input = 'block w-full rounded-lg border-slate-300 px-4 py-2.5 text-sm text-slate-800 focus:border-[#2563EB] focus:ring-[#2563EB]';
    $label = 'mb-2 block text-sm font-semibold text-slate-700';
    $recurso = old('recurso', 'archivo');
    $total = $leccion->materiales->count() + $leccion->videos->count();
@endphp

<x-professor-layout
    title="Videos y materiales"
    description="Publica videos, archivos y enlaces de apoyo para este tema."
>

    <div class="mx-auto max-w-4xl">

        <div class="mb-6">
            <a
                href="{{ route('professor.courses.structure', $curso) }}"
                class="inline-flex items-center gap-2 text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Volver al contenido del curso
            </a>
        </div>

        <x-flash />

        <x-ui.card class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#2563EB]">
                {{ $curso->titulo }} › {{ $modulo->titulo }}
            </p>
            <h2 class="mt-1 text-lg font-bold text-slate-800">{{ $leccion->titulo }}</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ $leccion->videos->count() }} videos · {{ $leccion->materiales->count() }} materiales registrados
            </p>
        </x-ui.card>


        {{-- Nuevo recurso --}}
        <x-ui.card class="mb-6">

            <h3 class="text-base font-bold text-slate-800">Nuevo material</h3>

            <form
                method="POST"
                action="{{ route('professor.materials.store', [$curso, $modulo, $leccion]) }}"
                enctype="multipart/form-data"
                class="mt-4 space-y-5"
                data-loading-form
                data-loading-text="Subiendo..."
            >
                @csrf

                <div>
                    <label for="titulo" class="{{ $label }}">Título</label>
                    <input id="titulo" name="titulo" type="text" maxlength="200" required value="{{ old('titulo') }}" class="{{ $input }}">
                    @error('titulo')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="descripcion" class="{{ $label }}">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="2" maxlength="1000" class="{{ $input }}">{{ old('descripcion') }}</textarea>
                    @error('descripcion')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="recurso" class="{{ $label }}">¿Qué deseas agregar?</label>
                    <select id="recurso" name="recurso" class="{{ $input }}">
                        <option value="archivo" @selected($recurso === 'archivo')>Un archivo</option>
                        <option value="enlace" @selected($recurso === 'enlace')>Un enlace</option>
                        <option value="video" @selected($recurso === 'video')>Un video</option>
                    </select>
                    <p class="mt-2 text-xs text-slate-500">Completa el campo correspondiente a la opción seleccionada.</p>
                    @error('recurso')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>


                {{-- Archivo --}}
                <div data-recurso="archivo">
                    <label for="archivo" class="{{ $label }}">Archivo</label>
                    <input
                        id="archivo"
                        name="archivo"
                        type="file"
                        class="block w-full rounded-lg border border-slate-300 bg-white p-2 text-sm text-slate-700"
                    >
                    <p class="mt-2 text-xs text-slate-500">
                        PDF, imágenes, documentos, presentaciones, TXT o ZIP. Máximo 10 MB.
                    </p>
                    @error('archivo')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Enlace --}}
                <div data-recurso="enlace">
                    <label for="enlace" class="{{ $label }}">Enlace</label>
                    <input id="enlace" name="enlace" type="url" maxlength="500" value="{{ old('enlace') }}" placeholder="https://ejemplo.com/recurso" class="{{ $input }}">
                    @error('enlace')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Video --}}
                <div data-recurso="video" class="space-y-4">

                    <div>
                        <label for="video_url" class="{{ $label }}">Enlace del video (YouTube o Vimeo)</label>
                        <input id="video_url" name="video_url" type="url" maxlength="500" value="{{ old('video_url') }}" placeholder="https://www.youtube.com/watch?v=..." class="{{ $input }}">
                        @error('video_url')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <p class="text-center text-xs font-semibold uppercase tracking-wide text-slate-400">o sube un archivo</p>

                    <div>
                        <label for="video_archivo" class="{{ $label }}">Archivo de video</label>
                        <input
                            id="video_archivo"
                            name="video_archivo"
                            type="file"
                            accept="video/mp4,video/webm,video/quicktime"
                            class="block w-full rounded-lg border border-slate-300 bg-white p-2 text-sm text-slate-700"
                        >
                        <p class="mt-2 text-xs text-slate-500">MP4, WebM o MOV. Máximo 100 MB.</p>
                        @error('video_archivo')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:max-w-xs">
                        <label for="duracion" class="{{ $label }}">
                            Duración en minutos <span class="font-normal text-slate-400">(opcional)</span>
                        </label>
                        <input id="duracion" name="duracion" type="number" min="0" max="10000" value="{{ old('duracion') }}" class="{{ $input }}">
                        @error('duracion')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                </div>


                <div class="flex justify-end">
                    <button
                        type="submit"
                        data-submit-button
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1D4ED8] disabled:cursor-not-allowed disabled:bg-slate-300"
                    >
                        <i class="fa-solid fa-plus" data-submit-icon></i>
                        <span data-submit-text>Agregar material</span>
                    </button>
                </div>

            </form>

        </x-ui.card>


        {{-- Videos --}}
        @if ($leccion->videos->isNotEmpty())

            <h3 class="mb-3 text-base font-bold text-slate-800">Videos</h3>

            <div class="mb-8 grid gap-5 md:grid-cols-2">
                @foreach ($leccion->videos as $video)
                    <div>
                        <x-video-player :video="$video" />

                        <form
                            method="POST"
                            action="{{ route('professor.videos.destroy', [$curso, $modulo, $leccion, $video]) }}"
                            onsubmit="return confirm('¿Eliminar este video?')"
                            class="mt-2 text-right"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-red-600">
                                <i class="fa-solid fa-trash"></i>
                                Eliminar video
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

        @endif


        {{-- Archivos y enlaces --}}
        @if ($leccion->materiales->isNotEmpty())

            <h3 class="mb-3 text-base font-bold text-slate-800">Archivos y enlaces</h3>

            <div class="space-y-3">
                @foreach ($leccion->materiales as $material)
                    <x-ui.card>
                        <div class="flex items-center justify-between gap-4">

                            <div class="flex min-w-0 items-center gap-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-[#2563EB]">
                                    <i class="{{ $material->icono }}"></i>
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-800">{{ $material->titulo }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ ucfirst($material->tipo) }}
                                        @if ($material->descripcion) · {{ \Illuminate\Support\Str::limit($material->descripcion, 80) }} @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-1">
                                <a
                                    href="{{ $material->enlace }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-[#2563EB]"
                                    title="Abrir"
                                >
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('professor.materials.destroy', [$curso, $modulo, $leccion, $material]) }}"
                                    onsubmit="return confirm('¿Eliminar este material?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>

                        </div>
                    </x-ui.card>
                @endforeach
            </div>

        @endif


        @if ($total === 0)
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-500">
                Este tema todavía no tiene videos ni materiales.
            </div>
        @endif

    </div>


    <script>
        (() => {
            const select = document.getElementById('recurso');
            const groups = document.querySelectorAll('[data-recurso]');

            const sync = () => {
                groups.forEach((group) => {
                    const active = group.dataset.recurso === select.value;
                    group.classList.toggle('hidden', !active);
                    group.querySelectorAll('input, textarea').forEach((el) => (el.disabled = !active));
                });
            };

            select.addEventListener('change', sync);
            sync();
        })();
    </script>

</x-professor-layout>
