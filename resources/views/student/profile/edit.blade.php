<x-student-layout
    title="Mi perfil"
    description="Tus datos personales y seguridad."
>

    @php
        $campo = 'block w-full rounded-lg border-slate-300 px-4 py-3 text-sm text-slate-800 focus:border-[#2563EB] focus:ring-[#2563EB]';
        $label = 'mb-2 block text-sm font-semibold text-slate-700';
    @endphp

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Resumen --}}
        <x-ui.card class="h-fit">
            <div class="flex flex-col items-center text-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-[#1E3A8A] text-2xl font-bold text-white">
                    {{ mb_strtoupper(mb_substr($user->nombre, 0, 1)) }}{{ mb_strtoupper(mb_substr($user->apellido_paterno, 0, 1)) }}
                </div>

                <h2 class="mt-4 text-lg font-bold text-slate-800">{{ $user->nombre_completo }}</h2>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>

                <x-ui.badge type="primary" class="mt-3">Estudiante</x-ui.badge>
            </div>

            <dl class="mt-6 space-y-3 border-t border-slate-100 pt-6 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Cursos</dt>
                    <dd class="font-semibold text-slate-800">{{ $cursos }}</dd>
                </div>

                <div class="flex justify-between">
                    <dt class="text-slate-500">Miembro desde</dt>
                    <dd class="font-semibold text-slate-800">{{ $user->creado_en?->format('d/m/Y') }}</dd>
                </div>
            </dl>
        </x-ui.card>


        <div class="space-y-6 lg:col-span-2">

            {{-- Datos --}}
            <x-ui.card>
                <h2 class="mb-1 text-base font-bold text-slate-800">Información personal</h2>
                <p class="mb-6 text-sm text-slate-500">Actualiza tu nombre y correo.</p>

                <form method="POST" action="{{ route('student.profile.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-5 md:grid-cols-3">
                        @foreach ([['nombre', 'Nombre'], ['apellido_paterno', 'Apellido paterno'], ['apellido_materno', 'Apellido materno']] as [$name, $text])
                            <div>
                                <label for="{{ $name }}" class="{{ $label }}">{{ $text }}</label>
                                <input id="{{ $name }}" name="{{ $name }}" type="text" value="{{ old($name, $user->$name) }}" required maxlength="100" class="{{ $campo }} @error($name) !border-[#DC2626] @enderror">
                                @error($name)<p class="mt-2 text-sm text-[#DC2626]">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <label for="email" class="{{ $label }}">Correo electrónico</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="150" class="{{ $campo }} @error('email') !border-[#DC2626] @enderror">
                        @error('email')<p class="mt-2 text-sm text-[#DC2626]">{{ $message }}</p>@enderror
                    </div>

                    <x-ui.button type="submit" icon="fa-solid fa-floppy-disk">Guardar cambios</x-ui.button>
                </form>
            </x-ui.card>


            {{-- Contraseña --}}
            <x-ui.card>
                <h2 class="mb-1 text-base font-bold text-slate-800">Cambiar contraseña</h2>
                <p class="mb-6 text-sm text-slate-500">Mínimo 8 caracteres, con letras y números.</p>

                <form method="POST" action="{{ route('student.profile.password') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="{{ $label }}">Contraseña actual</label>
                        <input id="current_password" name="current_password" type="password" autocomplete="current-password" required class="{{ $campo }} @error('current_password') !border-[#DC2626] @enderror">
                        @error('current_password')<p class="mt-2 text-sm text-[#DC2626]">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="password" class="{{ $label }}">Nueva contraseña</label>
                            <input id="password" name="password" type="password" autocomplete="new-password" required class="{{ $campo }} @error('password') !border-[#DC2626] @enderror">
                            @error('password')<p class="mt-2 text-sm text-[#DC2626]">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="{{ $label }}">Confirmar contraseña</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="{{ $campo }}">
                        </div>
                    </div>

                    <x-ui.button type="submit" icon="fa-solid fa-lock">Actualizar contraseña</x-ui.button>
                </form>
            </x-ui.card>

        </div>
    </div>

</x-student-layout>
