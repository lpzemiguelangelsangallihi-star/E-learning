@php
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
    $maximo = (float) $examen->puntaje_maximo;
    $porcentaje = $maximo > 0 ? ((float) $intento->calificacion / $maximo) * 100 : 0;
    $aprobado = $porcentaje >= \App\Models\Calificacion::NOTA_APROBACION;
    $segundos = $intento->fecha_fin ? (int) abs($intento->fecha_inicio->diffInSeconds($intento->fecha_fin)) : null;
@endphp

<x-student-layout :title="'Resultado: '.$examen->titulo" description="Tu nota ya quedó registrada.">

    <x-flash />

    <div class="mb-6 flex flex-wrap gap-2">
        <x-ui.button :href="route('student.evaluations.exam.show', $examen)" variant="secondary" icon="fa-solid fa-arrow-left">Volver al examen</x-ui.button>
        <x-ui.button :href="route('student.evaluations.history')" variant="secondary" icon="fa-solid fa-clock-rotate-left">Historial</x-ui.button>
    </div>

    <x-ui.card class="mb-6">
        <div class="flex flex-col items-center gap-2 py-4 text-center">
            <x-ui.badge :type="$aprobado ? 'success' : 'danger'">{{ $aprobado ? 'Aprobado' : 'No aprobado' }}</x-ui.badge>

            <p class="text-5xl font-bold tracking-tight {{ $aprobado ? 'text-[#16A34A]' : 'text-[#DC2626]' }}">
                {{ $fmt($intento->calificacion) }}<span class="text-2xl text-slate-400"> / {{ $fmt($maximo) }}</span>
            </p>

            <p class="text-sm text-slate-500">
                Intento {{ $intento->numero_intento }}
                @if ($segundos !== null) · tiempo usado {{ floor($segundos / 60) }} min {{ $segundos % 60 }} s @endif
            </p>

            @if ($intentosRestantes > 0)
                <p class="text-sm text-slate-500">Te quedan {{ $intentosRestantes }} intento(s). Se conserva tu mejor nota.</p>
            @endif
        </div>
    </x-ui.card>

    @if ($mostrarCorrectas)
        <div class="space-y-4">
            @foreach ($intento->examen->preguntas()->orderBy('orden')->orderBy('id')->with('opciones')->get() as $n => $pregunta)
                @php
                    $filas = $porPregunta->get($pregunta->id, collect());
                    $obtenido = (float) $filas->sum('puntaje_obtenido');
                    $completa = $obtenido >= (float) $pregunta->puntaje - 0.0001;
                    $elegidas = $filas->pluck('opcion_id')->filter()->all();
                    $texto = $filas->first()?->respuesta_texto;
                @endphp

                <x-ui.card>
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                        <span class="text-xs font-bold text-slate-400">Pregunta {{ $n + 1 }}</span>
                        <x-ui.badge :type="$completa ? 'success' : ($obtenido > 0 ? 'warning' : 'danger')">
                            {{ $fmt($obtenido) }} / {{ $fmt($pregunta->puntaje) }} pts
                        </x-ui.badge>
                    </div>

                    <p class="mb-3 whitespace-pre-line text-sm font-semibold text-slate-800">{{ $pregunta->pregunta }}</p>

                    @if ($pregunta->tipo === 'respuesta_corta')
                        <p class="text-sm {{ $completa ? 'text-[#16A34A]' : 'text-[#DC2626]' }}">
                            Tu respuesta: {{ $texto !== null && $texto !== '' ? '“'.$texto.'”' : 'sin responder' }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            Respuesta(s) aceptada(s): {{ $pregunta->opciones->where('es_correcta', true)->pluck('opcion')->implode(', ') }}
                        </p>
                    @else
                        <ul class="space-y-1 text-sm">
                            @foreach ($pregunta->opciones as $opcion)
                                @php($elegida = in_array($opcion->id, $elegidas))
                                <li @class([
                                    'flex items-center gap-2 rounded-lg px-3 py-2',
                                    'bg-green-50 text-green-800' => $opcion->es_correcta,
                                    'bg-red-50 text-red-800' => $elegida && ! $opcion->es_correcta,
                                    'text-slate-600' => ! $opcion->es_correcta && ! $elegida,
                                ])>
                                    <i class="fa-solid {{ $opcion->es_correcta ? 'fa-circle-check' : ($elegida ? 'fa-circle-xmark' : 'fa-circle') }} text-xs" aria-hidden="true"></i>
                                    {{ $opcion->opcion }}
                                    @if ($elegida) <span class="ml-auto text-xs font-semibold">Tu respuesta</span> @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card>
            @endforeach
        </div>
    @else
        <x-ui.alert type="info">Tu profesor decidió no mostrar las respuestas correctas de este examen.</x-ui.alert>
    @endif

</x-student-layout>
