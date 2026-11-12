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
])
    ->prefix('profesor')
    ->name('professor.')
    ->group(function () {

        // Exámenes
        Route::get('/examenes', [ProfessorExamController::class, 'index'])->name('exams.index');
        Route::get('/examenes/crear', [ProfessorExamController::class, 'create'])->name('exams.create');
        Route::post('/examenes', [ProfessorExamController::class, 'store'])->name('exams.store');
        Route::get('/examenes/{examen}/editar', [ProfessorExamController::class, 'edit'])->name('exams.edit');
        Route::delete('/examenes/{examen}', [ProfessorExamController::class, 'destroy'])->name('exams.destroy');

        // Tareas y entregas
        Route::get('/tareas', [ProfessorTaskController::class, 'index'])->name('tasks.index');
        Route::get('/tareas/crear', [ProfessorTaskController::class, 'create'])->name('tasks.create');
        Route::post('/tareas', [ProfessorTaskController::class, 'store'])->name('tasks.store');
        Route::get('/tareas/{tarea}/editar', [ProfessorTaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/tareas/{tarea}', [ProfessorTaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tareas/{tarea}', [ProfessorTaskController::class, 'destroy'])->name('tasks.destroy');
        Route::patch('/tareas/{tarea}/estado', [ProfessorTaskController::class, 'changeStatus'])->name('tasks.status');
        Route::get('/tareas/{tarea}/entregas', [ProfessorTaskController::class, 'submissions'])->name('tasks.submissions');
        Route::put('/tareas/{tarea}/entregas/{entrega}', [ProfessorTaskController::class, 'grade'])->name('tasks.grade');
        Route::get('/tareas/{tarea}/entregas/{entrega}/descargar', [ProfessorTaskController::class, 'download'])->name('tasks.download');

        // Reportes académicos (componente Livewire)
        Route::view('/reportes', 'professor.reports.index')->name('reports.index');
    });

/* ---------------------------- ESTUDIANTE ---------------------------- */

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:estudiante',
])
    ->prefix('estudiante')
    ->name('student.')
    ->group(function () {

        // Los nombres cuelgan de "evaluaciones" para que el menú resalte esa sección.
        Route::get('/examenes/{examen}', [StudentExamController::class, 'show'])->name('evaluations.exam.show');
        Route::post('/examenes/{examen}/iniciar', [StudentExamController::class, 'start'])->name('evaluations.exam.start');
        Route::get('/intentos/{intento}', [StudentExamController::class, 'attempt'])->name('evaluations.exam.attempt');
        Route::get('/intentos/{intento}/resultado', [StudentExamController::class, 'result'])->name('evaluations.exam.result');

        Route::get('/tareas/{tarea}', [StudentTaskController::class, 'show'])->name('evaluations.task.show');
        Route::post('/tareas/{tarea}/entregar', [StudentTaskController::class, 'submit'])->name('evaluations.task.submit');
    });
