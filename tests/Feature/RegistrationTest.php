<?php

use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
})->skip(function () {
    return ! Features::enabled(Features::registration());
}, 'Registration support is not enabled.');

test('registration screen cannot be rendered if support is disabled', function () {
    $response = $this->get('/register');

    $response->assertStatus(404);
})->skip(function () {
    return Features::enabled(Features::registration());
}, 'Registration support is enabled.');

test('new users can register', function () {
    
\App\Models\Rol::firstOrCreate(
    ['nombre' => 'estudiante'],
    ['descripcion' => 'Rol estudiante']
);

$response = $this->post('/register', [
    'nombre' => 'Usuario',
    'apellido_paterno' => 'Prueba',
    'apellido_materno' => 'Sistema',
    'email' => 'test@example.com',
    'password' => 'password',
    'password_confirmation' => 'password',
    'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
]);


    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
})->skip(function () {
    return ! Features::enabled(Features::registration());
}, 'Registration support is not enabled.');
