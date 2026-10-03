<x-admin-layout
    title="Usuarios"
    description="Gestiona estudiantes, profesores y administradores."
>

    <x-slot:actions>

        <a
            href="{{ route('admin.users.create') }}"
            class="
                inline-flex items-center gap-2
                rounded-lg bg-[#2563EB]
                px-4 py-2.5
                text-sm font-semibold text-white
                transition
                hover:bg-[#1D4ED8]
                active:bg-[#1E40AF]
            "
        >
            <i class="fa-solid fa-user-plus"></i>

            Nuevo usuario
        </a>

    </x-slot:actions>


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


    {{-- Filtros --}}
    <x-ui.card class="mb-6">

        <form
            method="GET"
            action="{{ route('admin.users.index') }}"
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
                            flex items-center pl-3.5
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
                        placeholder="Nombre o correo..."
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


            {{-- Rol --}}
            <div>

                <label
                    for="rol"
                    class="
                        mb-2 block
                        text-sm font-semibold
                        text-slate-700
                    "
                >
                    Rol
                </label>


                <select
                    id="rol"
                    name="rol"
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


                    @foreach ($roles as $rol)

                        <option
                            value="{{ $rol->id }}"
                            @selected(
                                $rolId === $rol->id
                            )
                        >
                            {{ ucfirst($rol->nombre) }}
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
                        focus:border-[#2563EB]
                        focus:ring-[#2563EB]
                    "
                >

                    <option value="">
                        Todos
                    </option>

                    <option
                        value="activo"
                        @selected($estado === 'activo')
                    >
                        Activos
                    </option>

                    <option
                        value="inactivo"
                        @selected($estado === 'inactivo')
                    >
                        Inactivos
                    </option>

                </select>

            </div>


            {{-- Acciones --}}
            <div class="flex gap-2">

                <button
                    type="submit"
                    class="
                        inline-flex items-center gap-2
                        rounded-lg bg-[#2563EB]
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


                @if ($search || $rolId || $estado)

                    <a
                        href="{{ route('admin.users.index') }}"
                        class="
                            inline-flex items-center
                            justify-center
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
                            Usuario
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
                            Rol
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
                                text-left
                                text-xs font-semibold
                                uppercase tracking-wide
                                text-slate-500
                            "
                        >
                            Registro
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

                    @forelse ($usuarios as $usuario)

                        <tr
                            class="
                                transition
                                hover:bg-slate-50
                            "
                        >

                            {{-- Usuario --}}
                            <td
                                class="
                                    whitespace-nowrap
                                    px-6 py-4
                                "
                            >

                                <div
                                    class="
                                        flex items-center
                                        gap-3
                                    "
                                >

                                    <div
                                        class="
                                            flex h-10 w-10
                                            shrink-0
                                            items-center
                                            justify-center
                                            rounded-full
                                            bg-blue-50
                                            font-semibold
                                            text-[#2563EB]
                                        "
                                    >
                                        {{
                                            mb_strtoupper(
                                                mb_substr(
                                                    $usuario->nombre,
                                                    0,
                                                    1
                                                )
                                            )
                                        }}
                                    </div>


                                    <div>

                                        <p
                                            class="
                                                text-sm
                                                font-semibold
                                                text-slate-800
                                            "
                                        >
                                            {{ $usuario->nombre_completo }}
                                        </p>

                                        <p
                                            class="
                                                mt-0.5
                                                text-xs
                                                text-slate-500
                                            "
                                        >
                                            {{ $usuario->email }}
                                        </p>

                                    </div>

                                </div>

                            </td>


                            {{-- Rol --}}
                            <td
                                class="
                                    whitespace-nowrap
                                    px-6 py-4
                                "
                            >

                                <x-ui.badge type="primary">

                                    {{
                                        ucfirst(
                                            $usuario->rol?->nombre
                                            ?? 'Sin rol'
                                        )
                                    }}

                                </x-ui.badge>

                            </td>


                            {{-- Estado --}}
                            <td
                                class="
                                    whitespace-nowrap
                                    px-6 py-4
                                "
                            >

                                <x-ui.badge
                                    :type="
                                        $usuario->estado === 'activo'
                                            ? 'success'
                                            : 'danger'
                                    "
                                >

                                    {{ ucfirst($usuario->estado) }}

                                </x-ui.badge>

                            </td>


                            {{-- Fecha --}}
                            <td
                                class="
                                    whitespace-nowrap
                                    px-6 py-4
                                    text-sm text-slate-500
                                "
                            >

                                {{
                                    optional(
                                        $usuario->creado_en
                                    )
                                    ?->format('d/m/Y')
                                }}

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
                                        items-center
                                        gap-1
                                    "
                                >

                                    {{-- Editar --}}
                                    <a
                                        href="{{
                                            route(
                                                'admin.users.edit',
                                                $usuario
                                            )
                                        }}"
                                        class="
                                            flex h-9 w-9
                                            items-center
                                            justify-center
                                            rounded-lg
                                            text-slate-500
                                            transition
                                            hover:bg-blue-50
                                            hover:text-[#2563EB]
                                        "
                                        title="Editar"
                                    >
                                        <i class="fa-solid fa-pen"></i>
                                    </a>


                                    {{-- Estado --}}
                                    @if ($usuario->id !== auth()->id())

                                        <form
                                            method="POST"
                                            action="{{
                                                route(
                                                    'admin.users.toggle-status',
                                                    $usuario
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
                                                        $usuario->estado
                                                            === 'activo'
                                                        ? 'hover:bg-yellow-50 hover:text-yellow-600'
                                                        : 'hover:bg-green-50 hover:text-green-600'
                                                    }}
                                                "
                                                title="{{
                                                    $usuario->estado
                                                        === 'activo'
                                                    ? 'Desactivar'
                                                    : 'Activar'
                                                }}"
                                            >

                                                <i
                                                    class="
                                                        fa-solid

                                                        {{
                                                            $usuario->estado
                                                                === 'activo'
                                                            ? 'fa-ban'
                                                            : 'fa-circle-check'
                                                        }}
                                                    "
                                                ></i>

                                            </button>

                                        </form>

                                    @endif


                                    {{-- Eliminar --}}
                                    @if ($usuario->id !== auth()->id())

                                        <form
                                            method="POST"
                                            action="{{
                                                route(
                                                    'admin.users.destroy',
                                                    $usuario
                                                )
                                            }}"
                                            onsubmit="
                                                return confirm(
                                                    '¿Eliminar este usuario?'
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
                                                <i class="fa-solid fa-trash"></i>
                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="5"
                                class="
                                    px-6 py-12
                                    text-center
                                "
                            >

                                <p
                                    class="
                                        text-sm
                                        text-slate-500
                                    "
                                >
                                    No se encontraron usuarios.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Paginación --}}
        @if ($usuarios->hasPages())

            <div
                class="
                    border-t
                    border-slate-200
                    px-6 py-4
                "
            >
                {{ $usuarios->links() }}
            </div>

        @endif

    </x-ui.card>

</x-admin-layout>