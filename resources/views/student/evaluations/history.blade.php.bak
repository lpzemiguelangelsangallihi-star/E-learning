<x-student-layout
    title="Historial de evaluaciones"
    description="Lo que ya entregaste o rendiste."
>

    <div class="mb-6">
        <a href="{{ route('student.evaluations.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            Volver a pendientes
        </a>
    </div>

    <x-ui.card :padding="false">
        @if ($historial->isEmpty())
            <x-ui.empty
                icon="fa-solid fa-clock-rotate-left"
                title="Todavía no tienes evaluaciones realizadas"
                text="Tus tareas entregadas y exámenes rendidos aparecerán aquí."
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Evaluación</th>
                            <th class="px-6 py-3">Curso</th>
                            <th class="px-6 py-3">Fecha</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3 text-right">Nota</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($historial as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-800">{{ $item['titulo'] }}</p>

                                    <p class="text-xs text-slate-500">
                                        {{ $item['tipo'] === 'examen' ? 'Examen' : 'Tarea' }}
                                        @if ($item['tipo'] === 'examen' && $item['detalle']) · {{ $item['detalle'] }} @endif
                                    </p>
                                </td>

                                <td class="px-6 py-4 text-slate-600">{{ $item['curso']?->titulo ?? '—' }}</td>

                                <td class="whitespace-nowrap px-6 py-4 text-slate-600">{{ $item['fecha']?->format('d/m/Y H:i') ?? '—' }}</td>

                                <td class="px-6 py-4">
                                    @switch($item['estado'])
                                        @case('calificada')
                                            <x-ui.badge type="success">Calificada</x-ui.badge>
                                            @break
                                        @case('tarde')
                                            <x-ui.badge type="warning">Entregada tarde</x-ui.badge>
                                            @break
                                        @case('entregada')
                                            <x-ui.badge type="primary">En revisión</x-ui.badge>
                                            @break
                                        @default
                                            <x-ui.badge type="success">Finalizado</x-ui.badge>
                                    @endswitch
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-right font-semibold text-slate-800">
                                    @if ($item['calificacion'] !== null)
                                        {{ rtrim(rtrim(number_format($item['calificacion'], 2), '0'), '.') }}
                                        @if ($item['maximo']) <span class="font-normal text-slate-400">/ {{ rtrim(rtrim(number_format($item['maximo'], 2), '0'), '.') }}</span> @endif
                                    @else
                                        <span class="font-normal text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>

</x-student-layout>
