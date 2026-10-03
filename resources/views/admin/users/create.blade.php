<x-admin-layout
    title="Crear usuario"
    description="Registra un nuevo usuario en la plataforma."
>

    <div class="mx-auto max-w-4xl">

        <div class="mb-6">

            <a
                href="{{ route('admin.users.index') }}"
                class="
                    inline-flex items-center gap-2
                    text-sm font-semibold
                    text-[#2563EB]
                    hover:text-[#1D4ED8]
                "
            >
                <i class="fa-solid fa-arrow-left"></i>

                Volver a usuarios
            </a>

        </div>


        @include('admin.users._form', [
            'user' => null,
            'roles' => $roles,
            'action' => route('admin.users.store'),
            'method' => 'POST',
            'submitText' => 'Crear usuario',
        ])

    </div>

</x-admin-layout>