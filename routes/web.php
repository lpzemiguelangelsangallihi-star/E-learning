<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;

use App\Http\Controllers\Student\CourseController;


/*
|--------------------------------------------------------------------------
| Página pública
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});


/*
|--------------------------------------------------------------------------
| Dashboard general
|--------------------------------------------------------------------------
|
| Jetstream redirige aquí después del login.
| Dependiendo del rol enviamos al usuario a su panel.
|
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->get('/dashboard', function () {

    $user = auth()->user();

    return match ($user->rol?->nombre) {

        'administrador' =>
            redirect()->route('admin.dashboard'),

        'profesor' =>
            redirect()->route('professor.dashboard'),

        'estudiante' =>
            view('student.dashboard'),

        default =>
            abort(403),
    };

})->name('dashboard');


/*
|--------------------------------------------------------------------------
| ESTUDIANTE
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:estudiante',
])
    ->prefix('estudiante')
    ->name('student.')
    ->group(function () {

        Route::get(
            '/mis-cursos',
            [CourseController::class, 'index']
        )->name('courses');

    });




Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:administrador',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    

        Route::get(
            '/dashboard',
            [DashboardController::class, 'index']
        )->name('dashboard');


        Route::get(
            '/usuarios',
            [UserController::class, 'index']
        )->name('users.index');


        Route::get(
            '/usuarios/crear',
            [UserController::class, 'create']
        )->name('users.create');


        Route::post(
            '/usuarios',
            [UserController::class, 'store']
        )->name('users.store');


        Route::get(
            '/usuarios/{user}/editar',
            [UserController::class, 'edit']
        )->name('users.edit');


        Route::put(
            '/usuarios/{user}',
            [UserController::class, 'update']
        )->name('users.update');


        Route::patch(
            '/usuarios/{user}/estado',
            [UserController::class, 'toggleStatus']
        )->name('users.toggle-status');


        Route::delete(
            '/usuarios/{user}',
            [UserController::class, 'destroy']
        )->name('users.destroy');

    });




Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:profesor',
])
    ->prefix('profesor')
    ->name('professor.')
    ->group(function () {

        Route::get('/dashboard', function () {

            return view('professor.dashboard');

        })->name('dashboard');

    });