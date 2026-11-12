@php
    $input = 'block w-full rounded-lg border-slate-300 px-4 py-2.5 text-sm text-slate-800 focus:border-[#2563EB] focus:ring-[#2563EB]';
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
@endphp

<x-professor-layout title="Entregas" :description="$tarea->titulo">

    <x-flash />

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <x-ui.button :href="route('professor.tasks.index')" variant="secondary" icon="fa-solid fa-arrow-left">Mis tareas</x-ui.button>

        <p class="text-sm text-slate-500">
            {{ $tarea->leccion?->modulo?->curso?->titulo }} · puntaje máximo {{ $fmt($tarea->puntaje_maximo) }}
            @if ($tarea->fecha_limite) · vence {{ $tarea->fecha_limite->format('d/m/Y H:i') }} @endif
        </p>
    </div>

    @if ($entregas->isEmpty())
        <x-ui.card>
            <x-ui.empty icon="fa-solid fa-inbox" title="Aún no hay entregas" text="Cuando tus estudiantes entreguen la tarea aparecerá aquí." />
        </x-ui.card>
    @else
        <div class="space-y-4">
            @foreach ($entregas as $entrega)
                @php
                    $tarde = $tarea->fecha_limite && $entrega->fecha_entrega->greaterThan($tarea->fecha_limite);
                @endphp

                <x-ui.card>
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-sm font-bold text-slate-800">{{ $entrega->estudiante?->nombre_completo }}</p>
                            <p class="text-xs text-slate-500">
                                Entregada el {{ $entrega->fecha_entrega->format('d/m/Y H:i') }}
                                @if ($tarde) · <span class="font-semibold text-[#DC2626]">con retraso</span> @endif
                            </p>
                        </div>

                        <x-ui.badge :type="$entrega->estado === 'calificada' ? 'success' : 'warning'">
                            {{ $entrega->estado === 'calificada' ? 'Calificada' : 'Por calificar' }}
                        </x-ui.badge>
                    </div>

                    @if ($entrega->respuesta)
                        <p class="mb-3 whitespace-pre-line rounded-lg bg-slate-50 p-3 text-sm text-slate-700">{{ $entrega->respuesta }}</p>
                    @endif

                    @if ($entrega->archivo)
                        <a href="{{ route('professor.tasks.download', [$tarea, $entrega]) }}" class="mb-3 inline-flex items-center gap-2 text-sm font-semibold text-[#2563EB] hover:underline">
                            <i class="fa-solid fa-paperclip" aria-hidden="true"></i> {{ basename($entrega->archivo) }}
                        </a>
                    @endif

                    <form method="POST" action="{{ route('professor.tasks.grade', [$tarea, $entrega]) }}" class="grid gap-3 md:grid-cols-[10rem_1fr_auto] md:items-end">
                        @csrf @method('PUT')

                        <div>
                            <label for="cal-{{ $entrega->id }}" class="mb-1 block text-xs font-medium text-slate-600">Calificación (0–{{ $fmt($tarea->puntaje_maximo) }})</label>
                            <input id="cal-{{ $entrega->id }}" type="number" name="calificacion" step="0.01" min="0" max="{{ $tarea->puntaje_maximo }}"
                                value="{{ old('calificacion', $entrega->calificacion !== null ? $fmt($entrega->calificacion) : '') }}" required class="{{ $input }}">
                        </div>

                        <div>
                            <label for="fb-{{ $entrega->id }}" class="mb-1 block text-xs font-medium text-slate-600">Retroalimentación</label>
                            <input id="fb-{{ $entrega->id }}" type="text" name="retroalimentacion" maxlength="2000"
                                value="{{ old('retroalimentacion', $entrega->retroalimentacion) }}" class="{{ $input }}">
                        </div>

                        <x-ui.button type="submit" icon="fa-solid fa-check">{{ $entrega->estado === 'calificada' ? 'Actualizar' : 'Calificar' }}</x-ui.button>
                    </form>
                </x-ui.card>
            @endforeach
        </div>

        @if ($errors->any())
            <x-ui.alert type="danger" class="mt-6">{{ $errors->first() }}</x-ui.alert>
        @endif
    @endif

</x-professor-layout>
