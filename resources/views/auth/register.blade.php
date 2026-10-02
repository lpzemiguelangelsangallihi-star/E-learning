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
                    Comienza tu aprendizaje
                </span>
                <h1 class="text-4xl font-bold leading-tight xl:text-5xl">
                    Tu camino de aprendizaje comienza aquí.
                </h1>
                <p class="mt-5 max-w-lg text-base leading-7 text-blue-100">
                    Crea tu cuenta y accede a cursos,
                    evaluaciones, seguimiento de progreso y
                    recomendaciones diseñadas para ayudarte
                    a mejorar.
                </p>

                <div class="mt-8 space-y-4">

                    <div class="flex items-center gap-3 text-sm text-blue-50">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10">
                            <i class="fa-solid fa-book-open" aria-hidden="true"></i>
                        </span>

                        <span>
                            Accede a tus cursos y contenidos
                        </span>
                    </div>


                    <div class="flex items-center gap-3 text-sm text-blue-50">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10">
                            <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                        </span>

                        <span>
                            Consulta tu progreso académico
                        </span>
                    </div>

                    <div class="flex items-center gap-3 text-sm text-blue-50">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10">
                            <i class="fa-solid fa-lightbulb" aria-hidden="true"></i>
                        </span>
                        <span>
                            Recibe recomendaciones de aprendizaje
                        </span>
                    </div>

                </div>

            </div>

            <p class="relative z-10 text-sm text-blue-200">
                Matemáticas · Física · Química
            </p>

            <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl"></div>

            <div class="absolute -bottom-24 -left-20 h-72 w-72 rounded-full bg-sky-400/10 blur-3xl"></div>
        </section>

        <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8 lg:px-12">

            <div class="w-full max-w-xl">

                <div class="mb-8 lg:hidden">

                    <a href="{{ url('/') }}" class="inline-flex items-center gap-3 text-xl font-bold text-[#1E3A8A]">

                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-[#2563EB]">
                            <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                        </span>
                        <span>E-Learning</span>
                    </a>
                </div>

                <div class="mb-7">

                    <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-[#2563EB]">
                        Registro
                    </p>

                    <h2 class="text-3xl font-bold tracking-tight text-slate-800">
                        Crear una cuenta
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Completa tus datos para comenzar
                        a utilizar E-Learning.
                    </p>

                </div>

                @if ($errors->has('registro'))

                    <div class="mb-5 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">

                        <i class="fa-solid fa-circle-exclamation mt-0.5" aria-hidden="true"></i>

                        <span>
                            {{ $errors->first('registro') }}
                        </span>
                    </div>

                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-5" data-loading-form data-loading-text="Creando cuenta...">

                    @csrf
                    <div>

                        <label for="nombre" class="mb-2 block text-sm font-semibold text-slate-700">
                            Nombre
                        </label>

                        <div class="relative">

                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <i class="fa-regular fa-user" aria-hidden="true"></i>
                            </span>

                            <input
                                id="nombre"
                                type="text"
                                name="nombre"
                                value="{{ old('nombre') }}"
                                required
                                autofocus
                                autocomplete="given-name"
                                placeholder="Ingresa tu nombre"
                                class="
                                    block w-full rounded-lg border
                                    @error('nombre')
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

                        @error('nombre')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>

                            <label for="apellido_paterno" class="mb-2 block text-sm font-semibold text-slate-700">
                                Apellido paterno
                            </label>

                            <input
                                id="apellido_paterno"
                                type="text"
                                name="apellido_paterno"
                                value="{{ old('apellido_paterno') }}"
                                required
                                autocomplete="family-name"
                                placeholder="Apellido paterno"
                                class="
                                    block w-full rounded-lg border
                                    @error('apellido_paterno')
                                        border-red-500
                                    @else
                                        border-slate-300
                                    @enderror
                                    bg-white px-4 py-3
                                    text-sm text-slate-800
                                    placeholder:text-slate-400
                                    transition duration-200
                                    focus:border-[#2563EB]
                                    focus:ring-2
                                    focus:ring-blue-200
                                "
                            >

                            @error('apellido_paterno')
                                <p
                                    class="mt-2 text-sm
                                           text-red-600"
                                >
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        <div>

                            <label for="apellido_materno" class="mb-2 block text-sm font-semibold text-slate-700">
                                Apellido materno
                            </label>

                            <input
                                id="apellido_materno"
                                type="text"
                                name="apellido_materno"
                                value="{{ old('apellido_materno') }}"
                                required
                                placeholder="Apellido materno"
                                class="
                                    block w-full rounded-lg border
                                    @error('apellido_materno')
                                        border-red-500
                                    @else
                                        border-slate-300
                                    @enderror
                                    bg-white px-4 py-3
                                    text-sm text-slate-800
                                    placeholder:text-slate-400
                                    transition duration-200
                                    focus:border-[#2563EB]
                                    focus:ring-2
                                    focus:ring-blue-200
                                "
                            >

                            @error('apellido_materno')
                                <p
                                    class="mt-2 text-sm
                                           text-red-600"
                                >
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>

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
                                autocomplete="username"
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


                    <div class="grid gap-5 sm:grid-cols-2">

                        <div>

                            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">
                                Contraseña
                            </label>

                            <div class="relative">

                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                </span>

                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Contraseña"
                                    class="
                                        block w-full rounded-lg border
                                        @error('password')
                                            border-red-500
                                        @else
                                            border-slate-300
                                        @enderror
                                        bg-white py-3
                                        pl-10 pr-11
                                        text-sm text-slate-800
                                        placeholder:text-slate-400
                                        transition duration-200
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
                                    class="absolute inset-y-0 right-0
                                           flex w-11 items-center
                                           justify-center
                                           rounded-r-lg text-slate-400
                                           transition
                                           hover:text-slate-600
                                           focus:outline-none
                                           focus:ring-2
                                           focus:ring-inset
                                           focus:ring-blue-200"
                                >

                                    <i
                                        class="fa-regular fa-eye"
                                        data-password-icon
                                        aria-hidden="true"
                                    ></i>

                                </button>

                            </div>

                        </div>


                        <div>

                            <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">
                                Confirmar contraseña
                            </label>

                            <div class="relative">

                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                </span>

                                <input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Repite tu contraseña"
                                    class="
                                        block w-full rounded-lg border
                                        border-slate-300
                                        bg-white py-3
                                        pl-10 pr-11
                                        text-sm text-slate-800
                                        placeholder:text-slate-400
                                        transition duration-200
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
                                    class="absolute inset-y-0 right-0
                                           flex w-11 items-center
                                           justify-center
                                           rounded-r-lg text-slate-400
                                           transition
                                           hover:text-slate-600
                                           focus:outline-none
                                           focus:ring-2
                                           focus:ring-inset
                                           focus:ring-blue-200"
                                >

                                    <i
                                        class="fa-regular fa-eye"
                                        data-password-icon
                                        aria-hidden="true"
                                    ></i>

                                </button>

                            </div>

                        </div>

                    </div>


                    @error('password')
                        <p class="-mt-3 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    @if (
                        Laravel\Jetstream\Jetstream::
                            hasTermsAndPrivacyPolicyFeature()
                    )

                        <label for="terms" class="flex cursor-pointer items-start gap-3 text-sm text-slate-600">

                            <input
                                id="terms"
                                type="checkbox"
                                name="terms"
                                required
                                class="mt-0.5 h-4 w-4 rounded
                                       border-slate-300
                                       text-[#2563EB]
                                       focus:ring-[#2563EB]"
                            >

                            <span>
                                Acepto los

                                <a href="{{ route('terms.show') }}" target="_blank" class="font-medium text-[#2563EB] hover:text-[#1D4ED8]">
                                    términos de servicio
                                </a>

                                y la

                                <a href="{{ route('policy.show') }}" target="_blank" class="font-medium text-[#2563EB] hover:text-[#1D4ED8]">
                                    política de privacidad
                                </a>.
                            </span>

                        </label>

                    @endif

                    <button
                        type="submit"
                        data-submit-button
                        class="
                            inline-flex w-full
                            items-center justify-center
                            gap-2 rounded-lg
                            bg-[#2563EB]
                            px-4 py-3
                            text-sm font-semibold
                            text-white
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

                        <i class="fa-solid fa-user-plus" data-submit-icon aria-hidden="true"></i>

                        <span data-submit-text>
                            Crear cuenta
                        </span>

                    </button>

                </form>

                <p class="mt-7 text-center text-sm text-slate-500">
                    ¿Ya tienes una cuenta?
                    <a  href="{{ route('login') }}" class="font-semibold text-[#2563EB] transition hover:text-[#1D4ED8] focus:outline-none focus:ring-2 focus:ring-blue-200 focus:ring-offset-2">
                        Iniciar sesión
                    </a>

                </p>

            </div>

        </section>

    </main>

</x-guest-layout>