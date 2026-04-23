<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExportMysqlCommand extends Command
{
    protected $signature = 'db:export-mysql
        {--out=database-export.sql : Output file path (relative to project root)}
        {--data-only : Skip CREATE TABLE statements and emit only INSERTs}';

    protected $description = 'Dump the current database (schema + data) as a MySQL-ready .sql file for cPanel phpMyAdmin import.';

    /**
     * Tables the portfolio app owns, plus the Laravel framework tables that
     * are required for sessions/queues/cache to work in production.
     */
    private const TABLES = [
        // Framework-level (kept so sessions/queues/cache work on fresh MySQL)
        'migrations',
        'cache',
        'cache_locks',
        'sessions',
        'jobs',
        'job_batches',
        'failed_jobs',
        // App domain
        'users',
        'password_reset_tokens',
        'personal_access_tokens',
        'site_settings',
        'projects',
        'blog_posts',
        'skills',
        'experiences',
        'certifications',
        'clients',
        'contact_messages',
    ];

    public function handle(): int
    {
        $out = base_path($this->option('out'));
        $dataOnly = (bool) $this->option('data-only');
        $sql = [];

        $sql[] = '-- Portfolio database export (schema + data)';
        $sql[] = '-- Generated: ' . now()->toDateTimeString();
        $sql[] = '-- Import target: empty MySQL database on cPanel.';
        $sql[] = '-- Usage: phpMyAdmin → select your DB → Import → upload this file.';
        $sql[] = '';
        $sql[] = 'SET NAMES utf8mb4;';
        $sql[] = 'SET FOREIGN_KEY_CHECKS = 0;';
        $sql[] = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";';
        $sql[] = '';

        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                $this->warn("skip   {$table}  (table not present locally)");
                continue;
            }

            if (! $dataOnly) {
                $sql[] = "-- ===== {$table} =====";
                $sql[] = "DROP TABLE IF EXISTS `{$table}`;";
                $sql[] = $this->buildCreateTable($table);
                $sql[] = '';
            }

            $rows = DB::table($table)->get();
            if ($rows->isEmpty()) {
                $this->line("empty  {$table}");
                continue;
            }
            $this->info("dump   {$table}  ({$rows->count()} rows)");

            $columns = array_keys((array) $rows->first());
            $colList = '`' . implode('`, `', $columns) . '`';

            foreach ($rows->chunk(50) as $chunk) {
                $valueGroups = [];
                foreach ($chunk as $row) {
                    $values = [];
                    foreach ($columns as $col) {
                        $values[] = $this->formatValue(((array) $row)[$col]);
                    }
                    $valueGroups[] = '(' . implode(', ', $values) . ')';
                }
                $sql[] = "INSERT INTO `{$table}` ({$colList}) VALUES";
                $sql[] = implode(",\n", $valueGroups) . ';';
            }
            $sql[] = '';
        }

        $sql[] = 'SET FOREIGN_KEY_CHECKS = 1;';
        $sql[] = '';

        file_put_contents($out, implode("\n", $sql));

        $this->newLine();
        $this->info("Done → {$out}");
        $this->line('Import on cPanel:');
        $this->line('  1. phpMyAdmin → select your MySQL database (keep it EMPTY).');
        $this->line('  2. Tab "Import" → choose this .sql file → Go.');
        $this->line('  3. Then in your app server: `php artisan storage:link`.');

        return self::SUCCESS;
    }

    /**
     * Build a MySQL CREATE TABLE statement by inspecting the current (SQLite)
     * schema via Laravel's Schema builder, then translating each column spec.
     *
     * Good enough for our 17-table schema. Foreign keys are skipped because we
     * wrap the dump in FOREIGN_KEY_CHECKS = 0 and Laravel uses soft references.
     */
    private function buildCreateTable(string $table): string
    {
        $columns = Schema::getColumns($table);
        $primary = [];
        $lines = [];

        foreach ($columns as $col) {
            $name = $col['name'];
            $line = '  `' . $name . '` ' . $this->mysqlType($col);

            if ($col['nullable']) {
                $line .= ' NULL';
            } else {
                $line .= ' NOT NULL';
            }

            if ($col['auto_increment']) {
                $line .= ' AUTO_INCREMENT';
                $primary[] = $name;
            }

            if ($col['default'] !== null && ! $col['auto_increment']) {
                $default = $col['default'];
                // Strip SQLite single-quotes around string defaults.
                if (is_string($default) && str_starts_with($default, "'") && str_ends_with($default, "'")) {
                    $default = substr($default, 1, -1);
                    $line .= ' DEFAULT ' . DB::connection()->getPdo()->quote($default);
                } elseif (is_numeric($default) || in_array(strtoupper((string) $default), ['CURRENT_TIMESTAMP', 'NULL'], true)) {
                    $line .= ' DEFAULT ' . $default;
                } else {
                    $line .= ' DEFAULT ' . DB::connection()->getPdo()->quote((string) $default);
                }
            }

            $lines[] = $line;
        }

        // Detect primary key via Schema::getIndexes if not an AUTO_INCREMENT
        if (empty($primary)) {
            foreach (Schema::getIndexes($table) as $idx) {
                if (! empty($idx['primary'])) {
                    $primary = $idx['columns'];
                    break;
                }
            }
        }
        if (! empty($primary)) {
            $lines[] = '  PRIMARY KEY (`' . implode('`, `', $primary) . '`)';
        }

        // Unique indexes (skip the primary)
        foreach (Schema::getIndexes($table) as $idx) {
            if (! empty($idx['primary'])) continue;
            if (empty($idx['unique'])) continue;
            $name = $idx['name'] ?: 'uniq_' . implode('_', $idx['columns']);
            $lines[] = '  UNIQUE KEY `' . $name . '` (`' . implode('`, `', $idx['columns']) . '`)';
        }

        return "CREATE TABLE `{$table}` (\n"
            . implode(",\n", $lines)
            . "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    }

    /**
     * Translate a column spec (as reported by Schema::getColumns on SQLite)
     * into a MySQL type declaration.
     */
    private function mysqlType(array $col): string
    {
        $sqliteType = strtolower($col['type'] ?? $col['type_name'] ?? 'text');

        // Laravel's SQLite driver sometimes reports types like "integer",
        // "varchar(255)", "text", "datetime", "tinyint(1)", "json", etc.
        return match (true) {
            str_contains($sqliteType, 'tinyint')   => 'TINYINT(1)',
            str_contains($sqliteType, 'bigint')    => 'BIGINT UNSIGNED',
            str_contains($sqliteType, 'int')       => 'BIGINT UNSIGNED',
            str_contains($sqliteType, 'varchar')   => $this->extractVarchar($sqliteType),
            str_contains($sqliteType, 'char')      => 'CHAR(36)',
            str_contains($sqliteType, 'longtext')  => 'LONGTEXT',
            str_contains($sqliteType, 'mediumtext')=> 'MEDIUMTEXT',
            str_contains($sqliteType, 'text')      => 'TEXT',
            str_contains($sqliteType, 'json')      => 'JSON',
            str_contains($sqliteType, 'datetime')  => 'DATETIME',
            str_contains($sqliteType, 'timestamp') => 'TIMESTAMP NULL',
            str_contains($sqliteType, 'date')      => 'DATE',
            str_contains($sqliteType, 'time')      => 'TIME',
            str_contains($sqliteType, 'float')     => 'DOUBLE',
            str_contains($sqliteType, 'double')    => 'DOUBLE',
            str_contains($sqliteType, 'decimal')   => 'DECIMAL(10,2)',
            str_contains($sqliteType, 'blob')      => 'LONGBLOB',
            default                                => 'TEXT',
        };
    }

    private function extractVarchar(string $type): string
    {
        if (preg_match('/varchar\((\d+)\)/', $type, $m)) {
            return 'VARCHAR(' . $m[1] . ')';
        }
        return 'VARCHAR(255)';
    }

    private function formatValue(mixed $value): string
    {
        if (is_null($value))   return 'NULL';
        if (is_bool($value))   return $value ? '1' : '0';
        if (is_int($value) || is_float($value)) return (string) $value;

        $pdo = DB::connection()->getPdo();
        return $pdo->quote((string) $value);
    }
}
