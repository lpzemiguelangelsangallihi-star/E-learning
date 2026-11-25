<x-admin-layout
    title="Respaldos"
    description="Genera, descarga y restaura respaldos de la base de datos."
>

    <div class="mx-auto max-w-4xl">

        @if (session('success'))

            <div
                class="
                    mb-6 flex items-start gap-3
                    rounded-lg
                    border border-green-200
                    bg-green-50
                    px-4 py-3
                    text-sm text-green-700
                "
            >
                <i
                    class="
                        fa-solid
                        fa-circle-check
                        mt-0.5
                    "
                ></i>

                {{ session('success') }}
            </div>

        @endif

        @if ($errors->any())

            <div
                class="
                    mb-6 flex items-start gap-3
                    rounded-lg
                    border border-red-200
                    bg-red-50
                    px-4 py-3
                    text-sm text-red-700
                "
            >
                <i
                    class="
                        fa-solid
                        fa-circle-exclamation
                        mt-0.5
                    "
                ></i>

                <ul class="list-disc list-inside mt-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>

        @endif

        {{-- Generar respaldo --}}
        <x-ui.card class="mb-6">

            <div class="mb-6 flex items-start gap-3">

                <div
                    class="
                        flex h-10 w-10
                        shrink-0
                        items-center
                        justify-center
                        rounded-lg
                        bg-blue-50
                        text-[#2563EB]
                    "
                >
                    <i class="fa-solid fa-database"></i>
                </div>

                <div>

                    <h2 class="text-base font-bold text-slate-800">
                        Generar respaldo de base de datos
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Crea una copia de seguridad completa de la base de datos actual.
                        El archivo se guarda en almacenamiento privado y solo puede ser
                        descargado por administradores.
                    </p>

                </div>

            </div>

            <form
                method="POST"
                action="{{ route('admin.backups.store') }}"
                data-loading-form
                data-loading-text="Generando respaldo..."
            >

                @csrf

                <input type="hidden" name="confirmar" value="1">

                <div
                    class="
                        mt-6 flex
                        justify-end
                    "
                >

                    <button
                        type="submit"
                        data-submit-button
                        class="
                            inline-flex
                            items-center gap-2
                            rounded-lg
                            bg-[#2563EB]
                            px-4 py-2.5
                            text-sm font-semibold
                            text-white
                            transition
                            hover:bg-[#1D4ED8]
                            disabled:cursor-not-allowed
                            disabled:bg-slate-300
                        "
                    >

                        <i
                            class="
                                fa-solid
                                fa-download
                            "
                            data-submit-icon
                        ></i>

                        <span data-submit-text>
                            Generar respaldo ahora
                        </span>

                    </button>

                </div>

            </form>

        </x-ui.card>

        {{-- Restaurar desde archivo --}}
        <x-ui.card class="mb-6">

            <div class="mb-6 flex items-start gap-3">

                <div
                    class="
                        flex h-10 w-10
                        shrink-0
                        items-center
                        justify-center
                        rounded-lg
                        bg-red-50
                        text-red-600
                    "
                >
                    <i class="fa-solid fa-file-arrow-up"></i>
                </div>

                <div>

                    <h2 class="text-base font-bold text-slate-800">
                        Restaurar desde un archivo
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Sube un respaldo .sql que hayas descargado antes. Esta acción
                        reemplaza los datos actuales de la base de datos.
                    </p>

                </div>

            </div>

            <div
                class="
                    mb-5 flex items-start gap-3
                    rounded-lg
                    border border-amber-200
                    bg-amber-50
                    px-4 py-3
                    text-sm text-amber-800
                "
            >
                <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>

                <p>
                    Antes de restaurar, el sistema genera automáticamente un respaldo
                    de seguridad. Sube solo archivos de fuentes confiables.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('admin.backups.upload') }}"
                enctype="multipart/form-data"
                data-loading-form
                data-loading-text="Restaurando..."
                onsubmit="return confirm('¿Restaurar la base de datos desde este archivo? Se reemplazarán los datos actuales.');"
            >

                @csrf

                <input type="hidden" name="confirmar" value="1">

                <div>

                    <label
                        for="archivo"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Archivo de respaldo
                    </label>

                    <input
                        id="archivo"
                        name="archivo"
                        type="file"
                        accept=".sql"
                        required
                        class="
                            block w-full cursor-pointer
                            rounded-lg border border-slate-300
                            bg-white
                            text-sm text-slate-600
                            file:mr-4 file:border-0
                            file:bg-slate-100
                            file:px-4 file:py-3
                            file:text-sm file:font-semibold
                            file:text-slate-700
                            hover:file:bg-slate-200
                            focus:border-[#2563EB]
                            focus:outline-none
                            focus:ring-2
                            focus:ring-blue-100
                        "
                    >

                    @error('archivo')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    <p class="mt-2 text-xs text-slate-500">
                        Solo archivos .sql de hasta 100 MB.
                    </p>

                </div>

                <div class="mt-6 flex justify-end">

                    <button
                        type="submit"
                        data-submit-button
                        class="
                            inline-flex
                            items-center gap-2
                            rounded-lg
                            bg-red-600
                            px-4 py-2.5
                            text-sm font-semibold
                            text-white
                            transition
                            hover:bg-red-700
                            disabled:cursor-not-allowed
                            disabled:bg-slate-300
                        "
                    >

                        <i
                            class="
                                fa-solid
                                fa-rotate-left
                            "
                            data-submit-icon
                        ></i>

                        <span data-submit-text>
                            Subir y restaurar
                        </span>

                    </button>

                </div>

            </form>

        </x-ui.card>

        {{-- Respaldos disponibles --}}
        <x-ui.card>

            <div class="mb-6">

                <h2 class="text-base font-bold text-slate-800">
                    Respaldos disponibles
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Archivos generados previamente, listos para descarga.
                </p>

            </div>

            @if ($backups->isEmpty())

                <div
                    class="
                        py-12 text-center
                    "
                >
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <i class="fa-solid fa-database text-xl" aria-hidden="true"></i>
                    </div>

                    <h3 class="mt-4 text-base font-semibold text-slate-800">
                        No hay respaldos generados.
                    </h3>

                    <p class="mx-auto mt-1 max-w-sm text-sm text-slate-500">
                        Genera tu primer respaldo desde el formulario de arriba.
                    </p>
                </div>

            @else

                <table class="w-full text-left border-collapse">

                    <thead>

                        <tr class="border-b border-slate-200">

                            <th
                                class="
                                    pb-4
                                    text-xs font-semibold
                                    uppercase tracking-wide
                                    text-slate-400
                                "
                            >
                                Archivo
                            </th>

                            <th
                                class="
                                    pb-4
                                    text-xs font-semibold
                                    uppercase tracking-wide
                                    text-slate-400
                                "
                            >
                                Tamaño
                            </th>

                            <th
                                class="
                                    pb-4
                                    text-xs font-semibold
                                    uppercase tracking-wide
                                    text-slate-400
                                "
                            >
                                Fecha
                            </th>

                            <th
                                class="
                                    pb-4
                                    text-xs font-semibold
                                    uppercase tracking-wide
                                    text-slate-400
                                    text-right
                                "
                            >
                                Acción
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach ($backups as $backup)

                            <tr class="border-b border-slate-100 hover:bg-slate-50">

                                <td class="py-4 text-sm text-slate-700">
                                    {{ $backup['filename'] }}
                                </td>

                                <td class="py-4 text-sm text-slate-500">
                                    {{ number_format($backup['size'] / 1024, 2) }} KB
                                </td>

                                <td class="py-4 text-sm text-slate-500">
                                    {{
                                        \Carbon\Carbon::parse(
                                            $backup['lastModified']
                                        )->format('d/m/Y H:i')
                                    }}
                                </td>

                                <td class="py-4 text-right">

                                    <a
                                        href="{{ route('admin.backups.download', $backup['filename']) }}"
                                        class="
                                            inline-flex items-center gap-2
                                            rounded-lg border border-slate-200
                                            bg-white px-3 py-2
                                            text-xs font-semibold
                                            text-slate-700
                                            transition
                                            hover:bg-slate-50
                                            mb-1
                                        "
                                    >
                                        <i class="fa-solid fa-download text-slate-400"></i>
                                        Descargar
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.backups.restore', $backup['filename']) }}"
                                        onsubmit="return confirm('¿Estás seguro de que quieres restaurar esta base de datos desde el respaldo \"{{ $backup['filename'] }}\"? Esta acción sobrescribirá los datos actuales.');"
                                        class="inline"
                                    >
                                        @csrf
                                        <input type="hidden" name="confirmar" value="1">
                                        <button
                                            type="submit"
                                            class="
                                                inline-flex items-center gap-2
                                                rounded-lg border border-red-200
                                                bg-white px-3 py-2
                                                text-xs font-semibold
                                                text-red-700
                                                transition
                                                hover:bg-red-50
                                            "
                                        >
                                            <i class="fa-solid fa-rotate-left text-red-500"></i>
                                            Restaurar
                                        </button>
                                    </form>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            @endif

        </x-ui.card>

    </div>

</x-admin-layout>
