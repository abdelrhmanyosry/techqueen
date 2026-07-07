<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SyncFromRemoteButton extends Component
{
    public function sync(): void
    {
        try {
            // Run the pull from remote command
            $exitCode = Artisan::call('db:pull-from-remote');

            if ($exitCode === 0) {
                Notification::make()
                    ->title(__('Data synced successfully!'))
                    ->success()
                    ->send();
                
                // Redirect back to refresh the current page and its data
                $this->redirect(request()->header('Referer') ?? route('filament.admin.pages.dashboard'));
            } else {
                Notification::make()
                    ->title(__('Database sync failed!'))
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Log::error('Remote sync button error: ' . $e->getMessage());
            Notification::make()
                ->title(__('Database sync failed!') . ' ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return <<<'HTML'
            <div>
                <x-filament::modal id="confirm-sync-modal" width="md" alignment="center" icon="heroicon-o-exclamation-triangle" icon-color="warning">
                    <x-slot name="trigger">
                        <button 
                            type="button"
                            wire:loading.attr="disabled"
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-300 transition mr-2 relative"
                            title="{{ __('Sync data from remote database') }}"
                        >
                            <!-- Cloud Down Icon -->
                            <svg wire:loading.remove wire:target="sync" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 20px; height: 20px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75v6.75m0 0l-3-3m3 3l3-3m-8.25 6a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                            </svg>

                            <!-- Spinner Icon (when loading) -->
                            <svg wire:loading wire:target="sync" class="animate-spin h-5 w-5 text-amber-600 dark:text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="heading">
                        {{ __('Sync data from remote database') }}
                    </x-slot>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Are you sure you want to download remote data? This will overwrite your local changes.') }}
                    </p>

                    <x-slot name="footerActions">
                        <x-filament::button
                            wire:click="sync"
                            x-on:click="close"
                            color="warning"
                            icon="heroicon-m-cloud-arrow-down"
                            wire:loading.attr="disabled"
                        >
                            {{ __('Yes, sync data') }}
                        </x-filament::button>

                        <x-filament::button
                            x-on:click="close"
                            color="gray"
                        >
                            {{ __('Cancel') }}
                        </x-filament::button>
                    </x-slot>
                </x-filament::modal>
            </div>
        HTML;
    }
}
