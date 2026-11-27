@props([
    'title',
    'description' => null,
])

@php
    $pendientes = \App\Models\Notificacion::query()
        ->where('usuario_id', auth()->id())
        ->where('leida', false)
        ->count();

    $cursosActivo = request()->routeIs('student.courses.*', 'student.lessons.*')
        && ! request()->routeIs('student.courses.join');
@endphp

<x-shell.layout
    :title="$title"
    :description="$description"
    role="Estudiante"
    panel="Panel del estudiante"
    home="student.dashboard"
    :bell="$pendientes"
    :flash="true"
    :items="[
        ['Dashboard',       'student.dashboard',              request()->routeIs('student.dashboard'),            'fa-house'],
        ['Mis cursos',      'student.courses.index',          $cursosActivo,                                      'fa-book-open'],
        ['Evaluaciones',    'student.evaluations.index',      request()->routeIs('student.evaluations.*'),        'fa-file-circle-check'],
        ['Calificaciones',  'student.grades.index',           request()->routeIs('student.grades.*'),             'fa-star'],
        ['Mi progreso',     'student.progress.index',         request()->routeIs('student.progress.*'),           'fa-chart-line'],
        ['Recomendaciones', 'student.recommendations.index',  request()->routeIs('student.recommendations.*'),    'fa-lightbulb'],
        ['Mi perfil',       'student.profile.edit',           request()->routeIs('student.profile.*'),            'fa-user'],
    ]"
>
    {{ $slot }}
</x-shell.layout>
