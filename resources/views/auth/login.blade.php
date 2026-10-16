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
                    Plataforma educativa
                </span>

                <h1 class="text-4xl font-bold leading-tight xl:text-5xl">
                    Aprende, practica y avanza a tu ritmo.
                </h1>

                <p class="mt-5 max-w-lg text-base leading-7 text-blue-100">
                    Accede a tus cursos, contenidos, evaluaciones y
                    recomendaciones de aprendizaje desde un solo lugar.
                </p>


                <div class="mt-8 flex flex-wrap gap-3 text-sm font-medium">
                    <span class="rounded-lg bg-white/10 px-4 py-2 ring-1 ring-white/15">
                        <i class="fa-solid fa-calculator mr-2" aria-hidden="true"></i>
                        Matemáticas
                    </span>

                    <span class="rounded-lg bg-white/10 px-4 py-2 ring-1 ring-white/15">
                        <i class="fa-solid fa-atom mr-2" aria-hidden="true"></i>
                        Física
                    </span>

                    <span class="rounded-lg bg-white/10 px-4 py-2 ring-1 ring-white/15">
                        <i class="fa-solid fa-flask mr-2" aria-hidden="true"></i>
                        Química
                    </span>
                </div>

            </div>

            <p class="relative z-10 text-sm text-blue-200">
                Educación clara, organizada y enfocada en tu progreso.
            </p>

            
            <div
                class="absolute -right-24 -top-24 h-80 w-80
                       rounded-full bg-blue-500/20 blur-3xl"
            ></div>

            <div
                class="absolute -bottom-24 -left-20 h-72 w-72
                       rounded-full bg-sky-400/10 blur-3xl"
            ></div>
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
                        Inicio de sesión
                    </p>

                    <h2 class="text-3xl font-bold tracking-tight text-slate-800">
                        Bienvenido de nuevo
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Ingresa tus credenciales para continuar con tu
                        aprendizaje.
                    </p>
                </div>

                @session('status')
                    <div class="mb-5 flex items-start gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                        <i class="fa-solid fa-circle-check mt-0.5" aria-hidden="true"></i>

                        <span>{{ $value }}</span>
                    </div>
                @endsession

                @if ($errors->any())
                    <div class="mb-5 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <i class="fa-solid fa-circle-exclamation mt-0.5" aria-hidden="true"></i>

                        <div>
                            <p class="font-semibold">
                                No se pudo iniciar sesión.
                            </p>

                            <p class="mt-1">
                                Revisa tu correo y contraseña e inténtalo
                                nuevamente.
                            </p>
                        </div>
                    </div>
                @endif


                
                <form method="POST" action="{{ route('login') }}" class="space-y-5" data-loading-form data-loading-text="Iniciando sesión...">

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
                                autocomplete="username"
                                placeholder="estudiante@ejemplo.com"
                                class="
                                    block w-full rounded-lg border
                                    @error('email')
                                        border-red-500
                                    @else
                                        border-slate-300
                                    @enderror
                                    bg-white
                                    py-3
                                    pl-10
                                    pr-4
                                    text-sm
                                    text-slate-800
                                    placeholder:text-slate-400
                                    transition
                                    duration-200
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

                    <div>

                        <div class="mb-2 flex items-center justify-between gap-4">
                            <label for="password" class="block text-sm font-semibold text-slate-700">
                                Contraseña
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                    class="
                                        text-sm
                                        font-medium
                                        text-[#2563EB]
                                        transition
                                        hover:text-[#1D4ED8]
                                        focus:outline-none
                                        focus:ring-2
                                        focus:ring-blue-200
                                        focus:ring-offset-2
                                    "
                                >
                                    ¿Olvidaste tu contraseña?
                                </a>
                            @endif
                        </div>


                        <div class="relative">

                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <i class="fa-solidfa-lock" aria-hidden="true"></i>
                            </span>

                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Ingresa tu contraseña"
                                class="
                                    block w-full rounded-lg border
                                    @error('password')
                                        border-red-500
                                    @else
                                        border-slate-300
                                    @enderror
                                    bg-white
                                    py-3
                                    pl-10
                                    pr-12
                                    text-sm
                                    text-slate-800
                                    placeholder:text-slate-400
                                    transition
                                    duration-200
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
                                    absolute
                                    inset-y-0
                                    right-0
                                    flex
                                    w-11
                                    items-center
                                    justify-center
                                    rounded-r-lg
                                    text-slate-400
                                    transition
                                    hover:text-slate-600
                                    focus:outline-none
                                    focus:ring-2
                                    focus:ring-inset
                                    focus:ring-blue-200
                                "
                            >
                                <i
                                    class="fa-regular fa-eye"
                                    data-password-icon
                                    aria-hidden="true"
                                ></i>
                            </button>

                        </div>

                        @error('password')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    <label for="remember_me" class="flex w-fit cursor-pointer items-center gap-2.5 text-sm text-slate-600">
                        <input
                            id="remember_me"
                            type="checkbox"
                            name="remember"
                            class="
                                h-4
                                w-4
                                rounded
                                border-slate-300
                                text-[#2563EB]
                                focus:ring-[#2563EB]
                            "
                        >

                        <span>Recordarme</span>
                    </label>

                    <button
                        type="submit"
                        data-submit-button
                        class=" inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#2563EB] px-4 py-3 text-sm font-semibold text-white transition duration-200 hover:bg-[#1D4ED8] focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2 active:bg-[#1E40AF] disabled:cursor-not-allowed disabled:bg-slate-300">

                        <i class="fa-solid fa-right-to-bracket" data-submit-icon aria-hidden="true"></i>
                        <span data-submit-text>
                            Iniciar sesión
                        </span>

                    </button>

                </form>

                @if (Route::has('register'))
                    <p class="mt-8 text-center text-sm text-slate-500">
                        ¿No tienes una cuenta?
                        <a href="{{ route('register') }}" class=" font-semibold text-[#2563EB] transition hover:text-[#1D4ED8] focus:outline-none focus:ring-2 focus:ring-blue-200 focus:ring-offset-2">
                            Crear una cuenta
                        </a>
                    </p>
                @endif

            </div>
        </section>

    </main>
</x-guest-layout>