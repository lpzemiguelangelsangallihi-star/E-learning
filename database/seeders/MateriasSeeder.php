<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MateriasSeeder extends Seeder
{
    public function run(): void
    {
        $materias = [
            ['nombre' => 'Matemáticas',
               'descripcion' => 'Funciones, trigonometría, geometría analítica, cálculo y estadística',
            ],

            [
                'nombre' => 'Física',
                'descripcion' =>
                    'Mecánica, electricidad, magnetismo, ondas y óptica',
            ],

            [
                'nombre' => 'Química',
                'descripcion' =>
                    'Estructura de la materia, reacciones y química orgánica',
            ],
        ];

        foreach ($materias as $materia) {

            DB::table('materias')->updateOrInsert(
                [
                    'nombre' => $materia['nombre'],
                ],
                [
                    'descripcion' => $materia['descripcion'],
                    'estado' => 'activo',
                ]
            );

        }
    }
}