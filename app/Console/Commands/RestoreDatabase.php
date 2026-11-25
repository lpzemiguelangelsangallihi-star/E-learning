<?php

namespace App\Console\Commands;

use App\Services\Backup\BitacoraTriggers;
use App\Services\Backup\SqlScriptParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RestoreDatabase extends Command
{
    protected $signature = 'backup:restore
        {filename : Nombre del archivo .sql dentro de storage/app/private/backups}
        {--skip-safety-backup : No genera un respaldo de seguridad antes de restaurar}';

    protected $description = 'Restaura la base de datos desde un archivo SQL de respaldo.';

    public function handle(SqlScriptParser $parser, BitacoraTriggers $triggers): int
    {
        $filename = (string) $this->argument('filename');

        if (! Str::endsWith($filename, '.sql')) {
            $this->error('Solo se permiten archivos con extensión .sql.');

            return 1;
        }

        if (Str::contains($filename, ['/', '\\', '..'])) {
            $this->error('El nombre de archivo no debe contener rutas o caracteres de traversal.');

            return 1;
        }

        $path = 'backups/'.$filename;

        if (! Storage::disk('local')->exists($path)) {
            $this->error("El archivo de respaldo '{$filename}' no existe.");

            return 1;
        }

        $sql = Storage::disk('local')->get($path);

        if (trim($sql) === '') {
            $this->error('El archivo de respaldo está vacío.');

            return 1;
        }

        if (str_contains($sql, "\0")) {
            $this->error('El archivo no es un respaldo SQL de texto válido.');

            return 1;
        }

        if (preg_match('/^\s*DELIMITER\b/im', $sql) === 1) {
            $this->error(
                'El archivo contiene instrucciones DELIMITER y no puede restaurarse desde la aplicación. '
                .'Usa un respaldo generado por el sistema o restáuralo con la consola de MySQL.'
            );

            return 1;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver !== 'mysql' && $driver !== 'mariadb' && $driver !== 'sqlite') {
            $this->error('Solo se admiten restauraciones de MySQL, MariaDB o SQLite.');

            return 1;
        }

        $statements = $parser->parse($sql);

        if (! $this->contieneCreateTable($statements)) {
            $this->error(
                'El archivo no parece un respaldo válido: no contiene ninguna sentencia CREATE TABLE. '
                .'Los respaldos vacíos o incompletos no se restauran.'
            );

            return 1;
        }

        if (($peligrosa = $this->buscarSentenciaPeligrosa($statements)) !== null) {
            $this->error(
                'El respaldo contiene una sentencia no permitida para restauraciones: '
                .Str::limit($peligrosa, 120)
            );

            return 1;
        }

        if (! $this->option('skip-safety-backup')) {
            $this->line('Generando un respaldo de seguridad antes de restaurar...');

            if (Artisan::call('backup:run') !== 0) {
                $this->error(
                    'No se pudo generar el respaldo de seguridad. La restauración se canceló '
                    .'para no perder los datos actuales.'
                );

                return 1;
            }

            $this->info('Respaldo de seguridad generado.');
        }

        try {
            if ($driver === 'sqlite') {
                DB::transaction(fn () => $this->restoreSqlite($statements));
            } else {
                $this->restoreMysql($statements);
            }
        } catch (\Throwable $e) {
            $this->error('No se pudo restaurar el respaldo: '.$e->getMessage());

            return 1;
        }

        // El respaldo no incluye triggers, así que se recrean al final.
        $triggers->recreate();

        $this->info("Base de datos restaurada desde '{$filename}'.");

        return 0;
    }

    /**
     * @param array<int, string> $statements
     */
    private function restoreMysql(array $statements): void
    {
        // MySQL/MariaDB no permite transacciones reales con DROP/CREATE TABLE:
        // cada DDL confirma automáticamente. Por eso no se usa DB::transaction.
        DB::unprepared('SET NAMES utf8mb4');
        DB::unprepared('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach ($statements as $statement) {
                DB::unprepared($statement);
            }
        } finally {
            DB::unprepared('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    /**
     * @param array<int, string> $statements
     */
    private function restoreSqlite(array $statements): void
    {
        foreach ($statements as $statement) {
            DB::unprepared($statement);
        }
    }

    /**
     * @param array<int, string> $statements
     */
    private function contieneCreateTable(array $statements): bool
    {
        foreach ($statements as $statement) {
            if (preg_match('/^CREATE\s+(TEMPORARY\s+)?TABLE\b/i', ltrim($statement)) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Evita que un archivo subido cambie de base de datos o cree usuarios.
     *
     * @param array<int, string> $statements
     */
    private function buscarSentenciaPeligrosa(array $statements): ?string
    {
        $prefijos = [
            'USE ',
            'CREATE DATABASE',
            'DROP DATABASE',
            'ALTER DATABASE',
            'CREATE USER',
            'ALTER USER',
            'DROP USER',
            'RENAME USER',
            'GRANT ',
            'REVOKE ',
            'SET GLOBAL',
            'SHUTDOWN',
            'INSTALL ',
            'UNINSTALL ',
        ];

        foreach ($statements as $statement) {
            $normalizada = strtoupper(ltrim($statement));

            foreach ($prefijos as $prefijo) {
                if (str_starts_with($normalizada, $prefijo)) {
                    return $statement;
                }
            }
        }

        return null;
    }
}
