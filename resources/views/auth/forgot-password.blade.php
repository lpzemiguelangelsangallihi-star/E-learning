<x-auth.shell
    eyebrow="Recuperar contraseña"
    title="¿Olvidaste tu contraseña?"
    subtitle="Ingresa el correo asociado a tu cuenta y te enviaremos un enlace de recuperación."
>
    @session('status')
        <x-auth.notice>{{ $value }}</x-auth.notice>
    @endsession

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" data-loading-form data-loading-text="Enviando enlace...">
        @csrf

        <x-auth.field name="email" label="Correo electrónico" type="email" icon="fa-regular fa-envelope" :value="old('email')" placeholder="estudiante@ejemplo.com" required autofocus autocomplete="username" />

        <x-auth.submit icon="fa-solid fa-paper-plane">Enviar enlace de recuperación</x-auth.submit>
    </form>

    <div class="mt-7 text-center">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#EAB308] transition hover:text-yellow-300">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            Volver al inicio de sesión
        </a>
    </div>
</x-auth.shell>
