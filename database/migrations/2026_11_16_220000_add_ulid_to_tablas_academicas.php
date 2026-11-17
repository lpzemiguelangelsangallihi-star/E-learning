<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Identificadores públicos para los recursos académicos.
 *
 * Las llaves primarias numéricas se conservan para las relaciones internas;
 * la columna `ulid` es la que se expone en las rutas para evitar que los
 * identificadores se puedan enumerar.
 */
return new class extends Migration
{
    /** @var array<int, string> */
    private const TABLAS = [
        'cursos',
        'modulos',
        'lecciones',
        'materiales',
        'videos',
        'tareas',
        'examenes',
        'intentos_examen',
        'entregas',
    ];

    public function up(): void
    {
        foreach (self::TABLAS as $tabla) {
            if (! Schema::hasTable($tabla) || Schema::hasColumn($tabla, 'ulid')) {
                continue;
            }

            Schema::table($tabla, function (Blueprint $table) {
                $table->ulid('ulid')->nullable()->unique()->after('id');
            });
        }

        $this->rellenarUlids();
    }

    public function down(): void
    {
        foreach (array_reverse(self::TABLAS) as $tabla) {
            if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, 'ulid')) {
                continue;
            }

            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                $table->dropUnique("{$tabla}_ulid_unique");
                $table->dropColumn('ulid');
            });
        }
    }

    private function rellenarUlids(): void
    {
        foreach (self::TABLAS as $tabla) {
            if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, 'ulid')) {
                continue;
            }

            DB::table($tabla)
                ->whereNull('ulid')
                ->orderBy('id')
                ->chunkById(500, function ($filas) use ($tabla) {
                    foreach ($filas as $fila) {
                        DB::table($tabla)
                            ->where('id', $fila->id)
                            ->update(['ulid' => (string) Str::ulid()]);
                    }
                }, 'id', 'id');
        }
    }
};
