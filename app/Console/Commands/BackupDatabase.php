<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:run {--dir=}';

    protected $description = 'Genera un respaldo SQL de la base de datos actual usando solo PHP.';

    public function handle(): int
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver !== 'mysql' && $driver !== 'mariadb') {
            $this->error('Solo se admiten respaldos de MySQL/MariaDB.');

            return 1;
        }

        $config = $connection->getConfig();
        $database = (string) ($config['database'] ?? '');

        if (trim($database) === '') {
            $this->error('La conexión no tiene un nombre de base de datos configurado.');

            return 1;
        }

        $tables = $connection->select(
            'SELECT TABLE_NAME AS name
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ?
             ORDER BY TABLE_NAME',
            [$database, 'BASE TABLE']
        );

        if ($tables === []) {
            $this->error('La base de datos no tiene tablas para respaldar.');

            return 1;
        }

        $sql = '-- Respaldo generado el '.now()->format('Y-m-d H:i:s')."\n";
        $sql .= "-- Base de datos: {$database}\n";
        $sql .= '-- Tablas: '.count($tables)."\n\n";
        $sql .= "SET NAMES utf8mb4;\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $row) {
            $name = $row->name;

            $createRow = $connection->select("SHOW CREATE TABLE `{$name}`")[0];
            $createTable = $createRow->{'Create Table'};

            $sql .= "DROP TABLE IF EXISTS `{$name}`;\n";
            $sql .= $createTable.";\n\n";

            $rows = $connection->table($name)->get();

            if ($rows->isNotEmpty()) {
                $columns = array_keys((array) $rows->first());

                $sql .= "INSERT INTO `{$name}` (`".implode('`, `', $columns)."`) VALUES\n";

                $values = [];
                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $escaped = [];

                    foreach ($rowArray as $value) {
                        $escaped[] = $this->escapeValue($value);
                    }

                    $values[] = '('.implode(', ', $escaped).')';
                }

                $sql .= implode(",\n", $values).";\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        $filename = 'backup_'.now()->format('Ymd_His').'.sql';
        $path = 'backups/'.$filename;

        if (! Storage::disk('local')->put($path, $sql)) {
            $this->error('No se pudo guardar el archivo de respaldo en el almacenamiento privado.');

            return 1;
        }

        $this->info("Respaldo generado: {$path} (".count($tables).' tablas)');

        return 0;
    }

    private function escapeValue($value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return "'".addslashes((string) $value)."'";
    }
}
