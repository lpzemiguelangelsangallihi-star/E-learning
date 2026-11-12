<x-student-layout :title="$examen->titulo" description="Tus respuestas se guardan automáticamente.">

    <livewire:student.resolver-examen :intento-id="$intento->id" :key="'intento-'.$intento->id" />

</x-student-layout>
