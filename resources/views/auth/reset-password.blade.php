<x-guest-layout>

    <main class="min-h-screen lg:grid lg:grid-cols-2">

        <section
            class="relative hidden overflow-hidden bg-[#1E3A8A]
                   px-12 py-10 text-white
                   lg:flex lg:flex-col lg:justify-between"
        >

            <div class="relative z-10">

                <a
                    href="{{ url('/') }}"
                    class="inline-flex items-center gap-3
                           text-xl font-bold"
                >

                    <span
                        class="flex h-11 w-11 items-center justify-center
                               rounded-xl bg-white/10
                               ring-1 ring-white/20"
                    >
                        <i
                            class="fa-solid fa-graduation-cap"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span>E-Learning</span>

                </a>

            </div>


            <div class="relative z-10 max-w-xl">

                <span
                    class="mb-5 inline-flex items-center
                           rounded-full bg-white/10
                           px-4 py-2 text-sm font-medium
                           ring-1 ring-white/15"
                >
                    Seguridad de la cuenta
                </span>

                <h1
                    class="text-4xl font-bold leading-tight
                           xl:text-5xl"
                >
                    Establece una nueva contraseña.
                </h1>

                <p
                    class="mt-5 max-w-lg text-base
                           leading-7 text-blue-100"
                >
                    Utiliza una contraseña segura que puedas
                    recordar y evita compartirla con otras personas.
                </p>


                <div class="mt-8 space-y-4">

                    <div
                        class="flex items-center gap-3
                               text-sm text-blue-50"
                    >
                        <i
                            class="fa-solid fa-check
                                   text-blue-200"
                            aria-hidden="true"
                        ></i>

                        <span>
                            Utiliza una contraseña difícil de adivinar
                        </span>
                    </div>

                    <div
                        class="flex items-center gap-3
                               text-sm text-blue-50"
                    >
                        <i
                            class="fa-solid fa-check
                                   text-blue-200"
                            aria-hidden="true"
                        ></i>

                        <span>
                            No reutilices contraseñas de otras cuentas
                        </span>
                    </div>

                </div>

            </div>


            <p class="relative z-10 text-sm text-blue-200">
                E-Learning
            </p>


            <div
                class="absolute -right-24 -top-24 h-80 w-80
                       rounded-full bg-blue-500/20 blur-3xl"
            ></div>

        </section>


        {{-- PANEL DERECHO --}}
        <section
            class="flex min-h-screen items-center justify-center
                   px-5 py-10 sm:px-8 lg:px-12"
        >

            <div class="w-full max-w-md">


                {{-- Logo móvil --}}
                <div class="mb-8 lg:hidden">

                    <a
                        href="{{ url('/') }}"
                        class="inline-flex items-center gap-3
                               text-xl font-bold
                               text-[#1E3A8A]"
                    >

                        <span
                            class="flex h-10 w-10 items-center
                                   justify-center rounded-lg
                                   bg-blue-50 text-[#2563EB]"
                        >
                            <i
                                class="fa-solid fa-graduation-cap"
                                aria-hidden="true"
                            ></i>
                        </span>

                        E-Learning

                    </a>

                </div>


                <div class="mb-8">

                    <p
                        class="mb-2 text-sm font-semibold uppercase
                               tracking-wider text-[#2563EB]"
                    >
                        Nueva contraseña
                    </p>

                    <h2
                        class="text-3xl font-bold tracking-tight
                               text-slate-800"
                    >
                        Restablecer contraseña
                    </h2>

                    <p
                        class="mt-2 text-sm leading-6
                               text-slate-500"
                    >
                        Ingresa y confirma tu nueva contraseña.
                    </p>

                </div>


                <form
                    method="POST"
                    action="{{ route('password.update') }}"
                    class="space-y-5"
                    data-loading-form
                    data-loading-text="Actualizando contraseña..."
                >

                    @csrf

                    <input
                        type="hidden"
                        name="token"
                        value="{{ $request->route('token') }}"
                    >


                    {{-- Email --}}
                    <div>

                        <label
                            for="email"
                            class="mb-2 block text-sm
                                   font-semibold text-slate-700"
                        >
                            Correo electrónico
                        </label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', $request->email) }}"
                            required
                            autocomplete="username"
                            readonly
                            class="
                                block w-full rounded-lg
                                border border-slate-200
                                bg-slate-100
                                px-4 py-3 text-sm
                                text-slate-500
                            "
                        >

                        @error('email')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Contraseña --}}
                    <div>

                        <label
                            for="password"
                            class="mb-2 block text-sm
                                   font-semibold text-slate-700"
                        >
                            Nueva contraseña
                        </label>

                        <div class="relative">

                            <span
                                class="pointer-events-none
                                       absolute inset-y-0 left-0
                                       flex items-center pl-3.5
                                       text-slate-400"
                            >
                                <i
                                    class="fa-solid fa-lock"
                                    aria-hidden="true"
                                ></i>
                            </span>

                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                placeholder="Nueva contraseña"
                                class="
                                    block w-full rounded-lg
                                    border border-slate-300
                                    bg-white py-3 pl-10 pr-11
                                    text-sm text-slate-800
                                    focus:border-[#2563EB]
                                    focus:ring-2
                                    focus:ring-blue-200
                                "
                            >

                            <button
                                type="button"
                                data-password-toggle="password"
                                aria-label="Mostrar contraseña"
                                aria-pressed="false"
                                class="
                                    absolute inset-y-0 right-0
                                    flex w-11 items-center
                                    justify-center
                                    text-slate-400
                                    hover:text-slate-600
                                "
                            >
                                <i
                                    class="fa-regular fa-eye"
                                    data-password-icon
                                ></i>
                            </button>

                        </div>

                    </div>


                    {{-- Confirmación --}}
                    <div>

                        <label
                            for="password_confirmation"
                            class="mb-2 block text-sm
                                   font-semibold text-slate-700"
                        >
                            Confirmar contraseña
                        </label>

                        <div class="relative">

                            <span
                                class="pointer-events-none
                                       absolute inset-y-0 left-0
                                       flex items-center pl-3.5
                                       text-slate-400"
                            >
                                <i
                                    class="fa-solid fa-lock"
                                    aria-hidden="true"
                                ></i>
                            </span>

                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Repite la contraseña"
                                class="
                                    block w-full rounded-lg
                                    border border-slate-300
                                    bg-white py-3 pl-10 pr-11
                                    text-sm text-slate-800
                                    focus:border-[#2563EB]
                                    focus:ring-2
                                    focus:ring-blue-200
                                "
                            >

                            <button
                                type="button"
                                data-password-toggle=
                                    "password_confirmation"
                                aria-label="Mostrar contraseña"
                                aria-pressed="false"
                                class="
                                    absolute inset-y-0 right-0
                                    flex w-11 items-center
                                    justify-center
                                    text-slate-400
                                    hover:text-slate-600
                                "
                            >
                                <i
                                    class="fa-regular fa-eye"
                                    data-password-icon
                                ></i>
                            </button>

                        </div>

                    </div>


                    @error('password')
                        <div
                            class="rounded-lg border border-red-200
                                   bg-red-50 px-4 py-3
                                   text-sm text-red-700"
                        >
                            {{ $message }}
                        </div>
                    @enderror


                    <button
                        type="submit"
                        data-submit-button
                        class="
                            inline-flex w-full items-center
                            justify-center gap-2
                            rounded-lg bg-[#2563EB]
                            px-4 py-3 text-sm
                            font-semibold text-white
                            transition duration-200
                            hover:bg-[#1D4ED8]
                            active:bg-[#1E40AF]
                            disabled:cursor-not-allowed
                            disabled:bg-slate-300
                        "
                    >

                        <i
                            class="fa-solid fa-key"
                            data-submit-icon
                            aria-hidden="true"
                        ></i>

                        <span data-submit-text>
                            Actualizar contraseña
                        </span>

                    </button>

                </form>

            </div>

        </section>

    </main>

</x-guest-layout>