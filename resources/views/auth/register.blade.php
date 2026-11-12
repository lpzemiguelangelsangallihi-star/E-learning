<x-auth.shell
    wide
    eyebrow="Registro"
    title="Crear una cuenta"
    subtitle="Completa tus datos para comenzar a utilizar E-Learning."
>
    @if ($errors->has('registro'))
        <x-auth.notice type="danger">{{ $errors->first('registro') }}</x-auth.notice>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4" data-loading-form data-loading-text="Creando cuenta...">
        @csrf

        <x-auth.field name="nombre" label="Nombre" icon="fa-regular fa-user" :value="old('nombre')" placeholder="Ingresa tu nombre" autocomplete="given-name" required autofocus />

        <div class="grid gap-4 sm:grid-cols-2">
            <x-auth.field name="apellido_paterno" label="Apellido paterno" :value="old('apellido_paterno')" placeholder="Apellido paterno" required />
            <x-auth.field name="apellido_materno" label="Apellido materno" :value="old('apellido_materno')" placeholder="Apellido materno" required />
        </div>

        <x-auth.field name="email" label="Correo electrónico" type="email" icon="fa-regular fa-envelope" :value="old('email')" placeholder="estudiante@ejemplo.com" autocomplete="username" required />

        <div class="grid gap-4 sm:grid-cols-2">
            <x-auth.field name="password" label="Contraseña" type="password" icon="fa-solid fa-lock" placeholder="Contraseña" autocomplete="new-password" required />
            <x-auth.field name="password_confirmation" label="Confirmar" type="password" icon="fa-solid fa-lock" placeholder="Repite tu contraseña" autocomplete="new-password" required />
        </div>

        @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
            <label for="terms" class="flex cursor-pointer items-start gap-3 text-sm text-blue-100">
                <input id="terms" type="checkbox" name="terms" required class="mt-0.5 h-4 w-4 rounded border-white/30 bg-white/10 text-[#2563EB] focus:ring-[#2563EB]">
                <span>
                    Acepto los
                    <a href="{{ route('terms.show') }}" target="_blank" class="font-medium text-[#EAB308] hover:text-yellow-300">términos de servicio</a>
                    y la
                    <a href="{{ route('policy.show') }}" target="_blank" class="font-medium text-[#EAB308] hover:text-yellow-300">política de privacidad</a>.
                </span>
            </label>
        @endif

        <x-auth.submit icon="fa-solid fa-user-plus">Crear cuenta</x-auth.submit>
    </form>

    <p class="mt-7 text-center text-sm text-blue-100/80">
        ¿Ya tienes una cuenta?
        <a href="{{ route('login') }}" class="font-semibold text-[#EAB308] transition hover:text-yellow-300">Iniciar sesión</a>
    </p>
</x-auth.shell>
