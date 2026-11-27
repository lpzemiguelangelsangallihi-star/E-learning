@props([
    'title',
    'description' => null,
])

<x-shell.layout
    :title="$title"
    :description="$description"
    role="Profesor"
    panel="Panel del profesor"
    home="professor.dashboard"
    :bell="null"
    :items="[
        ['Dashboard',       'professor.dashboard',          request()->routeIs('professor.dashboard'),            'fa-house'],
        ['Mis cursos',      'professor.courses.index',      request()->routeIs('professor.courses.*'),            'fa-book-open'],
        ['Exámenes',        'professor.exams.index',        request()->routeIs('professor.exams.*'),              'fa-file-pen'],
        ['Tareas',          'professor.tasks.index',        request()->routeIs('professor.tasks.*'),              'fa-clipboard-list'],
        ['Reportes',        'professor.reports.index',      request()->routeIs('professor.reports.*'),            'fa-chart-pie'],
        ['Supervisión IOP', 'professor.iop.seguimientos',   request()->routeIs('professor.iop.seguimientos*'),    'fa-chart-line'],
        ['Refuerzos IOP',   'professor.iop.refuerzos',      request()->routeIs('professor.iop.refuerzos'),        'fa-brain'],
        ['Estadísticas',    'professor.statistics.index',   request()->routeIs('professor.statistics.*'),         'fa-chart-column'],
    ]"
>
    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endisset

    {{ $slot }}
</x-shell.layout>
