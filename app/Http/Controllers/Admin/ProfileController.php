<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Perfil
    |--------------------------------------------------------------------------
    */

    public function edit(Request $request)
    {
        $user = $request->user();

        return view(
            'admin.profile.edit',
            compact('user')
        );
    }


    public function update(Request $request)
    {
        $user = $request->user();


        $validated = $request->validate([
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
                'email',
                'max:150',

                Rule::unique(
                    'users',
                    'email'
                )->ignore($user->id),
            ],
        ]);


        $user->update([
            'nombre' =>
                trim($validated['nombre']),

            'apellido_paterno' =>
                trim($validated['apellido_paterno']),

            'apellido_materno' =>
                trim($validated['apellido_materno']),

            'email' =>
                mb_strtolower(
                    trim($validated['email'])
                ),
        ]);


        return back()->with(
            'success',
            'Perfil actualizado correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Configuración
    |--------------------------------------------------------------------------
    */

    public function settings(Request $request)
    {
        $user = $request
            ->user()
            ->load('rol');


        return view(
            'admin.settings.index',
            compact('user')
        );
    }


    public function updatePassword(Request $request)
    {
        $user = $request->user();


        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'confirmed',

                Password::min(8)
                    ->letters()
                    ->numbers(),
            ],
        ]);


        if (!Hash::check(
            $validated['current_password'],
            $user->password
        )) {

            return back()
                ->withErrors([
                    'current_password' =>
                        'La contraseña actual no es correcta.',
                ])
                ->withInput();
        }


        $user->update([
            'password' =>
                Hash::make(
                    $validated['password']
                ),
        ]);


        return back()->with(
            'success',
            'Contraseña actualizada correctamente.'
        );
    }
}