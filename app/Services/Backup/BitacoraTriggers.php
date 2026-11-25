<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Recrea los triggers de auditoría de usuarios después de una restauración.
 *
 * El respaldo generado por la aplicación no incluye triggers, y al hacer
 * DROP TABLE sobre `users` MySQL los elimina. Este servicio los vuelve a
 * crear para que la bitácora siga funcionando después de restaurar.
 */
class BitacoraTriggers
{
    /** @var array<int, string> */
    private const NOMBRES = [
        'bitacoras_users_after_insert',
        'bitacoras_users_after_update',
        'bitacoras_users_before_delete',
    ];

    /** @var array<int, array{0: string, 1: string, 2: string, 3: string}> */
    private const EVENTOS = [
        ['bitacoras_users_after_insert', 'AFTER INSERT', 'NEW', 'Se creó'],
        ['bitacoras_users_after_update', 'AFTER UPDATE', 'NEW', 'Se actualizó'],
        ['bitacoras_users_before_delete', 'BEFORE DELETE', 'OLD', 'Se eliminó'],
    ];

    public function recreate(): void
    {
        $driver = DB::connection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
            return;
        }

        try {
            foreach (self::NOMBRES as $nombre) {
                DB::unprepared("DROP TRIGGER IF EXISTS {$nombre}");
            }

            foreach (self::EVENTOS as [$nombre, $timing, $row, $accion]) {
                DB::unprepared(
                    $driver === 'sqlite'
                        ? $this->sqliteTrigger($nombre, $timing, $row, $accion)
                        : $this->mysqlTrigger($nombre, $timing, $row, $accion)
                );
            }
        } catch (\Throwable $e) {
            // No se debe considerar fallida toda la restauración solo porque
            // el archivo restaurado no tenga la estructura esperada.
            Log::warning(
                'No se pudieron recrear los triggers de bitácoras después de restaurar: '
                .$e->getMessage()
            );
        }
    }

    private function mysqlTrigger(
        string $nombre,
        string $timing,
        string $row,
        string $accion
    ): string {
        $fullName = "TRIM(CONCAT_WS(' ', {$row}.nombre, {$row}.apellido_paterno, {$row}.apellido_materno))";

        return <<<SQL
            CREATE TRIGGER {$nombre}
            {$timing} ON users
            FOR EACH ROW
            BEGIN
                INSERT INTO bitacoras (accion, fecha, visible, usuario_id, usuario_nombre, rol_id)
                VALUES (
                    CONCAT('{$accion} el usuario ', LEFT({$fullName}, 220), '.'),
                    CURRENT_TIMESTAMP,
                    1,
                    {$row}.id,
                    LEFT({$fullName}, 255),
                    {$row}.rol_id
                );
            END
            SQL;
    }

    private function sqliteTrigger(
        string $nombre,
        string $timing,
        string $row,
        string $accion
    ): string {
        $fullName = "TRIM(COALESCE({$row}.nombre, '') || ' ' || COALESCE({$row}.apellido_paterno, '') || ' ' || COALESCE({$row}.apellido_materno, ''))";

        return <<<SQL
            CREATE TRIGGER {$nombre}
            {$timing} ON users
            FOR EACH ROW
            BEGIN
                INSERT INTO bitacoras (accion, fecha, visible, usuario_id, usuario_nombre, rol_id)
                VALUES (
                    '{$accion} el usuario ' || SUBSTR({$fullName}, 1, 220) || '.',
                    CURRENT_TIMESTAMP,
                    1,
                    {$row}.id,
                    SUBSTR({$fullName}, 1, 255),
                    {$row}.rol_id
                );
            END
            SQL;
    }
}
