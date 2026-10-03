<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

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
            redirect('/profesor/dashboard'),

        'estudiante' =>
            view('student.dashboard'),

        default =>
            abort(403),

    };

})->name('dashboard');

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

    });