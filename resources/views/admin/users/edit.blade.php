<x-admin-layout
    title="Editar usuario"
    description="Actualiza la información y permisos del usuario."
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


        <div
            class="
                mb-6 flex items-center gap-4
                rounded-xl
                border border-slate-200
                bg-white
                p-5
                shadow-sm
            "
        >

            <div
                class="
                    flex h-12 w-12
                    items-center justify-center
                    rounded-full
                    bg-blue-50
                    text-lg font-bold
                    text-[#2563EB]
                "
            >
                {{
                    mb_strtoupper(
                        mb_substr(
                            $user->nombre,
                            0,
                            1
                        )
                    )
                }}
            </div>


            <div>

                <p class="font-semibold text-slate-800">
                    {{ $user->nombre_completo }}
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $user->email }}
                </p>

            </div>

        </div>


        @include('admin.users._form', [
            'user' => $user,
            'roles' => $roles,
            'action' => route(
                'admin.users.update',
                $user
            ),
            'method' => 'PUT',
            'submitText' => 'Guardar cambios',
        ])

    </div>

</x-admin-layout>