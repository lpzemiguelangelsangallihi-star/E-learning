<x-admin-layout
    title="Cursos"
    description="Consulta y administra los cursos de la plataforma."
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
            <i
                class="fa-solid fa-circle-check mt-0.5"
            ></i>

            <span>
                {{ session('success') }}
            </span>
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
            <i
                class="fa-solid fa-circle-exclamation mt-0.5"
            ></i>

            <span>
                {{ session('error') }}
            </span>
        </div>

    @endif


    {{-- Filtros --}}
    <x-ui.card class="mb-6">

        <form
            method="GET"
            action="{{ route('admin.courses.index') }}"
            class="
                grid gap-4
                lg:grid-cols-[1fr_200px_180px_auto]
                lg:items-end
            "
        >

            {{-- Buscar --}}
            <div>

                <label
                    for="search"
                    class="
                        mb-2 block
                        text-sm font-semibold
                        text-slate-700
                    "
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
                            text-slate-800
                            focus:border-[#2563EB]
                            focus:ring-[#2563EB]
                        "
                    >

                </div>

            </div>


            {{-- Materia --}}
            <div>

                <label
                    for="materia"
                    class="
                        mb-2 block
                        text-sm font-semibold
                        text-slate-700
                    "
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
                        text-slate-700
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
                            @selected(
                                $materiaId === $materia->id
                            )
                        >
                            {{ $materia->nombre }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Estado --}}
            <div>

                <label
                    for="estado"
                    class="
                        mb-2 block
                        text-sm font-semibold
                        text-slate-700
                    "
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
                        text-slate-700
                        focus:border-[#2563EB]
                        focus:ring-[#2563EB]
                    "
                >

                    <option value="">
                        Todos
                    </option>

                    <option
                        value="publicado"
                        @selected($estado === 'publicado')
                    >
                        Publicados
                    </option>

                    <option
                        value="borrador"
                        @selected($estado === 'borrador')
                    >
                        Borradores
                    </option>

                    <option
                        value="oculto"
                        @selected($estado === 'oculto')
                    >
                        Ocultos
                    </option>

                </select>

            </div>


            {{-- Botones --}}
            <div class="flex gap-2">

                <button
                    type="submit"
                    class="
                        inline-flex
                        items-center gap-2
                        rounded-lg
                        bg-[#2563EB]
                        px-4 py-2.5
                        text-sm font-semibold
                        text-white
                        transition
                        hover:bg-[#1D4ED8]
                    "
                >
                    <i class="fa-solid fa-filter"></i>

                    Filtrar
                </button>


                @if ($search || $materiaId || $estado)

                    <a
                        href="{{ route('admin.courses.index') }}"
                        class="
                            inline-flex
                            items-center
                            justify-center
                            rounded-lg
                            border border-slate-300
                            bg-white
                            px-3 py-2.5
                            text-slate-600
                            transition
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


    {{-- Tabla --}}
    <x-ui.card :padding="false">

        <div class="overflow-x-auto">

            <table
                class="
                    min-w-full
                    divide-y divide-slate-200
                "
            >

                <thead class="bg-slate-50">

                    <tr>

                        <th
                            class="
                                px-6 py-3
                                text-left
                                text-xs font-semibold
                                uppercase tracking-wide
                                text-slate-500
                            "
                        >
                            Curso
                        </th>


                        <th
                            class="
                                px-6 py-3
                                text-left
                                text-xs font-semibold
                                uppercase tracking-wide
                                text-slate-500
                            "
                        >
                            Profesor
                        </th>


                        <th
                            class="
                                px-6 py-3
                                text-left
                                text-xs font-semibold
                                uppercase tracking-wide
                                text-slate-500
                            "
                        >
                            Materia
                        </th>


                        <th
                            class="
                                px-6 py-3
                                text-center
                                text-xs font-semibold
                                uppercase tracking-wide
                                text-slate-500
                            "
                        >
                            Estudiantes
                        </th>


                        <th
                            class="
                                px-6 py-3
                                text-left
                                text-xs font-semibold
                                uppercase tracking-wide
                                text-slate-500
                            "
                        >
                            Estado
                        </th>


                        <th
                            class="
                                px-6 py-3
                                text-right
                                text-xs font-semibold
                                uppercase tracking-wide
                                text-slate-500
                            "
                        >
                            Acciones
                        </th>

                    </tr>

                </thead>


                <tbody
                    class="
                        divide-y divide-slate-100
                        bg-white
                    "
                >

                    @forelse ($cursos as $curso)

                        <tr
                            class="
                                transition
                                hover:bg-slate-50
                            "
                        >

                            {{-- Curso --}}
                            <td
                                class="
                                    px-6 py-4
                                "
                            >

                                <div
                                    class="
                                        flex items-center gap-3
                                        min-w-[260px]
                                    "
                                >

                                    <div
                                        class="
                                            flex h-11 w-11
                                            shrink-0
                                            items-center
                                            justify-center
                                            rounded-lg
                                            bg-blue-50
                                            text-[#2563EB]
                                        "
                                    >

                                        @php
                                            $materiaNombre =
                                                mb_strtolower(
                                                    $curso->materia?->nombre
                                                    ?? ''
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


                                    <div>

                                        <p
                                            class="
                                                font-semibold
                                                text-slate-800
                                            "
                                        >
                                            {{ $curso->titulo }}
                                        </p>

                                        <div
                                            class="
                                                mt-1 flex
                                                flex-wrap gap-3
                                                text-xs
                                                text-slate-500
                                            "
                                        >

                                            <span>
                                                {{ ucfirst($curso->nivel) }}
                                            </span>

                                            @if ($curso->duracion > 0)

                                                <span>
                                                    <i
                                                        class="
                                                            fa-regular
                                                            fa-clock
                                                            mr-1
                                                        "
                                                    ></i>

                                                    {{ $curso->duracion }} h
                                                </span>

                                            @endif

                                        </div>

                                    </div>

                                </div>

                            </td>


                            {{-- Profesor --}}
                            <td
                                class="
                                    whitespace-nowrap
                                    px-6 py-4
                                    text-sm
                                    text-slate-600
                                "
                            >

                                @if ($curso->profesor)

                                    <p
                                        class="
                                            font-medium
                                            text-slate-700
                                        "
                                    >
                                        {{
                                            $curso->profesor
                                                ->nombre_completo
                                        }}
                                    </p>

                                    <p
                                        class="
                                            mt-1
                                            text-xs
                                            text-slate-500
                                        "
                                    >
                                        {{ $curso->profesor->email }}
                                    </p>

                                @else

                                    <span class="text-slate-400">
                                        Sin profesor
                                    </span>

                                @endif

                            </td>


                            {{-- Materia --}}
                            <td
                                class="
                                    whitespace-nowrap
                                    px-6 py-4
                                "
                            >

                                <x-ui.badge type="primary">
                                    {{
                                        $curso->materia?->nombre
                                        ?? 'Sin materia'
                                    }}
                                </x-ui.badge>

                            </td>


                            {{-- Estudiantes --}}
                            <td
                                class="
                                    whitespace-nowrap
                                    px-6 py-4
                                    text-center
                                "
                            >

                                <span
                                    class="
                                        inline-flex
                                        min-w-9
                                        items-center
                                        justify-center
                                        rounded-full
                                        bg-slate-100
                                        px-2.5 py-1
                                        text-sm
                                        font-semibold
                                        text-slate-700
                                    "
                                >
                                    {{ $curso->estudiantes_count }}
                                </span>

                            </td>


                            {{-- Estado --}}
                            <td
                                class="
                                    whitespace-nowrap
                                    px-6 py-4
                                "
                            >

                                @php
                                    $estadoType = match ($curso->estado) {
                                        'publicado' => 'success',
                                        'borrador' => 'warning',
                                        'oculto' => 'danger',
                                        default => 'info',
                                    };
                                @endphp

                                <x-ui.badge :type="$estadoType">
                                    {{ ucfirst($curso->estado) }}
                                </x-ui.badge>

                            </td>


                            {{-- Acciones --}}
                            <td
                                class="
                                    whitespace-nowrap
                                    px-6 py-4
                                    text-right
                                "
                            >

                                <div
                                    class="
                                        inline-flex
                                        items-center gap-1
                                    "
                                >

                                    {{-- Ocultar/publicar --}}
                                    @if ($curso->estado !== 'borrador')

                                        <form
                                            method="POST"
                                            action="{{
                                                route(
                                                    'admin.courses.toggle-visibility',
                                                    $curso
                                                )
                                            }}"
                                        >
                                            @csrf
                                            @method('PATCH')


                                            <button
                                                type="submit"
                                                class="
                                                    flex h-9 w-9
                                                    items-center
                                                    justify-center
                                                    rounded-lg
                                                    text-slate-500
                                                    transition

                                                    {{
                                                        $curso->estado
                                                            === 'publicado'
                                                        ? 'hover:bg-yellow-50 hover:text-yellow-600'
                                                        : 'hover:bg-green-50 hover:text-green-600'
                                                    }}
                                                "
                                                title="{{
                                                    $curso->estado
                                                        === 'publicado'
                                                    ? 'Ocultar curso'
                                                    : 'Publicar curso'
                                                }}"
                                            >

                                                <i
                                                    class="
                                                        fa-solid

                                                        {{
                                                            $curso->estado
                                                                === 'publicado'
                                                            ? 'fa-eye-slash'
                                                            : 'fa-eye'
                                                        }}
                                                    "
                                                ></i>

                                            </button>

                                        </form>

                                    @endif


                                    {{-- Eliminar --}}
                                    <form
                                        method="POST"
                                        action="{{
                                            route(
                                                'admin.courses.destroy',
                                                $curso
                                            )
                                        }}"
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
                                                items-center
                                                justify-center
                                                rounded-lg
                                                text-slate-500
                                                transition
                                                hover:bg-red-50
                                                hover:text-red-600
                                            "
                                            title="Eliminar"
                                        >
                                            <i
                                                class="fa-solid fa-trash"
                                            ></i>
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="
                                    px-6 py-14
                                    text-center
                                "
                            >

                                <div
                                    class="
                                        mx-auto
                                        flex h-12 w-12
                                        items-center
                                        justify-center
                                        rounded-full
                                        bg-slate-100
                                        text-slate-400
                                    "
                                >
                                    <i
                                        class="
                                            fa-solid
                                            fa-book-open
                                        "
                                    ></i>
                                </div>

                                <p
                                    class="
                                        mt-4
                                        font-semibold
                                        text-slate-700
                                    "
                                >
                                    No encontramos cursos
                                </p>

                                <p
                                    class="
                                        mt-1
                                        text-sm
                                        text-slate-500
                                    "
                                >
                                    No hay cursos que coincidan
                                    con los filtros seleccionados.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Paginación --}}
        @if ($cursos->hasPages())

            <div
                class="
                    border-t
                    border-slate-200
                    px-6 py-4
                "
            >
                {{ $cursos->links() }}
            </div>

        @endif

    </x-ui.card>

</x-admin-layout>