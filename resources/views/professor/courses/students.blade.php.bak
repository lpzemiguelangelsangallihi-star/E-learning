<x-professor-layout
    title="Estudiantes del curso"
    :description="$curso->titulo"
>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('professor.courses.index') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]">
            <i class="fa-solid fa-arrow-left"></i>
            Volver a mis cursos
        </a>

        <x-ui.badge :type="match ($curso->estado) { 'publicado' => 'success', 'oculto' => 'danger', default => 'warning' }">
            {{ ucfirst($curso->estado) }}
        </x-ui.badge>
    </div>


    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card title="Activos" :value="$totales['activo'] ?? 0" icon="fa-solid fa-user-check" type="success" />
        <x-ui.stat-card title="Finalizados" :value="$totales['finalizado'] ?? 0" icon="fa-solid fa-flag-checkered" type="warning" />
        <x-ui.stat-card title="Cancelados" :value="$totales['cancelado'] ?? 0" icon="fa-solid fa-user-xmark" type="danger" />
    </div>


    <x-ui.card class="mt-6">

        <form method="GET" action="{{ route('professor.courses.students', $curso) }}"
              class="mb-6 grid gap-4 md:grid-cols-[1fr_200px_auto]">

            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Buscar por nombre o correo"
                   class="block w-full rounded-lg border-slate-300 px-4 py-3 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">

            <select name="estado"
                    class="block w-full rounded-lg border-slate-300 px-4 py-3 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                <option value="">Todos los estados</option>
                @foreach (['activo' => 'Activo', 'finalizado' => 'Finalizado', 'cancelado' => 'Cancelado'] as $value => $label)
                    <option value="{{ $value }}" @selected($estado === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button type="submit"
                        class="rounded-lg bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white hover:bg-[#1D4ED8]">
                    Filtrar
                </button>

                @if ($search || $estado)
                    <a href="{{ route('professor.courses.students', $curso) }}"
                       class="rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>


        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="py-3 pr-4 font-semibold">Estudiante</th>
                        <th class="py-3 pr-4 font-semibold">Correo</th>
                        <th class="py-3 pr-4 font-semibold">Inscripción</th>
                        <th class="py-3 font-semibold">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($inscripciones as $inscripcion)
                        @php
                            $e = $inscripcion->estudiante;
                            $type = match ($inscripcion->estado) {
                                'activo' => 'success',
                                'finalizado' => 'primary',
                                default => 'danger',
                            };
                        @endphp
                        <tr>
                            <td class="py-3 pr-4 font-medium text-slate-800">
                                {{ $e?->nombre_completo ?? 'Usuario eliminado' }}
                            </td>
                            <td class="py-3 pr-4 text-slate-500">{{ $e?->email ?? '—' }}</td>
                            <td class="py-3 pr-4 text-slate-500">
                                {{ $inscripcion->fecha_inscripcion?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="py-3">
                                <x-ui.badge :type="$type">{{ ucfirst($inscripcion->estado) }}</x-ui.badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-slate-500">
                                <i class="fa-solid fa-user-graduate mb-2 text-2xl text-slate-300"></i>
                                <p>No hay estudiantes inscritos con estos filtros.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $inscripciones->links() }}</div>

    </x-ui.card>

</x-professor-layout>
