<x-filament-widgets::widget>
    @php
        $statusLabels = [
            'in_progress' => 'In Progress',
            'on_hold' => 'On Hold',
            'finished_unpaid' => 'Finished but Unpaid',
            'paid_unfinished' => 'Paid but Not Finished',
        ];

        $statusClasses = [
            'finished_unpaid' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20',
            'paid_unfinished' => 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-500/20',
            'in_progress' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20',
            'on_hold' => 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20',
        ];
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Left Side: Deliveries Reminder -->
        <div class="p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                    <span class="p-2 rounded-lg bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400">
                        <x-heroicon-o-clock class="w-6 h-6" style="width: 24px; height: 24px;" />
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Upcoming Deliveries Reminder</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Active models sorted by nearest delivery date</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse ($deliveries as $model)
                        @php
                            $diff = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($model->delivery_date), false);
                            $isOverdue = $diff < 0;
                            $diffDays = abs($diff);
                        @endphp
                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60 hover:bg-gray-100/50 dark:hover:bg-gray-800 transition">
                            <div class="min-w-0 flex-1 pr-3">
                                <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate">
                                    <a href="{{ route('filament.admin.resources.models.edit', ['record' => $model->id]) }}" class="hover:underline hover:text-primary-600 dark:hover:text-primary-400 transition">
                                        {{ $model->piece_name }}
                                    </a>
                                </h4>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                    Client: 
                                    @if ($model->client_id)
                                        <a href="{{ route('filament.admin.resources.clients.view', ['record' => $model->client_id]) }}" class="hover:underline hover:text-primary-600 dark:hover:text-primary-400 transition">
                                            {{ $model->client->name }}
                                        </a>
                                    @else
                                        Walk-in Client
                                    @endif
                                </p>
                            </div>
                            
                            <div class="flex items-center gap-3 shrink-0">
                                <!-- Status Badge -->
                                <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-semibold ring-1 ring-inset {{ $statusClasses[$model->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabels[$model->status] ?? $model->status }}
                                </span>

                                <!-- Delivery Date Countdown -->
                                <span @class([
                                    'text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap',
                                    'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-100 dark:border-rose-500/20' => $isOverdue,
                                    'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-100 dark:border-amber-500/20' => !$isOverdue && $diffDays <= 2,
                                    'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 border border-blue-100 dark:border-blue-500/20' => !$isOverdue && $diffDays > 2,
                                ])>
                                    @if ($isOverdue)
                                        Overdue {{ $diffDays }}d
                                    @elseif ($diffDays === 0)
                                        Today
                                    @elseif ($diffDays === 1)
                                        Tomorrow
                                    @else
                                        In {{ $diffDays }} days
                                    @endif
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-400 dark:text-gray-500 text-xs">
                            No active deliveries remaining.
                        </div>
                    @endforelse
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-right">
                <a href="{{ route('filament.admin.resources.models.index') }}" class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline">
                    View all models &rarr;
                </a>
            </div>
        </div>

        <!-- Right Side: Employee Workload -->
        <div class="p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                    <span class="p-2 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400">
                        <x-heroicon-o-user-group class="w-6 h-6" style="width: 24px; height: 24px;" />
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Active Employee Workloads</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Current work assigned to employees & admin</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Employees Section -->
                    @foreach ($employees as $employee)
                        @php
                            $activeModels = $employee->models->filter(fn($m) => !in_array($m->status, ['finished_paid', 'completed', 'canceled']));
                        @endphp
                        @if ($activeModels->count() > 0)
                            <div class="space-y-2 border-b border-gray-100 dark:border-gray-800/40 pb-3 last:border-b-0 last:pb-0">
                                <h4 class="text-xs font-bold text-gray-900 dark:text-white flex items-center justify-between">
                                    <span>{{ $employee->name }}</span>
                                    <span class="text-[10px] font-normal text-gray-500 dark:text-gray-400">
                                        {{ $activeModels->count() }} active {{ $activeModels->count() === 1 ? 'job' : 'jobs' }}
                                    </span>
                                </h4>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach ($activeModels as $model)
                                        <div class="p-2 rounded bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/50 flex items-center justify-between gap-2">
                                            <a href="{{ route('filament.admin.resources.models.edit', ['record' => $model->id]) }}" class="text-[10px] font-bold text-gray-900 dark:text-gray-300 truncate hover:underline hover:text-primary-600 dark:hover:text-primary-400 transition" title="{{ $model->piece_name }}">
                                                {{ $model->piece_name }}
                                            </a>
                                            <span class="inline-flex shrink-0 items-center rounded px-1.5 py-0.5 text-[8px] font-bold uppercase tracking-wider {{ $statusClasses[$model->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $statusLabels[$model->status] ?? $model->status }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    <!-- Admin / Unassigned Section -->
                    @if ($adminModels->count() > 0)
                        <div class="space-y-2 border-t border-gray-100 dark:border-gray-800/40 pt-3 first:border-t-0 first:pt-0">
                            <h4 class="text-xs font-bold text-amber-600 dark:text-amber-400 flex items-center justify-between">
                                <span>Admin / Self (Unassigned)</span>
                                <span class="text-[10px] font-normal text-gray-500 dark:text-gray-400">
                                    {{ $adminModels->count() }} active {{ $adminModels->count() === 1 ? 'job' : 'jobs' }}
                                </span>
                            </h4>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach ($adminModels as $model)
                                    <div class="p-2 rounded bg-amber-50/30 dark:bg-amber-950/10 border border-amber-100/50 dark:border-amber-800/20 flex items-center justify-between gap-2">
                                        <a href="{{ route('filament.admin.resources.models.edit', ['record' => $model->id]) }}" class="text-[10px] font-bold text-gray-900 dark:text-gray-300 truncate hover:underline hover:text-primary-600 dark:hover:text-primary-400 transition" title="{{ $model->piece_name }}">
                                            {{ $model->piece_name }}
                                        </a>
                                        <span class="inline-flex shrink-0 items-center rounded px-1.5 py-0.5 text-[8px] font-bold uppercase tracking-wider {{ $statusClasses[$model->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusLabels[$model->status] ?? $model->status }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($employees->every(fn($e) => $e->models->filter(fn($m) => !in_array($m->status, ['finished_paid', 'completed', 'canceled']))->count() === 0) && $adminModels->count() === 0)
                        <div class="text-center py-6 text-gray-400 dark:text-gray-500 text-xs">
                            No active workload at the moment.
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-right">
                <a href="{{ route('filament.admin.resources.employees.index') }}" class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline">
                    Manage employees &rarr;
                </a>
            </div>
        </div>

    </div>
</x-filament-widgets::widget>
