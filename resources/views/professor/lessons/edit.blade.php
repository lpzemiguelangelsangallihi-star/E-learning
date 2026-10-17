<x-professor-layout
    title="Editar tema"
    description="Actualiza el contenido y estado del tema."
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

        <div class="mb-6">
            <a
                href="{{ route('professor.materials.index', [$curso, $modulo, $leccion]) }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
                <i class="fa-solid fa-photo-film"></i>
                Videos y materiales de este tema
            </a>
        </div>

        @include('professor.lessons._form', [
            'leccion' => $leccion,
            'action' => route('professor.lessons.update', [$curso, $modulo, $leccion]),
            'method' => 'PUT',
            'cancelUrl' => route('professor.courses.structure', $curso),
            'submitText' => 'Guardar cambios',
        ])

    </div>

</x-professor-layout>
