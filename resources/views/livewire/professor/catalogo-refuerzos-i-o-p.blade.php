
<div class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-4">

        <div>
            <h2 class="text-xl font-bold text-gray-900">
                Catálogo de ejercicios IOP
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Lección: {{ $leccion->titulo }}
            </p>

            <p class="text-xs text-gray-500">
                Curso: {{ $leccion->modulo->curso->titulo }}
            </p>
        </div>

        <button
            type="button"
            wire:click="nuevo"
            class="rounded-md bg-blue-600 px-4 py-2
                   text-sm font-semibold text-white
                   hover:bg-blue-700"
        >
            Nuevo ejercicio
        </button>
    </div>

    @if (session()->has('mensaje_catalogo'))
        <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">
            {{ session('mensaje_catalogo') }}
        </div>
    @endif

    @error('ejercicio')
        <div class="rounded-md bg-red-50 p-4 text-sm text-red-700">
            {{ $message }}
        </div>
    @enderror

    {{-- FORMULARIO DE CREACIÓN / EDICIÓN --}}

    @if ($mostrarFormulario)

        <form wire:submit="guardar"
              class="space-y-4 rounded-lg border bg-white p-5">

            <h3 class="text-lg font-semibold">
                {{ $ejercicioId ? 'Editar ejercicio' : 'Crear ejercicio' }}
            </h3>

            <div>
                <label for="iop-titulo"
                       class="block text-sm font-medium">
                    Título
                </label>

                <input
                    id="iop-titulo"
                    type="text"
                    wire:model="titulo"
                    maxlength="200"
                    class="mt-1 block w-full rounded-md border-gray-300"
                >

                @error('titulo')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label for="iop-tipo"
                       class="block text-sm font-medium">
                    Tipo de refuerzo
                </label>

                <select
                    id="iop-tipo"
                    wire:model="tipo"
                    class="mt-1 block w-full rounded-md border-gray-300"
                >
                    <option value="teorico">Teórico</option>
                    <option value="practico">Práctico</option>
                    <option value="visual">Visual</option>
                </select>

                @error('tipo')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label for="iop-descripcion"
                       class="block text-sm font-medium">
                    Descripción
                </label>

                <textarea
                    id="iop-descripcion"
                    wire:model="descripcion"
                    rows="3"
                    maxlength="5000"
                    class="mt-1 block w-full rounded-md border-gray-300"
                ></textarea>

                @error('descripcion')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label for="iop-instrucciones"
                       class="block text-sm font-medium">
                    Instrucciones para el estudiante
                </label>

                <textarea
                    id="iop-instrucciones"
                    wire:model="instrucciones"
                    rows="5"
                    maxlength="10000"
                    class="mt-1 block w-full rounded-md border-gray-300"
                ></textarea>

                @error('instrucciones')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label for="iop-recurso"
                       class="block text-sm font-medium">
                    URL del recurso educativo (opcional)
                </label>

                <input
                    id="iop-recurso"
                    type="url"
                    wire:model="urlRecurso"
                    placeholder="https://..."
                    class="mt-1 block w-full rounded-md border-gray-300"
                >

                @error('urlRecurso')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label for="iop-orden"
                       class="block text-sm font-medium">
                    Orden de prioridad
                </label>

                <input
                    id="iop-orden"
                    type="number"
                    min="1"
                    max="100000"
                    wire:model="orden"
                    class="mt-1 block w-40 rounded-md border-gray-300"
                >

                <p class="mt-1 text-xs text-gray-500">
                    Maximin seleccionará primero el ejercicio activo
                    con menor número de orden dentro de cada categoría.
                </p>

                @error('orden')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="flex flex-wrap gap-3">

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="guardar"
                    class="rounded-md bg-blue-600 px-5 py-2
                           text-sm font-semibold text-white
                           hover:bg-blue-700 disabled:opacity-50"
                >
                    Guardar ejercicio
                </button>

                <button
                    type="button"
                    wire:click="cancelar"
                    class="rounded-md border px-5 py-2
                           text-sm font-medium text-gray-700"
                >
                    Cancelar
                </button>

            </div>

        </form>

    @endif

    {{-- LISTADO DE EJERCICIOS --}}

    <div class="space-y-4">

        @forelse ($ejercicios as $ejercicio)

            <div
                wire:key="catalogo-iop-{{ $ejercicio->id }}"
                class="rounded-lg border bg-white p-5"
            >

                <div class="flex flex-wrap items-start
                            justify-between gap-3">

                    <div>
                        <h3 class="font-semibold text-gray-900">
                            {{ $ejercicio->titulo }}
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Tipo: {{ ucfirst($ejercicio->tipo) }}
                            · Orden: {{ $ejercicio->orden }}
                        </p>
                    </div>

                    <span class="rounded-full px-3 py-1 text-xs
                                 font-semibold
                                 {{ $ejercicio->activo
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-gray-100 text-gray-600' }}">
                        {{ $ejercicio->activo ? 'Activo' : 'Inactivo' }}
                    </span>

                </div>

                <p class="mt-3 whitespace-pre-line text-sm text-gray-700">
                    {{ $ejercicio->descripcion }}
                </p>

                <p class="mt-3 text-xs text-gray-500">
                    Asignaciones registradas:
                    {{ $ejercicio->asignaciones_count }}
                </p>

                <div class="mt-4 flex flex-wrap gap-3">

                    @if ($ejercicio->asignaciones_count === 0)

                        <button
                            type="button"
                            wire:click="editar({{ $ejercicio->id }})"
                            class="rounded-md border px-4 py-2
                                   text-sm font-medium text-blue-700"
                        >
                            Editar
                        </button>

                    @else

                        <span class="self-center text-xs text-gray-500">
                            Contenido bloqueado por asignaciones existentes
                        </span>

                    @endif

                    <button
                        type="button"
                        wire:click="cambiarEstado({{ $ejercicio->id }})"
                        wire:loading.attr="disabled"
                        wire:target="cambiarEstado"
                        class="rounded-md border px-4 py-2
                               text-sm font-medium text-gray-700"
                    >
                        {{ $ejercicio->activo
                            ? 'Desactivar'
                            : 'Activar' }}
                    </button>

                </div>

            </div>

        @empty

            <div class="rounded-lg border bg-white p-6 text-center">

                <p class="font-semibold text-gray-700">
                    Aún no hay ejercicios registrados
                </p>

                <p class="mt-2 text-sm text-gray-500">
                    Crea ejercicios teóricos, prácticos y visuales
                    para que Maximin pueda asignarlos.
                </p>

            </div>

        @endforelse

    </div>

</div>
