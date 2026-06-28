<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('commission_rate')
                    ->label('Commission')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('total_earned')
                    ->label('Total Earned')
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->models()
                        ->whereIn('status', ['finished_paid', 'completed'])
                        ->get()
                        ->sum(fn ($m) => ($m->price * $record->commission_rate) / 100)
                    )
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' EGP'),
                TextColumn::make('pending_earned')
                    ->label('Pending Earnings')
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->models()
                        ->whereNotIn('status', ['finished_paid', 'completed', 'canceled'])
                        ->get()
                        ->sum(fn ($m) => ($m->price * $record->commission_rate) / 100)
                    )
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' EGP'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
