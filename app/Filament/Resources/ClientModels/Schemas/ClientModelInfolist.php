<?php

namespace App\Filament\Resources\ClientModels\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class ClientModelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Piece & Client Details')
                    ->icon('heroicon-o-scissors')
                    ->components([
                        Grid::make(5)
                            ->components([
                                TextEntry::make('piece_name')
                                    ->weight(FontWeight::Bold)
                                    ->color('primary')
                                    ->icon('heroicon-m-sparkles')
                                    ->iconColor('primary'),

                                TextEntry::make('client.name')
                                    ->label('Client Name')
                                    ->weight(FontWeight::SemiBold)
                                    ->icon('heroicon-m-user')
                                    ->iconColor('info')
                                    ->url(fn ($record): ?string => $record->client_id ? route('filament.admin.resources.clients.view', ['record' => $record->client_id]) : null),

                                TextEntry::make('employee.name')
                                    ->label('Assigned Employee')
                                    ->placeholder('Admin / Self')
                                    ->weight(FontWeight::SemiBold)
                                    ->icon('heroicon-m-user-group')
                                    ->iconColor('info'),

                                TextEntry::make('type')
                                    ->label('Model Type')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'scan' => 'info',
                                        'drawing' => 'warning',
                                        'scan_drawing' => 'success',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (?string $state) => match ($state) {
                                        'scan' => 'Scan',
                                        'drawing' => 'Drawing',
                                        'scan_drawing' => 'Scan + Drawing',
                                        default => '-',
                                    })
                                    ->icon('heroicon-m-tag')
                                    ->iconColor('info'),

                                TextEntry::make('status')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'in_progress' => 'In Progress',
                                        'canceled' => 'Canceled',
                                        'on_hold' => 'On Hold',
                                        'finished_unpaid' => 'Finished but Unpaid',
                                        'paid_unfinished' => 'Paid but Not Finished',
                                        'finished_paid' => 'Finished and Paid',
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

                Section::make('Financial Details')
                    ->icon('heroicon-o-currency-dollar')
                    ->components([
                        Grid::make(3)
                            ->components([
                                TextEntry::make('price')
                                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->price)
                                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' EGP')
                                    ->weight(FontWeight::Bold)
                                    ->color('success')
                                    ->icon('heroicon-m-banknotes')
                                    ->iconColor('success'),

                                TextEntry::make('deposit')
                                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->deposit)
                                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' EGP')
                                    ->weight(FontWeight::SemiBold)
                                    ->color('info')
                                    ->icon('heroicon-m-arrow-down-tray')
                                    ->iconColor('info'),

                                TextEntry::make('remaining')
                                    ->label('Remaining Balance')
                                    ->state(fn ($record) => session('hide_prices', false) ? '***' : (float)(($record->price ?? 0) - ($record->deposit ?? 0)))
                                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' EGP')
                                    ->weight(FontWeight::Bold)
                                    ->color(fn ($record): string => (($record->price ?? 0) - ($record->deposit ?? 0)) > 0 ? 'warning' : 'success')
                                    ->icon('heroicon-m-credit-card'),
                            ]),
                    ]),

                Section::make('Production Dates')
                    ->icon('heroicon-o-calendar-days')
                    ->components([
                        Grid::make(3)
                            ->components([
                                TextEntry::make('receiving_date')
                                    ->date()
                                    ->weight(FontWeight::SemiBold)
                                    ->icon('heroicon-m-calendar-days'),

                                TextEntry::make('delivery_date')
                                    ->date()
                                    ->weight(FontWeight::SemiBold)
                                    ->color('warning')
                                    ->icon('heroicon-m-truck'),

                                TextEntry::make('completed_at')
                                    ->date()
                                    ->placeholder('Not completed yet')
                                    ->weight(FontWeight::SemiBold)
                                    ->color('success')
                                    ->icon('heroicon-m-check-badge'),
                            ]),
                    ]),

                Section::make('Modifications & Notes')
                    ->icon('heroicon-o-document-text')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextEntry::make('modification')
                                    ->placeholder('No specific modifications required.')
                                    ->icon('heroicon-m-wrench-screwdriver')
                                    ->columnSpan(1),

                                TextEntry::make('notes')
                                    ->placeholder('No additional notes.')
                                    ->icon('heroicon-m-document-text')
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Model Images / Photos')
                    ->icon('heroicon-o-photo')
                    ->components([
                        \Filament\Infolists\Components\ViewEntry::make('images')
                            ->label('')
                            ->view('filament.infolists.components.model-images-lightbox')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('System Information')
                    ->icon('heroicon-o-cog')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextEntry::make('created_at')
                                    ->date()
                                    ->icon('heroicon-m-calendar')
                                    ->color('gray'),

                                TextEntry::make('updated_at')
                                    ->date()
                                    ->icon('heroicon-m-arrow-path')
                                    ->color('gray'),
                            ]),
                    ])
                    ->collapsed(),
            ]);
    }
}
