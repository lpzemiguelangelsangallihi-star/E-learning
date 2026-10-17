<x-student-layout
    title="Mi progreso"
    description="Tu avance, experiencia y logros."
>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card title="Progreso promedio" :value="$promedioProgreso . '%'" icon="fa-solid fa-chart-line" />
        <x-ui.stat-card title="Temas completados" :value="$lecciones" icon="fa-solid fa-circle-check" type="success" />
        <x-ui.stat-card title="Racha actual" :value="($racha->racha_actual ?? 0) . ' d'" icon="fa-solid fa-fire" type="warning"
            :description="'Mejor racha: ' . ($racha->mejor_racha ?? 0) . ' días'" />
        <x-ui.stat-card title="Experiencia" :value="$experiencia . ' XP'" icon="fa-solid fa-bolt"
            :description="$nivel ? 'Nivel: ' . $nivel->nombre : null" />
    </div>


    {{-- Nivel --}}
    @if ($siguienteNivel)
        @php
            $base = $nivel->experiencia_minima ?? 0;
            $meta = $siguienteNivel->experiencia_minima;
            $pctNivel = $meta > $base ? (($experiencia - $base) / ($meta - $base)) * 100 : 0;
        @endphp

        <x-ui.card class="mt-6">
            <div class="mb-3 flex items-center justify-between text-sm">
                <span class="font-semibold text-slate-800">Hacia «{{ $siguienteNivel->nombre }}»</span>
                <span class="text-slate-500">{{ $experiencia }} / {{ $meta }} XP</span>
            </div>

            <x-ui.progress :value="$pctNivel" :show-label="false" />
        </x-ui.card>
    @endif


    <div class="mt-6 grid gap-6 lg:grid-cols-3">

        <x-ui.card class="lg:col-span-2">
            <h2 class="mb-6 text-base font-bold text-slate-800">Progreso por curso</h2>

            <div class="space-y-5">
                @forelse ($inscripciones as $inscripcion)
                    @php
                        $curso = $inscripcion->curso;
                        $progreso = $progresos[$inscripcion->curso_id] ?? null;
                    @endphp

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <a href="{{ route('student.courses.show', $curso) }}" class="truncate font-semibold text-slate-800 hover:text-[#2563EB]">
                                {{ $curso->titulo }}
                            </a>

                            <span class="shrink-0 text-xs text-slate-500">{{ $progreso->lecciones_completadas ?? 0 }} temas</span>
                        </div>

                        <x-ui.progress :value="$progreso->porcentaje ?? 0" />
                    </div>
                @empty
                    <x-ui.empty icon="fa-solid fa-chart-line" title="Aún no hay progreso que mostrar" text="Inscríbete en un curso y completa tus primeros temas." />
                @endforelse
            </div>
        </x-ui.card>


        <x-ui.card>
            <h2 class="mb-6 text-base font-bold text-slate-800">Insignias</h2>

            <div class="space-y-3">
                @forelse ($insignias as $insignia)
                    <div class="flex items-center gap-3 rounded-lg border border-slate-200 p-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-yellow-50 text-[#EAB308]">
                            <i class="fa-solid fa-medal" aria-hidden="true"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ $insignia->nombre }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $insignia->descripcion }}</p>
                        </div>
                    </div>
                @empty
                    <x-ui.empty icon="fa-solid fa-medal" title="Sin insignias todavía" text="Sigue avanzando para ganarlas." class="py-6" />
                @endforelse
            </div>
        </x-ui.card>

    </div>

</x-student-layout>
