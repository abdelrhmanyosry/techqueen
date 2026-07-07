<x-filament-panels::page>
    @php
        $statusLabels = [
            'in_progress' => __('In Progress'),
            'canceled' => __('Canceled'),
            'on_hold' => __('On Hold'),
            'finished_unpaid' => __('Finished but Unpaid'),
            'paid_unfinished' => __('Paid but Not Finished'),
            'finished_paid' => __('Finished and Paid'),
        ];

        $statusClasses = [
            'finished_paid' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
            'finished_unpaid' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20',
            'paid_unfinished' => 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-500/20',
            'in_progress' => 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-500/20',
            'canceled' => 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20',
            'on_hold' => 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20',
        ];
    @endphp

    <!-- Custom CSS Styles to ensure layout structure without relying on external compilers -->
    <style>
        .calendar-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            align-items: start;
            width: 100%;
        }

        @media (min-width: 1024px) {
            .calendar-container {
                grid-template-columns: 2fr 1fr;
            }
        }

        .calendar-grid-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
            width: 100%;
        }

        .calendar-cell {
            aspect-ratio: 1 / 1 !important;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            border-radius: 0.5rem;
            position: relative;
            text-align: center;
            transition: all 0.2s ease;
            width: 100%;
            height: 100%;
        }
    </style>

    <div class="calendar-container">
        
        <!-- Left Side: Interactive Calendar -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            
            <!-- Calendar Header -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-6 border-b border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/20">
                <div class="flex items-center gap-3">
                    <span class="p-2 rounded-lg bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400">
                        <x-heroicon-o-calendar class="w-6 h-6" style="width: 24px; height: 24px;" />
                    </span>
                    <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ $this->monthName }}
                    </h2>
                </div>
                
                <div class="flex items-center gap-2">
                    <x-filament::button 
                        color="gray" 
                        size="sm"
                        icon="heroicon-m-chevron-left"
                        wire:click="previousMonth"
                        class="shadow-sm"
                    >
                        {{ __('Previous') }}
                    </x-filament::button>

                    <x-filament::button 
                        color="gray" 
                        size="sm"
                        wire:click="goToToday"
                        class="shadow-sm"
                    >
                        {{ __('Today') }}
                    </x-filament::button>

                    <x-filament::button 
                        color="gray" 
                        size="sm"
                        icon="heroicon-m-chevron-right"
                        icon-position="after"
                        wire:click="nextMonth"
                        class="shadow-sm"
                    >
                        {{ __('Next') }}
                    </x-filament::button>
                </div>
            </div>

            <!-- Calendar Body Grid -->
            <div class="p-6">
                <!-- Days of Week Header -->
                <div class="calendar-grid-header text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    <div>{{ __('Sun') }}</div>
                    <div>{{ __('Mon') }}</div>
                    <div>{{ __('Tue') }}</div>
                    <div>{{ __('Wed') }}</div>
                    <div>{{ __('Thu') }}</div>
                    <div>{{ __('Fri') }}</div>
                    <div>{{ __('Sat') }}</div>
                </div>

                <!-- Grid Calendar Cells -->
                <div class="calendar-grid">
                    @foreach ($this->calendarWeeks as $week)
                        @foreach ($week as $day)
                            @if ($day === null)
                                <!-- Empty Pad Cell -->
                                <div class="calendar-cell bg-gray-50/50 dark:bg-gray-950/10 border border-gray-100 dark:border-gray-800/40 opacity-40"></div>
                            @else
                                @php
                                    $dayString = $day->format('Y-m-d');
                                    $isToday = $day->isToday();
                                    $isSelected = ($this->selectedDate === $dayString);
                                    $deliveriesCount = $this->deliveriesCount[$dayString] ?? 0;
                                @endphp

                                <button 
                                    type="button"
                                    wire:click="selectDate('{{ $dayString }}')"
                                    @class([
                                        'calendar-cell border transition relative focus:outline-none focus:ring-2 focus:ring-primary-500',
                                        'bg-primary-50/30 dark:bg-primary-500/5 border-primary-500/50 dark:border-primary-500/30 ring-2 ring-primary-500/30' => $isSelected,
                                        'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50' => !$isSelected && !$isToday,
                                        'bg-white dark:bg-gray-800 border-primary-600 dark:border-primary-400 ring-1 ring-primary-600 dark:ring-primary-400' => $isToday && !$isSelected,
                                    ])
                                >
                                    <!-- Day Number -->
                                    <span @class([
                                        'text-sm font-bold w-8 h-8 flex items-center justify-center rounded-full',
                                        'bg-primary-600 text-white dark:bg-primary-500' => $isSelected,
                                        'text-primary-600 dark:text-primary-400 font-extrabold ring-1 ring-primary-500' => $isToday && !$isSelected,
                                        'text-gray-700 dark:text-gray-300' => !$isSelected && !$isToday,
                                    ])>
                                        {{ $day->day }}
                                    </span>

                                    <!-- Deliveries Indicator Dot/Badge -->
                                    @if ($deliveriesCount > 0)
                                        <span class="absolute top-1.5 right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-[10px] font-bold text-white shadow-sm ring-1 ring-white dark:ring-gray-800" style="width: 20px; height: 20px;">
                                            {{ $deliveriesCount }}
                                        </span>
                                    @endif
                                </button>
                            @endif
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Side: Deliveries List Panel -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-md overflow-hidden flex flex-col self-stretch min-w-[360px] md:w-[400px]">
    
    <div class="p-5 border-b border-gray-200 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-950/40">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h3 class="text-base font-bold text-gray-900 dark:text-white tracking-tight">
                    {{ __('Schedules on Date') }}
                </h3>
                <span class="text-xs font-semibold text-gray-600 dark:text-gray-300 px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200/50 dark:border-gray-700/50 whitespace-nowrap">
                    {{ \Illuminate\Support\Carbon::parse($this->selectedDate)->translatedFormat('d M Y') }}
                </span>
            </div>
            <a 
                href="{{ route('filament.admin.resources.models.create', ['delivery_date' => $this->selectedDate]) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-white bg-primary-600 hover:bg-primary-500 rounded-lg shadow-sm transition duration-200 whitespace-nowrap"
            >
                <x-heroicon-m-plus class="w-4 h-4" style="width: 16px; height: 16px;" />
                <span>{{ __('Create Model') }}</span>
            </a>
        </div>
    </div>

    <div class="p-5 flex-1 overflow-y-auto max-h-[600px] space-y-4">
        @if ($this->selectedDateDeliveries->isEmpty())
            <div class="flex flex-col items-center justify-center text-center py-16 px-4 h-full">
                <div class="p-3.5 rounded-full bg-emerald-50 dark:bg-emerald-950/30 text-emerald-500 mb-4 ring-8 ring-emerald-50/50 dark:ring-emerald-950/10">
                    <x-heroicon-o-check-circle class="w-7 h-7" style="width: 28px; height: 28px;" />
                </div>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                    {{ __('No Deliveries Scheduled') }}
                </h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-[220px] leading-relaxed mb-4">
                    {{ __('There are no client model pieces due for delivery on this day.') }}
                </p>
                <a 
                    href="{{ route('filament.admin.resources.models.create', ['delivery_date' => $this->selectedDate]) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-primary-600 hover:bg-primary-500 rounded-lg shadow-sm transition duration-200"
                >
                    <x-heroicon-m-plus class="w-4 h-4" style="width: 16px; height: 16px;" />
                    <span>{{ __('Create Model') }}</span>
                </a>
            </div>
        @else
            <div class="space-y-3.5">
                @foreach ($this->selectedDateDeliveries as $delivery)
                    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950/40 shadow-sm hover:border-[#ffb900] dark:hover:border-[#ffb900]/70 transition-all duration-200 group">
                        
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="space-y-0.5">
                                <a href="{{ route('filament.admin.resources.models.view', ['record' => $delivery->id]) }}" class="text-sm font-bold text-gray-900 dark:text-gray-100 group-hover:text-[#ffb900] transition-colors duration-150 line-clamp-1">
                                    {{ $delivery->piece_name }}
                                </a>
                                @if ($delivery->client)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ __('Client:') }} 
                                        <a href="{{ route('filament.admin.resources.clients.view', ['record' => $delivery->client_id]) }}" class="font-medium text-gray-700 dark:text-gray-300 hover:underline transition">
                                            {{ $delivery->client->name }}
                                        </a>
                                    </p>
                                @endif
                            </div>
                            
                            <a href="{{ route('filament.admin.resources.models.edit', ['record' => $delivery->id]) }}" class="text-gray-400 hover:text-[#ffb900] p-1 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800 transition" title="Edit Piece">
                                <x-heroicon-m-pencil-square class="w-4 h-4" style="width: 16px; height: 16px;" />
                            </a>
                        </div>

                        <div class="mb-4 relative" x-data="{ open: false }">
                            <!-- Toggle Button -->
                            <button 
                                type="button"
                                @click="open = !open"
                                @click.away="open = false"
                                @class([
                                    'inline-flex items-center gap-1.5 text-xs font-bold rounded-lg px-2.5 py-1 ring-1 ring-inset transition cursor-pointer',
                                    $statusClasses[$delivery->status] ?? 'bg-gray-50 text-gray-600 ring-gray-500/10',
                                ])
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                <span>{{ $statusLabels[$delivery->status] ?? $delivery->status }}</span>
                                <x-heroicon-m-chevron-down class="w-3.5 h-3.5 opacity-60" style="width: 14px; height: 14px;" />
                            </button>

                            <!-- Dropdown Menu -->
                            <div 
                                x-show="open"
                                x-transition
                                class="absolute start-0 mt-1 z-50 w-48 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-lg py-1.5 text-xs"
                                style="display: none;"
                            >
                                @foreach ($statusLabels as $statusKey => $statusVal)
                                    <button 
                                        type="button"
                                        wire:click="updateDeliveryStatus({{ $delivery->id }}, '{{ $statusKey }}')"
                                        @click="open = false"
                                        class="flex items-center gap-2 w-full text-start px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800/60 transition text-gray-700 dark:text-gray-300 font-semibold"
                                    >
                                        @php
                                            $dotColor = match ($statusKey) {
                                                'finished_paid', 'completed' => 'bg-emerald-500',
                                                'finished_unpaid' => 'bg-amber-500',
                                                'paid_unfinished' => 'bg-sky-500',
                                                'in_progress' => 'bg-blue-500',
                                                'canceled' => 'bg-rose-500',
                                                'on_hold' => 'bg-gray-400',
                                                default => 'bg-gray-400',
                                            };
                                        @endphp
                                        <span class="w-2 h-2 rounded-full {{ $dotColor }}"></span>
                                        <span>{{ $statusVal }}</span>
                                        @if ($delivery->status === $statusKey)
                                            <x-heroicon-m-check class="w-3.5 h-3.5 ml-auto text-primary-500" style="width: 14px; height: 14px;" />
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        @php
                            $hidePrices = session('hide_prices', false);
                        @endphp
                        <div class="pt-3 border-t border-gray-100 dark:border-gray-800/60 grid grid-cols-3 gap-2 text-xs">
                            <div>
                                <span class="block text-[10px] uppercase tracking-wider font-semibold text-gray-400 dark:text-gray-500 mb-0.5">{{ __('Price') }}</span>
                                <span class="font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $hidePrices ? '***' : number_format($delivery->price, 0) . ' ' . __('EGP') }}
                                </span>
                            </div>
                            <div class="text-center">
                                <span class="block text-[10px] uppercase tracking-wider font-semibold text-gray-400 dark:text-gray-500 mb-0.5">{{ __('Deposit') }}</span>
                                <span class="font-semibold text-sky-600 dark:text-sky-400 whitespace-nowrap">
                                    {{ $hidePrices ? '***' : number_format($delivery->deposit, 0) . ' ' . __('EGP') }}
                                </span>
                            </div>
                            <div class="text-right">
                                @php
                                    $remaining = $delivery->price - $delivery->deposit;
                                 @endphp
                                <span class="block text-[10px] uppercase tracking-wider font-semibold text-gray-400 dark:text-gray-500 mb-0.5">{{ __('Balance') }}</span>
                                <span @class([
                                    'font-bold whitespace-nowrap',
                                    'text-amber-600 dark:text-amber-400' => $remaining > 0 && !$hidePrices,
                                    'text-emerald-600 dark:text-emerald-400' => ($remaining <= 0 || $hidePrices),
                                ])>
                                    {{ $hidePrices ? '***' : number_format($remaining, 0) . ' ' . __('EGP') }}
                                </span>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

    </div>
</x-filament-panels::page>
