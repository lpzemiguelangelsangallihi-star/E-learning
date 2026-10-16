<x-admin-layout
    title="Configuración"
    description="Administra la seguridad y configuración de tu cuenta."
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


        {{-- Información de cuenta --}}
        <x-ui.card class="mb-6">

            <div class="mb-6">

                <h2
                    class="
                        text-base font-bold
                        text-slate-800
                    "
                >
                    Información de la cuenta
                </h2>

                <p
                    class="
                        mt-1 text-sm
                        text-slate-500
                    "
                >
                    Información general de tu usuario.
                </p>

            </div>


            <div
                class="
                    grid gap-4
                    sm:grid-cols-2
                    lg:grid-cols-3
                "
            >

                {{-- Rol --}}
                <div
                    class="
                        rounded-lg
                        bg-slate-50
                        p-4
                    "
                >

                    <p
                        class="
                            text-xs font-semibold
                            uppercase tracking-wide
                            text-slate-400
                        "
                    >
                        Rol
                    </p>

                    <div class="mt-2">

                        <x-ui.badge type="primary">

                            {{
                                ucfirst(
                                    $user->rol?->nombre
                                    ?? 'Sin rol'
                                )
                            }}

                        </x-ui.badge>

                    </div>

                </div>


                {{-- Estado --}}
                <div
                    class="
                        rounded-lg
                        bg-slate-50
                        p-4
                    "
                >

                    <p
                        class="
                            text-xs font-semibold
                            uppercase tracking-wide
                            text-slate-400
                        "
                    >
                        Estado
                    </p>

                    <div class="mt-2">

                        <x-ui.badge
                            :type="
                                $user->estado === 'activo'
                                    ? 'success'
                                    : 'danger'
                            "
                        >
                            {{ ucfirst($user->estado) }}
                        </x-ui.badge>

                    </div>

                </div>


                {{-- Registro --}}
                <div
                    class="
                        rounded-lg
                        bg-slate-50
                        p-4
                    "
                >

                    <p
                        class="
                            text-xs font-semibold
                            uppercase tracking-wide
                            text-slate-400
                        "
                    >
                        Miembro desde
                    </p>

                    <p
                        class="
                            mt-2
                            text-sm font-semibold
                            text-slate-700
                        "
                    >
                        {{
                            $user->creado_en
                                ?->format('d/m/Y')
                                ?? 'No disponible'
                        }}
                    </p>

                </div>

            </div>

        </x-ui.card>


        {{-- Contraseña --}}
        <form
            method="POST"
            action="{{
                route(
                    'admin.settings.password'
                )
            }}"
            data-loading-form
            data-loading-text="Actualizando..."
        >

            @csrf
            @method('PUT')


            <x-ui.card>

                <div class="mb-6">

                    <div
                        class="
                            flex items-start gap-3
                        "
                    >

                        <div
                            class="
                                flex h-10 w-10
                                shrink-0
                                items-center
                                justify-center
                                rounded-lg
                                bg-blue-50
                                text-[#2563EB]
                            "
                        >
                            <i
                                class="
                                    fa-solid
                                    fa-lock
                                "
                            ></i>
                        </div>


                        <div>

                            <h2
                                class="
                                    text-base font-bold
                                    text-slate-800
                                "
                            >
                                Cambiar contraseña
                            </h2>

                            <p
                                class="
                                    mt-1
                                    text-sm
                                    text-slate-500
                                "
                            >
                                Utiliza una contraseña segura
                                para proteger tu cuenta.
                            </p>

                        </div>

                    </div>

                </div>


                <div class="space-y-5">

                    {{-- Contraseña actual --}}
                    <div>

                        <label
                            for="current_password"
                            class="
                                mb-2 block
                                text-sm font-semibold
                                text-slate-700
                            "
                        >
                            Contraseña actual
                        </label>


                        <div class="relative">

                            <input
                                id="current_password"
                                type="password"
                                name="current_password"
                                required
                                autocomplete="current-password"
                                class="
                                    block w-full
                                    rounded-lg
                                    border-slate-300
                                    px-4 py-3 pr-11
                                    text-sm
                                    focus:border-[#2563EB]
                                    focus:ring-[#2563EB]
                                "
                            >


                            <button
                                type="button"
                                data-password-toggle="current_password"
                                class="
                                    absolute inset-y-0 right-0
                                    flex w-11
                                    items-center justify-center
                                    text-slate-400
                                    hover:text-slate-600
                                "
                            >
                                <i
                                    class="
                                        fa-regular
                                        fa-eye
                                    "
                                    data-password-icon
                                ></i>
                            </button>

                        </div>


                        @error('current_password')

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


                    <div
                        class="
                            grid gap-5
                            md:grid-cols-2
                        "
                    >

                        {{-- Nueva --}}
                        <div>

                            <label
                                for="password"
                                class="
                                    mb-2 block
                                    text-sm font-semibold
                                    text-slate-700
                                "
                            >
                                Nueva contraseña
                            </label>


                            <div class="relative">

                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="new-password"
                                    class="
                                        block w-full
                                        rounded-lg
                                        border-slate-300
                                        px-4 py-3 pr-11
                                        text-sm
                                        focus:border-[#2563EB]
                                        focus:ring-[#2563EB]
                                    "
                                >


                                <button
                                    type="button"
                                    data-password-toggle="password"
                                    class="
                                        absolute inset-y-0 right-0
                                        flex w-11
                                        items-center justify-center
                                        text-slate-400
                                        hover:text-slate-600
                                    "
                                >

                                    <i
                                        class="
                                            fa-regular
                                            fa-eye
                                        "
                                        data-password-icon
                                    ></i>

                                </button>

                            </div>


                            @error('password')

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


                        {{-- Confirmar --}}
                        <div>

                            <label
                                for="password_confirmation"
                                class="
                                    mb-2 block
                                    text-sm font-semibold
                                    text-slate-700
                                "
                            >
                                Confirmar contraseña
                            </label>


                            <div class="relative">

                                <input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    class="
                                        block w-full
                                        rounded-lg
                                        border-slate-300
                                        px-4 py-3 pr-11
                                        text-sm
                                        focus:border-[#2563EB]
                                        focus:ring-[#2563EB]
                                    "
                                >


                                <button
                                    type="button"
                                    data-password-toggle="password_confirmation"
                                    class="
                                        absolute inset-y-0 right-0
                                        flex w-11
                                        items-center justify-center
                                        text-slate-400
                                        hover:text-slate-600
                                    "
                                >
                                    <i
                                        class="
                                            fa-regular
                                            fa-eye
                                        "
                                        data-password-icon
                                    ></i>
                                </button>

                            </div>

                        </div>

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
                                fa-key
                            "
                            data-submit-icon
                        ></i>

                        <span data-submit-text>
                            Actualizar contraseña
                        </span>

                    </button>

                </div>

            </x-ui.card>

        </form>

    </div>

</x-admin-layout>