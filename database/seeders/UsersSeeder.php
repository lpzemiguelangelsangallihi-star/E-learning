<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // [rol, nombre, apellido paterno, apellido materno, email]
        $users = [
            ['administrador', 'Admin', 'Sistema', 'Demo', 'admin@elearning.test'],

            // Matemáticas
            ['profesor', 'Carlos', 'Mendoza', 'Rojas', 'prof.mat1@elearning.test'],
            ['profesor', 'Lucía', 'Fernández', 'Quispe', 'prof.mat2@elearning.test'],
            ['estudiante', 'Ana', 'Morales', 'Vargas', 'est.mat1@elearning.test'],
            ['estudiante', 'Jorge', 'Salazar', 'Mamani', 'est.mat2@elearning.test'],
            ['estudiante', 'María', 'Herrera', 'Choque', 'est.mat3@elearning.test'],

            // Física
            ['profesor', 'Roberto', 'Gutiérrez', 'Alanoca', 'prof.fis1@elearning.test'],
            ['profesor', 'Patricia', 'Vargas', 'Limachi', 'prof.fis2@elearning.test'],
            ['estudiante', 'Diego', 'Rivera', 'Flores', 'est.fis1@elearning.test'],
            ['estudiante', 'Sofía', 'Paredes', 'Condori', 'est.fis2@elearning.test'],
            ['estudiante', 'Luis', 'Cárdenas', 'Ticona', 'est.fis3@elearning.test'],

            // Química
            ['profesor', 'Marcela', 'Ortiz', 'Copa', 'prof.qui1@elearning.test'],
            ['profesor', 'Hugo', 'Delgado', 'Apaza', 'prof.qui2@elearning.test'],
            ['estudiante', 'Valeria', 'Rojas', 'Huanca', 'est.qui1@elearning.test'],
            ['estudiante', 'Andrés', 'Quispe', 'Nina', 'est.qui2@elearning.test'],
            ['estudiante', 'Camila', 'Torrez', 'Aliaga', 'est.qui3@elearning.test'],
        ];

        foreach ($users as [$rol, $nombre, $paterno, $materno, $email]) {
            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'rol_id' => DB::table('roles')->where('nombre', $rol)->value('id'),
                    'nombre' => $nombre,
                    'apellido_paterno' => $paterno,
                    'apellido_materno' => $materno,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'estado' => 'activo',
                ]
            );
        }
    }
}
