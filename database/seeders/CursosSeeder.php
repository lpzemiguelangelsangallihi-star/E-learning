<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CursosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cursos = [
            ['Matemáticas 5to de secundaria', 'Matemáticas', 'prof.mat1@elearning.test', 'basico',
                'Funciones, trigonometría, geometría analítica y logaritmos.',
                'Resolver problemas con funciones, razones trigonométricas, rectas, circunferencias y logaritmos.'],
            ['Matemáticas 6to de secundaria', 'Matemáticas', 'prof.mat2@elearning.test', 'intermedio, avanzado',
                'Límites, derivadas, estadística, cónicas y progresiones.',
                'Calcular límites y derivadas básicas, aplicar probabilidad y reconocer cónicas y progresiones.'],

            ['Física 5to de secundaria', 'Física', 'prof.fis1@elearning.test', 'basico',
                'Cinemática, dinámica, trabajo y energía, y fluidos.',
                'Describir el movimiento, aplicar las leyes de Newton y resolver problemas de energía y fluidos.'],
            ['Física 6to de secundaria', 'Física', 'prof.fis2@elearning.test', 'intermedio',
                'Electricidad, magnetismo, ondas y óptica.',
                'Analizar circuitos eléctricos, campos magnéticos, ondas y fenómenos ópticos.'],

            ['Química 5to de secundaria', 'Química', 'prof.qui1@elearning.test', 'basico',
                'Estructura atómica, enlace químico, nomenclatura y estequiometría.',
                'Interpretar la tabla periódica, nombrar compuestos inorgánicos y balancear reacciones.'],
            ['Química 6to de secundaria', 'Química', 'prof.qui2@elearning.test', 'avanzado',
                'Soluciones, ácidos y bases, química orgánica y equilibrio.',
                'Calcular concentraciones y pH, reconocer compuestos orgánicos y analizar equilibrios.'],
        ];

        foreach ($cursos as [$titulo, $materia, $profesor, $nivel, $descripcion, $objetivos]) {
            DB::table('cursos')->updateOrInsert(
                ['titulo' => $titulo],
                [
                    'categoria_id' => DB::table('categorias')->where('nombre', 'Secundaria')->value('id'),
                    'materia_id' => DB::table('materias')->where('nombre', $materia)->value('id'),
                    'profesor_id' => DB::table('users')->where('email', $profesor)->value('id'),
                    'descripcion' => $descripcion,
                    'objetivos' => $objetivos,
                    'nivel' => $nivel,
                    'duracion' => 40,
                    'estado' => 'publicado',
                ]
            );
        }
    }
}
