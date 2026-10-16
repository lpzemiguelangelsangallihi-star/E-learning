@props([
    'curso' => null,
    'materias',
    'action',
    'method' => 'POST',
    'submitText' => 'Guardar curso',
])

<form
    method="POST"
    action="{{ $action }}"
    class="space-y-6"
    data-loading-form
    data-loading-text="Guardando..."
>
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif


    <x-ui.card>

        <div class="mb-6">

            <h2
                class="
                    text-base
                    font-bold
                    text-slate-800
                "
            >
                Información del curso
            </h2>

            <p
                class="
                    mt-1
                    text-sm
                    text-slate-500
                "
            >
                Completa los datos principales del curso.
            </p>

        </div>


        <div
            class="
                grid gap-5
                md:grid-cols-2
            "
        >

            {{-- Materia --}}
            <div>

                <label
                    for="materia_id"
                    class="
                        mb-2 block
                        text-sm font-semibold
                        text-slate-700
                    "
                >
                    Materia
                </label>


                <select
                    id="materia_id"
                    name="materia_id"
                    required
                    class="
                        block w-full
                        rounded-lg
                        border-slate-300
                        px-4 py-3
                        text-sm
                        text-slate-800
                        focus:border-[#2563EB]
                        focus:ring-[#2563EB]
                    "
                >

                    <option value="">
                        Selecciona una materia
                    </option>


                    @foreach ($materias as $materia)

                        <option
                            value="{{ $materia->id }}"
                            @selected(
                                old(
                                    'materia_id',
                                    $curso?->materia_id
                                ) == $materia->id
                            )
                        >
                            {{ $materia->nombre }}
                        </option>

                    @endforeach

                </select>


                @error('materia_id')

                    <p
                        class="
                            mt-2
                            text-sm
                            text-red-600
                        "
                    >
                        {{ $message }}
                    </p>

                @enderror

            </div>


            {{-- Nivel --}}
            <div>

                <label
                    for="nivel"
                    class="
                        mb-2 block
                        text-sm font-semibold
                        text-slate-700
                    "
                >
                    Nivel
                </label>


                <select
                    id="nivel"
                    name="nivel"
                    required
                    class="
                        block w-full
                        rounded-lg
                        border-slate-300
                        px-4 py-3
                        text-sm
                        text-slate-800
                        focus:border-[#2563EB]
                        focus:ring-[#2563EB]
                    "
                >

                    <option value="">
                        Selecciona un nivel
                    </option>


                    <option
                        value="basico"
                        @selected(
                            old(
                                'nivel',
                                $curso?->nivel
                            ) === 'basico'
                        )
                    >
                        Básico
                    </option>


                    <option
                        value="intermedio"
                        @selected(
                            old(
                                'nivel',
                                $curso?->nivel
                            ) === 'intermedio'
                        )
                    >
                        Intermedio
                    </option>


                    <option
                        value="avanzado"
                        @selected(
                            old(
                                'nivel',
                                $curso?->nivel
                            ) === 'avanzado'
                        )
                    >
                        Avanzado
                    </option>

                </select>


                @error('nivel')

                    <p
                        class="
                            mt-2
                            text-sm
                            text-red-600
                        "
                    >
                        {{ $message }}
                    </p>

                @enderror

            </div>


            {{-- Título --}}
            <div class="md:col-span-2">

                <label
                    for="titulo"
                    class="
                        mb-2 block
                        text-sm font-semibold
                        text-slate-700
                    "
                >
                    Título del curso
                </label>


                <input
                    id="titulo"
                    type="text"
                    name="titulo"
                    maxlength="200"
                    value="{{
                        old(
                            'titulo',
                            $curso?->titulo
                        )
                    }}"
                    placeholder="Ej. Introducción a vectores"
                    required
                    class="
                        block w-full
                        rounded-lg
                        border-slate-300
                        px-4 py-3
                        text-sm
                        text-slate-800
                        focus:border-[#2563EB]
                        focus:ring-[#2563EB]
                    "
                >


                @error('titulo')

                    <p
                        class="
                            mt-2
                            text-sm
                            text-red-600
                        "
                    >
                        {{ $message }}
                    </p>

                @enderror

            </div>


            {{-- Descripción --}}
            <div class="md:col-span-2">

                <label
                    for="descripcion"
                    class="
                        mb-2 block
                        text-sm font-semibold
                        text-slate-700
                    "
                >
                    Descripción
                </label>


                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="5"
                    placeholder="Describe brevemente el contenido y objetivo del curso..."
                    class="
                        block w-full
                        rounded-lg
                        border-slate-300
                        px-4 py-3
                        text-sm
                        text-slate-800
                        focus:border-[#2563EB]
                        focus:ring-[#2563EB]
                    "
                >{{ old('descripcion', $curso?->descripcion) }}</textarea>


                @error('descripcion')

                    <p
                        class="
                            mt-2
                            text-sm
                            text-red-600
                        "
                    >
                        {{ $message }}
                    </p>

                @enderror

            </div>


            {{-- Duración --}}
            <div>

                <label
                    for="duracion"
                    class="
                        mb-2 block
                        text-sm font-semibold
                        text-slate-700
                    "
                >
                    Duración estimada
                </label>


                <div class="relative">

                    <input
                        id="duracion"
                        type="number"
                        name="duracion"
                        min="0"
                        step="1"
                        value="{{
                            old(
                                'duracion',
                                $curso?->duracion ?? 0
                            )
                        }}"
                        class="
                            block w-full
                            rounded-lg
                            border-slate-300
                            px-4 py-3
                            pr-16
                            text-sm
                            text-slate-800
                            focus:border-[#2563EB]
                            focus:ring-[#2563EB]
                        "
                    >


                    <span
                        class="
                            pointer-events-none
                            absolute inset-y-0 right-0
                            flex items-center
                            pr-4
                            text-sm
                            text-slate-400
                        "
                    >
                        horas
                    </span>

                </div>


                @error('duracion')

                    <p
                        class="
                            mt-2
                            text-sm
                            text-red-600
                        "
                    >
                        {{ $message }}
                    </p>

                @enderror

            </div>


            {{-- Tipo --}}
            <div>

                <label
                    for="tipo"
                    class="
                        mb-2 block
                        text-sm font-semibold
                        text-slate-700
                    "
                >
                    Tipo de curso
                </label>


                <select
                    id="tipo"
                    name="tipo"
                    required
                    class="
                        block w-full
                        rounded-lg
                        border-slate-300
                        px-4 py-3
                        text-sm
                        text-slate-800
                        focus:border-[#2563EB]
                        focus:ring-[#2563EB]
                    "
                >

                    <option
                        value="gratuito"
                        @selected(
                            old(
                                'tipo',
                                $curso?->tipo ?? 'gratuito'
                            ) === 'gratuito'
                        )
                    >
                        Gratuito
                    </option>


                    <option
                        value="pago"
                        @selected(
                            old(
                                'tipo',
                                $curso?->tipo
                            ) === 'pago'
                        )
                    >
                        Pago
                    </option>

                </select>


                @error('tipo')

                    <p
                        class="
                            mt-2
                            text-sm
                            text-red-600
                        "
                    >
                        {{ $message }}
                    </p>

                @enderror

            </div>


            {{-- Precio --}}
            <div class="md:col-span-2">

                <label
                    for="precio"
                    class="
                        mb-2 block
                        text-sm font-semibold
                        text-slate-700
                    "
                >
                    Precio
                </label>


                <div class="relative">

                    <span
                        class="
                            pointer-events-none
                            absolute inset-y-0 left-0
                            flex items-center
                            pl-4
                            text-sm
                            text-slate-400
                        "
                    >
                        Bs
                    </span>


                    <input
                        id="precio"
                        type="number"
                        name="precio"
                        min="0"
                        step="0.01"
                        value="{{
                            old(
                                'precio',
                                $curso?->precio ?? 0
                            )
                        }}"
                        class="
                            block w-full
                            rounded-lg
                            border-slate-300
                            py-3
                            pl-11 pr-4
                            text-sm
                            text-slate-800
                            focus:border-[#2563EB]
                            focus:ring-[#2563EB]
                        "
                    >

                </div>


                <p
                    class="
                        mt-2
                        text-xs
                        text-slate-500
                    "
                >
                    Si seleccionas "Gratuito", el precio se guardará automáticamente en 0.
                </p>


                @error('precio')

                    <p
                        class="
                            mt-2
                            text-sm
                            text-red-600
                        "
                    >
                        {{ $message }}
                    </p>

                @enderror

            </div>


            {{-- Estado --}}
            @if ($curso)

                <div class="md:col-span-2">

                    <label
                        for="estado"
                        class="
                            mb-2 block
                            text-sm font-semibold
                            text-slate-700
                        "
                    >
                        Estado del curso
                    </label>


                    <select
                        id="estado"
                        name="estado"
                        required
                        class="
                            block w-full
                            rounded-lg
                            border-slate-300
                            px-4 py-3
                            text-sm
                            text-slate-800
                            focus:border-[#2563EB]
                            focus:ring-[#2563EB]
                        "
                    >

                        <option
                            value="borrador"
                            @selected(
                                old(
                                    'estado',
                                    $curso->estado
                                ) === 'borrador'
                            )
                        >
                            Borrador
                        </option>


                        <option
                            value="publicado"
                            @selected(
                                old(
                                    'estado',
                                    $curso->estado
                                ) === 'publicado'
                            )
                        >
                            Publicado
                        </option>


                        <option
                            value="oculto"
                            @selected(
                                old(
                                    'estado',
                                    $curso->estado
                                ) === 'oculto'
                            )
                        >
                            Oculto
                        </option>

                    </select>


                    <div
                        class="
                            mt-3
                            rounded-lg
                            border border-slate-200
                            bg-slate-50
                            p-3
                            text-xs
                            text-slate-600
                        "
                    >

                        <p>
                            <strong>Borrador:</strong>
                            todavía estás preparando el curso.
                        </p>

                        <p class="mt-1">
                            <strong>Publicado:</strong>
                            el curso está disponible.
                        </p>

                        <p class="mt-1">
                            <strong>Oculto:</strong>
                            el curso deja de mostrarse temporalmente.
                        </p>

                    </div>


                    @error('estado')

                        <p
                            class="
                                mt-2
                                text-sm
                                text-red-600
                            "
                        >
                            {{ $message }}
                        </p>

                    @enderror

                </div>

            @endif

        </div>

    </x-ui.card>


    <div
        class="
            flex flex-col-reverse gap-3
            sm:flex-row
            sm:justify-end
        "
    >

        <a
            href="{{
                route(
                    'professor.courses.index'
                )
            }}"
            class="
                inline-flex
                items-center
                justify-center
                rounded-lg
                border border-slate-300
                bg-white
                px-4 py-2.5
                text-sm font-semibold
                text-slate-700
                transition
                hover:bg-slate-50
            "
        >
            Cancelar
        </a>


        <button
            type="submit"
            data-submit-button
            class="
                inline-flex
                items-center
                justify-center
                gap-2
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
                    fa-floppy-disk
                "
                data-submit-icon
            ></i>


            <span data-submit-text>
                {{ $submitText }}
            </span>

        </button>

    </div>

</form>