<x-guest-layout>

    <main class="min-h-screen lg:grid lg:grid-cols-2">

        <section class="relative hidden overflow-hidden bg-[#1E3A8A] px-12 py-10 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="relative z-10">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3 text-xl font-bold tracking-tight">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20">
                        <i class="fa-solid fa-graduation-cap text-xl" aria-hidden="true"></i>
                    </span>
                    <span>E-Learning</span>
                </a>
            </div>
            <div class="relative z-10 max-w-xl">
                <span class="mb-5 inline-flex items-center rounded-full bg-white/10 px-4 py-2 text-sm font-medium ring-1 ring-white/15">
                    Recuperación de acceso
                </span>

                <h1 class="text-4xl font-bold leading-tight xl:text-5xl">
                    Recupera el acceso a tu cuenta.
                </h1>

                <p class="mt-5 max-w-lg text-base leading-7 text-blue-100">
                    Ingresa tu correo electrónico y te enviaremos
                    las instrucciones necesarias para establecer
                    una nueva contraseña.
                </p>

                <div class="mt-8 space-y-4">
                    <div class="flex items-center gap-3 text-sm text-blue-50">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10">
                            <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                        </span>

                        <span>
                            Recibirás un enlace en tu correo
                        </span>
                    </div>

                    <div class="flex items-center gap-3 text-sm text-blue-50">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10">
                            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                        </span>
                        <span>
                            El proceso es seguro y confidencial
                        </span>
                    </div>

                </div>

            </div>

            <p class="relative z-10 text-sm text-blue-200">
                E-Learning
            </p>

            <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-20 h-72 w-72 rounded-full bg-sky-400/10 blur-3xl"></div>
        </section>

        <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8 lg:px-12">
            <div class="w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-3 text-xl font-bold text-[#1E3A8A]">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-[#2563EB]">
                            <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                        </span>
                        <span>E-Learning</span>
                    </a>
                </div>

                <div class="mb-8">

                    <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-[#2563EB]">
                        Recuperar contraseña
                    </p>

                    <h2 class="text-3xl font-bold tracking-tight text-slate-800">
                        ¿Olvidaste tu contraseña?
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Ingresa el correo asociado a tu cuenta
                        y te enviaremos un enlace de recuperación.
                    </p>

                </div>

                @session('status')
                    <div class="mb-5 flex items-start gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"role="status">
                        <i class="fa-solid fa-circle-check mt-0.5" aria-hidden="true"></i>
                        <span>{{ $value }}</span>
                    </div>
                @endsession

                <form method="POST" action="{{ route('password.email') }}" class="space-y-5" data-loading-form data-loading-text="Enviando enlace...">

                    @csrf
                    <div>

                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">
                            Correo electrónico
                        </label>

                        <div class="relative">

                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                            </span>

                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="estudiante@ejemplo.com"
                                class="
                                    block w-full rounded-lg border
                                    @error('email')
                                        border-red-500
                                    @else
                                        border-slate-300
                                    @enderror
                                    bg-white py-3 pl-10 pr-4
                                    text-sm text-slate-800
                                    placeholder:text-slate-400
                                    transition duration-200
                                    focus:border-[#2563EB]
                                    focus:ring-2
                                    focus:ring-blue-200
                                "
                            >

                        </div>

                        @error('email')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <button
                        type="submit"
                        data-submit-button
                        class="
                            inline-flex w-full items-center justify-center
                            gap-2 rounded-lg bg-[#2563EB]
                            px-4 py-3 text-sm font-semibold text-white
                            transition duration-200
                            hover:bg-[#1D4ED8]
                            focus:outline-none
                            focus:ring-2
                            focus:ring-[#2563EB]
                            focus:ring-offset-2
                            active:bg-[#1E40AF]
                            disabled:cursor-not-allowed
                            disabled:bg-slate-300
                        "
                    >

                        <i class="fa-solid fa-paper-plane" data-submit-icon aria-hidden="true"></i>

                        <span data-submit-text>
                            Enviar enlace de recuperación
                        </span>

                    </button>

                </form>

                <div class="mt-7 text-center">

                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#2563EB] transition hover:text-[#1D4ED8]">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                        Volver al inicio de sesión
                    </a>
                </div>

            </div>

        </section>

    </main>

</x-guest-layout>