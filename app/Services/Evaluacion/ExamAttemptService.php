<?php

namespace App\Services\Evaluacion;

use App\Models\Examen;
use App\Models\IntentoExamen;
use App\Models\Pregunta;
use App\Models\RespuestaEstudiante;
use App\Models\User;
use App\Services\IOP\IntegracionEvaluacionIOPService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Ciclo de vida de un intento: iniciar, guardar respuestas y entregar.
 * Evita dobles envíos y respeta el límite de intentos y el tiempo.
 */
class ExamAttemptService
{
    /** Segundos de tolerancia sobre el tiempo límite (latencia de red). */
    public const GRACIA_SEGUNDOS = 20;

    public function __construct(private ExamGradingService $calificador)
    {
    }

    public function limite(IntentoExamen $intento): ?Carbon
    {
        $minutos = $intento->examen?->tiempo_limite;

        return $minutos
            ? $intento->fecha_inicio->copy()->addMinutes((int) $minutos)
            : null;
    }

    public function expirado(IntentoExamen $intento): bool
    {
        $limite = $this->limite($intento);

        return $limite !== null
            && now()->greaterThan($limite->copy()->addSeconds(self::GRACIA_SEGUNDOS));
    }

    /** Intentos que el estudiante ya consumió (en proceso o finalizados). */
    public function usados(Examen $examen, int $estudianteId): int
    {
        return IntentoExamen::query()
            ->where('examen_id', $examen->id)
            ->where('estudiante_id', $estudianteId)
            ->count();
    }

    /** Intento en proceso y vigente, si existe. */
    public function abierto(Examen $examen, int $estudianteId): ?IntentoExamen
    {
        $intento = IntentoExamen::query()
            ->where('examen_id', $examen->id)
            ->where('estudiante_id', $estudianteId)
            ->where('estado', 'en_proceso')
            ->orderByDesc('id')
            ->first();

        if ($intento && $this->expirado($intento)) {
            $this->finalizar($intento);

            return null;
        }

        return $intento;
    }

    /** Inicia un intento nuevo o retoma el que está en proceso. */
    public function iniciar(Examen $examen, User $usuario): IntentoExamen
    {
        return DB::transaction(function () use ($examen, $usuario) {
            // Serializa los inicios simultáneos del mismo estudiante.
            User::query()->whereKey($usuario->id)->lockForUpdate()->first();

            $examen->refresh();

            if ($examen->estado !== 'publicado') {
                throw new ExamException('Este examen no está disponible.');
            }

            if (! $examen->preguntas()->exists()) {
                throw new ExamException('Este examen aún no tiene preguntas.');
            }

            $abierto = $this->abierto($examen, (int) $usuario->id);

            if ($abierto) {
                return $abierto;
            }

            $usados = $this->usados($examen, (int) $usuario->id);

            if ($usados >= (int) $examen->intentos_permitidos) {
                throw new ExamException('Ya usaste todos tus intentos para este examen.');
            }

            $ultimo = (int) IntentoExamen::query()
                ->where('examen_id', $examen->id)
                ->where('estudiante_id', $usuario->id)
                ->max('numero_intento');

            return IntentoExamen::create([
                'examen_id' => $examen->id,
                'estudiante_id' => $usuario->id,
                'numero_intento' => $ultimo + 1,
                'fecha_inicio' => now(),
                'fecha_fin' => null,
                'calificacion' => null,
                'estado' => 'en_proceso',
            ]);
        });
    }

    /**
     * Guarda (o reemplaza) la respuesta a una pregunta.
     *
     * @param  mixed  $valor  id de opción, arreglo de ids (varias) o texto (corta)
     */
    public function guardarRespuesta(IntentoExamen $intento, Pregunta $pregunta, mixed $valor): void
    {
        if ((int) $pregunta->examen_id !== (int) $intento->examen_id) {
            throw new ExamException('La pregunta no pertenece a este examen.');
        }

        DB::transaction(function () use ($intento, $pregunta, $valor) {
            // Se bloquea el intento y se revalida su estado dentro de la transacción:
            // evita que un guardado tardío se cuele mientras se entrega el examen.
            $bloqueado = IntentoExamen::query()
                ->with('examen')
                ->whereKey($intento->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($bloqueado->estado !== 'en_proceso') {
                throw new ExamException('Este intento ya fue entregado.');
            }

            if ($this->expirado($bloqueado)) {
                throw new ExamException('El tiempo del examen terminó.');
            }

            $base = ['intento_id' => $intento->id, 'pregunta_id' => $pregunta->id];

            $validas = $pregunta->opciones()->pluck('id')->map(fn ($id) => (int) $id)->all();

            if ($pregunta->tipo === 'respuesta_corta') {
                $texto = Str::limit(trim((string) $valor), 500, '');

                RespuestaEstudiante::query()->where($base)->delete();

                if ($texto !== '') {
                    RespuestaEstudiante::create($base + [
                        'opcion_id' => null,
                        'respuesta_texto' => $texto,
                        'es_correcta' => null,
                        'puntaje_obtenido' => 0,
                    ]);
                }

                return;
            }

            $ids = collect(is_array($valor) ? $valor : [$valor])
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => in_array($id, $validas, true))
                ->unique()
                ->values();

            if ($pregunta->tipo !== 'varias_respuestas') {
                $ids = $ids->take(1);
            }

            RespuestaEstudiante::query()->where($base)->delete();

            foreach ($ids as $id) {
                RespuestaEstudiante::create($base + [
                    'opcion_id' => $id,
                    'respuesta_texto' => null,
                    'es_correcta' => null,
                    'puntaje_obtenido' => 0,
                ]);
            }
        });
    }

    /**
     * Entrega y corrige el intento. Es idempotente: si ya estaba entregado
     * devuelve el resultado existente (evita doble envío).
     */
    public function finalizar(IntentoExamen $intento): IntentoExamen
    {
        $recienFinalizado = false;

        $resultado = DB::transaction(function () use ($intento, &$recienFinalizado) {
            $bloqueado = IntentoExamen::query()
                ->whereKey($intento->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($bloqueado->estado === 'finalizado') {
                return $bloqueado;
            }

            $recienFinalizado = true;

            return $this->calificador->grade($bloqueado);
        });

        // Fuera de la transacción: un fallo de IOP nunca debe deshacer la entrega.
        if ($recienFinalizado) {
            $this->notificarIOP($resultado);
        }

        return $resultado;
    }

    /**
     * Según el tipo de evaluación IOP del examen:
     *  diagnostico → inicia el seguimiento; refuerzo → registra la transición;
     *  ninguno / ordinaria → no hace nada.
     */
    private function notificarIOP(IntentoExamen $intento): void
    {
        $tipo = (string) $intento->examen()->value('tipo_iop');

        if (! in_array($tipo, [Examen::IOP_DIAGNOSTICO, Examen::IOP_REFUERZO], true)) {
            return;
        }

        try {
            $iop = app(IntegracionEvaluacionIOPService::class);

            $tipo === Examen::IOP_DIAGNOSTICO
                ? $iop->procesarDiagnostico((int) $intento->id)
                : $iop->procesarSeguimiento((int) $intento->id);
        } catch (\Throwable $e) {
            Log::warning('IOP no procesó el intento de examen.', [
                'intento_id' => $intento->id,
                'tipo_iop' => $tipo,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Respuestas guardadas en formato apto para la interfaz:
     * [pregunta_id => id | [ids] | texto]
     */
    public function respuestasGuardadas(IntentoExamen $intento): array
    {
        $preguntas = Pregunta::query()
            ->where('examen_id', $intento->examen_id)
            ->get()
            ->keyBy('id');

        $resultado = [];

        $filas = RespuestaEstudiante::query()
            ->where('intento_id', $intento->id)
            ->get()
            ->groupBy('pregunta_id');

        foreach ($filas as $preguntaId => $items) {
            $pregunta = $preguntas->get($preguntaId);

            if (! $pregunta) {
                continue;
            }

            $resultado[$preguntaId] = match ($pregunta->tipo) {
                'respuesta_corta' => (string) $items->first()->respuesta_texto,
                'varias_respuestas' => $items->pluck('opcion_id')->filter()->map(fn ($id) => (string) $id)->values()->all(),
                default => $items->first()->opcion_id !== null ? (string) $items->first()->opcion_id : null,
            };
        }

        return $resultado;
    }
}
