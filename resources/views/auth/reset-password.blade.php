<x-auth.shell
    eyebrow="Nueva contraseña"
    title="Restablecer contraseña"
    subtitle="Ingresa y confirma tu nueva contraseña."
>
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5" data-loading-form data-loading-text="Actualizando contraseña...">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-auth.field name="email" label="Correo electrónico" type="email" icon="fa-regular fa-envelope" :value="old('email', $request->email)" required autofocus autocomplete="username" />

        <x-auth.field name="password" label="Nueva contraseña" type="password" icon="fa-solid fa-lock" placeholder="Nueva contraseña" required autocomplete="new-password" />

        <x-auth.field name="password_confirmation" label="Confirmar contraseña" type="password" icon="fa-solid fa-lock" placeholder="Repite la contraseña" required autocomplete="new-password" />

        <x-auth.submit icon="fa-solid fa-key">Actualizar contraseña</x-auth.submit>
    </form>
</x-auth.shell>
