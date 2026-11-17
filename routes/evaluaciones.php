<?php

use App\Http\Controllers\Professor\ExamController as ProfessorExamController;
use App\Http\Controllers\Professor\TaskController as ProfessorTaskController;
use App\Http\Controllers\Student\ExamController as StudentExamController;
use App\Http\Controllers\Student\TaskController as StudentTaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Módulo académico: exámenes, tareas, calificación y reportes
|--------------------------------------------------------------------------
| Se carga desde routes/academico.php.
*/

/* ----------------------------- PROFESOR ----------------------------- */

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:profesor',
    'throttle:evaluaciones',
])
    ->prefix('profesor')
    ->name('professor.')
    ->scopeBindings()
    ->group(function () {

        // Exámenes
        Route::get('/examenes', [ProfessorExamController::class, 'index'])->name('exams.index');
        Route::get('/examenes/crear', [ProfessorExamController::class, 'create'])->name('exams.create');
        Route::post('/examenes', [ProfessorExamController::class, 'store'])->name('exams.store');
        Route::get('/examenes/{examen:ulid}/editar', [ProfessorExamController::class, 'edit'])->name('exams.edit');
        Route::delete('/examenes/{examen:ulid}', [ProfessorExamController::class, 'destroy'])->name('exams.destroy');

        // Tareas y entregas
        Route::get('/tareas', [ProfessorTaskController::class, 'index'])->name('tasks.index');
        Route::get('/tareas/crear', [ProfessorTaskController::class, 'create'])->name('tasks.create');
        Route::post('/tareas', [ProfessorTaskController::class, 'store'])->name('tasks.store');
        Route::get('/tareas/{tarea:ulid}/editar', [ProfessorTaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/tareas/{tarea:ulid}', [ProfessorTaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tareas/{tarea:ulid}', [ProfessorTaskController::class, 'destroy'])->name('tasks.destroy');
        Route::patch('/tareas/{tarea:ulid}/estado', [ProfessorTaskController::class, 'changeStatus'])->name('tasks.status');
        Route::get('/tareas/{tarea:ulid}/entregas', [ProfessorTaskController::class, 'submissions'])->name('tasks.submissions');
        Route::put('/tareas/{tarea:ulid}/entregas/{entrega:ulid}', [ProfessorTaskController::class, 'grade'])->name('tasks.grade');
        Route::get('/tareas/{tarea:ulid}/entregas/{entrega:ulid}/descargar', [ProfessorTaskController::class, 'download'])->name('tasks.download');

        // Reportes académicos (componente Livewire)
        Route::view('/reportes', 'professor.reports.index')->name('reports.index');
    });

/* ---------------------------- ESTUDIANTE ---------------------------- */

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:estudiante',
    'throttle:evaluaciones',
])
    ->prefix('estudiante')
    ->name('student.')
    ->scopeBindings()
    ->group(function () {

        // Los nombres cuelgan de "evaluaciones" para que el menú resalte esa sección.
        Route::get('/examenes/{examen:ulid}', [StudentExamController::class, 'show'])->name('evaluations.exam.show');
        Route::post('/examenes/{examen:ulid}/iniciar', [StudentExamController::class, 'start'])->name('evaluations.exam.start');
        Route::get('/intentos/{intento:ulid}', [StudentExamController::class, 'attempt'])->name('evaluations.exam.attempt');
        Route::get('/intentos/{intento:ulid}/resultado', [StudentExamController::class, 'result'])->name('evaluations.exam.result');

        Route::get('/tareas/{tarea:ulid}', [StudentTaskController::class, 'show'])->name('evaluations.task.show');
        Route::post('/tareas/{tarea:ulid}/entregar', [StudentTaskController::class, 'submit'])->name('evaluations.task.submit');
        Route::get('/tareas/{tarea:ulid}/mi-entrega/descargar', [StudentTaskController::class, 'download'])->name('evaluations.task.download');
    });
