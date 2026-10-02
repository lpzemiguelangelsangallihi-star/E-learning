<?php

namespace App\Actions\Fortify;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validar y crear un nuevo estudiante.
     *
     * @param array<string, string> $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'nombre' => [
                'required',
                'string',
                'max:100',
            ],

            'apellido_paterno' => [
                'required',
                'string',
                'max:100',
            ],

            'apellido_materno' => [
                'required',
                'string',
                'max:100',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:150',
                'unique:users,email',
            ],

            'password' => $this->passwordRules(),

            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature()
                ? ['accepted', 'required']
                : '',
        ], [
            'nombre.required' =>
                'El nombre es obligatorio.',

            'apellido_paterno.required' =>
                'El apellido paterno es obligatorio.',

            'apellido_materno.required' =>
                'El apellido materno es obligatorio.',

            'email.required' =>
                'El correo electrónico es obligatorio.',

            'email.email' =>
                'Ingresa un correo electrónico válido.',

            'email.unique' =>
                'Este correo electrónico ya está registrado.',

            'password.required' =>
                'La contraseña es obligatoria.',

            'password.confirmed' =>
                'Las contraseñas no coinciden.',
        ])->validate();


        /*
         * El registro público siempre crea estudiantes.
         *
         * No usamos un ID fijo porque podría cambiar
         * entre bases de datos.
         */
        $rolEstudiante = Rol::query()
            ->whereRaw('LOWER(nombre) = ?', ['estudiante'])
            ->first();


        if (!$rolEstudiante) {
            throw ValidationException::withMessages([
                'registro' =>
                    'No se encontró el rol Estudiante. '
                    .'Contacta al administrador.',
            ]);
        }


        return User::create([
            'rol_id' => $rolEstudiante->id,

            'nombre' => trim($input['nombre']),

            'apellido_paterno' =>
                trim($input['apellido_paterno']),

            'apellido_materno' =>
                trim($input['apellido_materno']),

            'email' =>
                mb_strtolower(trim($input['email'])),

            'password' =>
                Hash::make($input['password']),

            'estado' => 'activo',
        ]);
    }
}