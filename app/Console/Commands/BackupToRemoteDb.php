<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class BackupToRemoteDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup-to-remote';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup local SQLite database data to the remote PostgreSQL database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Database backup to remote Neon PostgreSQL started...');
        
        $sqlitePath = database_path('database.sqlite');
        if (!file_exists($sqlitePath)) {
            $this->error("Local SQLite database file not found at: {$sqlitePath}");
            Log::error("Local SQLite database file not found at: {$sqlitePath}");
            return 1;
        }

        // Define the source connection dynamically pointing to SQLite
        config([
            'database.connections.sqlite_source' => [
                'driver' => 'sqlite',
                'database' => $sqlitePath,
                'prefix' => '',
            ]
        ]);

        $targetConnection = 'pgsql';

        try {
            // 1. Run migrations on the remote database to ensure the schema is up to date
            Artisan::call('migrate', ['--database' => $targetConnection, '--force' => true]);
            
            // 2. Get list of tables from SQLite
            $tables = DB::connection('sqlite_source')
                ->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            
            $tableNames = array_map(fn($t) => $t->name, $tables);

            // Exclude system/migration tables
            $excludeTables = ['migrations'];

            // Sort tables to satisfy foreign key constraints (parents before children)
            $tableOrder = [
                'users',
                'employees',
                'clients',
                'sessions',
                'client_models',
            ];
            
            $sortedTableNames = [];
            foreach ($tableOrder as $orderedTable) {
                if (in_array($orderedTable, $tableNames)) {
                    $sortedTableNames[] = $orderedTable;
                }
            }
            foreach ($tableNames as $tableName) {
                if (!in_array($tableName, $sortedTableNames)) {
                    $sortedTableNames[] = $tableName;
                }
            }

            foreach ($sortedTableNames as $tableName) {
                if (in_array($tableName, $excludeTables)) {
                    continue;
                }

                // Delete existing records from target table
                DB::connection($targetConnection)->table($tableName)->delete();

                // Fetch records from SQLite
                $records = DB::connection('sqlite_source')->table($tableName)->get();

                if ($records->isEmpty()) {
                    continue;
                }

                // Convert to array
                $data = json_decode(json_encode($records), true);

                // Insert records in chunks
                foreach (array_chunk($data, 100) as $chunk) {
                    DB::connection($targetConnection)->table($tableName)->insert($chunk);
                }
            }

            Log::info('Database backup to remote Neon PostgreSQL completed successfully.');
            $this->info('Database backup completed successfully!');
            return 0;

        } catch (\Exception $e) {
            Log::error('Database backup failed: ' . $e->getMessage());
            $this->error('Database backup failed: ' . $e->getMessage());
            return 1;
        }
    }
}
