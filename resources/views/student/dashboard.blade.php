<x-student-layout
    title="Mis cursos"
    description="Únete a un curso con el código de tu profesor."
>

    <x-flash />

    {{-- Unirse con código --}}
    <x-ui.card class="mb-8">

        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

            <div>
                <h2 class="text-base font-bold text-slate-800">Unirme a un curso</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Ingresa el código de clase de 8 caracteres que te dio tu profesor.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('student.courses.join') }}"
                class="w-full lg:max-w-md"
            >
                @csrf

                <div class="flex gap-2">
                    <input
                        name="codigo"
                        type="text"
                        maxlength="20"
                        required
                        autocomplete="off"
                        value="{{ old('codigo') }}"
                        placeholder="Ej. K7M2XQ9A"
                        class="block w-full rounded-lg border-slate-300 px-4 py-2.5 font-mono text-sm uppercase tracking-widest focus:border-[#2563EB] focus:ring-[#2563EB]"
                    >

                    <button
                        type="submit"
                        class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1D4ED8]"
                    >
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Unirme
                    </button>
                </div>

                @error('codigo')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </form>

        </div>

    </x-ui.card>


    {{-- Cursos inscritos --}}
    <h2 class="mb-4 text-lg font-bold text-slate-800">Mis cursos inscritos</h2>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">

        @forelse ($inscripciones as $inscripcion)

            @php $curso = $inscripcion->curso; @endphp

            <x-ui.card hover>

                <div class="flex h-full flex-col">

                    <p class="text-xs font-semibold uppercase tracking-wide text-[#2563EB]">
                        {{ $curso->materia?->nombre ?? 'Sin materia' }}
                    </p>

                    <h3 class="mt-2 text-lg font-bold text-slate-800">{{ $curso->titulo }}</h3>

                    <p class="mt-1 text-sm text-slate-500">
                        <i class="fa-solid fa-chalkboard-user mr-1"></i>
                        {{ $curso->profesor?->nombre_completo ?? 'Profesor' }}
                    </p>

                    <div class="mt-4 flex flex-wrap gap-4 text-xs text-slate-500">
                        <span><i class="fa-solid fa-signal mr-1"></i>{{ ucfirst($curso->nivel) }}</span>
                        <span><i class="fa-solid fa-list-check mr-1"></i>{{ $temas[$curso->id] ?? 0 }} temas</span>
                    </div>

                    <div class="mt-5 border-t border-slate-100 pt-4">
                        <a
                            href="{{ route('student.courses.show', $curso) }}"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]"
                        >
                            Entrar al curso
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>

                </div>

            </x-ui.card>

        @empty

            <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-xl text-[#2563EB]">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <h3 class="mt-4 font-bold text-slate-800">Todavía no estás inscrito en ningún curso</h3>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                    Pide el código de clase a tu profesor y escríbelo arriba.
                </p>
            </div>

        @endforelse

    </div>

</x-student-layout>
