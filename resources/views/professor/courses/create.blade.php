<x-professor-layout
    title="Crear curso"
    description="Registra un nuevo curso para tus estudiantes."
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


        <div
            class="
                mb-6 flex items-start gap-3
                rounded-lg
                border border-blue-200
                bg-blue-50
                px-4 py-3
                text-sm text-blue-700
            "
        >

            <i class="fa-solid fa-circle-info mt-0.5"></i>

            <div>
                <p class="font-semibold">
                    El curso se guardará como borrador.
                </p>

                <p class="mt-1">
                    Después podrás agregar contenido y publicarlo cuando esté listo.
                </p>
            </div>

        </div>


        @include('professor.courses._form', [
            'curso' => null,
            'materias' => $materias,
            'action' => route('professor.courses.store'),
            'method' => 'POST',
            'submitText' => 'Crear curso',
        ])

    </div>

</x-professor-layout>