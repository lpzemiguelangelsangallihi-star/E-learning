@php
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
    $curso = $examen->leccion?->modulo?->curso;
@endphp

<x-student-layout :title="$examen->titulo" description="Revisa las reglas antes de comenzar.">

    <x-flash />

    <div class="mb-6">
        <x-ui.button :href="route('student.evaluations.index')" variant="secondary" icon="fa-solid fa-arrow-left">Evaluaciones</x-ui.button>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        <x-ui.card class="lg:col-span-2">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#2563EB]">Examen</p>
            <h2 class="mt-1 text-xl font-bold text-slate-800">{{ $examen->titulo }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $curso?->titulo }} · {{ $examen->leccion?->titulo }}</p>

            @if ($examen->descripcion)
                <p class="mt-4 whitespace-pre-line text-sm text-slate-700">{{ $examen->descripcion }}</p>
            @endif

            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-lg bg-slate-50 p-4">
                    <dt class="text-xs text-slate-500">Preguntas</dt>
                    <dd class="text-lg font-bold text-slate-800">{{ $totalPreguntas }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 p-4">
                    <dt class="text-xs text-slate-500">Tiempo límite</dt>
                    <dd class="text-lg font-bold text-slate-800">{{ $examen->tiempo_limite ? $examen->tiempo_limite.' min' : 'Sin límite' }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 p-4">
                    <dt class="text-xs text-slate-500">Intentos usados</dt>
                    <dd class="text-lg font-bold text-slate-800">{{ $usados }} / {{ $examen->intentos_permitidos }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 p-4">
                    <dt class="text-xs text-slate-500">Nota máxima</dt>
                    <dd class="text-lg font-bold text-slate-800">{{ $fmt($examen->puntaje_maximo) }}</dd>
                </div>
            </dl>

            @if ($examen->tiempo_limite)
                <x-ui.alert type="warning" class="mt-6">
                    El tiempo empieza a correr al comenzar y no se detiene aunque salgas de la página.
                    Al terminar el tiempo, el examen se entrega automáticamente con lo que hayas respondido.
                </x-ui.alert>
            @endif

            <div class="mt-6">
                @if ($puedeIniciar && $totalPreguntas > 0)
                    <form method="POST" action="{{ route('student.evaluations.exam.start', $examen) }}" data-loading-form data-loading-text="Preparando...">
                        @csrf
                        <x-ui.button type="submit" icon="fa-solid fa-play">
                            {{ $abierto ? 'Continuar intento' : 'Comenzar examen' }}
                        </x-ui.button>
                    </form>
                @elseif ($totalPreguntas === 0)
                    <x-ui.alert type="info">Este examen todavía no tiene preguntas.</x-ui.alert>
                @elseif ($examen->estado !== 'publicado')
                    <x-ui.alert type="info">Este examen está cerrado y ya no recibe nuevos intentos. Puedes consultar tus resultados.</x-ui.alert>
                @else
                    <x-ui.alert type="info">Ya usaste todos tus intentos para este examen.</x-ui.alert>
                @endif
            </div>
        </x-ui.card>

        <x-ui.card>
            <h3 class="mb-4 text-base font-bold text-slate-800">Mis intentos</h3>

            @if ($historial->isEmpty())
                <p class="text-sm text-slate-500">Todavía no has realizado este examen.</p>
            @else
                <ul class="space-y-3">
                    @foreach ($historial as $i)
                        <li class="rounded-lg border border-slate-200 p-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-semibold text-slate-800">Intento {{ $i->numero_intento }}</span>
                                <x-ui.badge :type="$i->estado === 'finalizado' ? 'success' : 'warning'">
                                    {{ $i->estado === 'finalizado' ? 'Entregado' : 'En proceso' }}
                                </x-ui.badge>
                            </div>

                            @if ($i->estado === 'finalizado')
                                <p class="mt-1 text-sm text-slate-600">Nota: <strong>{{ $fmt($i->calificacion) }}</strong> / {{ $fmt($examen->puntaje_maximo) }}</p>
                                <a href="{{ route('student.evaluations.exam.result', $i) }}" class="text-xs font-semibold text-[#2563EB] hover:underline">Ver resultado</a>
                            @else
                                <a href="{{ route('student.evaluations.exam.attempt', $i) }}" class="text-xs font-semibold text-[#2563EB] hover:underline">Continuar</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>

</x-student-layout>
