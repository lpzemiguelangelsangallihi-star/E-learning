<x-professor-layout
    title="Detalle de seguimiento IOP"
    description="Historial académico individual"
>
    <div class="space-y-6">

        <a
            href="{{ route('professor.iop.seguimientos') }}"
            class="inline-flex text-sm font-semibold
                   text-blue-700 hover:underline"
        >
            ← Volver a supervisión
        </a>

        {{-- DATOS GENERALES --}}

        <div class="rounded-lg border bg-white p-6">

            <h2 class="text-xl font-bold text-gray-900">
                {{ $seguimiento->estudiante?->nombre_completo
                    ?? 'Estudiante no disponible' }}
            </h2>

            <p class="mt-2 text-sm text-gray-600">
                Curso:
                {{ $seguimiento->leccion?->modulo?->curso?->titulo
                    ?? 'No disponible' }}
            </p>

            <p class="text-sm text-gray-600">
                Lección:
                {{ $seguimiento->leccion?->titulo
                    ?? 'No disponible' }}
            </p>

            <div class="mt-5 grid gap-3 sm:grid-cols-4">

                <div class="rounded-md bg-gray-50 p-4">
                    <p class="text-xs text-gray-500">Estado</p>
                    <p class="mt-1 text-xl font-bold">
                        {{ $seguimiento->estado_actual }}
                    </p>
                </div>

                <div class="rounded-md bg-gray-50 p-4">
                    <p class="text-xs text-gray-500">Ciclo</p>
                    <p class="mt-1 text-xl font-bold">
                        {{ $seguimiento->ciclo_actual }}
                    </p>
                </div>

                <div class="rounded-md bg-gray-50 p-4">
                    <p class="text-xs text-gray-500">
                        Nota inicial
                    </p>
                    <p class="mt-1 text-xl font-bold">
                        {{ $seguimiento->calificacion_inicial }}
                    </p>
                </div>

                <div class="rounded-md bg-gray-50 p-4">
                    <p class="text-xs text-gray-500">
                        Última nota
                    </p>
                    <p class="mt-1 text-xl font-bold">
                        {{ $seguimiento->ultima_calificacion ?? '-' }}
                    </p>
                </div>

            </div>

            @if ($seguimiento->estado_actual === 'S4')
                <div class="mt-5 rounded-md bg-green-50 p-4">
                    <p class="font-semibold text-green-900">
                        Aprendizaje consolidado
                    </p>
                    <p class="mt-1 text-sm text-green-800">
                        Fecha:
                        {{ $seguimiento->consolidado_en
                            ?->format('d/m/Y H:i') ?? '-' }}
                    </p>
                </div>
            @endif

        </div>

        {{-- HISTORIAL DE CICLOS --}}

        <div class="rounded-lg border bg-white p-6">

            <h2 class="text-lg font-bold text-gray-900">
                Historial de refuerzos adaptativos
            </h2>

            <div class="mt-5 space-y-4">

                @forelse (
                    $seguimiento->ciclosRefuerzo as $ciclo
                )

                    @php
                        $rec = $ciclo->recomendacion;
                        $asignacion = $rec?->asignacionRefuerzo;
                        $ejercicio = $asignacion?->ejercicio;

                        $transicion = $seguimiento
                            ->transiciones
                            ->firstWhere(
                                'ciclo',
                                (int) $ciclo->ciclo + 1
                            );
                    @endphp

                    <div class="rounded-lg border p-5">

                        <h3 class="font-semibold text-gray-900">
                            Ciclo {{ $ciclo->ciclo }}
                        </h3>

                        <div class="mt-3 grid gap-4 md:grid-cols-2">

                            <div>
                                <p class="text-sm text-gray-500">
                                    Recomendación Maximin
                                </p>

                                <p class="font-semibold">
                                    {{ ucfirst(
                                        $rec?->alternativa_recomendada
                                        ?? 'No disponible'
                                    ) }}
                                </p>

                                <p class="mt-1 text-sm">
                                    Nota de referencia:
                                    {{ $rec?->calificacion_inicial ?? '-' }}
                                </p>

                                <p class="text-sm">
                                    Valor Maximin:
                                    {{ $rec?->valor_maximin ?? '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">
                                    Actividad de refuerzo
                                </p>

                                <p class="font-semibold">
                                    {{ $ejercicio?->titulo
                                        ?? 'No disponible' }}
                                </p>

                                <p class="mt-1 text-sm">
                                    Estado:
                                    {{ ucfirst(str_replace(
                                        '_',
                                        ' ',
                                        $asignacion?->estado
                                        ?? 'sin asignación'
                                    )) }}
                                </p>

                                @if ($asignacion?->completado_en)
                                    <p class="text-sm text-gray-600">
                                        Aprobado:
                                        {{ $asignacion->completado_en
                                            ->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>

                        </div>

                        {{-- EVIDENCIA ACADÉMICA --}}

                        @if ($asignacion?->respuesta_estudiante)
                            <details class="mt-4 rounded-md
                                            border p-3">
                                <summary class="cursor-pointer
                                                font-medium">
                                    Ver respuesta del estudiante
                                </summary>

                                <p class="mt-3 whitespace-pre-line
                                          break-words text-sm">
                                    {{ $asignacion->respuesta_estudiante }}
                                </p>
                            </details>
                        @endif

                        @if ($asignacion?->retroalimentacion)
                            <div class="mt-3 rounded-md bg-gray-50 p-3">
                                <p class="text-sm font-semibold">
                                    Retroalimentación docente
                                </p>
                                <p class="mt-1 whitespace-pre-line
                                          text-sm">
                                    {{ $asignacion->retroalimentacion }}
                                </p>
                            </div>
                        @endif

                        {{-- RESULTADO DEL CICLO --}}

                        <div class="mt-4 border-t pt-4">

                            @if ($transicion)
                                <p class="text-sm font-semibold">
                                    Evaluación posterior
                                </p>

                                <p class="mt-1 text-sm">
                                    Calificación:
                                    {{ $transicion->calificacion_nueva }}
                                </p>

                                <p class="text-sm">
                                    Transición:
                                    {{ $transicion->estado_anterior }}
                                    →
                                    {{ $transicion->estado_siguiente }}
                                </p>
                            @else
                                <p class="text-sm text-gray-500">
                                    Sin evaluación posterior registrada.
                                </p>
                            @endif

                        </div>

                    </div>

                @empty
                    <p class="text-sm text-gray-500">
                        Este seguimiento todavía no tiene
                        ciclos de refuerzo registrados.
                    </p>
                @endforelse

            </div>

        </div>

    </div>
</x-professor-layout>
