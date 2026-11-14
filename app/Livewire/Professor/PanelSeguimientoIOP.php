<?php

namespace App\Livewire\Professor;

use App\Models\IopSeguimiento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PanelSeguimientoIOP extends Component
{
    use WithPagination;

    public string $filtro = 'todos';
    public string $busqueda = '';

    public function mount(): void
    {
        $this->autorizarProfesor();
    }

    public function hydrate(): void
    {
        $this->autorizarProfesor();
    }

    private function autorizarProfesor(): void
    {
        abort_unless(
            Auth::user()?->rol?->nombre === 'profesor',
            403
        );
    }

    public function updatedFiltro(): void
    {
        $this->resetPage();
    }

    public function updatedBusqueda(): void
    {
        $this->resetPage();
    }

    /**
     * El profesor únicamente puede consultar
     * seguimientos de sus propios cursos.
     */
    private function consultaBase(): Builder
    {
        return IopSeguimiento::query()
            ->whereNotNull('intento_diagnostico_id')
            ->whereHas(
                'leccion.modulo.curso',
                fn ($query) => $query->where(
                    'profesor_id',
                    Auth::id()
                )
            );
    }

    public function render()
    {
        $this->autorizarProfesor();

        $base = $this->consultaBase();

        $estadisticas = [
            'total' => (clone $base)->count(),

            'en_proceso' => (clone $base)
                ->where('activo', true)
                ->count(),

            'consolidados' => (clone $base)
                ->where('estado_actual', 'S4')
                ->where('activo', false)
                ->count(),

            'en_revision' => (clone $base)
                ->whereHas(
                    'ciclosRefuerzo.recomendacion.asignacionRefuerzo',
                    fn ($query) => $query->where(
                        'estado',
                        'en_revision'
                    )
                )
                ->count(),
        ];

        $consulta = $this->consultaBase();

        // Los filtros de progreso se aplican únicamente
        // a la lista; las estadísticas son globales
        // dentro de los cursos del profesor.
        if ($this->filtro === 'activos') {
            $consulta->where('activo', true);
        } elseif ($this->filtro === 'consolidados') {
            $consulta->where('estado_actual', 'S4')
                ->where('activo', false);
        } elseif ($this->filtro === 'en_revision') {
            $consulta->whereHas(
                'ciclosRefuerzo.recomendacion.asignacionRefuerzo',
                fn ($query) => $query->where(
                    'estado',
                    'en_revision'
                )
            );
        }

        $busqueda = trim($this->busqueda);

        if ($busqueda !== '') {
            $consulta->where(function ($query) use ($busqueda) {
                $query->whereHas(
                    'estudiante',
                    function ($estudiantes) use ($busqueda) {
                        $estudiantes->where(function ($q) use ($busqueda) {
                            $q->where(
                                'nombre',
                                'like',
                                "%{$busqueda}%"
                            )
                            ->orWhere(
                                'apellido_paterno',
                                'like',
                                "%{$busqueda}%"
                            )
                            ->orWhere(
                                'apellido_materno',
                                'like',
                                "%{$busqueda}%"
                            );
                        });
                    }
                )->orWhereHas(
                    'leccion',
                    fn ($q) => $q->where(
                        'titulo',
                        'like',
                        "%{$busqueda}%"
                    )
                );
            });
        }

        $seguimientos = $consulta
            ->with([
                'estudiante',
                'leccion.modulo.curso',
                'ciclosRefuerzo' => function ($query) {
                    $query->with([
                        'recomendacion.asignacionRefuerzo.ejercicio',
                    ]);
                },
            ])
            ->orderByDesc('id')
            ->paginate(10);

        return view(
            'livewire.professor.panel-seguimiento-i-o-p',
            [
                'seguimientos' => $seguimientos,
                'estadisticas' => $estadisticas,
            ]
        );
    }
}
