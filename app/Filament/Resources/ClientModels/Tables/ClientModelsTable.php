<?php

namespace App\Filament\Resources\ClientModels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class ClientModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->placeholder('Admin / Self')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('piece_name')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Type')
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
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('receiving_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('delivery_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('deposit')
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->deposit)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' EGP')
                    ->sortable(),
                TextColumn::make('price')
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->price)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' EGP')
                    ->sortable(),
                ViewColumn::make('status')
                    ->label('Status')
                    ->view('filament.tables.columns.status-dropdown')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('completed_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('employee')
                    ->label('Employee / Assignee')
                    ->options(function () {
                        $options = \App\Models\Employee::pluck('name', 'id')->toArray();
                        return ['admin' => 'Admin / Self'] + $options;
                    })
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return;
                        }

                        if ($data['value'] === 'admin') {
                            $query->whereNull('employee_id');
                        } else {
                            $query->where('employee_id', $data['value']);
                        }
                    })
                    ->placeholder('All Assignees')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label('Model Type')
                    ->options([
                        'scan' => 'Scan',
                        'drawing' => 'Drawing',
                        'scan_drawing' => 'Scan + Drawing',
                    ])
                    ->placeholder('All Types'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->recordUrl(null)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
