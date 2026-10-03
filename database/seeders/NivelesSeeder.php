<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NivelesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $niveles = [
            ['nombre' => 'Novato', 'minima' => 0, 'maxima' => 15, 'descripcion' => 'Recién comienzas tu camino'],
            ['nombre' => 'Aprendiz', 'minima' => 16, 'maxima' => 29, 'descripcion' => 'Ya dominas lo básico'],
            ['nombre' => 'Intermedio', 'minima' => 30, 'maxima' => 59, 'descripcion' => 'Avanzas con constancia'],
            ['nombre' => 'Avanzado', 'minima' => 60, 'maxima' => 90, 'descripcion' => 'Tienes un gran dominio'],
            ['nombre' => 'Experto', 'minima' => 91, 'maxima' => 100, 'descripcion' => 'Nivel máximo de la plataforma'],
        ];

        foreach ($niveles as $nivel) {
            DB::table('niveles')->updateOrInsert(
                ['nombre' => $nivel['nombre']],
                [
                    'experiencia_minima' => $nivel['minima'],
                    'experiencia_maxima' => $nivel['maxima'],
                    'descripcion' => $nivel['descripcion'],
                ]
            );
        }
    }
}
