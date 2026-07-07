<?php

namespace App\Filament\Resources\ClientModels\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class ClientModelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->tabs([
                        Tab::make(__('Model Details'))
                            ->icon('heroicon-o-document-text')
                            ->components([
                                 Section::make(__('Piece & Client Details'))
                                    ->icon('heroicon-o-scissors')
                                    ->components([
                                        Grid::make(5)
                                            ->components([
                                                TextEntry::make('piece_name')
                                                    ->label(__('Piece name'))
                                                    ->weight(FontWeight::Bold)
                                                    ->color('primary')
                                                    ->icon('heroicon-m-sparkles')
                                                    ->iconColor('primary'),

                                                TextEntry::make('client.name')
                                                    ->label(__('Client Name'))
                                                    ->weight(FontWeight::SemiBold)
                                                    ->icon('heroicon-m-user')
                                                    ->iconColor('info')
                                                    ->url(fn ($record): ?string => $record->client_id ? route('filament.admin.resources.clients.view', ['record' => $record->client_id]) : null),

                                                TextEntry::make('employee.name')
                                                    ->label(__('Assigned Employee'))
                                                    ->placeholder(__('Admin / Self'))
                                                    ->weight(FontWeight::SemiBold)
                                                    ->icon('heroicon-m-user-group')
                                                    ->iconColor('info'),

                                                TextEntry::make('type')
                                                    ->label(__('Model Type'))
                                                    ->badge()
                                                    ->color(fn (?string $state): string => match ($state) {
                                                        'scan' => 'info',
                                                        'drawing' => 'warning',
                                                        'scan_drawing' => 'success',
                                                        default => 'gray',
                                                    })
                                                    ->formatStateUsing(fn (?string $state) => match ($state) {
                                                        'scan' => __('Scan'),
                                                        'drawing' => __('Drawing'),
                                                        'scan_drawing' => __('Scan + Drawing'),
                                                        default => '-',
                                                    })
                                                    ->icon('heroicon-m-tag')
                                                    ->iconColor('info'),

                                                TextEntry::make('status')
                                                    ->label(__('Status'))
                                                    ->badge()
                                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                                        'in_progress' => __('In Progress'),
                                                        'canceled' => __('Canceled'),
                                                        'on_hold' => __('On Hold'),
                                                        'finished_unpaid' => __('Finished but Unpaid'),
                                                        'paid_unfinished' => __('Paid but Not Finished'),
                                                        'finished_paid' => __('Finished and Paid'),
                                                        default => $state,
                                                    })
                                                    ->color(fn (string $state): string => match ($state) {
                                                        'finished_paid' => 'success',
                                                        'finished_unpaid' => 'warning',
                                                        'paid_unfinished' => 'info',
                                                        'in_progress' => 'info',
                                                        'canceled' => 'danger',
                                                        'on_hold' => 'gray',
                                                        default => 'gray',
                                                    })
                                                    ->icon(fn (string $state): string => match ($state) {
                                                        'finished_paid' => 'heroicon-m-check-circle',
                                                        'finished_unpaid' => 'heroicon-m-exclamation-circle',
                                                        'paid_unfinished' => 'heroicon-m-clock',
                                                        'in_progress' => 'heroicon-m-arrow-path',
                                                        'canceled' => 'heroicon-m-x-circle',
                                                        'on_hold' => 'heroicon-m-pause',
                                                        default => 'heroicon-m-question-mark-circle',
                                                    }),
                                            ]),
                                    ]),

                                 Section::make(__('Financial Details'))
                                    ->icon('heroicon-o-currency-dollar')
                                    ->components([
                                        Grid::make(3)
                                            ->components([
                                                TextEntry::make('price')
                                                    ->label(__('Price'))
                                                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->price)
                                                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' ' . __('EGP'))
                                                    ->weight(FontWeight::Bold)
                                                    ->color('success')
                                                    ->icon('heroicon-m-banknotes')
                                                    ->iconColor('success'),

                                                TextEntry::make('deposit')
                                                    ->label(__('Deposit'))
                                                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->deposit)
                                                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' ' . __('EGP'))
                                                    ->weight(FontWeight::SemiBold)
                                                    ->color('info')
                                                    ->icon('heroicon-m-arrow-down-tray')
                                                    ->iconColor('info'),

                                                TextEntry::make('remaining')
                                                    ->label(__('Remaining Balance'))
                                                    ->state(fn ($record) => session('hide_prices', false) ? '***' : (float)(($record->price ?? 0) - ($record->deposit ?? 0)))
                                                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' ' . __('EGP'))
                                                    ->weight(FontWeight::Bold)
                                                    ->color(fn ($record): string => (($record->price ?? 0) - ($record->deposit ?? 0)) > 0 ? 'warning' : 'success')
                                                    ->icon('heroicon-m-credit-card'),
                                            ]),
                                    ]),

                                 Section::make(__('Production Dates'))
                                    ->icon('heroicon-o-calendar-days')
                                    ->components([
                                        Grid::make(3)
                                            ->components([
                                                TextEntry::make('receiving_date')
                                                    ->label(__('Receiving date'))
                                                    ->date()
                                                    ->weight(FontWeight::SemiBold)
                                                    ->icon('heroicon-m-calendar-days'),

                                                TextEntry::make('delivery_date')
                                                    ->label(__('Delivery date'))
                                                    ->date()
                                                    ->weight(FontWeight::SemiBold)
                                                    ->color('warning')
                                                    ->icon('heroicon-m-truck'),

                                                TextEntry::make('completed_at')
                                                    ->label(__('Completed at'))
                                                    ->date()
                                                    ->placeholder(__('Not completed yet'))
                                                    ->weight(FontWeight::SemiBold)
                                                    ->color('success')
                                                    ->icon('heroicon-m-check-badge'),
                                            ]),
                                    ]),

                                 Section::make(__('Modifications & Notes'))
                                    ->icon('heroicon-o-document-text')
                                    ->components([
                                        Grid::make(2)
                                            ->components([
                                                TextEntry::make('modification')
                                                    ->label(__('Modification'))
                                                    ->placeholder(__('No specific modifications required.'))
                                                    ->icon('heroicon-m-wrench-screwdriver')
                                                    ->columnSpan(1),

                                                TextEntry::make('notes')
                                                    ->label(__('Notes'))
                                                    ->placeholder(__('No additional notes.'))
                                                    ->icon('heroicon-m-document-text')
                                                    ->columnSpan(1),
                                            ]),
                                    ])
                                    ->collapsible(),

                                 Section::make(__('Model Images / Photos'))
                                    ->icon('heroicon-o-photo')
                                    ->components([
                                        \Filament\Infolists\Components\ViewEntry::make('images')
                                            ->label('')
                                            ->view('filament.infolists.components.model-images-lightbox')
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible(),

                                 Section::make(__('System Information'))
                                    ->icon('heroicon-o-cog')
                                    ->components([
                                        Grid::make(2)
                                            ->components([
                                                TextEntry::make('created_at')
                                                    ->label(__('Created At'))
                                                    ->date()
                                                    ->icon('heroicon-m-calendar')
                                                    ->color('gray'),

                                                TextEntry::make('updated_at')
                                                    ->label(__('Updated At'))
                                                    ->date()
                                                    ->icon('heroicon-m-arrow-path')
                                                    ->color('gray'),
                                            ]),
                                    ])
                                    ->collapsed(),
                            ]),

                        Tab::make(__('Scan Files'))
                            ->icon('heroicon-o-document-magnifying-glass')
                            ->components([
                                Section::make(__('Scan Files'))
                                    ->icon('heroicon-o-document-magnifying-glass')
                                    ->components([
                                        TextEntry::make('scan_files')
                                            ->label(__('Scan Files'))
                                            ->html()
                                            ->formatStateUsing(function ($state, $record) {
                                                if (empty($record->scan_files) || !is_array($record->scan_files)) {
                                                    return '<div class="text-gray-500 dark:text-gray-400 text-sm py-4 text-center border border-dashed border-gray-200 dark:border-gray-800 rounded-lg">' . __('No scan files uploaded.') . '</div>';
                                                }
                                                
                                                $html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-3 py-2">';
                                                foreach ($record->scan_files as $index => $path) {
                                                    $url = asset('storage/' . $path);
                                                    $filename = basename($path);
                                                    $html .= '<div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
                                                        <div class="flex items-center gap-3 overflow-hidden">
                                                            <svg class="w-8 h-8 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                            <div class="truncate">
                                                                <div class="text-sm font-semibold truncate text-gray-900 dark:text-gray-100" title="' . e($filename) . '">' . e($filename) . '</div>
                                                                <div class="text-xs text-gray-500 dark:text-gray-400">' . __('File') . ' ' . ($index + 1) . '</div>
                                                            </div>
                                                        </div>
                                                        <a href="' . $url . '" target="_blank" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow transition shrink-0">
                                                            ' . __('Download') . '
                                                        </a>
                                                    </div>';
                                                }
                                                $html .= '</div>';
                                                
                                                return $html;
                                            })
                                            ->columnSpanFull(),
                                    ])
                            ]),

                        Tab::make(__('SolidWorks Files'))
                            ->icon('heroicon-o-document-duplicate')
                            ->components([
                                Section::make(__('SolidWorks Files'))
                                    ->icon('heroicon-o-document-duplicate')
                                    ->components([
                                        TextEntry::make('solidworks_files')
                                            ->label(__('SolidWorks Files'))
                                            ->html()
                                            ->formatStateUsing(function ($state, $record) {
                                                if (empty($record->solidworks_files) || !is_array($record->solidworks_files)) {
                                                    return '<div class="text-gray-500 dark:text-gray-400 text-sm py-4 text-center border border-dashed border-gray-200 dark:border-gray-800 rounded-lg">' . __('No SolidWorks files uploaded.') . '</div>';
                                                }
                                                
                                                $html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-3 py-2">';
                                                foreach ($record->solidworks_files as $index => $path) {
                                                    $url = asset('storage/' . $path);
                                                    $filename = basename($path);
                                                    $html .= '<div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
                                                        <div class="flex items-center gap-3 overflow-hidden">
                                                            <svg class="w-8 h-8 text-violet-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                            <div class="truncate">
                                                                <div class="text-sm font-semibold truncate text-gray-900 dark:text-gray-100" title="' . e($filename) . '">' . e($filename) . '</div>
                                                                <div class="text-xs text-gray-500 dark:text-gray-400">' . __('File') . ' ' . ($index + 1) . '</div>
                                                            </div>
                                                        </div>
                                                        <a href="' . $url . '" target="_blank" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-violet-600 hover:bg-violet-700 text-white shadow transition shrink-0">
                                                            ' . __('Download') . '
                                                        </a>
                                                    </div>';
                                                }
                                                $html .= '</div>';
                                                
                                                return $html;
                                            })
                                            ->columnSpanFull(),
                                    ])
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
