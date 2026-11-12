@php
    $input = 'block w-full rounded-lg border-slate-300 px-4 py-2.5 text-sm text-slate-800 focus:border-[#2563EB] focus:ring-[#2563EB]';
    $fmt = fn ($n) => $n === null ? '—' : rtrim(rtrim(number_format((float) $n, 1), '0'), '.');
    $tab = fn ($clave) => $vista === $clave
        ? 'border-[#2563EB] text-[#2563EB]'
        : 'border-transparent text-slate-500 hover:text-slate-700';
    $color = fn ($n) => $n === null ? 'text-slate-400' : ((float) $n >= \App\Models\Calificacion::NOTA_APROBACION ? 'text-[#16A34A]' : 'text-[#DC2626]');
    $th = 'px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500';
    $td = 'px-4 py-3 text-sm text-slate-700';
@endphp

<div class="space-y-6">

    {{-- Filtros --}}
    <x-ui.card>
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label for="f-curso" class="mb-1 block text-sm font-medium text-slate-700">Curso</label>
                <select id="f-curso" wire:model.live="cursoId" class="{{ $input }}">
                    <option value="">Todos mis cursos</option>
                    @foreach ($cursos as $c)
                        <option value="{{ $c->id }}">{{ $c->titulo }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="f-examen" class="mb-1 block text-sm font-medium text-slate-700">Examen</label>
                <select id="f-examen" wire:model.live="examenId" class="{{ $input }}">
                    <option value="">Todos los exámenes</option>
                    @foreach ($examenes as $e)
                        <option value="{{ $e->id }}">{{ $e->titulo }}</option>
                    @endforeach
                </select>
            </div>

            @if (in_array($vista, ['estudiantes', 'intentos']))
                <div>
                    <label for="f-buscar" class="mb-1 block text-sm font-medium text-slate-700">Buscar estudiante</label>
                    <input id="f-buscar" type="search" wire:model.live.debounce.400ms="buscar" placeholder="Nombre o correo" class="{{ $input }}">
                </div>
            @endif
        </div>
    </x-ui.card>

    {{-- Resumen --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card title="Intentos finalizados" :value="$resumen['intentos']" icon="fa-solid fa-file-pen" />
        <x-ui.stat-card title="Estudiantes" :value="$resumen['estudiantes']" icon="fa-solid fa-users" type="success" />
        <x-ui.stat-card title="Promedio (sobre 100)" :value="$resumen['promedio'] ?? '—'" icon="fa-solid fa-chart-line" type="warning" />
        <x-ui.stat-card title="Aprobación %" :value="$resumen['aprobacion'] ?? '—'" icon="fa-solid fa-circle-check" type="success"
            :description="'Aprueba con ' . \App\Models\Calificacion::NOTA_APROBACION . '/100'" />
    </div>

    {{-- Pestañas --}}
    <x-ui.card :padding="false">
        <div class="flex flex-wrap gap-1 border-b border-slate-200 px-4">
            @foreach (['examenes' => 'Por examen', 'estudiantes' => 'Por estudiante', 'temas' => 'Por tema', 'tiempo' => 'En el tiempo', 'intentos' => 'Intentos y corrección'] as $clave => $nombre)
                <button type="button" wire:click="$set('vista', '{{ $clave }}')"
                    class="-mb-px border-b-2 px-4 py-3 text-sm font-semibold transition {{ $tab($clave) }}">
                    {{ $nombre }}
                </button>
            @endforeach
        </div>

        <div class="overflow-x-auto">

            {{-- POR EXAMEN --}}
            @if ($vista === 'examenes')
                @if ($filas->isEmpty())
                    <x-ui.empty icon="fa-solid fa-chart-column" title="Sin resultados todavía" text="Cuando los estudiantes entreguen exámenes verás aquí su rendimiento." />
                @else
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50"><tr>
                            <th class="{{ $th }}">Examen</th><th class="{{ $th }}">Curso</th>
                            <th class="{{ $th }}">Intentos</th><th class="{{ $th }}">Estudiantes</th>
                            <th class="{{ $th }}">Promedio</th><th class="{{ $th }}">Mejor</th><th class="{{ $th }}">Aprobación</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($filas as $f)
                                <tr>
                                    <td class="{{ $td }} font-semibold text-slate-800">{{ $f->examen }}</td>
                                    <td class="{{ $td }}">{{ $f->curso }}</td>
                                    <td class="{{ $td }}">{{ $f->intentos }}</td>
                                    <td class="{{ $td }}">{{ $f->estudiantes }}</td>
                                    <td class="{{ $td }} font-semibold {{ $color($f->promedio) }}">{{ $fmt($f->promedio) }}</td>
                                    <td class="{{ $td }}">{{ $fmt($f->mejor) }}</td>
                                    <td class="{{ $td }}">{{ $f->intentos ? $fmt(($f->aprobados / $f->intentos) * 100) : '—' }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

            {{-- POR ESTUDIANTE --}}
            @elseif ($vista === 'estudiantes')
                @if ($filas->isEmpty())
                    <x-ui.empty icon="fa-solid fa-user-graduate" title="Sin resultados" text="No hay estudiantes con exámenes entregados para este filtro." />
                @else
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50"><tr>
                            <th class="{{ $th }}">Estudiante</th><th class="{{ $th }}">Correo</th>
                            <th class="{{ $th }}">Intentos</th><th class="{{ $th }}">Promedio</th>
                            <th class="{{ $th }}">Mejor</th><th class="{{ $th }}">Último intento</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($filas as $f)
                                <tr>
                                    <td class="{{ $td }} font-semibold text-slate-800">{{ trim("{$f->nombre} {$f->apellido_paterno} {$f->apellido_materno}") }}</td>
                                    <td class="{{ $td }}">{{ $f->email }}</td>
                                    <td class="{{ $td }}">{{ $f->intentos }}</td>
                                    <td class="{{ $td }} font-semibold {{ $color($f->promedio) }}">{{ $fmt($f->promedio) }}</td>
                                    <td class="{{ $td }}">{{ $fmt($f->mejor) }}</td>
                                    <td class="{{ $td }}">{{ $f->ultimo ? \Illuminate\Support\Carbon::parse($f->ultimo)->format('d/m/Y H:i') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

            {{-- POR TEMA --}}
            @elseif ($vista === 'temas')
                @if ($filas->isEmpty())
                    <x-ui.empty icon="fa-solid fa-book-open" title="Sin datos por tema" text="Se calcula con las respuestas de los exámenes entregados." />
                @else
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50"><tr>
                            <th class="{{ $th }}">Tema</th><th class="{{ $th }}">Curso</th>
                            <th class="{{ $th }}">Preguntas</th><th class="{{ $th }}">Logro</th>
                            <th class="{{ $th }}">Pregunta más difícil</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($filas as $f)
                                <tr>
                                    <td class="{{ $td }} font-semibold text-slate-800">{{ $f->leccion }}</td>
                                    <td class="{{ $td }}">{{ $f->curso }}</td>
                                    <td class="{{ $td }}">{{ $f->preguntas }}</td>
                                    <td class="{{ $td }}" style="min-width: 11rem">
                                        <div class="flex items-center gap-2">
                                            <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full {{ ($f->logro ?? 0) >= 51 ? 'bg-[#16A34A]' : 'bg-[#DC2626]' }}" style="width: {{ min(100, $f->logro ?? 0) }}%"></div>
                                            </div>
                                            <span class="font-semibold {{ $color($f->logro) }}">{{ $fmt($f->logro) }}%</span>
                                        </div>
                                    </td>
                                    <td class="{{ $td }}">
                                        {{ \Illuminate\Support\Str::limit((string) $f->mas_dificil, 70) }}
                                        @if ($f->mas_dificil_logro !== null)
                                            <span class="text-xs text-slate-400">({{ $fmt($f->mas_dificil_logro) }}%)</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

            {{-- EN EL TIEMPO --}}
            @elseif ($vista === 'tiempo')
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50"><tr>
                        <th class="{{ $th }}">Mes</th><th class="{{ $th }}">Intentos</th><th class="{{ $th }}">Promedio</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($filas as $f)
                            <tr>
                                <td class="{{ $td }} font-semibold capitalize text-slate-800">{{ $f->mes }}</td>
                                <td class="{{ $td }}">{{ $f->intentos }}</td>
                                <td class="{{ $td }}" style="min-width: 12rem">
                                    @if ($f->promedio !== null)
                                        <div class="flex items-center gap-2">
                                            <div class="h-2 w-32 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full bg-[#2563EB]" style="width: {{ min(100, $f->promedio) }}%"></div>
                                            </div>
                                            <span class="font-semibold {{ $color($f->promedio) }}">{{ $fmt($f->promedio) }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            {{-- INTENTOS Y CORRECCIÓN --}}
            @else
                @if ($filas->isEmpty())
                    <x-ui.empty icon="fa-solid fa-inbox" title="Sin intentos" text="Aún no hay intentos entregados para este filtro." />
                @else
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50"><tr>
                            <th class="{{ $th }}">Estudiante</th><th class="{{ $th }}">Examen</th>
                            <th class="{{ $th }}">Intento</th><th class="{{ $th }}">Entregado</th>
                            <th class="{{ $th }}">Nota</th><th class="{{ $th }}"></th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($filas as $f)
                                <tr wire:key="intento-{{ $f->id }}">
                                    <td class="{{ $td }} font-semibold text-slate-800">{{ $f->estudiante }}</td>
                                    <td class="{{ $td }}">{{ $f->examen }}<br><span class="text-xs text-slate-400">{{ $f->curso }}</span></td>
                                    <td class="{{ $td }}">{{ $f->numero_intento }}</td>
                                    <td class="{{ $td }}">{{ $f->fecha_fin ? \Illuminate\Support\Carbon::parse($f->fecha_fin)->format('d/m/Y H:i') : '—' }}</td>
                                    <td class="{{ $td }} font-semibold">
                                        {{ $fmt($f->calificacion) }} / {{ $fmt($f->puntaje_maximo) }}
                                    </td>
                                    <td class="{{ $td }} text-right">
                                        <button type="button" wire:click="verDetalle({{ $f->id }})"
                                            class="text-sm font-semibold text-[#2563EB] hover:underline">Revisar</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="border-t border-slate-100 p-4">{{ $filas->links() }}</div>
                @endif
            @endif
        </div>
    </x-ui.card>

    {{-- Detalle / corrección manual --}}
    @if ($detalle)
        <x-ui.card id="detalle-intento">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-800">{{ $detalle->examen->titulo }}</h2>
                    <p class="text-sm text-slate-500">
                        {{ $detalle->estudiante?->nombre_completo }} · intento {{ $detalle->numero_intento }} ·
                        nota {{ $fmt($detalle->calificacion) }} / {{ $fmt($detalle->examen->puntaje_maximo) }}
                    </p>
                </div>

                <button type="button" wire:click="cerrarDetalle" class="text-sm font-semibold text-slate-500 hover:text-slate-800">
                    <i class="fa-solid fa-xmark mr-1" aria-hidden="true"></i> Cerrar
                </button>
            </div>

            @if ($mensaje)
                <x-ui.alert type="success" class="mb-4">{{ $mensaje }}</x-ui.alert>
            @endif

            <p class="mb-4 text-xs text-slate-500">
                Puedes ajustar el puntaje de cada respuesta (por ejemplo en respuestas cortas con otra redacción válida).
                Cada corrección queda registrada en la bitácora.
            </p>

            <div class="space-y-4">
                @foreach ($detalle->respuestas->groupBy('pregunta_id') as $preguntaId => $filasResp)
                    @php($pregunta = $filasResp->first()->pregunta)
                    <div class="rounded-xl border border-slate-200 p-4" wire:key="resp-{{ $preguntaId }}">
                        <p class="text-sm font-semibold text-slate-800">{{ $pregunta->pregunta }}</p>
                        <p class="mb-2 text-xs text-slate-400">Puntaje de la pregunta: {{ rtrim(rtrim(number_format((float) $pregunta->puntaje, 2), '0'), '.') }}</p>

                        @foreach ($filasResp as $r)
                            <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                                <p class="min-w-0 {{ $r->es_correcta ? 'text-[#16A34A]' : 'text-slate-700' }}">
                                    <i class="fa-solid {{ $r->es_correcta ? 'fa-circle-check' : 'fa-circle-xmark text-[#DC2626]' }} mr-1" aria-hidden="true"></i>
                                    @if ($r->opcion)
                                        {{ $r->opcion->opcion }}
                                    @elseif ($r->respuesta_texto !== null)
                                        “{{ $r->respuesta_texto }}”
                                    @else
                                        <span class="text-slate-400">Sin responder</span>
                                    @endif
                                </p>

                                <div class="flex items-center gap-2">
                                    <label for="aj-{{ $r->id }}" class="text-xs text-slate-500">Puntos</label>
                                    <input id="aj-{{ $r->id }}" type="number" step="0.01" min="0"
                                        wire:model="ajustes.{{ $r->id }}"
                                        class="w-24 rounded-lg border-slate-300 px-2 py-1 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                                </div>
                            </div>
                            @error('ajustes.'.$r->id) <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p> @enderror
                        @endforeach

                        @if ($pregunta->tipo === 'respuesta_corta')
                            <p class="mt-2 text-xs text-slate-400">
                                Aceptadas: {{ $pregunta->opciones->where('es_correcta', true)->pluck('opcion')->implode(', ') }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>

            <button type="button" wire:click="guardarAjustes" wire:loading.attr="disabled"
                class="mt-5 inline-flex items-center gap-2 rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1D4ED8] disabled:opacity-60">
                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                Guardar corrección
            </button>
        </x-ui.card>
    @endif
</div>
