<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            Supervisión del aprendizaje IOP
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Consulta el avance de los estudiantes,
            sus recomendaciones y el estado de
            sus ciclos de refuerzo.
        </p>
    </div>

    {{-- INDICADORES GENERALES --}}

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

        @foreach ([
            'total' => 'Seguimientos',
            'en_proceso' => 'En proceso',
            'en_revision' => 'Con entregas en revisión',
            'consolidados' => 'Consolidados S4',
        ] as $clave => $etiqueta)

            <div class="rounded-lg border bg-white p-5">
                <p class="text-sm text-gray-500">
                    {{ $etiqueta }}
                </p>

                <p class="mt-2 text-3xl font-bold text-gray-900">
                    {{ $estadisticas[$clave] }}
                </p>
            </div>

        @endforeach

    </div>

    {{-- FILTROS --}}

    <div class="flex flex-col gap-4 rounded-lg
                border bg-white p-5 md:flex-row">

        <div class="flex-1">
            <label for="buscar-seguimiento-iop"
                   class="text-sm font-medium text-gray-700">
                Buscar estudiante o lección
            </label>

            <input
                id="buscar-seguimiento-iop"
                type="search"
                wire:model.live.debounce.400ms="busqueda"
                placeholder="Nombre, apellido o lección..."
                class="mt-2 w-full rounded-md border-gray-300"
            >
        </div>

        <div class="md:w-64">
            <label for="estado-seguimiento-iop"
                   class="text-sm font-medium text-gray-700">
                Filtrar por estado
            </label>

            <select
                id="estado-seguimiento-iop"
                wire:model.live="filtro"
                class="mt-2 w-full rounded-md border-gray-300"
            >
                <option value="todos">
                    Todos
                </option>

                <option value="activos">
                    En proceso
                </option>

                <option value="en_revision">
                    Con entregas en revisión
                </option>

                <option value="consolidados">
                    Consolidados S4
                </option>
            </select>
        </div>

    </div>

    {{-- TABLA DE SEGUIMIENTOS --}}

    <div class="overflow-hidden rounded-lg border bg-white">

        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="p-4">Estudiante</th>
                        <th class="p-4">Curso / Lección</th>
                        <th class="p-4">Estado Markov</th>
                        <th class="p-4">Ciclo</th>
                        <th class="p-4">Última nota</th>
                        <th class="p-4">Refuerzo</th>
                        <th class="p-4">Acción</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($seguimientos as $seguimiento)

                        @php
                            // Obtener únicamente el ciclo vigente.
                            $cicloVigente = $seguimiento
                                ->ciclosRefuerzo
                                ->firstWhere(
                                    'ciclo',
                                    (int) $seguimiento->ciclo_actual
                                );

                            $recomendacion = $cicloVigente
                                ?->recomendacion;

                            $asignacion = $recomendacion
                                ?->asignacionRefuerzo;

                            $estadoRefuerzo = (
                                $seguimiento->estado_actual === 'S4'
                            )
                                ? 'Consolidado'
                                : ucfirst(str_replace(
                                    '_',
                                    ' ',
                                    $asignacion?->estado
                                        ?? 'sin asignación'
                                ));
                        @endphp

                        <tr wire:key="seguimiento-iop-{{ $seguimiento->id }}"
                            class="border-t">

                            <td class="p-4 font-medium text-gray-900">
                                {{ $seguimiento->estudiante
                                    ?->nombre_completo
                                    ?? 'No disponible' }}
                            </td>

                            <td class="p-4">
                                <p class="font-medium">
                                    {{ $seguimiento->leccion
                                        ?->modulo
                                        ?->curso
                                        ?->titulo ?? '-' }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $seguimiento->leccion
                                        ?->titulo ?? '-' }}
                                </p>
                            </td>

                            <td class="p-4">
                                <span class="rounded-full bg-blue-100
                                             px-3 py-1 text-xs
                                             font-semibold text-blue-800">
                                    {{ $seguimiento->estado_actual }}
                                </span>
                            </td>

                            <td class="p-4">
                                {{ $seguimiento->ciclo_actual }}
                            </td>

                            <td class="p-4">
                                {{ $seguimiento->ultima_calificacion
                                    ?? '-' }}
                            </td>

                            <td class="p-4">
                                <p class="font-medium">
                                    {{ $estadoRefuerzo }}
                                </p>

                                @if ($recomendacion)
                                    <p class="mt-1 text-xs text-gray-500">
                                        Maximin:
                                        {{ ucfirst(
                                            $recomendacion
                                                ->alternativa_recomendada
                                        ) }}
                                    </p>
                                @endif
                            </td>

                            <td class="p-4">
                                <a
                                    href="{{ route('professor.iop.refuerzos') }}"
                                    class="font-semibold text-blue-700
                                           hover:underline"
                                >
                                    Revisar refuerzos
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7"
                                class="p-6 text-center text-gray-500">
                                No hay seguimientos reales
                                que coincidan con los filtros.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="border-t p-4">
            {{ $seguimientos->links() }}
        </div>

    </div>

</div>
