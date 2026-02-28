<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncSqliteToPgsql extends Command
{
    protected $signature = 'db:sync-sqlite-to-pgsql {--truncate : Truncate destination tables before import}';

    protected $description = 'Sync data from local SQLite database file to the default PostgreSQL connection.';

    public function handle(): int
    {
        $source = DB::connection('sqlite_source');
        $destConnection = config('database.default');
        $dest = DB::connection($destConnection);

        $tables = collect($source->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"))
            ->pluck('name')
            ->values();

        $skipTables = collect([
            'migrations',
            'cache',
            'cache_locks',
            'failed_jobs',
            'job_batches',
            'password_reset_tokens',
            'sessions',
            'personal_access_tokens',
        ]);

        $tables = $tables->reject(fn ($name) => $skipTables->contains($name));

        if ($tables->isEmpty()) {
            $this->warn('No tables found to sync.');
            return self::SUCCESS;
        }

        if ($this->option('truncate')) {
            $this->info('Truncating destination tables...');
            foreach ($tables as $table) {
                if (Schema::connection($destConnection)->hasTable($table)) {
                    $dest->statement('TRUNCATE TABLE ' . $dest->getTablePrefix() . $table . ' RESTART IDENTITY CASCADE');
                }
            }
        }

        $dest->statement("SET session_replication_role = 'replica'");

        foreach ($tables as $table) {
            if (!Schema::connection($destConnection)->hasTable($table)) {
                $this->warn("Skipping {$table} (missing in destination)");
                continue;
            }

            $this->info("Syncing {$table}...");

            $columns = collect($source->select('PRAGMA table_info(' . $table . ')'))
                ->pluck('name')
                ->values();

            $source->table($table)->orderBy($columns->first() ?? 'rowid')->chunk(500, function ($rows) use ($dest, $table) {
                $payload = $rows->map(fn ($row) => (array) $row)->all();
                if (!empty($payload)) {
                    $dest->table($table)->insert($payload);
                }
            });

            if ($columns->contains('id')) {
                $dest->statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 1), true)");
            }
        }

        $dest->statement("SET session_replication_role = 'origin'");

        $this->info('Sync complete.');

        return self::SUCCESS;
    }
}
