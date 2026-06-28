<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

use Livewire\Attributes\On;

class ModelsRelationManager extends RelationManager
{
    protected static string $relationship = 'models';

    #[On('refreshRelationManager')]
    public function refreshRelation(): void
    {
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('piece_name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('piece_name')
            ->columns([
                TextColumn::make('piece_name')
                    ->label('Model Piece')
                    ->searchable(),
                TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable(),
                  ViewColumn::make('status')
                    ->label('Status')
                    ->view('filament.tables.columns.status-dropdown')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('price')
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->price)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' EGP'),
                TextColumn::make('commission')
                    ->label('Commission')
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : ($record->price * ($this->getOwnerRecord()->commission_rate ?? 50)) / 100)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' EGP'),
                TextColumn::make('commission_status')
                    ->label('Payout Status')
                    ->badge()
                    ->state(fn ($record) => in_array($record->status, ['finished_paid', 'completed']) ? 'earned' : 'pending')
                    ->color(fn (string $state): string => match ($state) {
                        'earned' => 'success',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                \Filament\Tables\Columns\ToggleColumn::make('employee_paid')
                    ->label('Paid to Employee')
                    ->disabled(fn ($record) => !in_array($record->status, ['finished_paid', 'completed']))
                    ->afterStateUpdated(function ($livewire) {
                        $livewire->dispatch('refreshMonthlyEarnings');
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                \Filament\Actions\Action::make('reassign')
                    ->label('Assign to another')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->form([
                        \Filament\Forms\Components\Select::make('employee_id')
                            ->label('Employee')
                            ->options(fn () => \App\Models\Employee::pluck('name', 'id')->toArray())
                            ->placeholder('Admin / Self (Unassigned)')
                            ->native(false),
                    ])
                    ->action(function ($record, array $data, $livewire) {
                        $record->update(['employee_id' => $data['employee_id']]);
                        $livewire->dispatch('refreshMonthlyEarnings');
                    }),
            ])
            ->bulkActions([]);
    }

    public function updateStatus($recordId, string $status): void
    {
        $record = \App\Models\ClientModel::find($recordId);
        if ($record) {
            $record->update(['status' => $status]);
            $this->dispatch('refreshMonthlyEarnings');
        }
    }
}
