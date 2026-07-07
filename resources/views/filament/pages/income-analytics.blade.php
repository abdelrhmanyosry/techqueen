<x-filament-panels::page>
    @php
        $statusLabels = [
            'in_progress' => __('In Progress'),
            'canceled' => __('Canceled'),
            'on_hold' => __('On Hold'),
            'finished_unpaid' => __('Finished but Unpaid'),
            'paid_unfinished' => __('Paid but Not Finished'),
            'finished_paid' => __('Finished and Paid'),
            'completed' => __('Completed (Paid)'),
            'delayed' => __('Delayed'),
        ];

        $statusClasses = [
            'finished_paid' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
            'completed' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
            'finished_unpaid' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20',
            'paid_unfinished' => 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-500/20',
            'in_progress' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20',
            'canceled' => 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20',
            'on_hold' => 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20',
            'delayed' => 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20',
        ];

        $hidePrices = session('hide_prices', false);
    @endphp

    <!-- Script CDN for Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom CSS Styles -->
    <style>

        /* Printing styling overrides */
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            header, nav, aside, .fi-sidebar, .fi-topbar, .no-print, .print-hidden {
                display: none !important;
            }
            .fi-main {
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-full-width {
                width: 100% !important;
                max-width: 100% !important;
                grid-template-columns: 1fr !important;
            }
            .print-card {
                border: 1px solid #e5e7eb !important;
                box-shadow: none !important;
            }
        }
    </style>

    <div class="space-y-6">
        
        <!-- Controls & Filters (Hidden in print) -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm no-print">
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-lg bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400">
                    <x-heroicon-o-presentation-chart-bar class="w-6 h-6" style="width: 24px; height: 24px;" />
                </span>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Filter Report Range') }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Select month and year for detailed insights') }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <!-- Month Select -->
                <div class="w-full sm:w-40">
                    <select 
                        wire:model.live="month" 
                        class="w-full text-sm font-medium rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500"
                    >
                        @foreach ($this->months as $mNum => $mName)
                            <option value="{{ $mNum }}">{{ $mName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Year Select -->
                <div class="w-full sm:w-28">
                    <select 
                        wire:model.live="year" 
                        class="w-full text-sm font-medium rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500"
                    >
                        @foreach ($this->years as $yVal)
                            <option value="{{ $yVal }}">{{ $yVal }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex gap-2 w-full sm:w-auto justify-end">
                    <x-filament::button 
                        color="gray" 
                        icon="heroicon-o-arrow-down-tray"
                        wire:click="exportCSV"
                        class="shadow-sm font-semibold"
                    >
                        {{ __('Export CSV') }}
                    </x-filament::button>

                    <x-filament::button 
                        color="primary" 
                        icon="heroicon-o-printer"
                        onclick="window.print()"
                        class="shadow-sm font-semibold"
                    >
                        {{ __('Print Report') }}
                    </x-filament::button>
                </div>
            </div>
        </div>

        <!-- Print Header (Only visible when printing) -->
        <div class="hidden print:block border-b border-gray-300 pb-4 mb-6">
            <div class="flex justify-between items-end">
                <div>
                    <h1 class="text-2xl font-bold text-black">{{ __('TechQueen Business Performance Report') }}</h1>
                    <p class="text-sm text-gray-600">{{ __('Generated on:') }} {{ now()->format('Y-m-d') }}</p>
                </div>
                <div class="text-end">
                    <h2 class="text-lg font-bold text-gray-800">{{ $this->months[$this->month] }} {{ $this->year }}</h2>
                    <p class="text-xs text-gray-600">{{ __('Total Models Created:') }} {{ $this->stats['total_jobs'] }}</p>
                </div>
            </div>
        </div>

        <!-- 4 KPI Cards Grid -->
        <div class="analytics-grid stats-row grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 print-full-width">
            
            <!-- Revenue Card -->
            <div class="print-card bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 shadow-sm relative overflow-hidden flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Estimated Revenue') }}</span>
                    <span class="p-2 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400">
                        <x-heroicon-o-currency-dollar class="w-6 h-6" style="width: 24px; height: 24px;" />
                    </span>
                </div>
                <div>
                    <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white">
                        {{ $hidePrices ? '***' : number_format($this->stats['total_revenue'], 0) }} 
                        @if (!$hidePrices) <span class="text-sm font-semibold text-gray-500">{{ __('EGP') }}</span> @endif
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Based on price of finished/paid jobs') }}</p>
                </div>
                <div class="absolute bottom-0 left-0 w-full h-1.5 bg-blue-500"></div>
            </div>

            <!-- Collected Card -->
            <div class="print-card bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 shadow-sm relative overflow-hidden flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Collected Income') }}</span>
                    <span class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <x-heroicon-o-check-circle class="w-6 h-6" style="width: 24px; height: 24px;" />
                    </span>
                </div>
                <div>
                    <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white">
                        {{ $hidePrices ? '***' : number_format($this->stats['total_collected'], 0) }} 
                        @if (!$hidePrices) <span class="text-sm font-semibold text-gray-500">{{ __('EGP') }}</span> @endif
                    </h3>
                    <div class="flex items-center gap-1.5 mt-1">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $this->insights['collection_rate'] }}%"></div>
                        </div>
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">{{ $this->insights['collection_rate'] }}%</span>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 w-full h-1.5 bg-emerald-500"></div>
            </div>

            <!-- Outstanding Card -->
            <div class="print-card bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 shadow-sm relative overflow-hidden flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Unpaid Receivables') }}</span>
                    <span class="p-2 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <x-heroicon-o-clock class="w-6 h-6" style="width: 24px; height: 24px;" />
                    </span>
                </div>
                <div>
                    <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white">
                        {{ $hidePrices ? '***' : number_format($this->stats['total_outstanding'], 0) }} 
                        @if (!$hidePrices) <span class="text-sm font-semibold text-gray-500">{{ __('EGP') }}</span> @endif
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Pending payments from active models') }}</p>
                </div>
                <div class="absolute bottom-0 left-0 w-full h-1.5 bg-amber-500"></div>
            </div>

            <!-- Performance Job Count Card -->
            <div class="print-card bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 shadow-sm relative overflow-hidden flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Jobs & Model Completion') }}</span>
                    <span class="p-2 rounded-lg bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400">
                        <x-heroicon-o-shopping-bag class="w-6 h-6" style="width: 24px; height: 24px;" />
                    </span>
                </div>
                <div>
                    <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $this->stats['total_jobs'] }} <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Models') }}</span></h3>
                    <div class="flex flex-wrap gap-1 mt-2">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">{{ __('Done:') }} {{ $this->stats['completed_count'] }}</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">{{ __('Active:') }} {{ $this->stats['in_progress_count'] }}</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">{{ __('Delayed:') }} {{ $this->stats['delayed_count'] }}</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ __('Hold:') }} {{ $this->stats['on_hold_count'] }}</span>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 w-full h-1.5 bg-purple-500"></div>
            </div>

        </div>

  
        <!-- Tables Row: Top Clients & Detailed Breakdown -->
        <div class="analytics-grid tables-row grid grid-cols-1 lg:grid-cols-3 gap-6 print-full-width">
            <!-- Right Card: Detailed Monthly List of Jobs -->
            <div class="print-card bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 shadow-sm overflow-hidden flex flex-col justify-between lg:col-span-2">
                <div class="mb-4 border-b border-gray-100 dark:border-gray-800 pb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ __('Monthly Models List') }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('All models scheduled or delivered in') }} {{ $this->months[$this->month] }}</p>
                    </div>
                    <span class="text-xs font-bold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-500/10 px-2.5 py-1 rounded-full border border-primary-100 dark:border-primary-500/20 self-start sm:self-center">
                        {{ __('Total Value:') }} {{ $hidePrices ? '***' : number_format($this->stats['total_revenue'], 0) . ' ' . __('EGP') }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-start text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800 pb-2 text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold">
                                <th class="pb-2 text-start">{{ __('Model Piece') }}</th>
                                <th class="pb-2 text-start">{{ __('Client') }}</th>
                                <th class="pb-2 text-center">{{ __('Dates') }}</th>
                                <th class="pb-2 text-end">{{ __('Price') }}</th>
                                <th class="pb-2 text-end">{{ __('Deposit') }}</th>
                                <th class="pb-2 text-end">{{ __('Balance') }}</th>
                                <th class="pb-2 text-center">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/40 text-gray-700 dark:text-gray-300">
                            @forelse ($this->monthlyModels as $model)
                                <tr>
                                    <td class="py-3 text-start font-semibold text-gray-900 dark:text-white">
                                        {{ $model->piece_name }}
                                        <span class="block text-[9px] font-normal text-gray-500 dark:text-gray-400">ID: #{{ $model->id }}</span>
                                    </td>
                                    <td class="py-3 text-start">{{ $model->client?->name ?? __('Walk-in Client') }}</td>
                                    <td class="py-3 text-center">
                                        <span class="block text-[10px]">{{ __('Rec:') }} {{ $model->receiving_date }}</span>
                                        <span class="block text-[10px] font-bold text-gray-500 dark:text-gray-400">{{ __('Del:') }} {{ $model->delivery_date }}</span>
                                    </td>
                                    <td class="py-3 text-end font-semibold">
                                        {{ $hidePrices ? '***' : number_format($model->price, 0) . ' ' . __('EGP') }}
                                    </td>
                                    <td class="py-3 text-end text-gray-500 dark:text-gray-400">
                                        {{ $hidePrices ? '***' : number_format($model->deposit, 0) . ' ' . __('EGP') }}
                                    </td>
                                    <td class="py-3 text-end font-bold {{ $model->price - $model->deposit > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }}">
                                        {{ $hidePrices ? '***' : number_format(max(0, $model->price - $model->deposit), 0) . ' ' . __('EGP') }}
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-bold ring-1 ring-inset {{ $statusClasses[$model->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusLabels[$model->status] ?? $model->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400 dark:text-gray-500">
                                        {{ __('No models registered for this month') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

               <!-- Left Card: Top Clients by Revenue -->
            <div class="print-card bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 shadow-sm overflow-hidden flex flex-col justify-between">
                <div class="mb-4 border-b border-gray-100 dark:border-gray-800 pb-3">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ __('Top Clients by Revenue') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Leading contributors for this month') }}</p>
                </div>

                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-start text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800/80 pb-2 text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold">
                                <th class="pb-2 text-start">{{ __('Client') }}</th>
                                <th class="pb-2 text-center">{{ __('Jobs') }}</th>
                                <th class="pb-2 text-end">{{ __('Revenue') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/40 text-gray-700 dark:text-gray-300">
                            @forelse ($this->topClients as $client)
                                <tr>
                                    <td class="py-2.5 text-start font-bold">
                                        {{ $client['name'] }}
                                        <span class="block text-[10px] font-normal text-gray-500 dark:text-gray-400">{{ $client['field'] }}</span>
                                    </td>
                                    <td class="py-2.5 text-center font-semibold">{{ $client['jobs_count'] }}</td>
                                    <td class="py-2.5 text-end font-extrabold text-emerald-600 dark:text-emerald-400">
                                        {{ $hidePrices ? '***' : number_format($client['revenue'], 0) . ' ' . __('EGP') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-gray-400 dark:text-gray-500">{{ __('No client data found for this month') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>


        </div>

    </div>
</x-filament-panels::page>
