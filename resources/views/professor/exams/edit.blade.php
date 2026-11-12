<x-professor-layout title="Constructor de examen" :description="$examen->titulo">

    <x-flash />

    <div class="mb-6 flex flex-wrap gap-2">
        <x-ui.button :href="route('professor.exams.index')" variant="secondary" icon="fa-solid fa-arrow-left">Mis exámenes</x-ui.button>
        <x-ui.button :href="route('professor.reports.index', ['examen' => $examen->id, 'vista' => 'intentos'])" variant="secondary" icon="fa-solid fa-chart-column">
            Ver resultados
        </x-ui.button>
    </div>

    <livewire:professor.constructor-examen :examen-id="$examen->id" :key="'constructor-'.$examen->id" />

</x-professor-layout>
