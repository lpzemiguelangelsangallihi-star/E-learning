<x-student-layout
    title="Inscribirme con código"
    description="Ingresa el código que te dio tu profesor."
>

    <div class="mx-auto max-w-xl">

        <x-ui.card>

            <div class="mb-6 flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 text-[#2563EB]">
                    <i class="fa-solid fa-key" aria-hidden="true"></i>
                </div>

                <div>
                    <h2 class="text-base font-bold text-slate-800">Código del curso</h2>
                    <p class="mt-1 text-sm text-slate-500">Tiene 8 letras y números, por ejemplo <span class="font-mono">AB12CD34</span>.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('student.courses.enroll') }}" class="space-y-5" data-loading-form data-loading-text="Verificando...">
                @csrf

                <div>
                    <label for="codigo" class="mb-2 block text-sm font-semibold text-slate-700">Código</label>

                    <input
                        id="codigo"
                        name="codigo"
                        type="text"
                        value="{{ old('codigo') }}"
                        maxlength="20"
                        required
                        autofocus
                        autocomplete="off"
                        placeholder="Ej. AB12CD34"
                        @class([
                            'block w-full rounded-lg px-4 py-3 font-mono text-sm uppercase tracking-widest placeholder:normal-case placeholder:tracking-normal placeholder:text-slate-400',
                            'border-slate-300 focus:border-[#2563EB] focus:ring-[#2563EB]' => !$errors->has('codigo'),
                            'border-[#DC2626] focus:border-[#DC2626] focus:ring-[#DC2626]' => $errors->has('codigo'),
                        ])
                    >

                    @error('codigo')
                        <p class="mt-2 text-sm text-[#DC2626]">{{ $message }}</p>
                    @enderror
                </div>

                <x-ui.button type="submit" class="w-full" data-submit-button>
                    <i class="fa-solid fa-right-to-bracket" data-submit-icon aria-hidden="true"></i>
                    <span data-submit-text>Inscribirme</span>
                </x-ui.button>
            </form>

        </x-ui.card>

    </div>

</x-student-layout>
