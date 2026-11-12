@php
    $input = 'block w-full rounded-lg border-slate-300 px-4 py-2.5 text-sm text-slate-800 focus:border-[#2563EB] focus:ring-[#2563EB]';
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
    $vencida = $tarea->fecha_limite?->isPast() ?? false;
@endphp

<x-student-layout :title="$tarea->titulo" description="Lee las instrucciones y entrega tu trabajo.">

    <x-flash />

    <div class="mb-6">
        <x-ui.button :href="route('student.evaluations.index')" variant="secondary" icon="fa-solid fa-arrow-left">Evaluaciones</x-ui.button>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        <x-ui.card class="lg:col-span-2">
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge type="warning">Tarea</x-ui.badge>
                @if ($tarea->estado === 'cerrada') <x-ui.badge type="danger">Cerrada</x-ui.badge> @endif
            </div>

            <h2 class="mt-2 text-xl font-bold text-slate-800">{{ $tarea->titulo }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $tarea->leccion?->modulo?->curso?->titulo }} · {{ $tarea->leccion?->titulo }}</p>

            <div class="mt-4 space-y-1 text-xs text-slate-500">
                @if ($tarea->fecha_limite)
                    <p @class(['font-medium text-[#DC2626]' => $vencida])>
                        <i class="fa-regular fa-calendar mr-1" aria-hidden="true"></i>Vence: {{ $tarea->fecha_limite->format('d/m/Y H:i') }}
                        @if ($vencida) (vencida: aún puedes entregar, quedará marcada como tardía) @endif
                    </p>
                @endif
                <p><i class="fa-solid fa-trophy mr-1" aria-hidden="true"></i>Puntaje máximo: {{ $fmt($tarea->puntaje_maximo) }}</p>
            </div>

            @if ($tarea->instrucciones)
                <div class="mt-5 whitespace-pre-line rounded-lg bg-slate-50 p-4 text-sm text-slate-700">{{ $tarea->instrucciones }}</div>
            @endif

            @if ($tarea->archivo)
                <a href="{{ asset('storage/'.$tarea->archivo) }}" target="_blank" rel="noopener"
                    class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-[#2563EB] hover:underline">
                    <i class="fa-solid fa-paperclip" aria-hidden="true"></i> Archivo de apoyo
                </a>
            @endif
        </x-ui.card>

        <div class="space-y-6">

            @if ($entrega)
                <x-ui.card>
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <h3 class="text-base font-bold text-slate-800">Tu entrega</h3>
                        <x-ui.badge :type="$entrega->estado === 'calificada' ? 'success' : ($entrega->estado === 'tarde' ? 'danger' : 'primary')">
                            {{ match ($entrega->estado) { 'calificada' => 'Calificada', 'tarde' => 'Tardía', default => 'Entregada' } }}
                        </x-ui.badge>
                    </div>

                    <p class="text-xs text-slate-500">{{ $entrega->fecha_entrega->format('d/m/Y H:i') }}</p>

                    @if ($entrega->archivo)
                        <p class="mt-2 text-sm text-slate-700"><i class="fa-solid fa-paperclip mr-1" aria-hidden="true"></i>{{ basename($entrega->archivo) }}</p>
                    @endif

                    @if ($entrega->respuesta)
                        <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $entrega->respuesta }}</p>
                    @endif

                    @if ($entrega->estado === 'calificada')
                        <div class="mt-4 rounded-lg bg-green-50 p-3">
                            <p class="text-sm text-green-800">Nota: <strong>{{ $fmt($entrega->calificacion) }}</strong> / {{ $fmt($tarea->puntaje_maximo) }}</p>
                            @if ($entrega->retroalimentacion)
                                <p class="mt-1 text-sm text-green-800">{{ $entrega->retroalimentacion }}</p>
                            @endif
                        </div>
                    @endif
                </x-ui.card>
            @endif

            @if ($puedeEntregar)
                <x-ui.card>
                    <h3 class="mb-4 text-base font-bold text-slate-800">{{ $entrega ? 'Actualizar entrega' : 'Entregar tarea' }}</h3>

                    <form method="POST" action="{{ route('student.evaluations.task.submit', $tarea) }}" enctype="multipart/form-data"
                        class="space-y-4" data-loading-form data-loading-text="Enviando...">
                        @csrf

                        <div>
                            <label for="respuesta" class="mb-1 block text-sm font-medium text-slate-700">Respuesta</label>
                            <textarea id="respuesta" name="respuesta" rows="5" maxlength="5000" class="{{ $input }}">{{ old('respuesta', $entrega?->respuesta) }}</textarea>
                            @error('respuesta') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="archivo" class="mb-1 block text-sm font-medium text-slate-700">Archivo (máx. 10 MB)</label>
                            <input id="archivo" type="file" name="archivo" class="{{ $input }}">
                            @error('archivo') <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
                        </div>

                        <x-ui.button type="submit" class="w-full" icon="fa-solid fa-paper-plane">
                            {{ $entrega ? 'Guardar cambios' : 'Entregar' }}
                        </x-ui.button>
                    </form>
                </x-ui.card>
            @elseif (! $entrega)
                <x-ui.alert type="info">Esta tarea está cerrada y ya no recibe entregas.</x-ui.alert>
            @endif
        </div>
    </div>

</x-student-layout>
