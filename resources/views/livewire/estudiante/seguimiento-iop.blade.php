
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            Seguimiento del aprendizaje
        </h1>

        <p class="text-sm text-gray-500">
            Investigación Operativa II — Lección {{ $leccionId }}
        </p>
    </div>

    @if (!$seguimiento)

        <div class="rounded-lg border bg-white p-6">
            <p class="text-gray-600">
                Todavía no tienes un seguimiento
                registrado para esta lección.
            </p>
        </div>

    @else

        <div class="grid gap-4 md:grid-cols-2">

            <div class="rounded-lg border bg-white p-5">
                <p class="text-sm text-gray-500">
                    Recomendación Maximin
                </p>

                <h2 class="mt-2 text-xl font-bold">
                    {{ ucfirst(
                        $recomendacion?->alternativa_recomendada
                        ?? 'No disponible'
                    ) }}
                </h2>

                <p class="mt-2 text-sm">
                    Valor Maximin:
                    {{ $recomendacion?->valor_maximin ?? '-' }}
                </p>
            </div>

            <div class="rounded-lg border bg-white p-5">
                <p class="text-sm text-gray-500">
                    Estado actual
                </p>

                <h2 class="mt-2 text-2xl font-bold">
                    {{ $seguimiento->estado_actual }}
                </h2>

                <p class="mt-2 text-sm">
                    Última calificación:
                    {{ $seguimiento->ultima_calificacion }}
                </p>

                <p class="text-sm">
                    Ciclo actual:
                    {{ $seguimiento->ciclo_actual }}
                </p>
            </div>

        </div>

        <div class="rounded-lg border bg-white p-5">
            <h2 class="mb-4 text-lg font-semibold">
                Historial de transiciones
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="p-2">Ciclo</th>
                            <th class="p-2">Anterior</th>
                            <th class="p-2">Siguiente</th>
                            <th class="p-2">Nota</th>
                            <th class="p-2">Origen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($seguimiento->transiciones as $transicion)
                            <tr class="border-b">
                                <td class="p-2">
                                    {{ $transicion->ciclo }}
                                </td>
                                <td class="p-2">
                                    {{ $transicion->estado_anterior }}
                                </td>
                                <td class="p-2">
                                    {{ $transicion->estado_siguiente }}
                                </td>
                                <td class="p-2">
                                    {{ $transicion->calificacion_nueva }}
                                </td>
                                <td class="p-2">
                                    {{ $transicion->origen }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-3 text-gray-500">
                                    No hay transiciones registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-lg border bg-white p-5">

            <h2 class="text-lg font-semibold">
                Proyección de Markov
            </h2>

            <p class="mt-1 text-sm text-amber-700">
                Ejemplo matemático con matriz hipotética.
                No es una predicción basada en datos reales.
            </p>

            <div class="mt-4">
                <label class="text-sm font-medium">
                    Ciclos adicionales
                </label>

                <select
                    wire:model.live="ciclos"
                    class="ml-3 rounded-md border-gray-300"
                >
                    @for ($n = 1; $n <= 10; $n++)
                        <option value="{{ $n }}">
                            {{ $n }}
                        </option>
                    @endfor
                </select>
            </div>

            @if ($proyeccion)
                <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-4">

                    @foreach ($proyeccion as $indice => $probabilidad)
                        <div class="rounded-md bg-gray-100 p-4 text-center">
                            <p class="text-sm text-gray-500">
                                S{{ $indice + 1 }}
                            </p>

                            <p class="text-xl font-bold">
                                {{ number_format(
                                    $probabilidad * 100,
                                    2
                                ) }}%
                            </p>
                        </div>
                    @endforeach

                </div>
            @endif

            <div class="mt-5 rounded-md bg-gray-50 p-3 text-sm">
                <p>
                    Matriz real:
                    @if ($matrizReal && $matrizReal->completa)
                        Disponible
                    @else
                        No disponible o incompleta
                    @endif
                </p>

                <p class="mt-1 text-gray-600">
                    Las estimaciones reales se habilitarán
                    cuando existan suficientes transiciones
                    académicas verificadas.
                </p>
            </div>

        </div>

    @endif

</div>
