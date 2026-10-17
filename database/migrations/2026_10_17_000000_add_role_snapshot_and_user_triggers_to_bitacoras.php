<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TRIGGERS = [
        'bitacoras_users_after_insert',
        'bitacoras_users_after_update',
        'bitacoras_users_before_delete',
    ];

    public function up(): void
    {
        $driver = DB::getDriverName();

        if (!in_array($driver, ['mysql', 'sqlite'], true)) {
            throw new \RuntimeException(
                "La bitácora de usuarios requiere triggers MySQL/MariaDB o SQLite; driver actual: {$driver}."
            );
        }

        Schema::table('bitacoras', function (Blueprint $table) {
            $table->string('usuario_nombre', 255)->nullable();
            $table->foreignId('rol_id')
                ->nullable()
                ->constrained('roles')
                ->nullOnDelete();
        });

        $this->backfillSnapshots();
        $this->createTriggers($driver);
    }

    public function down(): void
    {
        $this->dropTriggers();

        Schema::table('bitacoras', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rol_id');
            $table->dropColumn('usuario_nombre');
        });
    }

    private function backfillSnapshots(): void
    {
        DB::table('bitacoras')
            ->select(['id', 'usuario_id'])
            ->whereNotNull('usuario_id')
            ->orderBy('id')
            ->chunkById(100, function ($registros): void {
                $usuarios = DB::table('users')
                    ->whereIn('id', $registros->pluck('usuario_id')->unique())
                    ->get([
                        'id',
                        'rol_id',
                        'nombre',
                        'apellido_paterno',
                        'apellido_materno',
                    ])
                    ->keyBy('id');

                foreach ($registros as $registro) {
                    $usuario = $usuarios->get($registro->usuario_id);

                    if (!$usuario) {
                        continue;
                    }

                    $nombre = trim(implode(' ', array_filter([
                        $usuario->nombre,
                        $usuario->apellido_paterno,
                        $usuario->apellido_materno,
                    ], fn ($parte) => $parte !== null && $parte !== '')));

                    DB::table('bitacoras')
                        ->where('id', $registro->id)
                        ->update([
                            'usuario_nombre' => mb_substr($nombre, 0, 255),
                            'rol_id' => $usuario->rol_id,
                        ]);
                }
            });
    }

    private function createTriggers(string $driver): void
    {
        $this->dropTriggers();

        $events = [
            ['bitacoras_users_after_insert', 'AFTER INSERT', 'NEW', 'Se creó'],
            ['bitacoras_users_after_update', 'AFTER UPDATE', 'NEW', 'Se actualizó'],
            ['bitacoras_users_before_delete', 'BEFORE DELETE', 'OLD', 'Se eliminó'],
        ];

        foreach ($events as [$name, $timing, $row, $action]) {
            DB::unprepared(
                $driver === 'mysql'
                    ? $this->mysqlTrigger($name, $timing, $row, $action)
                    : $this->sqliteTrigger($name, $timing, $row, $action)
            );
        }
    }

    private function mysqlTrigger(string $name, string $timing, string $row, string $action): string
    {
        $fullName = "TRIM(CONCAT_WS(' ', {$row}.nombre, {$row}.apellido_paterno, {$row}.apellido_materno))";

        return <<<SQL
            CREATE TRIGGER {$name}
            {$timing} ON users
            FOR EACH ROW
            BEGIN
                INSERT INTO bitacoras (accion, fecha, visible, usuario_id, usuario_nombre, rol_id)
                VALUES (
                    CONCAT('{$action} el usuario ', LEFT({$fullName}, 220), '.'),
                    CURRENT_TIMESTAMP,
                    1,
                    {$row}.id,
                    LEFT({$fullName}, 255),
                    {$row}.rol_id
                );
            END
            SQL;
    }

    private function sqliteTrigger(string $name, string $timing, string $row, string $action): string
    {
        $fullName = "TRIM(COALESCE({$row}.nombre, '') || ' ' || COALESCE({$row}.apellido_paterno, '') || ' ' || COALESCE({$row}.apellido_materno, ''))";

        return <<<SQL
            CREATE TRIGGER {$name}
            {$timing} ON users
            FOR EACH ROW
            BEGIN
                INSERT INTO bitacoras (accion, fecha, visible, usuario_id, usuario_nombre, rol_id)
                VALUES (
                    '{$action} el usuario ' || SUBSTR({$fullName}, 1, 220) || '.',
                    CURRENT_TIMESTAMP,
                    1,
                    {$row}.id,
                    SUBSTR({$fullName}, 1, 255),
                    {$row}.rol_id
                );
            END
            SQL;
    }

    private function dropTriggers(): void
    {
        foreach (self::TRIGGERS as $trigger) {
            DB::unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }
    }
};
