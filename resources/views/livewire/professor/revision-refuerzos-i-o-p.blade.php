
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            Revisión de refuerzos IOP
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Revisa las actividades de refuerzo
            de los estudiantes de tus cursos.
        </p>
    </div>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('professor.iop.seguimientos') }}"
           class="inline-flex rounded-md bg-blue-600
                  px-4 py-2 text-sm font-semibold text-white
                  hover:bg-blue-700">
            Supervisar progreso IOP
        </a>
    </div>

    {{-- MENSAJES DE CONFIRMACIÓN --}}

    @if (session()->has('mensaje_revision'))
        <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">
            {{ session('mensaje_revision') }}
        </div>
    @endif

    {{-- FILTRO --}}

    <div class="rounded-lg border bg-white p-4">
        <label for="filtro-refuerzos"
               class="block text-sm font-medium text-gray-700">
            Filtrar por estado
        </label>

        <select
            id="filtro-refuerzos"
            wire:model.live="filtro"
            class="mt-2 w-full rounded-md border-gray-300
                   md:w-64"
        >
            <option value="en_revision">Pendientes de revisión</option>
            <option value="completado">Completados</option>
            <option value="requiere_correccion">Requieren corrección</option>
            <option value="todos">Todos</option>
        </select>
    </div>

    {{-- LISTADO DE ENTREGAS --}}

    <div class="overflow-hidden rounded-lg border bg-white">

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">

                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="p-4">Estudiante</th>
                        <th class="p-4">Curso</th>
                        <th class="p-4">Ejercicio</th>
                        <th class="p-4">Estado</th>
                        <th class="p-4">Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($asignaciones as $asignacion)

                        <tr wire:key="refuerzo-{{ $asignacion->id }}"
                            class="border-t">

                            <td class="p-4">
                                {{ $asignacion->recomendacion
                                    ?->estudiante
                                    ?->nombre_completo ?? 'No disponible' }}
                            </td>

                            <td class="p-4">
                                {{ $asignacion->recomendacion
                                    ?->leccion
                                    ?->modulo
                                    ?->curso
                                    ?->titulo ?? 'No disponible' }}
                            </td>

                            <td class="p-4">
                                {{ $asignacion->ejercicio?->titulo
                                    ?? 'No disponible' }}
                            </td>

                            <td class="p-4">
                                {{ ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $asignacion->estado
                                    )
                                ) }}
                            </td>

                            <td class="p-4">
                                <button
                                    type="button"
                                    wire:click="verDetalle({{ $asignacion->id }})"
                                    class="font-semibold text-blue-700
                                           hover:underline"
                                >
                                    Ver detalle
                                </button>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5"
                                class="p-6 text-center text-gray-500">
                                No se encontraron refuerzos
                                con el filtro seleccionado.
                            </td>
                        </tr>

                    @endforelse
                </tbody>

            </table>
        </div>

        <div class="border-t p-4">
            {{ $asignaciones->links() }}
        </div>

    </div>

    {{-- DETALLE DE LA ENTREGA --}}

    @if ($detalle)

        <div class="rounded-lg border bg-white p-6">

            <div class="flex items-start justify-between gap-4">

                <div>
                    <h2 class="text-xl font-bold text-gray-900">
                        Detalle del refuerzo
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        {{ $detalle->recomendacion
                            ?->estudiante
                            ?->nombre_completo }}
                    </p>
                </div>

                <button
                    type="button"
                    wire:click="cerrarDetalle"
                    class="text-sm text-gray-500 hover:text-gray-900"
                >
                    Cerrar
                </button>

            </div>

            <div class="mt-5 space-y-4">

                <div>
                    <p class="text-sm font-semibold">
                        Ejercicio
                    </p>

                    <p class="mt-1 text-gray-700">
                        {{ $detalle->ejercicio?->titulo }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-semibold">
                        Instrucciones
                    </p>

                    <p class="mt-1 whitespace-pre-line
                              text-sm text-gray-700">
                        {{ $detalle->ejercicio?->instrucciones }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-semibold">
                        Respuesta del estudiante
                    </p>

                    <div class="mt-2 rounded-md bg-gray-50 p-4">
                        <p class="whitespace-pre-line text-sm text-gray-700">
                            {{ $detalle->respuesta_estudiante
                                ?: 'Sin respuesta registrada.' }}
                        </p>
                    </div>
                </div>

                @if ($detalle->entregado_en)
                    <p class="text-xs text-gray-500">
                        Entregado el:
                        {{ $detalle->entregado_en->format('d/m/Y H:i') }}
                    </p>
                @endif

                {{-- FORMULARIO DE REVISIÓN --}}

                @if ($detalle->estado === 'en_revision')

                    <div class="border-t pt-5">

                        <h3 class="mb-3 text-lg font-semibold">
                            Verificación docente
                        </h3>

                        <label
                            for="retroalimentacion-iop"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Retroalimentación
                        </label>

                        <textarea
                            id="retroalimentacion-iop"
                            wire:model="retroalimentacion"
                            rows="4"
                            maxlength="5000"
                            class="mt-2 block w-full rounded-md
                                   border-gray-300"
                            placeholder="Escribe observaciones para el estudiante..."
                        ></textarea>

                        @error('retroalimentacion')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                        @error('asignacion')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                        <div class="mt-4 flex flex-wrap gap-3">

                            <button
                                type="button"
                                wire:click="aprobar"
                                wire:loading.attr="disabled"
                                wire:target="aprobar,solicitarCorreccion"
                                class="rounded-md bg-green-700 px-4 py-2
                                       text-sm font-semibold text-white
                                       hover:bg-green-800
                                       disabled:opacity-50"
                            >
                                Aprobar ejercicio
                            </button>

                            <button
                                type="button"
                                wire:click="solicitarCorreccion"
                                wire:loading.attr="disabled"
                                wire:target="aprobar,solicitarCorreccion"
                                class="rounded-md bg-amber-600 px-4 py-2
                                       text-sm font-semibold text-white
                                       hover:bg-amber-700
                                       disabled:opacity-50"
                            >
                                Solicitar corrección
                            </button>

                        </div>

                    </div>

                @else

                    <div class="border-t pt-4">

                        <p class="text-sm font-semibold">
                            Estado actual:
                            {{ ucfirst(
                                str_replace('_', ' ', $detalle->estado)
                            ) }}
                        </p>

                        @if ($detalle->retroalimentacion)
                            <p class="mt-3 text-sm font-semibold">
                                Retroalimentación
                            </p>

                            <p class="mt-1 whitespace-pre-line
                                      text-sm text-gray-700">
                                {{ $detalle->retroalimentacion }}
                            </p>
                        @endif

                    </div>

                @endif

            </div>

        </div>

    @endif

</div>
