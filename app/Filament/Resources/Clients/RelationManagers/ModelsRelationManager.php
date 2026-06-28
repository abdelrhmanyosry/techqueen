<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class ModelsRelationManager extends RelationManager
{
    protected static string $relationship = 'models';

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('piece_name')
                    ->searchable(),

                Tables\Columns\ViewColumn::make('status')
                    ->label('Status')
                    ->view('filament.tables.columns.status-dropdown')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->price)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' EGP'),

                Tables\Columns\TextColumn::make('deposit')
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->deposit)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' EGP'),

                Tables\Columns\TextColumn::make('receiving_date')
                    ->date(),

                Tables\Columns\TextColumn::make('delivery_date')
                    ->date(),

            ])

            ->filters([

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'in_progress' => 'In Progress',
                        'canceled' => 'Canceled',
                        'on_hold' => 'On Hold',
                        'finished_unpaid' => 'Finished but Unpaid',
                        'paid_unfinished' => 'Paid but Not Finished',
                        'finished_paid' => 'Finished and Paid',
                    ])

            ]);
    }

    public function updateStatus($recordId, string $status): void
    {
        $record = \App\Models\ClientModel::find($recordId);
        if ($record) {
            $record->update(['status' => $status]);
        }
    }
}
