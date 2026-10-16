<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'rol' => 'administrador',
                'nombre' => 'Admin',
                'apellido_paterno' => 'Sistema',
                'apellido_materno' => 'Demo',
                'email' => 'admin@elearning.test',
            ],

            [
                'rol' => 'profesor',
                'nombre' => 'Carlos',
                'apellido_paterno' => 'Mendoza',
                'apellido_materno' => 'Quispe',
                'email' => 'profesor@elearning.test',
            ],

            [
                'rol' => 'estudiante',
                'nombre' => 'Juan',
                'apellido_paterno' => 'Pérez',
                'apellido_materno' => 'Mamani',
                'email' => 'estudiante@elearning.test',
            ],
        ];


        foreach ($users as $user) {

            $rolId = DB::table('roles')
                ->where('nombre', $user['rol'])
                ->value('id');


            if (!$rolId) {
                throw new RuntimeException(
                    "No existe el rol {$user['rol']}."
                );
            }


            DB::table('users')->updateOrInsert(
                [
                    'email' => $user['email'],
                ],
                [
                    'rol_id' => $rolId,

                    'nombre' =>
                        $user['nombre'],

                    'apellido_paterno' =>
                        $user['apellido_paterno'],

                    'apellido_materno' =>
                        $user['apellido_materno'],

                    'password' =>
                        Hash::make('password'),

                    'email_verified_at' =>
                        now(),

                    'estado' =>
                        'activo',
                ]
            );
        }
    }
}