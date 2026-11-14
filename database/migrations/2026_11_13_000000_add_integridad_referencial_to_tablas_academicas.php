<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Auditoría de integridad referencial de las tablas académicas.
 *
 * Antes de crear cada llave foránea se respaldan y eliminan los registros
 * huérfanos (filas que apuntan a un usuario, curso, lección, etc. inexistente).
 * El respaldo queda en la tabla "integridad_referencial_respaldo" y se
 * restaura automáticamente al revertir la migración.
 */
return new class extends Migration
{
    private const TABLA_RESPALDO = 'integridad_referencial_respaldo';

    /**
     * [tabla, columna, tabla_referenciada, acción_al_eliminar, referencia_a_users]
     *
     * @return array<int, array{0: string, 1: string, 2: string, 3: string, 4: bool}>
     */
    private function relaciones(): array
    {
        return [
            // Estructura académica.
            ['cursos', 'profesor_id', 'users', 'restrict', true],
            ['cursos', 'materia_id', 'materias', 'set null', false],
            ['modulos', 'curso_id', 'cursos', 'cascade', false],
            ['lecciones', 'modulo_id', 'modulos', 'cascade', false],
            ['videos', 'leccion_id', 'lecciones', 'cascade', false],
            ['materiales', 'leccion_id', 'lecciones', 'cascade', false],

            // Tareas y entregas.
            ['tareas', 'leccion_id', 'lecciones', 'cascade', false],
            ['tareas', 'profesor_id', 'users', 'restrict', true],
            ['entregas', 'tarea_id', 'tareas', 'cascade', false],
            ['entregas', 'estudiante_id', 'users', 'cascade', true],

            // Evaluaciones.
            ['examenes', 'leccion_id', 'lecciones', 'cascade', false],
            ['examenes', 'profesor_id', 'users', 'restrict', true],
            ['preguntas', 'examen_id', 'examenes', 'cascade', false],
            ['opciones_preguntas', 'pregunta_id', 'preguntas', 'cascade', false],
            ['intentos_examen', 'examen_id', 'examenes', 'cascade', false],
            ['intentos_examen', 'estudiante_id', 'users', 'cascade', true],
            ['respuestas_estudiante', 'intento_id', 'intentos_examen', 'cascade', false],
            ['respuestas_estudiante', 'pregunta_id', 'preguntas', 'cascade', false],
            ['respuestas_estudiante', 'opcion_id', 'opciones_preguntas', 'set null', false],
            ['calificaciones', 'estudiante_id', 'users', 'cascade', true],
            ['calificaciones', 'profesor_id', 'users', 'restrict', true],
            ['calificaciones', 'curso_id', 'cursos', 'cascade', false],
            ['calificaciones', 'tarea_id', 'tareas', 'set null', false],
            ['calificaciones', 'examen_id', 'examenes', 'set null', false],

            // Inscripciones y progreso.
            ['inscripciones', 'curso_id', 'cursos', 'cascade', false],
            ['inscripciones', 'estudiante_id', 'users', 'cascade', true],
            ['progreso_cursos', 'curso_id', 'cursos', 'cascade', false],
            ['progreso_cursos', 'estudiante_id', 'users', 'cascade', true],
            ['progreso_lecciones', 'leccion_id', 'lecciones', 'cascade', false],
            ['progreso_lecciones', 'estudiante_id', 'users', 'cascade', true],

            // Foros y mensajería.
            ['foros', 'curso_id', 'cursos', 'cascade', false],
            ['publicaciones_foro', 'foro_id', 'foros', 'cascade', false],
            ['publicaciones_foro', 'usuario_id', 'users', 'cascade', true],
            ['comentarios', 'publicacion_id', 'publicaciones_foro', 'cascade', false],
            ['comentarios', 'usuario_id', 'users', 'cascade', true],
            ['mensajes', 'remitente_id', 'users', 'cascade', true],
            ['mensajes', 'destinatario_id', 'users', 'cascade', true],

            // Datos personales del estudiante.
            ['notificaciones', 'usuario_id', 'users', 'cascade', true],
            ['rachas', 'estudiante_id', 'users', 'cascade', true],
            ['usuario_experiencia', 'estudiante_id', 'users', 'cascade', true],
            ['usuario_insignias', 'estudiante_id', 'users', 'cascade', true],
            ['usuario_insignias', 'insignia_id', 'insignias', 'cascade', false],
        ];
    }

    public function up(): void
    {
        $this->crearTablaRespaldo();
        $this->respaldarYEliminarHuerfanos();

        foreach ($this->relaciones() as [$tabla, $columna, $referencia, $alEliminar, $esUsuario]) {
            $this->agregarLlaveForanea($tabla, $columna, $referencia, $alEliminar, $esUsuario);
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->relaciones()) as [$tabla, $columna, $referencia, $alEliminar, $esUsuario]) {
            if (! Schema::hasTable($tabla) || ! $this->tieneLlaveForanea($tabla, $columna, $referencia)) {
                continue;
            }

            Schema::table($tabla, function (Blueprint $table) use ($columna) {
                $table->dropForeign([$columna]);
            });
        }

        // Las columnas que apuntan a users.id vuelven a ser INT con signo,
        // tal como estaban definidas originalmente.
        foreach ($this->relaciones() as [$tabla, $columna, $referencia, $alEliminar, $esUsuario]) {
            if (! $esUsuario || ! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, $columna)) {
                continue;
            }

            Schema::table($tabla, function (Blueprint $table) use ($columna) {
                $table->integer($columna)->change();
            });
        }

        $this->restaurarHuerfanos();

        Schema::dropIfExists(self::TABLA_RESPALDO);
    }

    private function crearTablaRespaldo(): void
    {
        if (Schema::hasTable(self::TABLA_RESPALDO)) {
            return;
        }

        Schema::create(self::TABLA_RESPALDO, function (Blueprint $table) {
            $table->id();
            $table->string('tabla', 64);
            $table->string('columna', 64);
            $table->unsignedBigInteger('registro_id');
            $table->string('referencia', 128);
            $table->json('datos');
            $table->timestamp('detectado_en')->useCurrent();
            $table->unique(['tabla', 'columna', 'registro_id'], 'uk_respaldo_huerfano');
        });
    }

    private function respaldarYEliminarHuerfanos(): void
    {
        foreach ($this->relaciones() as [$tabla, $columna, $referencia]) {
            if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, $columna)) {
                continue;
            }

            DB::table("{$tabla} as origen")
                ->leftJoin("{$referencia} as destino", "origen.{$columna}", '=', 'destino.id')
                ->whereNotNull("origen.{$columna}")
                ->whereNull('destino.id')
                ->select('origen.*')
                ->orderBy('origen.id')
                ->chunkById(500, function ($filas) use ($tabla, $columna, $referencia) {
                    $ids = [];

                    foreach ($filas as $fila) {
                        $ids[] = $fila->id;

                        DB::table(self::TABLA_RESPALDO)->insertOrIgnore([
                            'tabla' => $tabla,
                            'columna' => $columna,
                            'registro_id' => $fila->id,
                            'referencia' => "{$referencia}.id",
                            'datos' => json_encode($fila, JSON_THROW_ON_ERROR),
                            'detectado_en' => now(),
                        ]);
                    }

                    DB::table($tabla)->whereIn('id', $ids)->delete();
                }, 'origen.id', 'id');
        }
    }

    private function agregarLlaveForanea(
        string $tabla,
        string $columna,
        string $referencia,
        string $alEliminar,
        bool $esUsuario
    ): void {
        if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, $columna)) {
            return;
        }

        if ($this->tieneLlaveForanea($tabla, $columna, $referencia)) {
            return;
        }

        // users.id es BIGINT UNSIGNED; la columna debe tener exactamente el
        // mismo tipo para poder crear la llave foránea en MySQL/MariaDB.
        if ($esUsuario) {
            Schema::table($tabla, function (Blueprint $table) use ($columna) {
                $table->unsignedBigInteger($columna)->change();
            });
        }

        Schema::table($tabla, function (Blueprint $table) use ($columna, $referencia, $alEliminar) {
            $foreign = $table->foreign($columna)->references('id')->on($referencia);

            match ($alEliminar) {
                'cascade' => $foreign->cascadeOnDelete(),
                'set null' => $foreign->nullOnDelete(),
                'restrict' => $foreign->restrictOnDelete(),
                default => null,
            };
        });
    }

    private function tieneLlaveForanea(string $tabla, string $columna, string $referencia): bool
    {
        if (! Schema::hasTable($tabla)) {
            return false;
        }

        foreach (Schema::getForeignKeys($tabla) as $llave) {
            if (($llave['columns'] ?? []) === [$columna]
                && ($llave['foreign_table'] ?? null) === $referencia) {
                return true;
            }
        }

        return false;
    }

    private function restaurarHuerfanos(): void
    {
        if (! Schema::hasTable(self::TABLA_RESPALDO)) {
            return;
        }

        DB::table(self::TABLA_RESPALDO)
            ->orderBy('id')
            ->chunkById(500, function ($filas) {
                foreach ($filas as $fila) {
                    if (! Schema::hasTable($fila->tabla)) {
                        continue;
                    }

                    $datos = json_decode($fila->datos, true);

                    if (is_array($datos)) {
                        DB::table($fila->tabla)->insertOrIgnore($datos);
                    }
                }
            });
    }
};
