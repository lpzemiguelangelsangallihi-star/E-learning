<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $canonicalNames = [
                'matematicas' => 'Matemáticas',
                'fisica' => 'Física',
                'quimica' => 'Química',
            ];

            $materias = DB::table('materias')
                ->orderBy('id')
                ->get(['id', 'nombre']);

            $keptIds = [];
            $hasCoursesTable = Schema::hasTable('cursos');

            foreach ($materias as $materia) {
                $normalizedName = Str::lower(
                    Str::ascii(trim($materia->nombre))
                );

                if (!array_key_exists($normalizedName, $keptIds)) {
                    $keptIds[$normalizedName] = $materia->id;

                    DB::table('materias')
                        ->where('id', $materia->id)
                        ->update([
                            'nombre' =>
                                $canonicalNames[$normalizedName]
                                ?? trim($materia->nombre),
                        ]);

                    continue;
                }

                $keptId = $keptIds[$normalizedName];

                if ($hasCoursesTable) {
                    DB::table('cursos')
                        ->where('materia_id', $materia->id)
                        ->update(['materia_id' => $keptId]);
                }

                DB::table('materias')
                    ->where('id', $materia->id)
                    ->delete();
            }
        });

        Schema::table('materias', function (Blueprint $table) {
            $table->unique('nombre', 'materias_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->dropUnique('materias_nombre_unique');
        });
    }
};
