<?php

namespace App\Livewire;

use Livewire\Component;

class HidePricesToggle extends Component
{
    public bool $hidePrices = false;

    public function mount(): void
    {
        $this->hidePrices = session('hide_prices', false);
    }

    public function toggle(): void
    {
        $this->hidePrices = !$this->hidePrices;
        session(['hide_prices' => $this->hidePrices]);

        // Redirect back to the referrer to refresh the page state
        $this->redirect(request()->header('Referer') ?? route('filament.admin.pages.dashboard'));
    }

    public function render()
    {
        return <<<'HTML'
            <button 
                type="button"
                wire:click="toggle"
                class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-300 transition mr-2"
                title="{{ $hidePrices ? 'Show Prices' : 'Hide Prices' }}"
            >
                @if ($hidePrices)
                    <!-- Eye-slash Icon (Prices are hidden) -->
                    <x-heroicon-o-eye-slash class="h-5 w-5" style="width: 20px; height: 20px;" />
                @else
                    <!-- Eye Icon (Prices are visible) -->
                    <x-heroicon-o-eye class="h-5 w-5" style="width: 20px; height: 20px;" />
                @endif
            </button>
        HTML;
    }
}
