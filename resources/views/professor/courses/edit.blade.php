<x-professor-layout
    title="Editar curso"
    description="Actualiza la información y estado del curso."
>

    <div class="mx-auto max-w-4xl">

        <div class="mb-6">

            <a
                href="{{ route('professor.courses.index') }}"
                class="
                    inline-flex items-center gap-2
                    text-sm font-semibold
                    text-[#2563EB]
                    hover:text-[#1D4ED8]
                "
            >
                <i class="fa-solid fa-arrow-left"></i>

                Volver a mis cursos
            </a>

        </div>


        <x-ui.card class="mb-6">

            <div
                class="
                    flex flex-col gap-4
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div class="flex items-center gap-4">

                    <div
                        class="
                            flex h-12 w-12
                            shrink-0
                            items-center justify-center
                            rounded-lg
                            bg-blue-50
                            text-[#2563EB]
                        "
                    >
                        <i class="fa-solid fa-book-open"></i>
                    </div>


                    <div>

                        <h2 class="font-bold text-slate-800">
                            {{ $curso->titulo }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $curso->materia?->nombre ?? 'Sin materia' }}
                        </p>

                    </div>

                </div>


                @php
                    $statusType = match ($curso->estado) {
                        'publicado' => 'success',
                        'borrador' => 'warning',
                        'oculto' => 'danger',
                        default => 'primary',
                    };
                @endphp


                <x-ui.badge :type="$statusType">
                    {{ ucfirst($curso->estado) }}
                </x-ui.badge>

            </div>

        </x-ui.card>


        @include('professor.courses._form', [
            'curso' => $curso,
            'materias' => $materias,
            'action' => route(
                'professor.courses.update',
                $curso
            ),
            'method' => 'PUT',
            'submitText' => 'Guardar cambios',
        ])

    </div>

</x-professor-layout>