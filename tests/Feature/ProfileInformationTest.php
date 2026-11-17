<?php

use App\Models\User;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm;
use Livewire\Livewire;

test('current profile information is available', function () {

    $this->actingAs($user = User::factory()->create());

    $component = Livewire::test(UpdateProfileInformationForm::class);

    expect($component->state['nombre'])
        ->toEqual($user->nombre);

    expect($component->state['apellido_paterno'])
        ->toEqual($user->apellido_paterno);

    expect($component->state['apellido_materno'])
        ->toEqual($user->apellido_materno);

    expect($component->state['email'])
        ->toEqual($user->email);
});

test('profile information can be updated', function () {

    $this->actingAs($user = User::factory()->create());

    Livewire::test(UpdateProfileInformationForm::class)
        ->set('state.nombre', 'Usuario')
        ->set('state.apellido_paterno', 'Actualizado')
        ->set('state.apellido_materno', 'Prueba')
        ->set('state.email', 'test@example.com')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    $actualizado = $user->fresh();

    expect($actualizado->nombre)
        ->toBe('Usuario');

    expect($actualizado->apellido_paterno)
        ->toBe('Actualizado');

    expect($actualizado->apellido_materno)
        ->toBe('Prueba');

    expect($actualizado->email)
        ->toBe('test@example.com');
});
