<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Professor\DashboardController as ProfessorDashboardController;
use App\Http\Controllers\Professor\CourseController as ProfessorCourseController;

use App\Http\Controllers\Admin\CourseController as AdminCourseController;


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
| Dashboard general después del login
|--------------------------------------------------------------------------
|
| Jetstream redirige aquí.
| Según el rol, enviamos al usuario a su módulo.
|
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])
    ->get('/dashboard', function () {

        $user = auth()->user();

        return match ($user->rol?->nombre) {

            'administrador' =>
                redirect()->route('admin.dashboard'),

            'profesor' =>
                redirect()->route('professor.dashboard'),

            'estudiante' =>
                redirect()->route('student.dashboard'),

            default =>
                abort(403),
        };

    })
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| ADMINISTRADOR
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:administrador',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            [DashboardController::class, 'index']
        )->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Usuarios
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Cursos
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/cursos',
            [AdminCourseController::class, 'index']
        )->name('courses.index');


        Route::patch(
            '/cursos/{curso}/visibilidad',
            [AdminCourseController::class, 'toggleVisibility']
        )->name('courses.toggle-visibility');


        Route::delete(
            '/cursos/{curso}',
            [AdminCourseController::class, 'destroy']
        )->name('courses.destroy');


        /*
        |--------------------------------------------------------------------------
        | Estadísticas
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/estadisticas',
            [StatisticsController::class, 'index']
        )->name('statistics.index');


        /*
        |--------------------------------------------------------------------------
        | Perfil
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/perfil',
            [ProfileController::class, 'edit']
        )->name('profile.edit');


        Route::put(
            '/perfil',
            [ProfileController::class, 'update']
        )->name('profile.update');


        /*
        |--------------------------------------------------------------------------
        | Configuración
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/configuracion',
            [ProfileController::class, 'settings']
        )->name('settings.index');


        Route::put(
            '/configuracion/password',
            [ProfileController::class, 'updatePassword']
        )->name('settings.password');

    });


/*
|--------------------------------------------------------------------------
| PROFESOR
|--------------------------------------------------------------------------
|
| Por ahora dejamos solo una ruta básica.
| Después construiremos:
|
| - Dashboard
| - Mis cursos
| - Aulas
| - Contenido
| - Evaluaciones
| - Resultados
| - Estadísticas
|
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:profesor',
])
    ->prefix('profesor')
    ->name('professor.')
    ->group(function () {

        Route::get(
            '/dashboard',
            [ProfessorDashboardController::class, 'index']
        )->name('dashboard');


        Route::get(
            '/cursos',
            [ProfessorCourseController::class, 'index']
        )->name('courses.index');


        Route::get(
            '/cursos/crear',
            [ProfessorCourseController::class, 'create']
        )->name('courses.create');


        Route::post(
            '/cursos',
            [ProfessorCourseController::class, 'store']
        )->name('courses.store');


        Route::get(
            '/cursos/{curso}/editar',
            [ProfessorCourseController::class, 'edit']
        )->name('courses.edit');


        Route::put(
            '/cursos/{curso}',
            [ProfessorCourseController::class, 'update']
        )->name('courses.update');


        Route::delete(
            '/cursos/{curso}',
            [ProfessorCourseController::class, 'destroy']
        )->name('courses.destroy');

    });


/*
|--------------------------------------------------------------------------
| ESTUDIANTE
|--------------------------------------------------------------------------
|
| Lo dejamos mínimo por ahora.
| Después retomaremos:
|
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
            '/dashboard',
            function () {
                return view('student.dashboard');
            }
        )->name('dashboard');

    });