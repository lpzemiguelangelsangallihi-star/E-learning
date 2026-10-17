<x-student-layout
    title="Recomendaciones"
    description="Cursos que podrían interesarte."
>

    <x-ui.alert type="info" class="mb-6">
        Para inscribirte en un curso necesitas su código. Pídeselo al profesor y úsalo en
        <a href="{{ route('student.courses.join') }}" class="font-semibold underline">Inscribirme con código</a>.
    </x-ui.alert>

    @if ($cursos->isEmpty())
        <x-ui.card>
            <x-ui.empty
                icon="fa-solid fa-lightbulb"
                title="Por ahora no tenemos recomendaciones"
                text="Cuando haya más cursos publicados los verás aquí."
            />
        </x-ui.card>
    @else
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($cursos as $curso)
                <x-student.course-card :curso="$curso" :motivo="$curso->motivo" />
            @endforeach
        </div>
    @endif

</x-student-layout>
