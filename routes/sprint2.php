<?php

/*
|--------------------------------------------------------------------------
| SPRINT 2 - Gestión académica
|--------------------------------------------------------------------------
| Se carga desde routes/web.php con:
|     require __DIR__.'/sprint2.php';
*/

use App\Http\Controllers\Professor\CourseContentController;
use App\Http\Controllers\Professor\LessonController;
use App\Http\Controllers\Professor\MaterialController;
use App\Http\Controllers\Professor\ModuleController;
use App\Http\Controllers\Student\CourseController as StudentCourseController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use Illuminate\Support\Facades\Route;

/*
| PROFESOR
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

        // Estructura del curso y código de clase
        Route::get('/cursos/{curso}/contenido', [CourseContentController::class, 'show'])
            ->name('courses.structure');

        Route::post('/cursos/{curso}/codigo', [CourseContentController::class, 'regenerateCode'])
            ->name('courses.regenerate-code');

        // Unidades
        Route::post('/cursos/{curso}/modulos', [ModuleController::class, 'store'])
            ->name('modules.store');

        Route::put('/cursos/{curso}/modulos/{modulo}', [ModuleController::class, 'update'])
            ->name('modules.update');

        Route::delete('/cursos/{curso}/modulos/{modulo}', [ModuleController::class, 'destroy'])
            ->name('modules.destroy');

        Route::post('/cursos/{curso}/modulos/{modulo}/mover', [ModuleController::class, 'move'])
            ->name('modules.move');

        // Temas
        Route::get('/cursos/{curso}/modulos/{modulo}/lecciones/crear', [LessonController::class, 'create'])
            ->name('lessons.create');

        Route::post('/cursos/{curso}/modulos/{modulo}/lecciones', [LessonController::class, 'store'])
            ->name('lessons.store');

        Route::get('/cursos/{curso}/modulos/{modulo}/lecciones/{leccion}/editar', [LessonController::class, 'edit'])
            ->name('lessons.edit');

        Route::put('/cursos/{curso}/modulos/{modulo}/lecciones/{leccion}', [LessonController::class, 'update'])
            ->name('lessons.update');

        Route::delete('/cursos/{curso}/modulos/{modulo}/lecciones/{leccion}', [LessonController::class, 'destroy'])
            ->name('lessons.destroy');

        Route::post('/cursos/{curso}/modulos/{modulo}/lecciones/{leccion}/mover', [LessonController::class, 'move'])
            ->name('lessons.move');

        Route::patch('/cursos/{curso}/modulos/{modulo}/lecciones/{leccion}/estado', [LessonController::class, 'toggleStatus'])
            ->name('lessons.toggle-status');

        // Materiales y videos de un tema
        Route::get('/cursos/{curso}/modulos/{modulo}/lecciones/{leccion}/materiales', [MaterialController::class, 'index'])
            ->name('materials.index');

        Route::post('/cursos/{curso}/modulos/{modulo}/lecciones/{leccion}/materiales', [MaterialController::class, 'store'])
            ->name('materials.store');

        Route::delete('/cursos/{curso}/modulos/{modulo}/lecciones/{leccion}/materiales/{material}', [MaterialController::class, 'destroyMaterial'])
            ->name('materials.destroy');

        Route::delete('/cursos/{curso}/modulos/{modulo}/lecciones/{leccion}/videos/{video}', [MaterialController::class, 'destroyVideo'])
            ->name('videos.destroy');
    });

/*
| ESTUDIANTE
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

        Route::get('/dashboard', [StudentDashboardController::class, 'index'])
            ->name('dashboard');

        Route::post('/cursos/unirse', [StudentCourseController::class, 'join'])
            ->name('courses.join');

        Route::get('/cursos/{curso}', [StudentCourseController::class, 'show'])
            ->name('courses.show');

        Route::get('/cursos/{curso}/temas/{leccion}', [StudentCourseController::class, 'lesson'])
            ->name('lessons.show');
    });
