<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MateriasSeeder extends Seeder
{
    public function run(): void
    {
        $materias = [
            [
                'nombre' => 'Matemáticas',
                'descripcion' => 'Funciones, trigonometría, geometría analítica, cálculo y estadística',
                'estado' => 'activo',
            ],

            [
                'nombre' => 'Física',
                'descripcion' =>
                    'Mecánica, electricidad, magnetismo, ondas y óptica',
                'estado' => 'activo',
            ],

            [
                'nombre' => 'Química',
                'descripcion' =>
                    'Estructura de la materia, reacciones y química orgánica',
                'estado' => 'activo',
            ],
        ];

        $nombresFijos = array_column($materias, 'nombre');

        DB::table('materias')
            ->whereNotIn('nombre', $nombresFijos)
            ->update(['estado' => 'inactivo']);

        DB::table('materias')->upsert(
            $materias,
            ['nombre'],
            ['descripcion', 'estado']
        );
    }
}
