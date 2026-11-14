<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Contraseña de los usuarios de demostración
    |--------------------------------------------------------------------------
    |
    | UsersSeeder solo crea las cuentas demo cuando la aplicación está en
    | local/testing y esta variable tiene un valor. Así no se publica una
    | contraseña conocida en entornos reales.
    |
    */

    'password' => env('DEMO_USER_PASSWORD'),

];
