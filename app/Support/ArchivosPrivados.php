<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

/**
 * Genera enlaces temporales y firmados para archivos privados.
 *
 * La firma impide alterar la URL o compartirla indefinidamente; la
 * autorización del controlador sigue siendo la que decide quién puede ver
 * el archivo.
 */
final class ArchivosPrivados
{
    public const MINUTOS_VIGENCIA = 30;

    public static function url(string $nombreRuta, string $parametro, Model $modelo): string
    {
        return URL::temporarySignedRoute(
            $nombreRuta,
            now()->addMinutes(self::MINUTOS_VIGENCIA),
            [$parametro => $modelo]
        );
    }
}
