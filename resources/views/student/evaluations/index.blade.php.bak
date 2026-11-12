<x-student-layout
    title="Evaluaciones pendientes"
    description="Tareas y exámenes que aún puedes resolver."
>

    <div class="mb-6 flex justify-end">
        <x-ui.button :href="route('student.evaluations.history')" variant="secondary" icon="fa-solid fa-clock-rotate-left">
            Ver historial
        </x-ui.button>
    </div>

    @if ($pendientes->isEmpty())
        <x-ui.card>
            <x-ui.empty
                icon="fa-solid fa-circle-check"
                title="No tienes evaluaciones pendientes"
                text="Cuando tus profesores publiquen tareas o exámenes aparecerán aquí."
            />
        </x-ui.card>
    @else
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($pendientes as $item)
                <x-ui.card hover>
                    <div class="flex h-full flex-col">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div @class([
                                'flex h-11 w-11 items-center justify-center rounded-lg',
                                'bg-blue-50 text-[#2563EB]' => $item['tipo'] === 'examen',
                                'bg-yellow-50 text-yellow-600' => $item['tipo'] === 'tarea',
                            ])>
                                <i class="fa-solid {{ $item['tipo'] === 'examen' ? 'fa-file-pen' : 'fa-clipboard-list' }}" aria-hidden="true"></i>
                            </div>

                            @if ($item['vencida'])
                                <x-ui.badge type="danger">Vencida</x-ui.badge>
                            @else
                                <x-ui.badge type="warning">Pendiente</x-ui.badge>
                            @endif
                        </div>

                        <p class="text-xs font-semibold uppercase tracking-wide text-[#2563EB]">
                            {{ $item['tipo'] === 'examen' ? 'Examen' : 'Tarea' }}
                        </p>

                        <h3 class="mt-1 text-lg font-bold text-slate-800">{{ $item['titulo'] }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $item['curso']?->titulo }}</p>

                        <div class="mt-4 space-y-1 text-xs text-slate-500">
                            @if ($item['fecha_limite'])
                                <p @class(['font-medium text-[#DC2626]' => $item['vencida']])>
                                    <i class="fa-regular fa-calendar mr-1" aria-hidden="true"></i>
                                    Vence: {{ $item['fecha_limite']->format('d/m/Y H:i') }}
                                </p>
                            @endif

                            @if ($item['detalle'])
                                <p><i class="fa-regular fa-clock mr-1" aria-hidden="true"></i>{{ $item['detalle'] }}</p>
                            @endif

                            <p><i class="fa-solid fa-trophy mr-1" aria-hidden="true"></i>Puntaje máximo: {{ rtrim(rtrim(number_format($item['puntaje'], 2), '0'), '.') }}</p>
                        </div>

                        @if ($item['curso'] && $item['leccion'])
                            <x-ui.button :href="route('student.lessons.show', [$item['curso'], $item['leccion']])" variant="secondary" class="mt-5 w-full">
                                Ir al tema
                            </x-ui.button>
                        @endif
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @endif

</x-student-layout>
