<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Código de acceso que el estudiante usa para inscribirse a un curso.
     */
    public function up(): void
    {
        // Si la columna ya existe (migración anterior del mismo cambio), no se repite.
        $yaExiste = Schema::hasColumn('cursos', 'codigo_acceso');

        if (!$yaExiste) {
            Schema::table('cursos', function (Blueprint $table) {
                $table->string('codigo_acceso', 10)
                    ->nullable()
                    ->after('titulo');
            });
        }

        // Los cursos que ya existen reciben su código.
        DB::table('cursos')
            ->whereNull('codigo_acceso')
            ->pluck('id')
            ->each(function ($id) {
                do {
                    $codigo = strtoupper(Str::random(8));
                } while (
                    DB::table('cursos')
                        ->where('codigo_acceso', $codigo)
                        ->exists()
                );

                DB::table('cursos')
                    ->where('id', $id)
                    ->update(['codigo_acceso' => $codigo]);
            });

        if (!$yaExiste) {
            Schema::table('cursos', function (Blueprint $table) {
                $table->unique('codigo_acceso', 'uk_cursos_codigo_acceso');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cursos', 'codigo_acceso')) {
            Schema::table('cursos', function (Blueprint $table) {
                $table->dropUnique('uk_cursos_codigo_acceso');
                $table->dropColumn('codigo_acceso');
            });
        }
    }
};
