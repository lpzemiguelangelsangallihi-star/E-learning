<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MateriasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materias = [
            'Matemáticas' => 'Funciones, trigonometría, geometría analítica, cálculo y estadística',
            'Física' => 'Mecánica, electricidad, magnetismo, ondas y óptica',
            'Química' => 'Estructura de la materia, reacciones, soluciones y química orgánica',
        ];

        foreach ($materias as $nombre => $descripcion) {
            DB::table('materias')->updateOrInsert(
                ['nombre' => $nombre],
                [
                    'descripcion' => $descripcion,
                    'estado' => 'activo',
                ]
            );
        }
    }
}
