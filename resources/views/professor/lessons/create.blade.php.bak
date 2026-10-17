<x-professor-layout
    title="Nuevo tema"
    description="Agrega un tema a la unidad."
>

    <div class="mx-auto max-w-4xl">

        <div class="mb-6">
            <a
                href="{{ route('professor.courses.structure', $curso) }}"
                class="inline-flex items-center gap-2 text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Volver al contenido del curso
            </a>
        </div>

        <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
            <p>
                <span class="font-semibold">Curso:</span> {{ $curso->titulo }}
                &nbsp;›&nbsp;
                <span class="font-semibold">Unidad:</span> {{ $modulo->titulo }}
            </p>
        </div>

        @include('professor.lessons._form', [
            'leccion' => null,
            'action' => route('professor.lessons.store', [$curso, $modulo]),
            'method' => 'POST',
            'cancelUrl' => route('professor.courses.structure', $curso),
            'submitText' => 'Crear tema',
        ])

    </div>

</x-professor-layout>
