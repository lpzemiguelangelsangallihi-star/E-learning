@props([
    'user' => null,
    'roles',
    'action',
    'method' => 'POST',
    'submitText' => 'Guardar usuario',
])

<form
    method="POST"
    action="{{ $action }}"
    class="space-y-6"
    data-loading-form
    data-loading-text="Guardando..."
>
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>

        <div class="grid gap-5 md:grid-cols-2">

            {{-- Nombre --}}
            <div>
                <label
                    for="nombre"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Nombre
                </label>

                <input
                    id="nombre"
                    type="text"
                    name="nombre"
                    value="{{ old('nombre', $user?->nombre) }}"
                    required
                    class="
                        block w-full rounded-lg
                        border-slate-300
                        px-4 py-3
                        text-sm text-slate-800
                        focus:border-[#2563EB]
                        focus:ring-[#2563EB]
                    "
                >

                @error('nombre')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            {{-- Apellido paterno --}}
            <div>
                <label
                    for="apellido_paterno"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Apellido paterno
                </label>

                <input
                    id="apellido_paterno"
                    type="text"
                    name="apellido_paterno"
                    value="{{ old('apellido_paterno', $user?->apellido_paterno) }}"
                    required
                    class="
                        block w-full rounded-lg
                        border-slate-300
                        px-4 py-3
                        text-sm text-slate-800
                        focus:border-[#2563EB]
                        focus:ring-[#2563EB]
                    "
                >

                @error('apellido_paterno')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            {{-- Apellido materno --}}
            <div>
                <label
                    for="apellido_materno"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Apellido materno
                </label>

                <input
                    id="apellido_materno"
                    type="text"
                    name="apellido_materno"
                    value="{{ old('apellido_materno', $user?->apellido_materno) }}"
                    required
                    class="
                        block w-full rounded-lg
                        border-slate-300
                        px-4 py-3
                        text-sm text-slate-800
                        focus:border-[#2563EB]
                        focus:ring-[#2563EB]
                    "
                >

                @error('apellido_materno')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            {{-- Email --}}
            <div>
                <label
                    for="email"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Correo electrónico
                </label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $user?->email) }}"
                    required
                    class="
                        block w-full rounded-lg
                        border-slate-300
                        px-4 py-3
                        text-sm text-slate-800
                        focus:border-[#2563EB]
                        focus:ring-[#2563EB]
                    "
                >

                @error('email')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            {{-- Rol --}}
            <div>
                <label
                    for="rol_id"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Rol
                </label>

                <select
                    id="rol_id"
                    name="rol_id"
                    required
                    class="
                        block w-full rounded-lg
                        border-slate-300
                        px-4 py-3
                        text-sm text-slate-800
                        focus:border-[#2563EB]
                        focus:ring-[#2563EB]
                    "
                >

                    <option value="">
                        Selecciona un rol
                    </option>

                    @foreach ($roles as $rol)

                        <option
                            value="{{ $rol->id }}"
                            @selected(
                                old(
                                    'rol_id',
                                    $user?->rol_id
                                ) == $rol->id
                            )
                        >
                            {{ ucfirst($rol->nombre) }}
                        </option>

                    @endforeach

                </select>

                @error('rol_id')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            {{-- Estado --}}
            <div>
                <label
                    for="estado"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Estado
                </label>

                <select
                    id="estado"
                    name="estado"
                    required
                    class="
                        block w-full rounded-lg
                        border-slate-300
                        px-4 py-3
                        text-sm text-slate-800
                        focus:border-[#2563EB]
                        focus:ring-[#2563EB]
                    "
                >

                    <option
                        value="activo"
                        @selected(
                            old(
                                'estado',
                                $user?->estado ?? 'activo'
                            ) === 'activo'
                        )
                    >
                        Activo
                    </option>

                    <option
                        value="inactivo"
                        @selected(
                            old(
                                'estado',
                                $user?->estado
                            ) === 'inactivo'
                        )
                    >
                        Inactivo
                    </option>

                </select>

                @error('estado')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            {{-- Password --}}
            <div>
                <label
                    for="password"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Contraseña
                </label>

                <div class="relative">

                    <input
                        id="password"
                        type="password"
                        name="password"
                        {{ $user ? '' : 'required' }}
                        autocomplete="new-password"
                        class="
                            block w-full rounded-lg
                            border-slate-300
                            px-4 py-3 pr-11
                            text-sm text-slate-800
                            focus:border-[#2563EB]
                            focus:ring-[#2563EB]
                        "
                    >

                    <button
                        type="button"
                        data-password-toggle="password"
                        aria-label="Mostrar contraseña"
                        aria-pressed="false"
                        class="
                            absolute inset-y-0 right-0
                            flex w-11 items-center justify-center
                            text-slate-400
                            hover:text-slate-600
                        "
                    >
                        <i
                            class="fa-regular fa-eye"
                            data-password-icon
                        ></i>
                    </button>

                </div>

                @if ($user)
                    <p class="mt-2 text-xs text-slate-500">
                        Déjala vacía si no deseas cambiarla.
                    </p>
                @endif

                @error('password')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            {{-- Confirmar --}}
            <div>
                <label
                    for="password_confirmation"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Confirmar contraseña
                </label>

                <div class="relative">

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        {{ $user ? '' : 'required' }}
                        autocomplete="new-password"
                        class="
                            block w-full rounded-lg
                            border-slate-300
                            px-4 py-3 pr-11
                            text-sm text-slate-800
                            focus:border-[#2563EB]
                            focus:ring-[#2563EB]
                        "
                    >

                    <button
                        type="button"
                        data-password-toggle="password_confirmation"
                        aria-label="Mostrar contraseña"
                        aria-pressed="false"
                        class="
                            absolute inset-y-0 right-0
                            flex w-11 items-center justify-center
                            text-slate-400
                            hover:text-slate-600
                        "
                    >
                        <i
                            class="fa-regular fa-eye"
                            data-password-icon
                        ></i>
                    </button>

                </div>
            </div>

        </div>

    </x-ui.card>


    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

        <a
            href="{{ route('admin.users.index') }}"
            class="
                inline-flex items-center justify-center
                rounded-lg
                border border-slate-300
                bg-white
                px-4 py-2.5
                text-sm font-semibold
                text-slate-700
                transition
                hover:bg-slate-50
            "
        >
            Cancelar
        </a>


        <button
            type="submit"
            data-submit-button
            class="
                inline-flex items-center justify-center
                gap-2 rounded-lg
                bg-[#2563EB]
                px-4 py-2.5
                text-sm font-semibold
                text-white
                transition
                hover:bg-[#1D4ED8]
                active:bg-[#1E40AF]
                disabled:cursor-not-allowed
                disabled:bg-slate-300
            "
        >
            <i
                class="fa-solid fa-floppy-disk"
                data-submit-icon
            ></i>

            <span data-submit-text>
                {{ $submitText }}
            </span>
        </button>

    </div>

</form>