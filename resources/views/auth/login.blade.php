<x-auth.shell
    eyebrow="Inicio de sesión"
    title="Bienvenido de nuevo"
    subtitle="Ingresa tus credenciales para continuar con tu aprendizaje."
>
    @session('status')
        <x-auth.notice>{{ $value }}</x-auth.notice>
    @endsession

    @if ($errors->any())
        <x-auth.notice type="danger">
            <p class="font-semibold">No se pudo iniciar sesión.</p>
            <p class="mt-1">Revisa tu correo y contraseña e inténtalo nuevamente.</p>
        </x-auth.notice>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5" data-loading-form data-loading-text="Iniciando sesión...">
        @csrf

        <x-auth.field
            name="email"
            label="Correo electrónico"
            type="email"
            icon="fa-regular fa-envelope"
            :value="old('email')"
            placeholder="estudiante@ejemplo.com"
            autocomplete="username"
            required
            autofocus
        />

        <div>
            <x-auth.field
                name="password"
                label="Contraseña"
                type="password"
                icon="fa-solid fa-lock"
                placeholder="Ingresa tu contraseña"
                autocomplete="current-password"
                required
            />

            @if (Route::has('password.request'))
                <div class="mt-2 text-right">
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-200 transition hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-300">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
            @endif
        </div>

        <label for="remember_me" class="flex w-fit cursor-pointer items-center gap-2.5 text-sm text-blue-100">
            <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-white/30 bg-white/10 text-[#2563EB] focus:ring-[#2563EB]">
            <span>Recordarme</span>
        </label>

        <x-auth.submit>Iniciar sesión</x-auth.submit>
    </form>

    @if (Route::has('register'))
        <p class="mt-7 text-center text-sm text-blue-100/80">
            ¿No tienes una cuenta?
            <a href="{{ route('register') }}" class="font-semibold text-[#EAB308] transition hover:text-yellow-300 focus:outline-none focus:ring-2 focus:ring-yellow-300">
                Crear una cuenta
            </a>
        </p>
    @endif
</x-auth.shell>
