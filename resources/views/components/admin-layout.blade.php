@props([
    'title',
    'description' => null,
])

<x-shell.layout
    :title="$title"
    :description="$description"
    role="Administrador"
    panel="Administración"
    home="admin.dashboard"
    :bell="null"
    :items="[
        ['Dashboard',     'admin.dashboard',        request()->routeIs('admin.dashboard'),     'fa-house'],
        ['Usuarios',      'admin.users.index',      request()->routeIs('admin.users.*'),       'fa-users'],
        ['Cursos',        'admin.courses.index',    request()->routeIs('admin.courses.*'),     'fa-book-open'],
        ['Bitácora',      'admin.bitacora.index',   request()->routeIs('admin.bitacora.*'),    'fa-clock-rotate-left'],
        ['Estadísticas',  'admin.statistics.index', request()->routeIs('admin.statistics.*'),  'fa-chart-column'],
        ['Configuración', 'admin.settings.index',   request()->routeIs('admin.settings.*'),    'fa-gear'],
        ['Mi perfil',     'admin.profile.edit',     request()->routeIs('admin.profile.*'),     'fa-user'],
    ]"
>
    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endisset

    {{ $slot }}
</x-shell.layout>
