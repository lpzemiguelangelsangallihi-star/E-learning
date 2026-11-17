<?php

use App\Http\Controllers\Professor\CourseContentController;
use App\Http\Controllers\Professor\LessonController;
use App\Http\Controllers\Professor\MaterialController;
use App\Http\Controllers\Professor\ModuleController;
use App\Http\Controllers\Student\CourseController as StudentCourseController;
use Illuminate\Support\Facades\Route;

/*
| PROFESOR
|
| Las rutas anidadas activan scopeBindings(): Laravel comprueba que el
| módulo pertenezca al curso y que el tema pertenezca al módulo antes de
| entregar el modelo al controlador. Un cruce de identificadores da 404.
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:profesor',
])
    ->prefix('profesor')
    ->name('professor.')
    ->scopeBindings()
    ->group(function () {

        // Estructura del curso y código de clase
        Route::get('/cursos/{curso:ulid}/contenido', [CourseContentController::class, 'show'])
            ->name('courses.structure');

        Route::post('/cursos/{curso:ulid}/codigo', [CourseContentController::class, 'regenerateCode'])
            ->name('courses.regenerate-code');

        // Unidades
        Route::post('/cursos/{curso:ulid}/modulos', [ModuleController::class, 'store'])
            ->name('modules.store');

        Route::put('/cursos/{curso:ulid}/modulos/{modulo:ulid}', [ModuleController::class, 'update'])
            ->name('modules.update');

        Route::delete('/cursos/{curso:ulid}/modulos/{modulo:ulid}', [ModuleController::class, 'destroy'])
            ->name('modules.destroy');

        Route::post('/cursos/{curso:ulid}/modulos/{modulo:ulid}/mover', [ModuleController::class, 'move'])
            ->name('modules.move');

        // Temas
        Route::get('/cursos/{curso:ulid}/modulos/{modulo:ulid}/lecciones/crear', [LessonController::class, 'create'])
            ->name('lessons.create');

        Route::post('/cursos/{curso:ulid}/modulos/{modulo:ulid}/lecciones', [LessonController::class, 'store'])
            ->name('lessons.store');

        Route::get('/cursos/{curso:ulid}/modulos/{modulo:ulid}/lecciones/{leccion:ulid}/editar', [LessonController::class, 'edit'])
            ->name('lessons.edit');

        Route::put('/cursos/{curso:ulid}/modulos/{modulo:ulid}/lecciones/{leccion:ulid}', [LessonController::class, 'update'])
            ->name('lessons.update');

        Route::delete('/cursos/{curso:ulid}/modulos/{modulo:ulid}/lecciones/{leccion:ulid}', [LessonController::class, 'destroy'])
            ->name('lessons.destroy');

        Route::post('/cursos/{curso:ulid}/modulos/{modulo:ulid}/lecciones/{leccion:ulid}/mover', [LessonController::class, 'move'])
            ->name('lessons.move');

        Route::patch('/cursos/{curso:ulid}/modulos/{modulo:ulid}/lecciones/{leccion:ulid}/estado', [LessonController::class, 'toggleStatus'])
            ->name('lessons.toggle-status');

        // Materiales y videos de un tema
        Route::get('/cursos/{curso:ulid}/modulos/{modulo:ulid}/lecciones/{leccion:ulid}/materiales', [MaterialController::class, 'index'])
            ->name('materials.index');

        Route::post('/cursos/{curso:ulid}/modulos/{modulo:ulid}/lecciones/{leccion:ulid}/materiales', [MaterialController::class, 'store'])
            ->name('materials.store');

        Route::delete('/cursos/{curso:ulid}/modulos/{modulo:ulid}/lecciones/{leccion:ulid}/materiales/{material:ulid}', [MaterialController::class, 'destroyMaterial'])
            ->name('materials.destroy');

        Route::delete('/cursos/{curso:ulid}/modulos/{modulo:ulid}/lecciones/{leccion:ulid}/videos/{video:ulid}', [MaterialController::class, 'destroyVideo'])
            ->name('videos.destroy');
    });

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
    ->scopeBindings()
    ->group(function () {

        // Compatibilidad con el formulario anterior.
        Route::post(
            '/cursos/unirse',
            [StudentCourseController::class, 'enroll']
        )->name('courses.join-legacy');

        Route::get(
            '/cursos/{curso:ulid}',
            [StudentCourseController::class, 'show']
        )->name('courses.show');

        Route::post(
            '/cursos/{curso:ulid}/temas/{leccion:ulid}/completar',
            [StudentCourseController::class, 'completeLesson']
        )->name('lessons.complete');

        Route::get(
            '/cursos/{curso:ulid}/temas/{leccion:ulid}',
            [StudentCourseController::class, 'lesson']
        )->name('lessons.show');

    });

require __DIR__.'/evaluaciones.php';
