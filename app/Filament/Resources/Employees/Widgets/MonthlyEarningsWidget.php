<?php

namespace App\Filament\Resources\Employees\Widgets;

use App\Models\Employee;
use App\Models\ClientModel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Livewire\Attributes\On;

class MonthlyEarningsWidget extends TableWidget
{
    public ?Employee $record = null;

    protected int | string | array $columnSpan = 'full';

    #[On('refreshMonthlyEarnings')]
    public function refreshWidget(): void
    {
    }

    public function table(Table $table): Table
    {
        if (!$this->record) {
            return $table
                ->query(fn (): Builder => ClientModel::query()->whereRaw('1 = 0'))
                ->columns([]);
        }

        $subquery = ClientModel::query()
            ->where('employee_id', $this->record->id)
            ->whereIn('status', ['finished_paid', 'completed'])
            ->selectRaw('
                MIN(id) as id,
                DATE_FORMAT(COALESCE(completed_at, created_at), "%Y-%m") as month_year, 
                COUNT(*) as jobs_count, 
                SUM(price) as total_revenue,
                SUM((price * ?) / 100) as total_commission,
                SUM(CASE WHEN employee_paid = 1 THEN (price * ?) / 100 ELSE 0 END) as paid_commission,
                SUM(CASE WHEN employee_paid = 0 THEN (price * ?) / 100 ELSE 0 END) as unpaid_commission
            ', [$this->record->commission_rate, $this->record->commission_rate, $this->record->commission_rate])
            ->groupByRaw('DATE_FORMAT(COALESCE(completed_at, created_at), "%Y-%m")');

        return $table
            ->query(
                fn (): Builder => ClientModel::query()
                    ->fromSub($subquery, 'client_models')
            )
            ->defaultSort('month_year', 'desc')
            ->heading('Monthly Earnings Summary (Completed & Paid Work Only)')
            ->columns([
                TextColumn::make('month_year')
                    ->label('Month')
                    ->formatStateUsing(fn ($state) => Carbon::parse($state . '-01')->format('F Y'))
                    ->weight('bold'),
                TextColumn::make('jobs_count')
                    ->label('Completed Models')
                    ->alignCenter(),
                TextColumn::make('total_revenue')
                    ->label('Total Models Value')
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->total_revenue)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' EGP')
                    ->alignRight(),
                TextColumn::make('total_commission')
                    ->label('Total Earnings')
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->total_commission)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' EGP')
                    ->weight('semibold')
                    ->alignRight(),
                TextColumn::make('paid_commission')
                    ->label('Paid to Employee')
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->paid_commission)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' EGP')
                    ->color('success')
                    ->weight('bold')
                    ->alignRight(),
                TextColumn::make('unpaid_commission')
                    ->label('Owed (Unpaid)')
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->unpaid_commission)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' EGP')
                    ->color(fn ($state) => $state === '***' ? 'gray' : ($state > 0 ? 'danger' : 'gray'))
                    ->weight('bold')
                    ->alignRight(),
            ])
            ->filters([])
            ->actions([
                \Filament\Actions\Action::make('markPaid')
                    ->label('Mark Paid')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->modalHeading('Mark Month as Paid')
                    ->modalDescription('Are you sure you want to mark all completed models in this month as paid to this employee?')
                    ->action(fn ($record, $livewire) => ClientModel::query()
                        ->where('employee_id', $this->record->id)
                        ->whereIn('status', ['finished_paid', 'completed'])
                        ->whereRaw('DATE_FORMAT(COALESCE(completed_at, created_at), "%Y-%m") = ?', [$record->month_year])
                        ->update(['employee_paid' => true])
                    ),
            ])
            ->bulkActions([]);
    }
}
