<?php

namespace App\Livewire\Professor;

use App\Models\Examen;
use App\Models\OpcionPregunta;
use App\Models\Pregunta;
use App\Services\BitacoraService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Constructor de exámenes del profesor: datos del examen, preguntas,
 * opciones, banco (importar preguntas de otros exámenes) y publicación.
 */
class ConstructorExamen extends Component
{
    public const TIPOS = [
        'opcion_multiple' => 'Opción múltiple (una correcta)',
        'varias_respuestas' => 'Selección múltiple (varias correctas)',
        'verdadero_falso' => 'Verdadero / Falso',
        'respuesta_corta' => 'Respuesta corta',
    ];

    #[Locked]
    public int $examenId;

    // Datos del examen
    public string $titulo = '';
    public string $descripcion = '';
    public $tiempo_limite = null;
    public $intentos_permitidos = 1;
    public $puntaje_maximo = 100;
    public bool $mostrar_correctas = true;

    // Formulario de pregunta
    public bool $formAbierto = false;
    public ?int $editandoId = null;
    public string $q_tipo = 'opcion_multiple';
    public string $q_texto = '';
    public $q_puntaje = 1;
    public array $q_opciones = [];
    public string $q_vf = 'verdadero';

    // Banco de preguntas
    public $bancoExamenId = '';
    public array $bancoSeleccion = [];

    public string $mensaje = '';
    public string $error = '';

    public function mount(int $examenId): void
    {
        $this->examenId = $examenId;

        $examen = $this->cargar();

        $this->titulo = $examen->titulo;
        $this->descripcion = (string) $examen->descripcion;
        $this->tiempo_limite = $examen->tiempo_limite;
        $this->intentos_permitidos = $examen->intentos_permitidos;
        $this->puntaje_maximo = rtrim(rtrim((string) $examen->puntaje_maximo, '0'), '.');
        $this->mostrar_correctas = (bool) ($examen->mostrar_correctas ?? true);
    }

    /* ---------------------------------------------------------------
     | Utilidades
     * ------------------------------------------------------------- */

    private function cargar(): Examen
    {
        $examen = Examen::with('leccion.modulo.curso')->findOrFail($this->examenId);

        Gate::authorize('manage', $examen);

        return $examen;
    }

    private function limpiarMensajes(): void
    {
        $this->mensaje = '';
        $this->error = '';
        $this->resetErrorBag();
    }

    private function tieneIntentos(Examen $examen): bool
    {
        return $examen->intentos()->exists();
    }

    /** Las preguntas no se tocan si ya existen intentos (se conserva el historial). */
    private function bloqueado(Examen $examen): bool
    {
        if ($this->tieneIntentos($examen)) {
            $this->error = 'Este examen ya tiene intentos de estudiantes; sus preguntas no se pueden modificar.';

            return true;
        }

        return false;
    }

    /** Devuelve un texto con el problema de la pregunta o null si está bien. */
    private function problemaPregunta(Pregunta $pregunta): ?string
    {
        $opciones = $pregunta->opciones;
        $correctas = $opciones->where('es_correcta', true)->count();

        return match ($pregunta->tipo) {
            'opcion_multiple' => ($opciones->count() < 2 || $correctas !== 1)
                ? 'necesita al menos 2 opciones y exactamente 1 correcta' : null,
            'varias_respuestas' => ($opciones->count() < 2 || $correctas < 1)
                ? 'necesita al menos 2 opciones y 1 o más correctas' : null,
            'verdadero_falso' => ($opciones->count() !== 2 || $correctas !== 1)
                ? 'necesita las opciones Verdadero/Falso con una correcta' : null,
            'respuesta_corta' => $correctas < 1
                ? 'necesita al menos una respuesta aceptada' : null,
            default => 'tiene un tipo desconocido',
        };
    }

    /* ---------------------------------------------------------------
     | Datos del examen
     * ------------------------------------------------------------- */

    public function guardarExamen(): void
    {
        $this->limpiarMensajes();
        $examen = $this->cargar();

        $this->validate([
            'titulo' => 'required|string|min:3|max:200',
            'descripcion' => 'nullable|string|max:2000',
            'tiempo_limite' => 'nullable|integer|min:1|max:600',
            'intentos_permitidos' => 'required|integer|min:1|max:20',
            'puntaje_maximo' => 'required|numeric|min:1|max:999',
        ], [], [
            'titulo' => 'título',
            'tiempo_limite' => 'tiempo límite',
            'intentos_permitidos' => 'intentos permitidos',
            'puntaje_maximo' => 'puntaje máximo',
        ]);

        $examen->forceFill([
            'titulo' => trim($this->titulo),
            'descripcion' => trim($this->descripcion) !== '' ? trim($this->descripcion) : null,
            'tiempo_limite' => ($this->tiempo_limite === null || $this->tiempo_limite === '') ? null : (int) $this->tiempo_limite,
            'intentos_permitidos' => (int) $this->intentos_permitidos,
            'puntaje_maximo' => (float) $this->puntaje_maximo,
            'mostrar_correctas' => $this->mostrar_correctas,
        ])->save();

        BitacoraService::registrar("Actualizó los datos del examen «{$examen->titulo}»");

        $this->mensaje = 'Datos del examen guardados.';
    }

    public function cambiarEstado(string $nuevo): void
    {
        $this->limpiarMensajes();
        $examen = $this->cargar();

        if (! in_array($nuevo, ['borrador', 'publicado', 'cerrado'], true)) {
            return;
        }

        if ($nuevo === 'publicado') {
            $preguntas = $examen->preguntas()->with('opciones')->get();

            if ($preguntas->isEmpty()) {
                $this->error = 'Agrega al menos una pregunta antes de publicar.';

                return;
            }

            foreach ($preguntas->values() as $i => $pregunta) {
                $problema = $this->problemaPregunta($pregunta);

                if ($problema) {
                    $this->error = 'La pregunta '.($i + 1).' '.$problema.'.';

                    return;
                }
            }
        }

        if ($nuevo === 'borrador' && $this->tieneIntentos($examen)) {
            $this->error = 'No puedes volver a borrador un examen que ya tiene intentos. Ciérralo en su lugar.';

            return;
        }

        $examen->estado = $nuevo;
        $examen->save();

        BitacoraService::registrar("Cambió el examen «{$examen->titulo}» a estado {$nuevo}");

        $this->mensaje = match ($nuevo) {
            'publicado' => 'Examen publicado: los estudiantes inscritos ya pueden resolverlo.',
            'cerrado' => 'Examen cerrado: ya no recibe nuevos intentos.',
            default => 'Examen devuelto a borrador.',
        };
    }

    /* ---------------------------------------------------------------
     | Formulario de pregunta
     * ------------------------------------------------------------- */

    private function opcionesVacias(int $n = 2): array
    {
        return array_fill(0, $n, ['texto' => '', 'correcta' => false]);
    }

    public function nuevaPregunta(): void
    {
        $this->limpiarMensajes();

        if ($this->bloqueado($this->cargar())) {
            return;
        }

        $this->editandoId = null;
        $this->q_tipo = 'opcion_multiple';
        $this->q_texto = '';
        $this->q_puntaje = 1;
        $this->q_vf = 'verdadero';
        $this->q_opciones = $this->opcionesVacias(4);
        $this->formAbierto = true;
    }

    public function cancelarPregunta(): void
    {
        $this->limpiarMensajes();
        $this->formAbierto = false;
        $this->editandoId = null;
    }

    public function updatedQTipo(): void
    {
        $this->resetErrorBag();

        $this->q_opciones = match ($this->q_tipo) {
            'verdadero_falso' => [],
            'respuesta_corta' => [['texto' => '', 'correcta' => true]],
            default => $this->opcionesVacias(4),
        };
    }

    public function agregarOpcion(): void
    {
        if (count($this->q_opciones) >= 8) {
            return;
        }

        $this->q_opciones[] = [
            'texto' => '',
            'correcta' => $this->q_tipo === 'respuesta_corta',
        ];
    }

    public function quitarOpcion(int $indice): void
    {
        $minimo = $this->q_tipo === 'respuesta_corta' ? 1 : 2;

        if (count($this->q_opciones) <= $minimo) {
            return;
        }

        unset($this->q_opciones[$indice]);
        $this->q_opciones = array_values($this->q_opciones);
    }

    /** En opción múltiple solo una opción puede ser la correcta. */
    public function marcarCorrecta(int $indice): void
    {
        foreach ($this->q_opciones as $i => $opcion) {
            $this->q_opciones[$i]['correcta'] = ($i === $indice);
        }
    }

    public function editarPregunta(int $id): void
    {
        $this->limpiarMensajes();
        $examen = $this->cargar();

        if ($this->bloqueado($examen)) {
            return;
        }

        $pregunta = Pregunta::with('opciones')
            ->where('examen_id', $examen->id)
            ->findOrFail($id);

        $this->editandoId = $pregunta->id;
        $this->q_tipo = $pregunta->tipo;
        $this->q_texto = $pregunta->pregunta;
        $this->q_puntaje = rtrim(rtrim((string) $pregunta->puntaje, '0'), '.');
        $this->q_vf = 'verdadero';
        $this->q_opciones = [];

        if ($pregunta->tipo === 'verdadero_falso') {
            $correcta = $pregunta->opciones->firstWhere('es_correcta', true);
            $this->q_vf = mb_strtolower((string) $correcta?->opcion) === 'falso' ? 'falso' : 'verdadero';
        } else {
            $this->q_opciones = $pregunta->opciones
                ->map(fn ($o) => ['texto' => $o->opcion, 'correcta' => (bool) $o->es_correcta])
                ->values()
                ->all();
        }

        $this->formAbierto = true;
    }

    public function guardarPregunta(): void
    {
        $this->limpiarMensajes();
        $examen = $this->cargar();

        if ($this->bloqueado($examen)) {
            return;
        }

        $this->validate([
            'q_tipo' => 'required|in:opcion_multiple,varias_respuestas,verdadero_falso,respuesta_corta',
            'q_texto' => 'required|string|min:3|max:2000',
            'q_puntaje' => 'required|numeric|min:0.01|max:100',
        ], [], [
            'q_texto' => 'enunciado',
            'q_puntaje' => 'puntaje',
        ]);

        // Construye las opciones según el tipo.
        $opciones = [];

        if ($this->q_tipo === 'verdadero_falso') {
            $opciones = [
                ['opcion' => 'Verdadero', 'es_correcta' => $this->q_vf === 'verdadero'],
                ['opcion' => 'Falso', 'es_correcta' => $this->q_vf === 'falso'],
            ];
        } else {
            foreach ($this->q_opciones as $o) {
                $texto = trim((string) ($o['texto'] ?? ''));

                if ($texto === '') {
                    continue;
                }

                $opciones[] = [
                    'opcion' => mb_substr($texto, 0, 500),
                    'es_correcta' => $this->q_tipo === 'respuesta_corta' ? true : (bool) ($o['correcta'] ?? false),
                ];
            }

            $correctas = collect($opciones)->where('es_correcta', true)->count();

            if ($this->q_tipo === 'respuesta_corta') {
                if (count($opciones) < 1) {
                    $this->addError('q_opciones', 'Escribe al menos una respuesta aceptada.');

                    return;
                }
            } else {
                if (count($opciones) < 2) {
                    $this->addError('q_opciones', 'Escribe al menos 2 opciones.');

                    return;
                }

                if ($this->q_tipo === 'opcion_multiple' && $correctas !== 1) {
                    $this->addError('q_opciones', 'Marca exactamente una opción como correcta.');

                    return;
                }

                if ($this->q_tipo === 'varias_respuestas' && $correctas < 1) {
                    $this->addError('q_opciones', 'Marca al menos una opción correcta.');

                    return;
                }
            }
        }

        DB::transaction(function () use ($examen, $opciones) {
            $datos = [
                'pregunta' => trim($this->q_texto),
                'tipo' => $this->q_tipo,
                'puntaje' => (float) $this->q_puntaje,
            ];

            if ($this->editandoId) {
                $pregunta = Pregunta::where('examen_id', $examen->id)->findOrFail($this->editandoId);
                $pregunta->update($datos);
                $pregunta->opciones()->delete();
            } else {
                $pregunta = Pregunta::create($datos + [
                    'examen_id' => $examen->id,
                    'orden' => (int) $examen->preguntas()->max('orden') + 1,
                ]);
            }

            foreach ($opciones as $opcion) {
                OpcionPregunta::create($opcion + ['pregunta_id' => $pregunta->id]);
            }
        });

        $this->formAbierto = false;
        $this->editandoId = null;
        $this->mensaje = 'Pregunta guardada.';
    }

    public function eliminarPregunta(int $id): void
    {
        $this->limpiarMensajes();
        $examen = $this->cargar();

        if ($this->bloqueado($examen)) {
            return;
        }

        DB::transaction(function () use ($examen, $id) {
            $pregunta = Pregunta::where('examen_id', $examen->id)->findOrFail($id);
            $pregunta->opciones()->delete();
            $pregunta->delete();

            $this->renumerar($examen);
        });

        $this->mensaje = 'Pregunta eliminada.';
    }

    public function duplicarPregunta(int $id): void
    {
        $this->limpiarMensajes();
        $examen = $this->cargar();

        if ($this->bloqueado($examen)) {
            return;
        }

        $origen = Pregunta::with('opciones')->where('examen_id', $examen->id)->findOrFail($id);

        $this->copiarPregunta($origen, $examen);

        $this->mensaje = 'Pregunta duplicada.';
    }

    public function mover(int $id, string $direccion): void
    {
        $this->limpiarMensajes();
        $examen = $this->cargar();

        if ($this->bloqueado($examen)) {
            return;
        }

        $lista = $examen->preguntas()->orderBy('orden')->orderBy('id')->get()->values();
        $i = $lista->search(fn ($p) => (int) $p->id === $id);

        if ($i === false) {
            return;
        }

        $j = $direccion === 'up' ? $i - 1 : $i + 1;

        if ($j < 0 || $j >= $lista->count()) {
            return;
        }

        $arreglo = $lista->all();
        [$arreglo[$i], $arreglo[$j]] = [$arreglo[$j], $arreglo[$i]];

        foreach (array_values($arreglo) as $k => $pregunta) {
            if ((int) $pregunta->orden !== $k + 1) {
                $pregunta->orden = $k + 1;
                $pregunta->save();
            }
        }
    }

    private function renumerar(Examen $examen): void
    {
        $examen->preguntas()->orderBy('orden')->orderBy('id')->get()
            ->values()
            ->each(function ($pregunta, $k) {
                if ((int) $pregunta->orden !== $k + 1) {
                    $pregunta->orden = $k + 1;
                    $pregunta->save();
                }
            });
    }

    private function copiarPregunta(Pregunta $origen, Examen $destino): void
    {
        DB::transaction(function () use ($origen, $destino) {
            $nueva = Pregunta::create([
                'examen_id' => $destino->id,
                'pregunta' => $origen->pregunta,
                'tipo' => $origen->tipo,
                'puntaje' => $origen->puntaje,
                'orden' => (int) $destino->preguntas()->max('orden') + 1,
            ]);

            foreach ($origen->opciones as $opcion) {
                OpcionPregunta::create([
                    'pregunta_id' => $nueva->id,
                    'opcion' => $opcion->opcion,
                    'es_correcta' => $opcion->es_correcta,
                ]);
            }
        });
    }

    /* ---------------------------------------------------------------
     | Banco de preguntas (importar desde otros exámenes del profesor)
     * ------------------------------------------------------------- */

    public function updatedBancoExamenId(): void
    {
        $this->bancoSeleccion = [];
    }

    public function importarDelBanco(): void
    {
        $this->limpiarMensajes();
        $examen = $this->cargar();

        if ($this->bloqueado($examen)) {
            return;
        }

        if (empty($this->bancoSeleccion) || ! $this->bancoExamenId) {
            $this->error = 'Selecciona al menos una pregunta del banco.';

            return;
        }

        $origen = Examen::with('leccion.modulo.curso')->findOrFail((int) $this->bancoExamenId);
        Gate::authorize('manage', $origen);

        $preguntas = Pregunta::with('opciones')
            ->where('examen_id', $origen->id)
            ->whereIn('id', array_map('intval', $this->bancoSeleccion))
            ->orderBy('orden')
            ->get();

        foreach ($preguntas as $pregunta) {
            $this->copiarPregunta($pregunta, $examen);
        }

        $this->bancoSeleccion = [];
        $this->mensaje = $preguntas->count().' pregunta(s) importada(s) del banco.';
    }

    /* ---------------------------------------------------------------
     | Render
     * ------------------------------------------------------------- */

    public function render()
    {
        $examen = $this->cargar();

        $preguntas = $examen->preguntas()
            ->with('opciones')
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        $otros = Examen::query()
            ->where('id', '!=', $examen->id)
            ->whereHas('leccion.modulo.curso', fn ($q) => $q->where('profesor_id', $examen->leccion->modulo->curso->profesor_id))
            ->withCount('preguntas')
            ->orderBy('titulo')
            ->get()
            ->filter(fn ($e) => $e->preguntas_count > 0)
            ->values();

        $bancoPreguntas = $this->bancoExamenId
            ? Pregunta::where('examen_id', (int) $this->bancoExamenId)
                ->whereIn('examen_id', $otros->pluck('id'))
                ->orderBy('orden')
                ->get()
            : collect();

        return view('livewire.professor.constructor-examen', [
            'examen' => $examen,
            'preguntas' => $preguntas,
            'bloqueado' => $this->tieneIntentos($examen),
            'totalPuntos' => $preguntas->sum(fn ($p) => (float) $p->puntaje),
            'otrosExamenes' => $otros,
            'bancoPreguntas' => $bancoPreguntas,
            'tipos' => self::TIPOS,
        ]);
    }
}
