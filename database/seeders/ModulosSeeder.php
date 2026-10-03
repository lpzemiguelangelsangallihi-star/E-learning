<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modulos = [
            'Matemáticas 5to de secundaria' => [
                'Funciones', 'Trigonometría', 'Geometría analítica', 'Exponenciales y logaritmos',
            ],
            'Matemáticas 6to de secundaria' => [
                'Límites y derivadas', 'Estadística y probabilidad',
            ],
            'Física 5to de secundaria' => [
                'Cinemática', 'Dinámica', 'Trabajo y energía',
            ],
            'Física 6to de secundaria' => [
                'Electricidad', 'Magnetismo', 'Ondas y sonido',
            ],
            'Química 5to de secundaria' => [
                'Estructura atómica y tabla periódica', 'Nomenclatura inorgánica',
            ],
            'Química 6to de secundaria' => [
                'Ácidos y Gases', 'Química orgánica', 'Termodinamica', 'Termoquimica',
            ],
        ];

        foreach ($modulos as $curso => $titulos) {
            $cursoId = DB::table('cursos')->where('titulo', $curso)->value('id');

            foreach ($titulos as $indice => $titulo) {
                DB::table('modulos')->updateOrInsert(
                    ['curso_id' => $cursoId, 'titulo' => $titulo],
                    [
                        'descripcion' => 'Tema: ' . $titulo,
                        'orden' => $indice + 1,
                        'estado' => 'activo',
                    ]
                );
            }
        }
    }
}
