<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->updateOrInsert(
            ['nombre' => 'administrador'],
            ['descripcion' => 'Administrador del sistema']
        );

        DB::table('roles')->updateOrInsert(
            ['nombre' => 'profesor'],
            ['descripcion' => 'Profesor de la plataforma']
        );

        DB::table('roles')->updateOrInsert(
            ['nombre' => 'estudiante'],
            ['descripcion' => 'Estudiante de la plataforma']
        );
    }
}