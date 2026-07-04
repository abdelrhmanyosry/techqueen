<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PullFromRemoteDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:pull-from-remote';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull employees, client_models, and clients from remote PostgreSQL to local SQLite';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Database pull from remote Neon PostgreSQL started...');
        
        $sqlitePath = database_path('database.sqlite');
        if (!file_exists($sqlitePath)) {
            $this->error("Local SQLite database file not found at: {$sqlitePath}");
            Log::error("Local SQLite database file not found at: {$sqlitePath}");
            return 1;
        }

        $sourceConnection = 'pgsql';
        $targetConnection = config('database.default');

        try {
            // Disable foreign key constraints on SQLite
            if ($targetConnection === 'sqlite') {
                DB::connection($targetConnection)->statement('PRAGMA foreign_keys = OFF;');
            } else {
                DB::connection($targetConnection)->statement('SET FOREIGN_KEY_CHECKS = 0;');
            }

            // Tables to sync
            $tables = ['employees', 'clients', 'client_models'];

            foreach ($tables as $tableName) {
                // Delete existing local records
                DB::connection($targetConnection)->table($tableName)->delete();

                // Fetch from remote connection
                $records = DB::connection($sourceConnection)->table($tableName)->get();

                if ($records->isEmpty()) {
                    $this->line("Table {$tableName} is empty on remote DB.");
                    continue;
                }

                // Convert records to array
                $data = json_decode(json_encode($records), true);

                // Insert into local database in chunks
                foreach (array_chunk($data, 100) as $chunk) {
                    DB::connection($targetConnection)->table($tableName)->insert($chunk);
                }

                $this->info("Successfully copied " . count($data) . " records for table: {$tableName}");
            }

            // Re-enable foreign key constraints
            if ($targetConnection === 'sqlite') {
                DB::connection($targetConnection)->statement('PRAGMA foreign_keys = ON;');
            } else {
                DB::connection($targetConnection)->statement('SET FOREIGN_KEY_CHECKS = 1;');
            }

            Log::info('Database pull from remote Neon PostgreSQL completed successfully.');
            $this->info('Database pull completed successfully!');
            return 0;

        } catch (\Exception $e) {
            // Re-enable constraints on error
            try {
                if ($targetConnection === 'sqlite') {
                    DB::connection($targetConnection)->statement('PRAGMA foreign_keys = ON;');
                } else {
                    DB::connection($targetConnection)->statement('SET FOREIGN_KEY_CHECKS = 1;');
                }
            } catch (\Exception $ex) {}

            Log::error('Database pull failed: ' . $e->getMessage());
            $this->error('Database pull failed: ' . $e->getMessage());
            return 1;
        }
    }
}
