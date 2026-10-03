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
            ['profesor', 'Peter', 'Mendoza', 'Parker', 'peterparker15356@gmail.com'],
            ['profesor', 'Lucía', 'Fernández', 'Quispe', 'prof.mat2@elearning.test'],
            ['estudiante', 'Ana', 'Morales', 'Vargas', 'est.mat1@elearning.test'],
            ['estudiante', 'Israel', 'Choque', 'Carrillo', 'isra.ch0018@gmail.com'],
            ['estudiante', 'María', 'Herrera', 'Choque', 'est.mat3@elearning.test'],

            // Física
            ['profesor', 'Roberto', 'Gutiérrez', 'Alanoca', 'prof.fis1@elearning.test'],
            ['profesor', 'Miguel', 'Sangalli', 'Hilari', 'mickysangalli@gmail.com'],
            ['estudiante', 'Israel', 'Choque', 'Carrillo', 'isra.ch0018@gmail.com'],
            ['estudiante', 'Sofía', 'Paredes', 'Condori', 'est.fis2@elearning.test'],
            ['estudiante', 'Luis', 'Cárdenas', 'Ticona', 'est.fis3@elearning.test'],

            // Química
            ['profesor', 'Marcela', 'Ortiz', 'Copa', 'prof.qui1@elearning.test'],
            ['profesor', 'Oscar', 'Savedra', 'Zambrana', 'zambranasaavedraoscar@gmail.com'],
            ['estudiante', 'Valeria', 'Rojas', 'Huanca', 'est.qui1@elearning.test'],
            ['estudiante', 'Andrés', 'Quispe', 'Nina', 'est.qui2@elearning.test'],
            ['estudiante','Israel', 'Choque', 'Carrillo', 'isra.ch0018@gmail.com'],
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
