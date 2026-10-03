<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categorias')->updateOrInsert(
            ['nombre' => 'Secundaria'],
            [
                'descripcion' => 'Cursos de 5to y 6to de secundaria',
                'estado' => 'activo',
            ]
        );
    }
}
