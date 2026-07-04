<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class MigrateToRemoteDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:migrate-to-remote';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate local SQLite database tables to the remote database configured in .env';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database migration from SQLite to Remote DB...');

        // 1. Check if SQLite database exists
        $sqlitePath = database_path('database.sqlite');
        if (!file_exists($sqlitePath)) {
            $this->error("Local SQLite database file not found at: {$sqlitePath}");
            return 1;
        }

        // 2. Define the source connection dynamically to bypass .env defaults
        config([
            'database.connections.sqlite_source' => [
                'driver' => 'sqlite',
                'database' => $sqlitePath,
                'prefix' => '',
            ]
        ]);

        // 3. The target connection is the current default (which the user configured in .env to be mysql/remote)
        $targetConnection = DB::getDefaultConnection();
        $this->info("Target connection is: {$targetConnection}");

        if ($targetConnection === 'sqlite' || $targetConnection === 'sqlite_source') {
            $this->error('Target connection is still SQLite. Please configure your remote database credentials (e.g. mysql) in your .env first!');
            return 1;
        }

        if (!$this->confirm('This will wipe all existing tables on the remote database and replace them with local SQLite data. Do you want to proceed?')) {
            $this->info('Migration cancelled.');
            return 0;
        }

        // 4. Run migrations on the remote database to create the schema
        $this->info('Wiping remote database...');
        $wipeExitCode = Artisan::call('db:wipe', ['--database' => $targetConnection, '--force' => true]);
        $this->info("db:wipe exit code: {$wipeExitCode}");
        $this->info(Artisan::output());

        $this->info('Running migrations to create clean tables...');
        $migrateExitCode = Artisan::call('migrate', ['--database' => $targetConnection, '--force' => true]);
        $this->info("migrate exit code: {$migrateExitCode}");
        $this->info(Artisan::output());

        // 5. Get list of tables from SQLite
        $tables = DB::connection('sqlite_source')
            ->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        
        $tableNames = array_map(fn($t) => $t->name, $tables);

        // Sort tables so parent tables are created/seeded before child tables to satisfy constraints
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

        // Exclude migrations and sessions/cache tables that are automatically handled or shouldn't be copied
        $excludeTables = ['migrations'];

        // Disable foreign key constraints on MySQL target for clean insertion
        $targetDriver = config("database.connections.{$targetConnection}.driver");
        if ($targetDriver === 'mysql') {
            DB::connection($targetConnection)->statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        foreach ($sortedTableNames as $tableName) {
            if (in_array($tableName, $excludeTables)) {
                continue;
            }

            $this->info("Copying table: {$tableName}...");

            // Clear target table first to avoid duplicate keys (e.g. from migrations)
            DB::connection($targetConnection)->table($tableName)->delete();

            // Fetch records from SQLite
            $records = DB::connection('sqlite_source')->table($tableName)->get();

            if ($records->isEmpty()) {
                $this->info("Table {$tableName} is empty, skipping.");
                continue;
            }

            // Convert to array
            $data = json_decode(json_encode($records), true);

            // Insert in chunks to avoid query size limits
            $chunks = array_chunk($data, 100);
            $bar = $this->output->createProgressBar(count($chunks));

            foreach ($chunks as $chunk) {
                DB::connection($targetConnection)->table($tableName)->insert($chunk);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        // Re-enable foreign key constraints
        if ($targetDriver === 'mysql') {
            DB::connection($targetConnection)->statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->info('Database migration completed successfully!');
        return 0;
    }
}
