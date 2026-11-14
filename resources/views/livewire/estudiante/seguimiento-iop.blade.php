
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            Seguimiento del aprendizaje
        </h1>

        <p class="text-sm text-gray-500">
            Investigación Operativa II —
            Lección {{ $leccionId }}
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

        {{-- RESUMEN MAXIMIN Y MARKOV --}}

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

        @if ($seguimiento && $cicloRefuerzo)
            <div class="rounded-lg border border-blue-200
                        bg-blue-50 p-4">

                <p class="text-sm font-semibold text-blue-900">
                    Ciclo de refuerzo
                    {{ $cicloRefuerzo->ciclo }}
                </p>
            
                <p class="mt-1 text-sm text-blue-700">
                    @if ($cicloRefuerzo->ciclo === 0)
                        Actividad recomendada después
                        de tu diagnóstico inicial.
                    @else
                        Nueva actividad recomendada
                        según tu última evaluación.
                    @endif
                </p>
            
            </div>
        @endif

        {{-- EJERCICIO DE REFUERZO ASIGNADO --}}
            
        <div class="rounded-lg border bg-white p-5">
        
            <h2 class="mb-4 text-lg font-semibold">
                Ejercicio de refuerzo asignado
            </h2>
        
            @if ($asignacion && $ejercicio)
        
                <div class="rounded-lg bg-gray-50 p-5">
                
                    <div class="flex flex-wrap items-center justify-between gap-3">
                    
                        <h3 class="text-lg font-bold text-gray-900">
                            {{ $ejercicio->titulo }}
                        </h3>
                    
                        <span class="rounded-full bg-blue-100 px-3 py-1
                                     text-xs font-semibold text-blue-800">
                            {{ ucfirst(str_replace('_', ' ', $asignacion->estado)) }}
                        </span>
                    
                    </div>
                
                    {{-- TIPO DE REFUERZO --}}
                
                    <div class="mt-3">
                        <p class="text-sm text-gray-500">
                            Tipo de refuerzo
                        </p>
                    
                        <p class="font-medium">
                            {{ ucfirst($ejercicio->tipo) }}
                        </p>
                    </div>
                
                    {{-- DESCRIPCIÓN --}}
                
                    <div class="mt-4">
                        <p class="text-sm font-semibold">
                            Descripción
                        </p>
                    
                        <p class="mt-1 whitespace-pre-line text-sm text-gray-700">
                            {{ $ejercicio->descripcion }}
                        </p>
                    </div>
                
                    {{-- INSTRUCCIONES --}}
                
                    <div class="mt-4">
                        <p class="text-sm font-semibold">
                            Instrucciones
                        </p>
                    
                        <p class="mt-1 whitespace-pre-line text-sm text-gray-700">
                            {{ $ejercicio->instrucciones }}
                        </p>
                    </div>
                
                    {{-- RECURSO EDUCATIVO --}}
                
                    @if ($ejercicio->url_recurso)
                        <div class="mt-4">
                            <a
                                href="{{ $ejercicio->url_recurso }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex rounded-md bg-blue-600
                                       px-4 py-2 text-sm font-semibold
                                       text-white hover:bg-blue-700"
                            >
                                Abrir recurso educativo
                            </a>
                        </div>
                    @endif
                    
                    {{-- ENTREGA DEL EJERCICIO --}}
                    
                    <div class="mt-6 border-t pt-5">
                    
                        <h4 class="mb-3 text-base font-semibold text-gray-900">
                            Mi respuesta
                        </h4>
                    
                        {{-- MENSAJE DE ÉXITO --}}
                    
                        @if (session()->has('mensaje_refuerzo'))
                            <div class="mb-4 rounded-md bg-green-50 p-3
                                        text-sm text-green-800">
                                {{ session('mensaje_refuerzo') }}
                            </div>
                        @endif
                        
                        {{-- FORMULARIO DE ENTREGA --}}
                        
                        @if (in_array(
                            $asignacion->estado,
                            ['asignado', 'requiere_correccion'],
                            true
                        ))
        
                            {{-- RETROALIMENTACIÓN DEL PROFESOR --}}
                        
                            @if ($asignacion->estado === 'requiere_correccion')
                        
                                <div class="mb-4 rounded-md bg-amber-50 p-4">
                                
                                    <p class="font-semibold text-amber-900">
                                        El profesor solicitó una corrección
                                    </p>
                                
                                    <p class="mt-2 whitespace-pre-line
                                              text-sm text-amber-800">
                                        {{ $asignacion->retroalimentacion }}
                                    </p>
                                
                                </div>
                            
                                @if ($asignacion->respuesta_estudiante)
                                    <div class="mb-4">
                                        <p class="text-sm font-semibold text-gray-700">
                                            Respuesta anterior
                                        </p>
                                    
                                        <p class="mt-2 whitespace-pre-line
                                                  text-sm text-gray-600">
                                            {{ $asignacion->respuesta_estudiante }}
                                        </p>
                                    </div>
                                @endif
                                
                            @endif
                                
                            <form wire:submit="entregarRefuerzo"
                                  class="space-y-4">
                                
                                <div>
                                    <label
                                        for="respuesta-refuerzo"
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Escribe tu solución
                                    </label>
                                
                                    <textarea
                                        id="respuesta-refuerzo"
                                        wire:model="respuesta"
                                        rows="6"
                                        maxlength="10000"
                                        class="mt-2 block w-full rounded-md
                                               border-gray-300 shadow-sm
                                               focus:border-blue-500
                                               focus:ring-blue-500"
                                        placeholder="Explica cómo resolviste el ejercicio..."
                                    ></textarea>
                                
                                    @error('respuesta')
                                        <p class="mt-2 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                    
                                    @error('asignacion')
                                        <p class="mt-2 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                    
                                </div>
                            
                                <button
                                    type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="entregarRefuerzo"
                                    class="rounded-md bg-blue-600 px-5 py-2
                                           text-sm font-semibold text-white
                                           hover:bg-blue-700
                                           disabled:cursor-not-allowed
                                           disabled:opacity-50"
                                >
                                    <span
                                        wire:loading.remove
                                        wire:target="entregarRefuerzo"
                                    >
                                        {{ $asignacion->estado === 'requiere_correccion'
                                            ? 'Enviar corrección'
                                            : 'Enviar para revisión' }}
                                    </span>
                                
                                    <span
                                        wire:loading
                                        wire:target="entregarRefuerzo"
                                    >
                                        Enviando...
                                    </span>
                                </button>
                            
                            </form>
                        
                        {{-- EJERCICIO EN REVISIÓN --}}
                        
                        @elseif ($asignacion->estado === 'en_revision')
                        
                            <div class="rounded-md bg-blue-50 p-4">
                            
                                <p class="font-semibold text-blue-900">
                                    Tu ejercicio está en revisión
                                </p>
                            
                                <p class="mt-1 text-sm text-blue-800">
                                    Tu respuesta fue recibida y está pendiente
                                    de verificación por parte del profesor.
                                </p>
                            
                            </div>
                        
                            <div class="mt-4">
                            
                                <p class="text-sm font-semibold">
                                    Respuesta entregada
                                </p>
                            
                                <p class="mt-2 whitespace-pre-line text-sm
                                          text-gray-700">
                                    {{ $asignacion->respuesta_estudiante }}
                                </p>
                            
                                @if ($asignacion->entregado_en)
                                    <p class="mt-2 text-xs text-gray-500">
                                        Entregado el:
                                        {{ $asignacion->entregado_en->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                                
                            </div>
                        
                        {{-- EJERCICIO COMPLETADO --}}
                        
                        @elseif ($asignacion->estado === 'completado')
                        
                            <div class="rounded-md bg-green-50 p-4">
                            
                                <p class="font-semibold text-green-900">
                                    Ejercicio completado
                                </p>
                            
                                <p class="mt-1 text-sm text-green-800">
                                    Tu actividad fue verificada
                                    satisfactoriamente por el profesor.
                                </p>
                            
                                @if ($asignacion->retroalimentacion)
                            
                                    <div class="mt-3">
                                    
                                        <p class="text-sm font-semibold text-green-900">
                                            Retroalimentación del profesor
                                        </p>
                                    
                                        <p class="mt-1 whitespace-pre-line
                                                  text-sm text-green-800">
                                            {{ $asignacion->retroalimentacion }}
                                        </p>
                                    
                                    </div>
                                
                                @endif
                                
                                @if ($asignacion->completado_en)
                                    <p class="mt-3 text-xs text-green-800">
                                        Completado el:
                                        {{ $asignacion->completado_en->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                                
                            </div>
                        
                        @endif
                        
                    </div>
                
                    {{-- FECHA DE ASIGNACIÓN --}}
                
                    <div class="mt-5 border-t pt-3 text-xs text-gray-500">
                        Asignado el:
                        {{ $asignacion->asignado_en?->format('d/m/Y H:i')
                            ?? 'Fecha no disponible' }}
                    </div>
                
                </div>
            
            @elseif ($recomendacion)
            
                <div class="rounded-lg bg-yellow-50 p-4">
                
                    <p class="text-sm text-yellow-800">
                        Tienes una recomendación Maximin,
                        pero todavía no se ha registrado
                        un ejercicio de refuerzo para ella.
                    </p>
                
                </div>
            
            @else
            
                <p class="text-sm text-gray-500">
                    No existe una recomendación disponible.
                </p>
            
            @endif
            
        </div>


        {{-- HISTORIAL DE TRANSICIONES --}}

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
                        @forelse (
                            $seguimiento->transiciones as $transicion
                        )
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
                                    {{ ucfirst($transicion->origen) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"
                                    class="p-3 text-gray-500">
                                    No hay transiciones registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        {{-- PROYECCIÓN DE MARKOV --}}

        <div class="rounded-lg border bg-white p-5">

            <h2 class="text-lg font-semibold">
                Proyección de Markov
            </h2>

            <p class="mt-1 text-sm text-amber-700">
                Ejemplo matemático con matriz hipotética.
                No representa una predicción basada
                en evaluaciones reales.
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

                <div class="mt-5 grid grid-cols-2
                            gap-3 md:grid-cols-4">

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
