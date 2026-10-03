@props([
    'title',
    'description' => null,
])

<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        {{ $title }} | {{ config('app.name', 'E-Learning') }}
    </title>


    {{-- Fuente Inter --}}
    <link
        rel="preconnect"
        href="https://fonts.bunny.net"
    >

    <link
        href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap"
        rel="stylesheet"
    >


    {{-- Tailwind + Font Awesome + JS --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])


    @livewireStyles

</head>


<body
    class="
        bg-[#F8FAFC]
        font-sans
        text-slate-800
        antialiased
    "
>

    <div
        class="min-h-screen"
    >

        {{-- Overlay móvil --}}
        <div
            data-admin-sidebar-overlay
            class="
                fixed inset-0 z-40
                hidden
                bg-slate-950/50
                lg:hidden
            "
        ></div>


        {{-- SIDEBAR --}}
        <aside
            data-admin-sidebar
            class="
                fixed inset-y-0 left-0
                z-50
                flex w-64
                -translate-x-full
                flex-col
                bg-[#1E3A8A]
                text-white
                transition-transform
                duration-200
                lg:translate-x-0
            "
        >

            {{-- Logo --}}
            <div
                class="
                    flex h-16
                    items-center
                    border-b
                    border-white/10
                    px-6
                "
            >

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="
                        flex items-center
                        gap-3
                    "
                >

                    <span
                        class="
                            flex h-9 w-9
                            items-center
                            justify-center
                            rounded-lg
                            bg-white/10
                        "
                    >

                        <i
                            class="
                                fa-solid
                                fa-graduation-cap
                            "
                            aria-hidden="true"
                        ></i>

                    </span>


                    <span
                        class="
                            text-lg
                            font-bold
                        "
                    >
                        E-Learning
                    </span>

                </a>

            </div>


            {{-- Navegación --}}
            <nav
                class="
                    flex-1
                    overflow-y-auto
                    px-3 py-6
                "
            >

                <p
                    class="
                        mb-3 px-3
                        text-xs
                        font-semibold
                        uppercase
                        tracking-wider
                        text-blue-200
                    "
                >
                    Administración
                </p>


                {{-- Dashboard --}}
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="
                        mb-1 flex
                        items-center gap-3
                        rounded-lg
                        px-3 py-2.5
                        text-sm
                        font-medium
                        transition

                        {{ request()->routeIs('admin.dashboard')
                            ? 'bg-[#2563EB] text-white'
                            : 'text-blue-50 hover:bg-white/10'
                        }}
                    "
                >

                    <i
                        class="
                            fa-solid
                            fa-house
                            w-5
                            text-center
                        "
                    ></i>

                    Dashboard

                </a>


                {{-- Usuarios --}}
                <a
                    href="{{ route('admin.users.index') }}"
                    class="
                             mb-1 flex items-center gap-3
                             rounded-lg px-3 py-2.5
                             text-sm font-medium
                             transition
                                        
                             {{ request()->routeIs('admin.users.*')
                                 ? 'bg-[#2563EB] text-white'
                                 : 'text-blue-50 hover:bg-white/10'
                             }}
                            "
                >

                    <i
                        class="
                            fa-solid
                            fa-users
                            w-5
                            text-center
                        "
                    ></i>

                    Usuarios

                </a>


                {{-- Cursos --}}
                <a
                    href="{{ route('admin.courses.index') }}"
                    class="
                        mb-1 flex items-center gap-3
                        rounded-lg px-3 py-2.5
                        text-sm font-medium
                        transition

                        {{ request()->routeIs('admin.courses.*')
                            ? 'bg-[#2563EB] text-white'
                            : 'text-blue-50 hover:bg-white/10'
                        }}
                    "
                >
                    <i
                        class="
                            fa-solid
                            fa-book-open
                            w-5 text-center
                        "
                    ></i>

                    Cursos
</a>




                {{-- Estadísticas --}}
                <a
                    href="#"
                    class="
                        mb-1 flex
                        items-center gap-3
                        rounded-lg
                        px-3 py-2.5
                        text-sm
                        font-medium
                        text-blue-50
                        transition
                        hover:bg-white/10
                    "
                >

                    <i
                        class="
                            fa-solid
                            fa-chart-column
                            w-5
                            text-center
                        "
                    ></i>

                    Estadísticas

                </a>

            </nav>


            {{-- Usuario --}}
            <div
                class="
                    border-t
                    border-white/10
                    p-4
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
                            bg-white/10
                            font-semibold
                        "
                    >
                        {{
                            mb_strtoupper(
                                mb_substr(
                                    auth()->user()->nombre,
                                    0,
                                    1
                                )
                            )
                        }}
                    </div>


                    <div
                        class="
                            min-w-0
                            flex-1
                        "
                    >

                        <p
                            class="
                                truncate
                                text-sm
                                font-semibold
                                text-white
                            "
                        >
                            {{ auth()->user()->nombre_completo }}
                        </p>

                        <p
                            class="
                                truncate
                                text-xs
                                text-blue-200
                            "
                        >
                            Administrador
                        </p>

                    </div>

                </div>

            </div>

        </aside>


        {{-- CONTENIDO PRINCIPAL --}}
        <div
            class="
                min-h-screen
                lg:pl-64
            "
        >

            {{-- NAVBAR --}}
            <header
                class="
                    sticky top-0
                    z-30
                    flex h-16
                    items-center
                    justify-between
                    border-b
                    border-slate-200
                    bg-white
                    px-4
                    sm:px-6
                    lg:px-8
                "
            >

                {{-- Izquierda --}}
                <div
                    class="
                        flex items-center
                        gap-3
                    "
                >

                    {{-- Menú móvil --}}
                    <button
                        type="button"
                        data-admin-sidebar-toggle
                        class="
                            flex h-10 w-10
                            items-center
                            justify-center
                            rounded-lg
                            text-slate-600
                            transition
                            hover:bg-slate-100
                            lg:hidden
                        "
                        aria-label="Abrir menú"
                    >

                        <i
                            class="
                                fa-solid
                                fa-bars
                            "
                        ></i>

                    </button>


                    <div>

                        <h1
                            class="
                                text-lg
                                font-bold
                                text-slate-800
                                sm:text-xl
                            "
                        >
                            {{ $title }}
                        </h1>


                        @if ($description)

                            <p
                                class="
                                    hidden
                                    text-xs
                                    text-slate-500
                                    sm:block
                                "
                            >
                                {{ $description }}
                            </p>

                        @endif

                    </div>

                </div>


                {{-- Derecha --}}
                <div
                    class="
                        flex items-center
                        gap-2
                    "
                >

                    {{-- Notificaciones --}}
                    <button
                        type="button"
                        class="
                            relative
                            flex h-10 w-10
                            items-center
                            justify-center
                            rounded-lg
                            text-slate-500
                            transition
                            hover:bg-slate-100
                            hover:text-slate-700
                        "
                    >

                        <i
                            class="
                                fa-regular
                                fa-bell
                            "
                        ></i>

                    </button>


                    {{-- Usuario --}}
                    <div
                        class="
                            hidden
                            text-right
                            sm:block
                        "
                    >

                        <p
                            class="
                                text-sm
                                font-semibold
                                text-slate-700
                            "
                        >
                            {{ auth()->user()->nombre }}
                        </p>

                        <p
                            class="
                                text-xs
                                text-slate-500
                            "
                        >
                            Administrador
                        </p>

                    </div>


                    {{-- Logout --}}
                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >

                        @csrf

                        <button
                            type="submit"
                            class="
                                flex h-10 w-10
                                items-center
                                justify-center
                                rounded-lg
                                text-slate-500
                                transition
                                hover:bg-red-50
                                hover:text-red-600
                            "
                            title="Cerrar sesión"
                        >

                            <i
                                class="
                                    fa-solid
                                    fa-arrow-right-from-bracket
                                "
                            ></i>

                        </button>

                    </form>

                </div>

            </header>


            {{-- CONTENIDO --}}
            <main
                class="
                    px-4 py-6
                    sm:px-6
                    lg:px-8
                "
            >

                {{ $slot }}

            </main>

        </div>

    </div>


    @livewireScripts

</body>

</html>