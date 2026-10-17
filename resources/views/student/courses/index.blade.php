<x-student-layout
    title="Mis cursos"
    description="Los cursos en los que estás inscrito."
>

<div x-data="{ open: {{ $errors->has('codigo') ? 'true' : 'false' }} }" @keydown.escape.window="open = false">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <form method="GET" action="{{ route('student.courses.index') }}" class="flex flex-1 flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true"></i>

                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar curso..."
                    aria-label="Buscar curso"
                    class="block w-full rounded-lg border-slate-300 py-2.5 pl-11 pr-4 text-sm placeholder:text-slate-400 focus:border-[#2563EB] focus:ring-[#2563EB]"
                >
            </div>

            <select
                name="estado"
                aria-label="Filtrar por estado"
                onchange="this.form.submit()"
                class="rounded-lg border-slate-300 py-2.5 pl-4 pr-10 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]"
            >
                <option value="">Todos</option>
                <option value="activo" @selected($estado === 'activo')>En curso</option>
                <option value="finalizado" @selected($estado === 'finalizado')>Completados</option>
            </select>

            <x-ui.button type="submit" variant="secondary">Buscar</x-ui.button>
        </form>

        <x-ui.button type="button" icon="fa-solid fa-key" @click="open = true">
            Inscribirme con código
        </x-ui.button>
    </div>


    @if ($inscripciones->isEmpty())
        <x-ui.card>
            <x-ui.empty
                icon="fa-solid fa-book-open"
                :title="($search !== '' || $estado !== '') ? 'No encontramos cursos con ese filtro' : 'Aún no tienes cursos'"
                :text="($search !== '' || $estado !== '') ? 'Prueba con otra búsqueda.' : 'Ingresa el código que te dio tu profesor para inscribirte.'"
            />
        </x-ui.card>
    @else
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($inscripciones as $inscripcion)
                <x-student.course-card
                    :curso="$inscripcion->curso"
                    :inscripcion="$inscripcion"
                    :progreso="$progresos[$inscripcion->curso_id] ?? null"
                />
            @endforeach
        </div>
    @endif


    {{-- Ventana emergente: inscribirse con código --}}
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-codigo-titulo"
    >
        <div class="absolute inset-0 bg-slate-950/50" @click="open = false"></div>

        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
            <h2 id="modal-codigo-titulo" class="text-lg font-bold text-slate-800">Inscribirme con código</h2>
            <p class="mt-1 text-sm text-slate-500">Ingresa el código que te dio tu profesor.</p>

            <form method="POST" action="{{ route('student.courses.enroll') }}" class="mt-5 space-y-5">
                @csrf

                <div>
                    <label for="codigo-modal" class="mb-2 block text-sm font-semibold text-slate-700">Código</label>

                    <input
                        id="codigo-modal"
                        name="codigo"
                        type="text"
                        value="{{ old('codigo') }}"
                        maxlength="20"
                        required
                        autocomplete="off"
                        placeholder="Ej. AB12CD34"
                        x-effect="if (open) $nextTick(() => $el.focus())"
                        @class([
                            'block w-full rounded-lg px-4 py-3 font-mono text-sm uppercase tracking-widest placeholder:normal-case placeholder:tracking-normal placeholder:text-slate-400',
                            'border-slate-300 focus:border-[#2563EB] focus:ring-[#2563EB]' => !$errors->has('codigo'),
                            'border-[#DC2626] focus:border-[#DC2626] focus:ring-[#DC2626]' => $errors->has('codigo'),
                        ])
                    >

                    @error('codigo')
                        <p class="mt-2 text-sm text-[#DC2626]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="secondary" @click="open = false">Cancelar</x-ui.button>
                    <x-ui.button type="submit" icon="fa-solid fa-right-to-bracket">Inscribirme</x-ui.button>
                </div>
            </form>
        </div>
    </div>

</div>

</x-student-layout>
