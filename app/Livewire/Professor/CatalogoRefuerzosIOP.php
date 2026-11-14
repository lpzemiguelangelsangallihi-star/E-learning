<?php

namespace App\Livewire\Professor;

use App\Models\IopEjercicioRefuerzo;
use App\Models\Leccion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CatalogoRefuerzosIOP extends Component
{
    #[Locked]
    public int $leccionId;

    #[Locked]
    public ?int $ejercicioId = null;

    public string $titulo = '';
    public string $descripcion = '';
    public string $tipo = 'practico';
    public string $instrucciones = '';
    public string $urlRecurso = '';
    public int $orden = 1;

    public bool $mostrarFormulario = false;

    public function mount(int $leccionId): void
    {
        $this->leccionId = $leccionId;
        $this->autorizarLeccion();
    }

    public function hydrate(): void
    {
        $this->autorizarLeccion();
    }

    /**
     * Comprueba que la lección pertenezca
     * a un curso del profesor autenticado.
     */
    private function autorizarLeccion(): Leccion
    {
        $profesor = Auth::user();

        abort_unless(
            $profesor &&
            $profesor->rol?->nombre === 'profesor',
            403
        );

        return Leccion::query()
            ->whereKey($this->leccionId)
            ->whereHas(
                'modulo.curso',
                fn ($q) => $q->where(
                    'profesor_id',
                    $profesor->id
                )
            )
            ->with('modulo.curso')
            ->firstOrFail();
    }

    private function obtenerEjercicio(int $id): IopEjercicioRefuerzo
    {
        $this->autorizarLeccion();

        return IopEjercicioRefuerzo::query()
            ->where('leccion_id', $this->leccionId)
            ->findOrFail($id);
    }

    private function tieneAsignaciones(
        IopEjercicioRefuerzo $ejercicio
    ): bool {
        return $ejercicio->asignaciones()->exists();
    }

    public function nuevo(): void
    {
        $this->autorizarLeccion();

        $this->limpiarFormulario();
        $this->orden = (
            (int) IopEjercicioRefuerzo::query()
                ->where('leccion_id', $this->leccionId)
                ->max('orden')
        ) + 1;

        $this->mostrarFormulario = true;
    }

    public function editar(int $id): void
    {
        $ejercicio = $this->obtenerEjercicio($id);

        if ($this->tieneAsignaciones($ejercicio)) {
            throw ValidationException::withMessages([
                'ejercicio' =>
                    'No se puede editar un ejercicio que ya fue asignado. '
                    .'Puedes desactivarlo y crear una nueva versión.',
            ]);
        }

        $this->limpiarFormulario();

        $this->ejercicioId = $ejercicio->id;
        $this->titulo = $ejercicio->titulo;
        $this->descripcion = $ejercicio->descripcion;
        $this->tipo = $ejercicio->tipo;
        $this->instrucciones = $ejercicio->instrucciones;
        $this->urlRecurso = $ejercicio->url_recurso ?? '';
        $this->orden = $ejercicio->orden;

        $this->mostrarFormulario = true;
    }

    public function guardar(): void
    {
        $this->autorizarLeccion();

        $datos = $this->validate([
            'titulo' => ['required', 'string', 'min:3', 'max:200'],
            'descripcion' => ['required', 'string', 'max:5000'],
            'tipo' => [
                'required',
                Rule::in(['teorico', 'practico', 'visual']),
            ],
            'instrucciones' => [
                'required',
                'string',
                'max:10000',
            ],
            'urlRecurso' => ['nullable', 'url', 'max:2048'],
            'orden' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        $valores = [
            'titulo' => trim($datos['titulo']),
            'descripcion' => trim($datos['descripcion']),
            'tipo' => $datos['tipo'],
            'instrucciones' => trim($datos['instrucciones']),
            'url_recurso' =>
                trim($datos['urlRecurso'] ?? '') ?: null,
            'orden' => $datos['orden'],
        ];

        if ($this->ejercicioId !== null) {
            $ejercicio = $this->obtenerEjercicio(
                $this->ejercicioId
            );

            // No modificar contenido que ya fue
            // entregado a un estudiante.
            if ($this->tieneAsignaciones($ejercicio)) {
                throw ValidationException::withMessages([
                    'ejercicio' =>
                        'Este ejercicio ya fue asignado. '
                        .'No se puede modificar su contenido.',
                ]);
            }

            $ejercicio->update($valores);

            $mensaje = 'Ejercicio actualizado correctamente.';
        } else {
            IopEjercicioRefuerzo::create([
                'leccion_id' => $this->leccionId,
                ...$valores,
                'activo' => true,
            ]);

            $mensaje = 'Ejercicio creado correctamente.';
        }

        $this->limpiarFormulario();

        session()->flash('mensaje_catalogo', $mensaje);
    }

    public function cambiarEstado(int $id): void
    {
        $ejercicio = $this->obtenerEjercicio($id);

        // Desactivar impide futuras asignaciones.
        // No elimina ejercicios ya entregados.
        $ejercicio->update([
            'activo' => !$ejercicio->activo,
        ]);

        session()->flash(
            'mensaje_catalogo',
            $ejercicio->activo
                ? 'Ejercicio activado.'
                : 'Ejercicio desactivado.'
        );
    }

    public function cancelar(): void
    {
        $this->limpiarFormulario();
    }

    private function limpiarFormulario(): void
    {
        $this->ejercicioId = null;
        $this->titulo = '';
        $this->descripcion = '';
        $this->tipo = 'practico';
        $this->instrucciones = '';
        $this->urlRecurso = '';
        $this->orden = 1;
        $this->mostrarFormulario = false;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $leccion = $this->autorizarLeccion();

        $ejercicios = IopEjercicioRefuerzo::query()
            ->where('leccion_id', $this->leccionId)
            ->withCount('asignaciones')
            ->orderBy('tipo')
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        return view(
            'livewire.professor.catalogo-refuerzos-i-o-p',
            compact('leccion', 'ejercicios')
        );
    }
}
