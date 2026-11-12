<x-auth.shell
    eyebrow="Área segura"
    title="Confirma tu contraseña"
    subtitle="Esta es una zona protegida. Confirma tu contraseña antes de continuar."
>
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <x-auth.field name="password" label="Contraseña" type="password" icon="fa-solid fa-lock" placeholder="Tu contraseña" required autofocus autocomplete="current-password" />

        <x-auth.submit icon="fa-solid fa-shield-halved">Confirmar</x-auth.submit>
    </form>
</x-auth.shell>
