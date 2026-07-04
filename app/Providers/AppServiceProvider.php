<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['en', 'ar'])
                ->visible(outsidePanels: true);
        });

        $dbConnection = config('database.default');

        if ($dbConnection === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');

            if ($dbPath && !file_exists($dbPath)) {
                $dir = dirname($dbPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                // Create empty sqlite file
                touch($dbPath);

                // Run migrations
                try {
                    \Illuminate\Support\Facades\Artisan::call('migrate', [
                        '--force' => true,
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Auto-migration failed: " . $e->getMessage());
                }
            }
        }
    }
}

