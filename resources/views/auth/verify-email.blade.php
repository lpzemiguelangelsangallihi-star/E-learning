<x-auth.shell
    eyebrow="Verificación"
    title="Verifica tu correo"
    subtitle="Antes de continuar, confirma tu correo con el enlace que te enviamos. Si no lo recibiste, podemos enviarte otro."
>
    @if (session('status') == 'verification-link-sent')
        <x-auth.notice>Se envió un nuevo enlace de verificación a tu correo.</x-auth.notice>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="space-y-5">
        @csrf
        <x-auth.submit icon="fa-solid fa-paper-plane">Reenviar correo de verificación</x-auth.submit>
    </form>

    <div class="mt-6 flex items-center justify-center gap-5 text-sm">
        <a href="{{ route('profile.show') }}" class="font-medium text-blue-200 transition hover:text-white">Editar perfil</a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="font-medium text-[#EAB308] transition hover:text-yellow-300">Cerrar sesión</button>
        </form>
    </div>
</x-auth.shell>
