<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ALFABETO = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function up(): void
    {
        if (! Schema::hasColumn('cursos', 'codigo_clase')) {
            Schema::table('cursos', function (Blueprint $table) {
                $table->string('codigo_clase', 12)->nullable()->after('titulo');
            });
        }

        // Cursos que ya existían reciben su código.
        $usados = DB::table('cursos')->whereNotNull('codigo_clase')->pluck('codigo_clase')->all();

        foreach (DB::table('cursos')->whereNull('codigo_clase')->pluck('id') as $id) {
            do {
                $codigo = '';
                for ($i = 0; $i < 8; $i++) {
                    $codigo .= self::ALFABETO[random_int(0, strlen(self::ALFABETO) - 1)];
                }
            } while (in_array($codigo, $usados, true));

            $usados[] = $codigo;
            DB::table('cursos')->where('id', $id)->update(['codigo_clase' => $codigo]);
        }

        Schema::table('cursos', function (Blueprint $table) {
            $table->unique('codigo_clase', 'uk_cursos_codigo_clase');
        });
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropUnique('uk_cursos_codigo_clase');
            $table->dropColumn('codigo_clase');
        });
    }
};
