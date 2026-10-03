<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InsigniasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insignias = [
            ['nombre' => 'Primer curso', 'descripcion' => 'Te inscribiste a tu primer curso', 'icono' => 'insignia-primer-curso.png', 'condicion' => 'inscripcion_primer_curso'],
            ['nombre' => 'Constante', 'descripcion' => 'Estudiaste 7 días seguidos', 'icono' => 'insignia-constante.png', 'condicion' => 'racha_7_dias'],
            ['nombre' => 'Estudioso', 'descripcion' => 'Completaste 10 lecciones', 'icono' => 'insignia-estudioso.png', 'condicion' => 'lecciones_completadas_10'],
            ['nombre' => 'Examen perfecto', 'descripcion' => 'Obtuviste 100 en un examen', 'icono' => 'insignia-examen-perfecto.png', 'condicion' => 'examen_100'],
        ];

        foreach ($insignias as $insignia) {
            DB::table('insignias')->updateOrInsert(
                ['nombre' => $insignia['nombre']],
                [
                    'descripcion' => $insignia['descripcion'],
                    'icono' => $insignia['icono'],
                    'condicion' => $insignia['condicion'],
                ]
            );
        }
    }
}
