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
                    ->label(__('Model Piece'))
                    ->searchable(),
                TextColumn::make('client.name')
                    ->label(__('Client'))
                    ->searchable(),
                  ViewColumn::make('status')
                    ->label(__('Status'))
                    ->view('filament.tables.columns.status-dropdown')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('price')
                    ->label(__('Price'))
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->price)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' ' . __('EGP')),
                TextColumn::make('commission')
                    ->label(__('Commission'))
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : ($record->price * ($this->getOwnerRecord()->commission_rate ?? 50)) / 100)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' ' . __('EGP')),
                TextColumn::make('commission_status')
                    ->label(__('Payout Status'))
                    ->badge()
                    ->state(fn ($record) => in_array($record->status, ['finished_paid', 'completed']) ? 'earned' : 'pending')
                    ->color(fn (string $state): string => match ($state) {
                        'earned' => 'success',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'earned' => __('Earned'),
                        'pending' => __('Pending'),
                        default => $state,
                    }),
                TextColumn::make('employee_paid_amount')
                    ->label(__('Paid to Employee'))
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : ($record->employee_paid_amount ?? 0))
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' ' . __('EGP'))
                    ->color(fn ($record) => $record->employee_paid ? 'success' : (($record->employee_paid_amount ?? 0) > 0 ? 'warning' : 'gray')),
                \Filament\Tables\Columns\ToggleColumn::make('employee_paid')
                    ->label(__('Fully Paid'))
                    ->disabled(fn ($record) => !in_array($record->status, ['finished_paid', 'completed']))
                    ->updateStateUsing(function ($record, $state) {
                        $commission = $state ? (int)(($record->price * ($this->getOwnerRecord()->commission_rate ?? 50)) / 100) : 0;
                        $record->update([
                            'employee_paid' => $state,
                            'employee_paid_amount' => $commission,
                        ]);
                    })
                    ->afterStateUpdated(function ($livewire) {
                        $livewire->dispatch('refreshMonthlyEarnings');
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                \Filament\Actions\Action::make('recordPayment')
                    ->label(__('Record Payment'))
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->form([
                        TextInput::make('amount')
                            ->label(__('Amount Paid'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->suffix(__('EGP')),
                    ])
                    ->modalDescription(function () {
                        $employee = $this->getOwnerRecord();
                        $commissionRate = $employee->commission_rate ?? 50;
                        
                        $unpaidCommission = \App\Models\ClientModel::query()
                            ->where('employee_id', $employee->id)
                            ->whereIn('status', ['finished_paid', 'completed'])
                            ->get()
                            ->sum(function ($model) use ($commissionRate) {
                                $totalCommission = (int) (($model->price * $commissionRate) / 100);
                                return $totalCommission - ($model->employee_paid_amount ?? 0);
                            });

                        return __('Total unpaid commission owed to this employee: :amount EGP', [
                            'amount' => number_format($unpaidCommission, 0)
                        ]);
                    })
                    ->action(function (array $data, $livewire) {
                        $amount = (int) $data['amount'];
                        $employee = $this->getOwnerRecord();
                        $commissionRate = $employee->commission_rate ?? 50;

                        // Fetch completed models that are unpaid or partially paid
                        $models = \App\Models\ClientModel::query()
                            ->where('employee_id', $employee->id)
                            ->whereIn('status', ['finished_paid', 'completed'])
                            ->where(function ($query) use ($commissionRate) {
                                $query->where('employee_paid', false)
                                      ->orWhereRaw('employee_paid_amount < (price * ?) / 100', [$commissionRate]);
                            })
                            ->orderBy('completed_at')
                            ->orderBy('id')
                            ->get();

                        $totalDistributed = 0;
                        foreach ($models as $model) {
                            if ($amount <= 0) {
                                break;
                            }

                            $totalCommission = (int) (($model->price * $commissionRate) / 100);
                            $alreadyPaid = $model->employee_paid_amount ?? 0;
                            $remaining = $totalCommission - $alreadyPaid;

                            if ($remaining <= 0) {
                                continue;
                            }

                            if ($amount >= $remaining) {
                                $model->update([
                                    'employee_paid_amount' => $totalCommission,
                                    'employee_paid' => true,
                                ]);
                                $amount -= $remaining;
                                $totalDistributed += $remaining;
                            } else {
                                $model->update([
                                    'employee_paid_amount' => $alreadyPaid + $amount,
                                    'employee_paid' => false,
                                ]);
                                $totalDistributed += $amount;
                                $amount = 0;
                            }
                        }

                        $livewire->dispatch('refreshMonthlyEarnings');
                        
                        \Filament\Notifications\Notification::make()
                            ->title(__('Payment Recorded Successfully'))
                            ->body(__('Distributed :distributed EGP among employee models.', ['distributed' => number_format($totalDistributed, 0)]))
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('reassign')
                    ->label(__('Assign to another'))
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->form([
                        \Filament\Forms\Components\Select::make('employee_id')
                            ->label(__('Employee'))
                            ->options(fn () => \App\Models\Employee::pluck('name', 'id')->toArray())
                            ->placeholder(__('Admin / Self (Unassigned)'))
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
