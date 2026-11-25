<?php

use App\Models\bitacoras as Bitacora;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

function backupUsuario(string $rol, array $atributos = []): User
{
    $rolModel = Rol::firstOrCreate(
        ['nombre' => $rol],
        ['descripcion' => "Rol de prueba {$rol}"]
    );

    return User::create(array_merge([
        'rol_id' => $rolModel->id,
        'nombre' => ucfirst($rol),
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Backup',
        'email' => $rol.'-backup-'.uniqid().'@example.test',
        'password' => 'password',
        'estado' => 'activo',
        'email_verified_at' => now(),
    ], $atributos));
}

it('solo el administrador puede acceder a la página de respaldos', function () {
    $profesor = backupUsuario('profesor');
    $admin = backupUsuario('administrador');

    // El profesor no debe acceder (redirigido a su panel)
    $this->actingAs($profesor)
        ->get(route('admin.backups.index'))
        ->assertRedirect(route('professor.dashboard'));

    // El administrador no debe ser redirigido al panel del profesor
    // (eso significaría que el role middleware lo bloquea incorrectamente)
    $response = $this->actingAs($admin)
        ->get(route('admin.backups.index'));

    expect($response->headers->get('location'))
        ->not->toBe(route('professor.dashboard'));
});

it('un profesor no puede descargar respaldos', function () {
    $profesor = backupUsuario('profesor');

    $this->actingAs($profesor)
        ->get(route('admin.backups.download', 'backup_20260101_000000.sql'))
        ->assertRedirect(route('professor.dashboard'));
});

it('el administrador puede generar un respaldo y registrarlo en la bitácora', function () {
    Storage::fake('local');

    Artisan::shouldReceive('call')
        ->with('backup:run')
        ->andReturn(0);

    $admin = backupUsuario('administrador');

    $response = $this->actingAs($admin)
        ->from(route('admin.backups.index'))
        ->post(route('admin.backups.store'), ['confirmar' => true]);

    $response->assertRedirect(route('admin.backups.index'))
        ->assertSessionHas('success');

    expect(Bitacora::where('accion', 'Generó respaldo de base de datos.')
        ->where('usuario_id', $admin->id)
        ->exists())->toBeTrue();
});

it('el administrador no puede generar un respaldo sin confirmar', function () {
    $admin = backupUsuario('administrador');

    $response = $this->actingAs($admin)
        ->from(route('admin.backups.index'))
        ->post(route('admin.backups.store'), []);

    $response->assertRedirect(route('admin.backups.index'))
        ->assertSessionHas('errors');
});

it('el administrador puede descargar un respaldo existente', function () {
    Storage::fake('local');

    Storage::disk('local')->put('backups/backup_20260101_000000.sql', 'contenido de prueba');

    $admin = backupUsuario('administrador');

    $response = $this->actingAs($admin)
        ->get(route('admin.backups.download', 'backup_20260101_000000.sql'));

    $response->assertOk();
    $response->assertHeader('content-disposition', 'attachment; filename=backup_20260101_000000.sql');
});

it('el administrador recibe 404 al descargar un respaldo inexistente', function () {
    Storage::fake('local');

    $admin = backupUsuario('administrador');

    $this->actingAs($admin)
        ->get(route('admin.backups.download', 'backup_inexistente.sql'))
        ->assertNotFound();
});

it('un profesor no puede restaurar respaldos', function () {
    $profesor = backupUsuario('profesor');

    $this->actingAs($profesor)
        ->post(route('admin.backups.restore', 'backup_20260101_000000.sql'), ['confirmar' => true])
        ->assertStatus(403);
});

it('el administrador puede restaurar un respaldo y registrarlo en la bitácora', function () {
    Storage::fake('local');

    Storage::disk('local')->put('backups/backup_20260101_000000.sql', '-- test backup');

    Artisan::shouldReceive('call')
        ->with('backup:restore', ['filename' => 'backup_20260101_000000.sql'])
        ->andReturn(0);

    $admin = backupUsuario('administrador');

    $response = $this->actingAs($admin)
        ->from(route('admin.backups.index'))
        ->post(route('admin.backups.restore', 'backup_20260101_000000.sql'), ['confirmar' => true]);

    $response->assertRedirect(route('admin.backups.index'))
        ->assertSessionHas('success');

    expect(Bitacora::where('accion', 'Restauró respaldo de base de datos: backup_20260101_000000.sql')
        ->where('usuario_id', $admin->id)
        ->exists())->toBeTrue();
});

it('el administrador no puede restaurar un respaldo sin confirmar', function () {
    $admin = backupUsuario('administrador');

    $response = $this->actingAs($admin)
        ->from(route('admin.backups.index'))
        ->post(route('admin.backups.restore', 'backup_20260101_000000.sql'), []);

    $response->assertRedirect(route('admin.backups.index'))
        ->assertSessionHas('errors');
});

it('el administrador recibe error al restaurar un respaldo inexistente', function () {
    Storage::fake('local');

    Artisan::shouldReceive('call')
        ->with('backup:restore', ['filename' => 'backup_inexistente.sql'])
        ->andReturn(1);

    Artisan::shouldReceive('output')
        ->andReturn('El archivo de respaldo no existe.');

    $admin = backupUsuario('administrador');

    $response = $this->actingAs($admin)
        ->from(route('admin.backups.index'))
        ->post(route('admin.backups.restore', 'backup_inexistente.sql'), ['confirmar' => true]);

    $response->assertRedirect(route('admin.backups.index'))
        ->assertSessionHas('errors');
});

it('el administrador no puede restaurar con nombre de archivo que contenga traversal de ruta', function () {
    $admin = backupUsuario('administrador');

    $response = $this->actingAs($admin)
        ->from(route('admin.backups.index'))
        ->post(route('admin.backups.restore', 'backup..sql'), ['confirmar' => true]);

    $response->assertRedirect(route('admin.backups.index'))
        ->assertSessionHas('errors');
});

it('un profesor no puede subir respaldos', function () {
    Storage::fake('local');

    $profesor = backupUsuario('profesor');

    $archivo = UploadedFile::fake()->createWithContent(
        'respaldo.sql',
        "CREATE TABLE ejemplo (id INT);\n"
    );

    $this->actingAs($profesor)
        ->post(route('admin.backups.upload'), [
            'archivo' => $archivo,
            'confirmar' => true,
        ])
        ->assertStatus(403);
});

it('el administrador puede subir y restaurar un respaldo sql', function () {
    Storage::fake('local');

    Artisan::shouldReceive('call')
        ->once()
        ->with('backup:restore', Mockery::on(
            fn ($parametros) => is_array($parametros)
                && str_starts_with($parametros['filename'] ?? '', 'subido_')
                && str_ends_with($parametros['filename'] ?? '', '.sql')
        ))
        ->andReturn(0);

    $admin = backupUsuario('administrador');

    $archivo = UploadedFile::fake()->createWithContent(
        'respaldo_guardado.sql',
        "CREATE TABLE ejemplo (id INT);\n"
    );

    $response = $this->actingAs($admin)
        ->from(route('admin.backups.index'))
        ->post(route('admin.backups.upload'), [
            'archivo' => $archivo,
            'confirmar' => true,
        ]);

    $response->assertRedirect(route('admin.backups.index'))
        ->assertSessionHas('success');

    expect(Bitacora::where('accion', 'like', 'Restauró respaldo subido:%')
        ->where('usuario_id', $admin->id)
        ->exists())->toBeTrue();

    expect(Storage::disk('local')->files('backups'))->toHaveCount(1);
});

it('el administrador no puede subir un archivo que no sea sql', function () {
    Storage::fake('local');

    $admin = backupUsuario('administrador');

    $archivo = UploadedFile::fake()->createWithContent(
        'respaldo.txt',
        "CREATE TABLE ejemplo (id INT);\n"
    );

    $response = $this->actingAs($admin)
        ->from(route('admin.backups.index'))
        ->post(route('admin.backups.upload'), [
            'archivo' => $archivo,
            'confirmar' => true,
        ]);

    $response->assertRedirect(route('admin.backups.index'))
        ->assertSessionHasErrors('archivo');

    expect(Storage::disk('local')->files('backups'))->toBeEmpty();
});
