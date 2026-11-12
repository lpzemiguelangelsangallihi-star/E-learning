<x-auth.shell
    eyebrow="Verificación en dos pasos"
    title="Confirma tu acceso"
    subtitle="Ingresa el código de tu aplicación autenticadora o un código de recuperación."
>
    <div x-data="{ recovery: false }">
        <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-5">
            @csrf

            <div x-show="! recovery">
                <x-auth.field name="code" label="Código de autenticación" icon="fa-solid fa-mobile-screen" inputmode="numeric" placeholder="123456" autocomplete="one-time-code" autofocus x-ref="code" />
            </div>

            <div x-cloak x-show="recovery">
                <x-auth.field name="recovery_code" label="Código de recuperación" icon="fa-solid fa-key" autocomplete="one-time-code" x-ref="recovery_code" />
            </div>

            <x-auth.submit>Verificar</x-auth.submit>

            <div class="text-center">
                <button type="button" class="text-sm font-medium text-blue-200 underline-offset-4 transition hover:text-white hover:underline"
                    x-show="! recovery"
                    x-on:click="recovery = true; $nextTick(() => { $refs.recovery_code.focus() })">
                    Usar un código de recuperación
                </button>

                <button type="button" class="text-sm font-medium text-blue-200 underline-offset-4 transition hover:text-white hover:underline"
                    x-cloak
                    x-show="recovery"
                    x-on:click="recovery = false; $nextTick(() => { $refs.code.focus() })">
                    Usar un código de autenticación
                </button>
            </div>
        </form>
    </div>
</x-auth.shell>
