<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Añade un identificador público ULID y lo usa como llave de ruta.
 *
 * La llave primaria numérica sigue existiendo para las relaciones internas;
 * el ULID solo evita exponer identificadores secuenciales en las URLs.
 */
trait HasPublicUlid
{
    protected static function bootHasPublicUlid(): void
    {
        static::creating(function ($model) {
            if (blank($model->ulid)) {
                $model->ulid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}
