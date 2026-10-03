<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InscripcionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // [email del estudiante, curso, estado]
        $inscripciones = [
            ['est.mat1@elearning.test', 'Matemáticas 5to de secundaria', 'activo'],
            ['est.mat2@elearning.test', 'Matemáticas 5to de secundaria', 'activo'],
            ['est.mat3@elearning.test', 'Matemáticas 6to de secundaria', 'activo'],

            ['est.fis1@elearning.test', 'Física 5to de secundaria', 'activo'],
            ['est.fis2@elearning.test', 'Física 5to de secundaria', 'finalizado'],
            ['est.fis3@elearning.test', 'Física 6to de secundaria', 'activo'],

            ['est.qui1@elearning.test', 'Química 5to de secundaria', 'activo'],
            ['est.qui2@elearning.test', 'Química 5to de secundaria', 'activo'],
            ['est.qui3@elearning.test', 'Química 6to de secundaria', 'activo'],
        ];

        foreach ($inscripciones as [$email, $curso, $estado]) {
            DB::table('inscripciones')->updateOrInsert(
                [
                    'curso_id' => DB::table('cursos')->where('titulo', $curso)->value('id'),
                    'estudiante_id' => DB::table('users')->where('email', $email)->value('id'),
                ],
                ['estado' => $estado]
            );
        }
    }
}
