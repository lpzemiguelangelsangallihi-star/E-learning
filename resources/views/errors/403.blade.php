@php
    $user = auth()->user();

    $panel = match ($user?->rol?->nombre) {
        'administrador' => route('admin.dashboard'),
        'profesor' => route('professor.dashboard'),
        'estudiante' => route('student.dashboard'),
        default => url('/'),
    };
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso denegado</title>
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: #0f172a;
            color: #e2e8f0;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
        }
        .card {
            width: 100%;
            max-width: 520px;
            padding: 40px 32px;
            border: 1px solid #1e293b;
            border-radius: 16px;
            background: #111827;
            text-align: center;
        }
        .code {
            margin: 0 0 8px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .18em;
            color: #60a5fa;
        }
        h1 { margin: 0 0 12px; font-size: 26px; line-height: 1.2; }
        p { margin: 0 0 24px; color: #94a3b8; line-height: 1.6; }
        a {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 10px;
            background: #2563eb;
            color: #fff;
            font-weight: 600;
            text-decoration: none;
        }
        a:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <main class="card">
        <p class="code">403 · ACCESO DENEGADO</p>
        <h1>No tienes permiso para ver esta sección</h1>
        <p>Tu cuenta no tiene acceso a este recurso. Si crees que es un error, vuelve a tu panel e inténtalo desde allí.</p>
        <a href="{{ $panel }}">Volver a mi panel</a>
    </main>
</body>
</html>
