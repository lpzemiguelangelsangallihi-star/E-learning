<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeccionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lecciones = [
            'Matemáticas 5to de secundaria' => [
                'Funciones' => ['Función lineal', 'Función cuadrática'],
                'Trigonometría' => ['Razones trigonométricas', 'Identidades trigonométricas'],
                'Geometría analítica' => ['La recta', 'La circunferencia'],
                'Exponenciales y logaritmos' => ['Función exponencial', 'Propiedades de los logaritmos'],
            ],
            'Matemáticas 6to de secundaria' => [
                'Límites y derivadas' => ['Límite de una función', 'La derivada y sus reglas'],
                'Estadística y probabilidad' => ['Medidas de tendencia central y dispersión', 'Probabilidad condicional'],
            ],
            'Física 5to de secundaria' => [
                'Cinemática' => ['Movimiento rectilíneo uniforme', 'Movimiento variado y caída libre'],
                'Dinámica' => ['Leyes de Newton', 'Fuerza de rozamiento'],
                'Trabajo y energía' => ['Trabajo y potencia', 'Energía cinética y potencial'],
            ],
            'Física 6to de secundaria' => [
                'Electricidad' => ['Ley de Coulomb y campo eléctrico', 'Ley de Ohm y circuitos'],
                'Magnetismo' => ['Campo magnético', 'Inducción electromagnética'],
                'Ondas y sonido' => ['Características de las ondas', 'El sonido'],
            ],
            'Química 5to de secundaria' => [
                'Estructura atómica y tabla periódica' => ['Modelos atómicos y configuración electrónica', 'Propiedades periódicas'],
                'Nomenclatura inorgánica' => ['Óxidos, hidróxidos y ácidos', 'Sales'],
            ],
            'Química 6to de secundaria' => [
                'Ácidos y Gases' => ['Teorías de ácidos y Gases', 'pH y neutralización'],
                'Química orgánica' => ['Hidrocarburos', 'Grupos funcionales'],
                'Termodinamica' => ['Introduccion a la termodinamica'],
                'Termoquimica' => ['Calor y trabajo'],
            ],
        ];

        foreach ($lecciones as $curso => $modulos) {
            $cursoId = DB::table('cursos')->where('titulo', $curso)->value('id');

            foreach ($modulos as $modulo => $titulos) {
                $moduloId = DB::table('modulos')
                    ->where('curso_id', $cursoId)
                    ->where('titulo', $modulo)
                    ->value('id');

                foreach ($titulos as $indice => $titulo) {
                    DB::table('lecciones')->updateOrInsert(
                        ['modulo_id' => $moduloId, 'titulo' => $titulo],
                        [
                            'descripcion' => 'Lección: ' . $titulo,
                            'contenido' => 'Contenido de ejemplo de la lección "' . $titulo . '".',
                            'orden' => $indice + 1,
                            'estado' => 'publicado',
                        ]
                    );
                }
            }
        }
    }
}
