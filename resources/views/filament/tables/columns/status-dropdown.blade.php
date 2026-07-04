@php
    $status = $getState();
    $record = $getRecord();

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
        'completed' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
        'finished_unpaid' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20',
        'paid_unfinished' => 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-500/20',
        'in_progress' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20',
        'canceled' => 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20',
        'on_hold' => 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20',
    ];
@endphp

<div class="relative py-1 px-4" x-data="{ open: false }" @click.stop x-on:close-other-dropdowns.window="if ($event.detail.id !== {{ $record->id }}) open = false" wire:key="status-dropdown-{{ $record->id }}-{{ $record->status }}">
    <!-- Toggle Button -->
    <button 
        x-ref="button"
        type="button"
        @click.stop="open = !open; if (open) $dispatch('close-other-dropdowns', { id: {{ $record->id }} })"
        @click.away="open = false"
        @class([
            'inline-flex items-center gap-1.5 text-xs font-bold rounded-lg px-2.5 py-1.5 ring-1 ring-inset transition cursor-pointer',
            $statusClasses[$status] ?? 'bg-gray-50 text-gray-600 ring-gray-500/10',
        ])
    >
        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
        <span>{{ $statusLabels[$status] ?? $status }}</span>
        <x-heroicon-m-chevron-down class="w-3.5 h-3.5 opacity-60" style="width: 14px; height: 14px;" />
    </button>

    <!-- Dropdown Menu -->
    <div 
        x-show="open"
        x-transition
        x-anchor.bottom-start="$refs.button"
        class="fixed z-50 w-48 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-lg py-1.5 text-xs"
        style="display: none;"
    >
        @foreach ($statusLabels as $statusKey => $statusVal)
            <button 
                type="button"
                wire:click="updateStatus({{ $record->id }}, '{{ $statusKey }}')"
                @click.stop="open = false"
                class="flex items-center gap-2 w-full text-start px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800/60 transition text-gray-700 dark:text-gray-300 font-semibold"
            >
                @php
                    $dotColor = match ($statusKey) {
                        'finished_paid' => 'bg-emerald-500',
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
                @if ($status === $statusKey)
                    <x-heroicon-m-check class="w-3.5 h-3.5 ml-auto text-primary-500" style="width: 14px; height: 14px;" />
                @endif
            </button>
        @endforeach
    </div>
</div>
