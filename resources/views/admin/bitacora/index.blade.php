<x-admin-layout
    title="Bitácora"
    description="Consulta las acciones realizadas en la plataforma."
>

    <x-ui.card class="mb-6">

        <form
            method="GET"
            action="{{ route('admin.bitacora.index') }}"
            class="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-end"
        >

            <div>
                <label
                    for="search"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Buscar en la bitácora
                </label>

                <div class="relative">
                    <span
                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400"
                    >
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>

                    <input
                        id="search"
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Acción, usuario o correo..."
                        class="block w-full rounded-lg border-slate-300 py-2.5 pl-10 pr-4 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]"
                    >
                </div>
            </div>

            <div class="flex gap-2">
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1D4ED8]"
                >
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Buscar
                </button>

                @if ($search)
                    <a
                        href="{{ route('admin.bitacora.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-3 py-2.5 text-slate-600 transition hover:bg-slate-50"
                        title="Limpiar búsqueda"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>

        </form>

    </x-ui.card>


    <x-ui.card :padding="false">

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">

                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Fecha y hora
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Acción
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Usuario
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Visibilidad
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($registros as $registro)
                        <tr class="transition hover:bg-slate-50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                {{ $registro->fecha?->format('d/m/Y H:i') }}
                            </td>

                            <td class="min-w-[260px] px-6 py-4 text-sm font-medium text-slate-800">
                                {{ $registro->accion }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">
                                @if ($registro->usuario)
                                    <p class="text-sm font-semibold text-slate-800">
                                        {{ $registro->usuario->nombre_completo }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $registro->usuario->email }}
                                    </p>
                                @else
                                    <span class="text-sm text-slate-500">
                                        Usuario eliminado
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                <x-ui.badge :type="$registro->visible ? 'success' : 'danger'">
                                    {{ $registro->visible ? 'Visible' : 'Oculta' }}
                                </x-ui.badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">
                                No hay acciones registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        @if ($registros->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $registros->links() }}
            </div>
        @endif

    </x-ui.card>

</x-admin-layout>
