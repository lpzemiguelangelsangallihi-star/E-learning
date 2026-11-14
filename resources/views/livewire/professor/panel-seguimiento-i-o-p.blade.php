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

    <div class="flex flex-wrap items-center gap-3">
    
        <a
            href="{{ route('professor.iop.reportes.exportar') }}"
            class="inline-flex items-center gap-2
                   rounded-lg bg-blue-600 px-4 py-2.5
                   text-sm font-semibold text-white
                   hover:bg-blue-700"
        >
            <i class="fa-solid fa-file-csv"
               aria-hidden="true"></i>
    
            Exportar reporte IOP
        </a>
    
        <a
            href="{{ route('professor.iop.refuerzos') }}"
            class="inline-flex items-center gap-2
                   rounded-lg border border-gray-300
                   bg-white px-4 py-2.5 text-sm
                   font-semibold text-gray-700
                   hover:bg-gray-50"
        >
            Revisar actividades
        </a>
    
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

    {{-- ALERTAS ACADÉMICAS IOP --}}

    @if (
        $estadisticas['correcciones'] > 0 ||
        $estadisticas['sin_asignacion'] > 0
    )
        <div class="rounded-lg border border-amber-200
                    bg-amber-50 p-5">

            <h2 class="font-semibold text-amber-900">
                Situaciones que requieren atención
            </h2>

            <div class="mt-3 space-y-2 text-sm
                        text-amber-900">

                @if ($estadisticas['correcciones'] > 0)
                    <p>
                        {{ $estadisticas['correcciones'] }}
                        seguimiento(s) tienen una actividad
                        del ciclo vigente que requiere corrección.
                    </p>
                @endif

                @if ($estadisticas['sin_asignacion'] > 0)
                    <p>
                        {{ $estadisticas['sin_asignacion'] }}
                        seguimiento(s) activos no tienen
                        una actividad asignada al ciclo vigente.
                    </p>
                @endif

            </div>

            <p class="mt-3 text-xs text-amber-800">
                Estas alertas son informativas.
                No modifican automáticamente los seguimientos.
            </p>

        </div>
    @endif


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
                                <div class="flex flex-col gap-2">
                                
                                    <a
                                        href="{{ route(
                                            'professor.iop.seguimientos.detalle',
                                            ['seguimientoId' => $seguimiento->id]
                                        ) }}"
                                        class="font-semibold text-blue-700
                                               hover:underline"
                                    >
                                        Ver progreso
                                    </a>
                                
                                    <a
                                        href="{{ route('professor.iop.refuerzos') }}"
                                        class="text-sm text-gray-600
                                               hover:underline"
                                    >
                                        Revisar refuerzos
                                    </a>
                                
                                </div>
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
