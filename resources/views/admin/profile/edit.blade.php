<x-admin-layout
    title="Mi perfil"
    description="Administra tu información personal."
>

    <div class="mx-auto max-w-4xl">

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
                    class="
                        fa-solid
                        fa-circle-check
                        mt-0.5
                    "
                ></i>

                {{ session('success') }}
            </div>

        @endif


        {{-- Resumen --}}
        <x-ui.card class="mb-6">

            <div
                class="
                    flex flex-col gap-4
                    sm:flex-row
                    sm:items-center
                "
            >

                <div
                    class="
                        flex h-16 w-16
                        shrink-0
                        items-center justify-center
                        rounded-full
                        bg-blue-50
                        text-2xl font-bold
                        text-[#2563EB]
                    "
                >
                    {{
                        mb_strtoupper(
                            mb_substr(
                                $user->nombre,
                                0,
                                1
                            )
                        )
                    }}
                </div>


                <div>

                    <h2
                        class="
                            text-lg font-bold
                            text-slate-800
                        "
                    >
                        {{ $user->nombre_completo }}
                    </h2>


                    <p
                        class="
                            mt-1
                            text-sm
                            text-slate-500
                        "
                    >
                        {{ $user->email }}
                    </p>


                    <div class="mt-2">

                        <x-ui.badge type="primary">
                            Administrador
                        </x-ui.badge>

                    </div>

                </div>

            </div>

        </x-ui.card>


        {{-- Formulario --}}
        <form
            method="POST"
            action="{{ route('admin.profile.update') }}"
            data-loading-form
            data-loading-text="Guardando..."
        >

            @csrf
            @method('PUT')


            <x-ui.card>

                <div class="mb-6">

                    <h2
                        class="
                            text-base font-bold
                            text-slate-800
                        "
                    >
                        Información personal
                    </h2>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-slate-500
                        "
                    >
                        Actualiza los datos asociados a tu cuenta.
                    </p>

                </div>


                <div
                    class="
                        grid gap-5
                        md:grid-cols-2
                    "
                >

                    {{-- Nombre --}}
                    <div>

                        <label
                            for="nombre"
                            class="
                                mb-2 block
                                text-sm font-semibold
                                text-slate-700
                            "
                        >
                            Nombre
                        </label>

                        <input
                            id="nombre"
                            type="text"
                            name="nombre"
                            required
                            value="{{
                                old(
                                    'nombre',
                                    $user->nombre
                                )
                            }}"
                            class="
                                block w-full
                                rounded-lg
                                border-slate-300
                                px-4 py-3
                                text-sm
                                focus:border-[#2563EB]
                                focus:ring-[#2563EB]
                            "
                        >

                        @error('nombre')

                            <p
                                class="
                                    mt-2
                                    text-sm
                                    text-red-600
                                "
                            >
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    {{-- Apellido paterno --}}
                    <div>

                        <label
                            for="apellido_paterno"
                            class="
                                mb-2 block
                                text-sm font-semibold
                                text-slate-700
                            "
                        >
                            Apellido paterno
                        </label>

                        <input
                            id="apellido_paterno"
                            type="text"
                            name="apellido_paterno"
                            required
                            value="{{
                                old(
                                    'apellido_paterno',
                                    $user->apellido_paterno
                                )
                            }}"
                            class="
                                block w-full
                                rounded-lg
                                border-slate-300
                                px-4 py-3
                                text-sm
                                focus:border-[#2563EB]
                                focus:ring-[#2563EB]
                            "
                        >

                        @error('apellido_paterno')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Apellido materno --}}
                    <div>

                        <label
                            for="apellido_materno"
                            class="
                                mb-2 block
                                text-sm font-semibold
                                text-slate-700
                            "
                        >
                            Apellido materno
                        </label>

                        <input
                            id="apellido_materno"
                            type="text"
                            name="apellido_materno"
                            required
                            value="{{
                                old(
                                    'apellido_materno',
                                    $user->apellido_materno
                                )
                            }}"
                            class="
                                block w-full
                                rounded-lg
                                border-slate-300
                                px-4 py-3
                                text-sm
                                focus:border-[#2563EB]
                                focus:ring-[#2563EB]
                            "
                        >

                        @error('apellido_materno')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Email --}}
                    <div>

                        <label
                            for="email"
                            class="
                                mb-2 block
                                text-sm font-semibold
                                text-slate-700
                            "
                        >
                            Correo electrónico
                        </label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            required
                            value="{{
                                old(
                                    'email',
                                    $user->email
                                )
                            }}"
                            class="
                                block w-full
                                rounded-lg
                                border-slate-300
                                px-4 py-3
                                text-sm
                                focus:border-[#2563EB]
                                focus:ring-[#2563EB]
                            "
                        >

                        @error('email')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>


                <div
                    class="
                        mt-6 flex
                        justify-end
                    "
                >

                    <button
                        type="submit"
                        data-submit-button
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
                            disabled:cursor-not-allowed
                            disabled:bg-slate-300
                        "
                    >

                        <i
                            class="
                                fa-solid
                                fa-floppy-disk
                            "
                            data-submit-icon
                        ></i>

                        <span data-submit-text>
                            Guardar cambios
                        </span>

                    </button>

                </div>

            </x-ui.card>

        </form>

    </div>

</x-admin-layout>