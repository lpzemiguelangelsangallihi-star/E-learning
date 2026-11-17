<?php

namespace App\Support;

class EstadosIOP
{
    public const NOMBRES = [
        'S1' => 'Refuerzo inicial',
        'S2' => 'En desarrollo',
        'S3' => 'Rendimiento satisfactorio',
        'S4' => 'Aprendizaje consolidado',
    ];

    public static function nombre(?string $estado): string
    {
        return self::NOMBRES[$estado ?? '']
            ?? 'Estado no disponible';
    }
}
