<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackupController extends Controller
{
    public function index()
    {
        $backups = collect();

        if (Storage::disk('local')->exists('backups')) {
            $backups = collect(Storage::disk('local')->files('backups'))
                ->map(fn ($path) => [
                    'filename' => basename($path),
                    'size' => Storage::disk('local')->size($path),
                    'lastModified' => Storage::disk('local')->lastModified($path),
                ])
                ->sortByDesc('lastModified')
                ->values();
        }

        return view('admin.backups.index', compact('backups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'confirmar' => ['required', 'boolean'],
        ]);

        if (! $request->boolean('confirmar')) {
            return back()->withErrors(['confirmar' => 'Debes confirmar la generación del respaldo.']);
        }

        $exitCode = Artisan::call('backup:run');

        if ($exitCode !== 0) {
            return back()->withErrors(['backup' => $this->detalleError(
                'No se pudo generar el respaldo.'
            )]);
        }

        $this->registrarBitacora('Generó respaldo de base de datos.');

        return back()->with('success', 'Respaldo generado correctamente.');
    }

    /**
     * Recibe un archivo .sql descargado previamente y lo restaura.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'extensions:sql', 'max:102400'],
            'confirmar' => ['required', 'boolean'],
        ], [
            'archivo.required' => 'Selecciona un archivo .sql para restaurar.',
            'archivo.file' => 'El archivo no se pudo subir. Intenta de nuevo.',
            'archivo.extensions' => 'El archivo debe tener extensión .sql.',
            'archivo.max' => 'El archivo no puede superar los 100 MB.',
            'confirmar.required' => 'Debes confirmar la restauración del respaldo.',
        ]);

        if (! $request->boolean('confirmar')) {
            return back()->withErrors(['confirmar' => 'Debes confirmar la restauración del respaldo.']);
        }

        $file = $request->file('archivo');

        $filename = 'subido_'.now()->format('Ymd_His').'_'.Str::lower(Str::random(6)).'.sql';

        if (! $file->storeAs('backups', $filename, 'local')) {
            return back()->withErrors([
                'archivo' => 'No se pudo guardar el archivo en el almacenamiento privado.',
            ]);
        }

        $exitCode = Artisan::call('backup:restore', ['filename' => $filename]);

        if ($exitCode !== 0) {
            return back()->withErrors([
                'restore' => $this->detalleError(
                    'No se pudo restaurar el respaldo. Verifica que el archivo sea un respaldo SQL válido.'
                ),
            ]);
        }

        $this->registrarBitacora('Restauró respaldo subido: '.$filename);

        $nombreOriginal = $file->getClientOriginalName();

        return back()->with(
            'success',
            "Base de datos restaurada desde el archivo '{$nombreOriginal}'. "
            .'Se generó un respaldo de seguridad antes de restaurar.'
        );
    }

    public function show(string $filename)
    {
        if (! $this->nombreValido($filename)) {
            abort(404);
        }

        $path = 'backups/'.$filename;

        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->download($path);
    }

    public function restore(Request $request, string $backup)
    {
        $filename = $backup;

        $request->validate([
            'confirmar' => ['required', 'boolean'],
        ]);

        if (! $request->boolean('confirmar')) {
            return back()->withErrors(['confirmar' => 'Debes confirmar la restauración del respaldo.']);
        }

        if (! $this->nombreValido($filename)) {
            return back()->withErrors(['backup' => 'Nombre de archivo no válido.']);
        }

        $path = 'backups/'.$filename;

        if (! Storage::disk('local')->exists($path)) {
            return back()->withErrors(['backup' => "El archivo de respaldo '{$filename}' no existe."]);
        }

        $exitCode = Artisan::call('backup:restore', ['filename' => $filename]);

        if ($exitCode !== 0) {
            return back()->withErrors([
                'restore' => $this->detalleError('No se pudo restaurar el respaldo.'),
            ]);
        }

        $this->registrarBitacora('Restauró respaldo de base de datos: '.$filename);

        return back()->with(
            'success',
            "Base de datos restaurada desde '{$filename}'. "
            .'Se generó un respaldo de seguridad antes de restaurar.'
        );
    }

    private function nombreValido(string $filename): bool
    {
        return Str::endsWith($filename, '.sql')
            && ! Str::contains($filename, ['/', '\\', '..'])
            && $filename === basename($filename);
    }

    private function detalleError(string $mensajePorDefecto): string
    {
        $detalle = trim(Artisan::output());

        if ($detalle === '') {
            return $mensajePorDefecto;
        }

        return Str::limit($detalle, 500);
    }

    private function registrarBitacora(string $accion): void
    {
        try {
            BitacoraService::registrar($accion);
        } catch (\Throwable $e) {
            // Si el respaldo restaurado no tiene la misma estructura, la
            // restauración ya se completó; no se debe reportar como fallida.
            Log::warning('No se pudo registrar la acción en la bitácora: '.$e->getMessage());
        }
    }
}
