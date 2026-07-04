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
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable(),
                TextColumn::make('commission_rate')
                    ->label(__('Commission'))
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('total_earned')
                    ->label(__('Total Earned'))
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->models()
                        ->whereIn('status', ['finished_paid', 'completed'])
                        ->get()
                        ->sum(fn ($m) => ($m->price * $record->commission_rate) / 100)
                    )
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' ' . __('EGP')),
                TextColumn::make('pending_earned')
                    ->label(__('Pending Earnings'))
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->models()
                        ->whereNotIn('status', ['finished_paid', 'completed', 'canceled'])
                        ->get()
                        ->sum(fn ($m) => ($m->price * $record->commission_rate) / 100)
                    )
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' ' . __('EGP')),
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
