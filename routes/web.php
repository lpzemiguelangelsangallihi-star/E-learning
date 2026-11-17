<?php

use App\Http\Controllers\Admin\BitacoraController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CourseFileController;
use App\Http\Controllers\NotificacionWebController;
use App\Http\Controllers\Professor\CourseController as ProfessorCourseController;
use App\Http\Controllers\Professor\DashboardController as ProfessorDashboardController;
use App\Http\Controllers\Professor\ReporteIOPController;
use App\Http\Controllers\Professor\StatisticsController as ProfessorStatisticsController;
use App\Http\Controllers\Student\CourseController as StudentCourseController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\EvaluationController;
use App\Http\Controllers\Student\GradeController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Student\ProgressController;
use App\Http\Controllers\Student\RecommendationController;
use App\Models\IopSeguimiento;
use App\Models\Leccion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
| Notificaciones del usuario autenticado
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'role:estudiante'])
    ->prefix('notificaciones')
    ->name('notificaciones.')
    ->group(function () {
        Route::get('/', [NotificacionWebController::class, 'index'])->name('index');
        Route::patch('/{notificacion}/leida', [NotificacionWebController::class, 'marcarLeida'])
            ->whereNumber('notificacion')
            ->name('leida');
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

        $user = Auth::user();

        return match ($user->rol?->nombre) {

            'administrador' => redirect()->route('admin.dashboard'),

            'profesor' => redirect()->route('professor.dashboard'),

            'estudiante' => redirect()->route('student.dashboard'),

            default => abort(403),
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

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get(
            '/bitacora',
            [BitacoraController::class, 'index']
        )->name('bitacora.index');

        /*
        |--------------------------------------------------------------------------
        | Usuarios
        |--------------------------------------------------------------------------
        */

        Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
        Route::get('/usuarios/crear', [UserController::class, 'create'])->name('users.create');
        Route::post('/usuarios', [UserController::class, 'store'])->name('users.store');
        Route::get('/usuarios/{user}/editar', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/usuarios/{user}/estado', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');

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
            '/cursos/{curso:ulid}/visibilidad',
            [AdminCourseController::class, 'toggleVisibility']
        )->name('courses.toggle-visibility');

        Route::delete(
            '/cursos/{curso:ulid}',
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

        Route::get('/dashboard', [ProfessorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/cursos', [ProfessorCourseController::class, 'index'])->name('courses.index');
        Route::get('/cursos/crear', [ProfessorCourseController::class, 'create'])->name('courses.create');
        Route::post('/cursos', [ProfessorCourseController::class, 'store'])->name('courses.store');
        Route::get('/cursos/{curso:ulid}/editar', [ProfessorCourseController::class, 'edit'])->name('courses.edit');
        Route::put('/cursos/{curso:ulid}', [ProfessorCourseController::class, 'update'])->name('courses.update');
        Route::delete('/cursos/{curso:ulid}', [ProfessorCourseController::class, 'destroy'])->name('courses.destroy');

        Route::patch('/cursos/{curso:ulid}/estado', [ProfessorCourseController::class, 'toggleStatus'])->name('courses.toggle-status');
        Route::get('/cursos/{curso:ulid}/estudiantes', [ProfessorCourseController::class, 'students'])->name('courses.students');
        Route::get('/estadisticas', [ProfessorStatisticsController::class, 'index'])->name('statistics.index');

        Route::get('/iop/refuerzos', fn () => view('professor.iop.refuerzos'))->name('iop.refuerzos');
        Route::get('/iop/seguimientos', fn () => view('professor.iop.seguimientos'))->name('iop.seguimientos');

        Route::get('/iop/lecciones/{leccion:ulid}/ejercicios', function (Leccion $leccion) {
            return view('professor.iop.catalogo', [
                'leccionId' => $leccion->id,
            ]);
        })->name('iop.catalogo');

        Route::get(
            '/iop/seguimientos/{seguimientoId}',
            function (int $seguimientoId) {
                $seguimiento = IopSeguimiento::query()
                    ->whereKey($seguimientoId)
                    ->whereHas(
                        'leccion.modulo.curso',
                        fn ($query) => $query->where(
                            'profesor_id',
                            Auth::id()
                        )
                    )
                    ->whereNotNull('intento_diagnostico_id')
                    ->with([
                        'estudiante',
                        'leccion.modulo.curso',
                        'transiciones',
                        'ciclosRefuerzo.recomendacion.asignacionRefuerzo.ejercicio',
                    ])
                    ->firstOrFail();

                
$evidencia = app(
    \App\Services\IOP\MatrizPersonalIOPService::class
)->evidencia(
    (int) $seguimiento->estudiante_id,
    (int) $seguimiento->leccion_id
);

return view('professor.iop.detalle-seguimiento', [
    'seguimiento' => $seguimiento,
    'evidencia' => $evidencia,
]);

            }
        )
            ->whereNumber('seguimientoId')
            ->name('iop.seguimientos.detalle');

        Route::get('/iop/reportes/exportar', [ReporteIOPController::class, 'exportar'])->name('iop.reportes.exportar');

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
    ->group(function () {

        Route::get(
            '/dashboard',
            [StudentDashboardController::class, 'index']
        )->name('dashboard');

        Route::get(
            '/cursos',
            [StudentCourseController::class, 'index']
        )->name('courses.index');

        Route::get(
            '/cursos/inscribirse',
            [StudentCourseController::class, 'join']
        )->name('courses.join');

        Route::post(
            '/cursos/inscribirse',
            [StudentCourseController::class, 'enroll']
        )->name('courses.enroll');

        Route::get(
            '/evaluaciones',
            [EvaluationController::class, 'index']
        )->name('evaluations.index');

        Route::get(
            '/evaluaciones/historial',
            [EvaluationController::class, 'history']
        )->name('evaluations.history');

        Route::get(
            '/calificaciones',
            [GradeController::class, 'index']
        )->name('grades.index');

        Route::get(
            '/progreso',
            [ProgressController::class, 'index']
        )->name('progress.index');

        Route::get(
            '/recomendaciones',
            [RecommendationController::class, 'index']
        )->name('recommendations.index');

        Route::get(
            '/perfil',
            [StudentProfileController::class, 'edit']
        )->name('profile.edit');

        Route::put(
            '/perfil',
            [StudentProfileController::class, 'update']
        )->name('profile.update');

        Route::put(
            '/perfil/password',
            [StudentProfileController::class, 'updatePassword']
        )->name('profile.password');

    });

require __DIR__.'/academico.php';

/*
|--------------------------------------------------------------------------
| Archivos académicos privados
|--------------------------------------------------------------------------
|
| Los materiales, videos y adjuntos de tareas se sirven solo a usuarios
| con acceso al curso correspondiente.
|
*/
Route::middleware(['auth', 'signed', 'throttle:archivos'])
    ->prefix('archivos')
    ->name('files.')
    ->group(function () {
        Route::get('/materiales/{material:ulid}', [CourseFileController::class, 'material'])
            ->name('material');

        Route::get('/videos/{video:ulid}', [CourseFileController::class, 'video'])
            ->name('video');

        Route::get('/tareas/{tarea:ulid}', [CourseFileController::class, 'tarea'])
            ->name('tarea');
    });

/*
|--------------------------------------------------------------------------
| INVESTIGACIÓN OPERATIVA II - ESTUDIANTE
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:estudiante',
])
    ->get(
        '/iop/lecciones/{leccion:ulid}/seguimiento',
        function (Leccion $leccion) {
            return view('iop.seguimiento', [
                'leccionId' => $leccion->id,
            ]);
        }
    )
    ->name('iop.seguimiento');
