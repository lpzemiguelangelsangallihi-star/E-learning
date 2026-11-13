<?php

namespace App\Livewire\Professor;

use App\Models\IopAsignacionRefuerzo;
use App\Services\IOP\EntregaRefuerzoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class RevisionRefuerzosIOP extends Component
{
    use WithPagination;

    public ?int $asignacionId = null;

    public string $retroalimentacion = '';

    public string $filtro = 'en_revision';

    public function updatedFiltro(): void
    {
        $this->resetPage();
        $this->cerrarDetalle();
    }

    /**
     * Consulta únicamente asignaciones de los
     * cursos que pertenecen al profesor.
     */
    private function consultarAsignaciones()
    {
        return IopAsignacionRefuerzo::query()
            ->whereHas(
                'recomendacion.leccion.modulo.curso',
                fn ($query) => $query->where(
                    'profesor_id',
                    Auth::id()
                )
            );
    }

    /**
     * Seleccionar una entrega para revisión.
     */
    public function verDetalle(int $id): void
    {
        $asignacion = $this->consultarAsignaciones()
            ->whereKey($id)
            ->firstOrFail();

        $this->asignacionId = $asignacion->id;

        $this->retroalimentacion = '';

        $this->resetErrorBag();
    }

    public function cerrarDetalle(): void
    {
        $this->asignacionId = null;
        $this->retroalimentacion = '';

        $this->resetErrorBag();
    }

    /**
     * Aprobar una entrega.
     */
    public function aprobar(
        EntregaRefuerzoService $servicio
    ): void {
        $this->procesarRevision(
            $servicio,
            true
        );
    }

    /**
     * Solicitar correcciones al estudiante.
     */
    public function solicitarCorreccion(
        EntregaRefuerzoService $servicio
    ): void {
        $this->procesarRevision(
            $servicio,
            false
        );
    }

    private function procesarRevision(
        EntregaRefuerzoService $servicio,
        bool $aprobado
    ): void {
        $profesor = Auth::user();

        abort_unless(
            $profesor &&
            $profesor->rol?->nombre === 'profesor',
            403
        );

        if (!$this->asignacionId) {
            return;
        }

        $datos = $this->validate([
            'retroalimentacion' => [
                $aprobado ? 'nullable' : 'required',
                'string',
                'max:5000',
            ],
        ], [
            'retroalimentacion.required' =>
                'Debes explicar qué debe corregir el estudiante.',
            'retroalimentacion.max' =>
                'La retroalimentación es demasiado extensa.',
        ]);

        // Verificación adicional de propiedad del curso.
        $asignacion = $this->consultarAsignaciones()
            ->whereKey($this->asignacionId)
            ->firstOrFail();

        try {
            $servicio->revisar(
                (int) $asignacion->id,
                $profesor,
                $aprobado,
                $datos['retroalimentacion'] ?? ''
            );
        } catch (ValidationException $e) {
            throw $e;
        }

        session()->flash(
            'mensaje_revision',
            $aprobado
                ? 'Ejercicio aprobado correctamente.'
                : 'Se solicitaron correcciones al estudiante.'
        );

        $this->cerrarDetalle();
    }

    public function render()
    {
        $permitidos = [
            'en_revision',
            'completado',
            'requiere_correccion',
            'todos',
        ];

        $filtro = in_array(
            $this->filtro,
            $permitidos,
            true
        )
            ? $this->filtro
            : 'en_revision';

        $consulta = $this->consultarAsignaciones()
            ->with([
                'recomendacion.estudiante',
                'recomendacion.leccion.modulo.curso',
                'ejercicio',
                'revisor',
            ]);

        if ($filtro !== 'todos') {
            $consulta->where('estado', $filtro);
        }

        $asignaciones = $consulta
            ->orderByDesc('entregado_en')
            ->orderByDesc('id')
            ->paginate(10);

        $detalle = null;

        if ($this->asignacionId) {
            $detalle = $this->consultarAsignaciones()
                ->with([
                    'recomendacion.estudiante',
                    'recomendacion.leccion.modulo.curso',
                    'ejercicio',
                ])
                ->whereKey($this->asignacionId)
                ->first();

            if (!$detalle) {
                $this->cerrarDetalle();
            }
        }

        return view(
            'livewire.professor.revision-refuerzos-i-o-p',
            [
                'asignaciones' => $asignaciones,
                'detalle' => $detalle,
            ]
        );
    }
}
