<x-professor-layout
    title="Mis cursos"
    description="Gestiona los cursos que tienes asignados."
>

    {{-- Mensajes --}}
    @if (session('success'))
        <div
            class="
                mb-6 flex items-start gap-3
                rounded-lg
                border border-green-200
                bg-green-50
                px-4 py-3
                text-sm text-green-700
            "
        >
            <i class="fa-solid fa-circle-check mt-0.5"></i>

            {{ session('success') }}
        </div>
    @endif


    @if (session('error'))
        <div
            class="
                mb-6 flex items-start gap-3
                rounded-lg
                border border-red-200
                bg-red-50
                px-4 py-3
                text-sm text-red-700
            "
        >
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>

            {{ session('error') }}
        </div>
    @endif


    {{-- Cabecera --}}
    <div
        class="
            mb-6
            flex flex-col gap-4
            sm:flex-row
            sm:items-center
            sm:justify-between
        "
    >

        <div>
            <h2 class="text-lg font-bold text-slate-800">
                Cursos creados
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Consulta, edita y administra tus cursos.
            </p>
        </div>


        <a
            href="{{ route('professor.courses.create') }}"
            class="
                inline-flex items-center justify-center gap-2
                rounded-lg
                bg-[#2563EB]
                px-4 py-2.5
                text-sm font-semibold
                text-white
                transition
                hover:bg-[#1D4ED8]
            "
        >
            <i class="fa-solid fa-plus"></i>

            Nuevo curso
        </a>

    </div>


    {{-- Filtros --}}
    <x-ui.card class="mb-6">

        <form
            method="GET"
            action="{{ route('professor.courses.index') }}"
            class="
                grid gap-4
                lg:grid-cols-[1fr_200px_180px_auto]
                lg:items-end
            "
        >

            <div>

                <label
                    for="search"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Buscar
                </label>

                <div class="relative">

                    <span
                        class="
                            pointer-events-none
                            absolute inset-y-0 left-0
                            flex items-center
                            pl-3.5
                            text-slate-400
                        "
                    >
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>

                    <input
                        id="search"
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Nombre del curso..."
                        class="
                            block w-full
                            rounded-lg
                            border-slate-300
                            py-2.5
                            pl-10 pr-4
                            text-sm
                            focus:border-[#2563EB]
                            focus:ring-[#2563EB]
                        "
                    >

                </div>

            </div>


            <div>

                <label
                    for="materia"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Materia
                </label>

                <select
                    id="materia"
                    name="materia"
                    class="
                        block w-full
                        rounded-lg
                        border-slate-300
                        py-2.5
                        text-sm
                        focus:border-[#2563EB]
                        focus:ring-[#2563EB]
                    "
                >

                    <option value="">
                        Todas
                    </option>

                    @foreach ($materias as $materia)
                        <option
                            value="{{ $materia->id }}"
                            @selected($materiaId === $materia->id)
                        >
                            {{ $materia->nombre }}
                        </option>
                    @endforeach

                </select>

            </div>


            <div>

                <label
                    for="estado"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Estado
                </label>

                <select
                    id="estado"
                    name="estado"
                    class="
                        block w-full
                        rounded-lg
                        border-slate-300
                        py-2.5
                        text-sm
                        focus:border-[#2563EB]
                        focus:ring-[#2563EB]
                    "
                >

                    <option value="">
                        Todos
                    </option>

                    <option
                        value="borrador"
                        @selected($estado === 'borrador')
                    >
                        Borrador
                    </option>

                    <option
                        value="publicado"
                        @selected($estado === 'publicado')
                    >
                        Publicado
                    </option>

                    <option
                        value="oculto"
                        @selected($estado === 'oculto')
                    >
                        Oculto
                    </option>

                </select>

            </div>


            <div class="flex gap-2">

                <button
                    type="submit"
                    class="
                        inline-flex items-center gap-2
                        rounded-lg
                        bg-[#2563EB]
                        px-4 py-2.5
                        text-sm font-semibold
                        text-white
                        hover:bg-[#1D4ED8]
                    "
                >
                    <i class="fa-solid fa-filter"></i>

                    Filtrar
                </button>

                @if ($search || $materiaId || $estado)
                    <a
                        href="{{ route('professor.courses.index') }}"
                        class="
                            inline-flex items-center justify-center
                            rounded-lg
                            border border-slate-300
                            px-3 py-2.5
                            text-slate-600
                            hover:bg-slate-50
                        "
                        title="Limpiar filtros"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif

            </div>

        </form>

    </x-ui.card>


    {{-- Cursos --}}
    <div
        class="
            grid gap-5
            md:grid-cols-2
            xl:grid-cols-3
        "
    >

        @forelse ($cursos as $curso)

            @php
                $type = match ($curso->estado) {
                    'publicado' => 'success',
                    'borrador' => 'warning',
                    'oculto' => 'danger',
                    default => 'primary',
                };
            @endphp

            <x-ui.card hover>

                <div class="flex h-full flex-col">

                    <div
                        class="
                            mb-4
                            flex items-start
                            justify-between
                            gap-3
                        "
                    >

                        <div
                            class="
                                flex h-11 w-11
                                items-center justify-center
                                rounded-lg
                                bg-blue-50
                                text-[#2563EB]
                            "
                        >

                            @php
                                $materiaNombre =
                                    mb_strtolower(
                                        $curso->materia?->nombre ?? ''
                                    );

                                $icon = match ($materiaNombre) {
                                    'matemáticas',
                                    'matematicas'
                                        => 'fa-solid fa-calculator',

                                    'física',
                                    'fisica'
                                        => 'fa-solid fa-atom',

                                    'química',
                                    'quimica'
                                        => 'fa-solid fa-flask',

                                    default
                                        => 'fa-solid fa-book-open',
                                };
                            @endphp

                            <i class="{{ $icon }}"></i>

                        </div>


                        <x-ui.badge :type="$type">
                            {{ ucfirst($curso->estado) }}
                        </x-ui.badge>

                    </div>


                    <div class="flex-1">

                        <p
                            class="
                                text-xs font-semibold
                                uppercase tracking-wide
                                text-[#2563EB]
                            "
                        >
                            {{ $curso->materia?->nombre ?? 'Sin materia' }}
                        </p>

                        <h3
                            class="
                                mt-2
                                text-lg font-bold
                                text-slate-800
                            "
                        >
                            {{ $curso->titulo }}
                        </h3>

                        <p
                            class="
                                mt-2
                                line-clamp-3
                                text-sm
                                text-slate-500
                            "
                        >
                            {{
                                $curso->descripcion
                                ?: 'Sin descripción.'
                            }}
                        </p>


                        <div
                            class="
                                mt-4
                                flex flex-wrap gap-4
                                text-xs
                                text-slate-500
                            "
                        >

                            <span>
                                <i class="fa-solid fa-signal mr-1"></i>

                                {{ ucfirst($curso->nivel) }}
                            </span>

                            <span>
                                <i class="fa-solid fa-user-graduate mr-1"></i>

                                {{ $curso->estudiantes_count }}
                                estudiantes
                            </span>

                            @if ($curso->duracion)
                                <span>
                                    <i class="fa-regular fa-clock mr-1"></i>

                                    {{ $curso->duracion }} h
                                </span>
                            @endif

                        </div>

                    </div>


                    <div
                        class="
                            mt-5
                            flex items-center
                            justify-between
                            border-t
                            border-slate-100
                            pt-4
                        "
                    >

                        <a
                            href="{{ route('professor.courses.edit', $curso) }}"
                            class="
                                inline-flex items-center gap-2
                                text-sm font-semibold
                                text-[#2563EB]
                                hover:text-[#1D4ED8]
                            "
                        >
                            <i class="fa-solid fa-pen"></i>

                            Editar
                        </a>


                        <form
                            method="POST"
                            action="{{ route('professor.courses.destroy', $curso) }}"
                            onsubmit="
                                return confirm(
                                    '¿Eliminar este curso?'
                                )
                            "
                        >

                            @csrf
                            @method('DELETE')


                            <button
                                type="submit"
                                class="
                                    flex h-9 w-9
                                    items-center justify-center
                                    rounded-lg
                                    text-slate-400
                                    transition
                                    hover:bg-red-50
                                    hover:text-red-600
                                "
                                title="Eliminar curso"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </button>

                        </form>

                    </div>

                </div>

            </x-ui.card>

        @empty

            <div
                class="
                    col-span-full
                    rounded-xl
                    border border-dashed
                    border-slate-300
                    bg-white
                    px-6 py-14
                    text-center
                "
            >

                <div
                    class="
                        mx-auto
                        flex h-14 w-14
                        items-center justify-center
                        rounded-full
                        bg-blue-50
                        text-xl
                        text-[#2563EB]
                    "
                >
                    <i class="fa-solid fa-book-open"></i>
                </div>

                <h3
                    class="
                        mt-4
                        font-bold
                        text-slate-800
                    "
                >
                    Todavía no tienes cursos
                </h3>

                <p
                    class="
                        mx-auto mt-2
                        max-w-md
                        text-sm
                        text-slate-500
                    "
                >
                    Crea tu primer curso para comenzar a agregar
                    contenido y estudiantes.
                </p>


                <a
                    href="{{ route('professor.courses.create') }}"
                    class="
                        mt-5
                        inline-flex items-center gap-2
                        rounded-lg
                        bg-[#2563EB]
                        px-4 py-2.5
                        text-sm font-semibold
                        text-white
                        hover:bg-[#1D4ED8]
                    "
                >
                    <i class="fa-solid fa-plus"></i>

                    Crear curso
                </a>

            </div>

        @endforelse

    </div>


    @if ($cursos->hasPages())

        <div class="mt-6">
            {{ $cursos->links() }}
        </div>

    @endif

</x-professor-layout>