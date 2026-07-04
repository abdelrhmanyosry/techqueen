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

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Models');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('piece_name')
                    ->label(__('Piece name'))
                    ->searchable(),

                Tables\Columns\ViewColumn::make('status')
                    ->label(__('Status'))
                    ->view('filament.tables.columns.status-dropdown')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('Price'))
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->price)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' ' . __('EGP')),

                Tables\Columns\TextColumn::make('deposit')
                    ->label(__('Deposit'))
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->deposit)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' ' . __('EGP')),

                Tables\Columns\TextColumn::make('receiving_date')
                    ->label(__('Receiving date'))
                    ->date(),

                Tables\Columns\TextColumn::make('delivery_date')
                    ->label(__('Delivery date'))
                    ->date(),

            ])

            ->filters([

                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'in_progress' => __('In Progress'),
                        'canceled' => __('Canceled'),
                        'on_hold' => __('On Hold'),
                        'finished_unpaid' => __('Finished but Unpaid'),
                        'paid_unfinished' => __('Paid but Not Finished'),
                        'finished_paid' => __('Finished and Paid'),
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
