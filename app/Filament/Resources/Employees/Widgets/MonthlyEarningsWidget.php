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

        $isSqlite = \DB::connection()->getDriverName() === 'sqlite';
        $dateExpr = $isSqlite 
            ? 'strftime("%Y-%m", COALESCE(completed_at, created_at))'
            : 'DATE_FORMAT(COALESCE(completed_at, created_at), "%Y-%m")';

        $subquery = ClientModel::query()
            ->where('employee_id', $this->record->id)
            ->whereIn('status', ['finished_paid', 'completed'])
            ->selectRaw("
                MIN(id) as id,
                {$dateExpr} as month_year, 
                COUNT(*) as jobs_count, 
                SUM(price) as total_revenue,
                SUM((price * ?) / 100) as total_commission,
                SUM(CASE WHEN employee_paid = 1 THEN (price * ?) / 100 ELSE 0 END) as paid_commission,
                SUM(CASE WHEN employee_paid = 0 THEN (price * ?) / 100 ELSE 0 END) as unpaid_commission
            ", [$this->record->commission_rate, $this->record->commission_rate, $this->record->commission_rate])
            ->groupByRaw($dateExpr);

        return $table
            ->query(
                fn (): Builder => ClientModel::query()
                    ->fromSub($subquery, 'client_models')
            )
            ->defaultSort('month_year', 'desc')
            ->heading(__('Monthly Earnings Summary (Completed & Paid Work Only)'))
            ->columns([
                TextColumn::make('month_year')
                    ->label(__('Month'))
                    ->formatStateUsing(fn ($state) => Carbon::parse($state . '-01')->translatedFormat('F Y'))
                    ->weight('bold'),
                TextColumn::make('jobs_count')
                    ->label(__('Completed Models'))
                    ->alignCenter(),
                TextColumn::make('total_revenue')
                    ->label(__('Total Models Value'))
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->total_revenue)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' ' . __('EGP'))
                    ->alignRight(),
                TextColumn::make('total_commission')
                    ->label(__('Total Earnings'))
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->total_commission)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' ' . __('EGP'))
                    ->weight('semibold')
                    ->alignRight(),
                TextColumn::make('paid_commission')
                    ->label(__('Paid to Employee'))
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->paid_commission)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' ' . __('EGP'))
                    ->color('success')
                    ->weight('bold')
                    ->alignRight(),
                TextColumn::make('unpaid_commission')
                    ->label(__('Owed (Unpaid)'))
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->unpaid_commission)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' ' . __('EGP'))
                    ->color(fn ($state) => $state === '***' ? 'gray' : ($state > 0 ? 'danger' : 'gray'))
                    ->weight('bold')
                    ->alignRight(),
            ])
            ->filters([])
            ->actions([
                \Filament\Actions\Action::make('markPaid')
                    ->label(__('Mark Paid'))
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->modalHeading(__('Mark Month as Paid'))
                    ->modalDescription(__('Are you sure you want to mark all completed models in this month as paid to this employee?'))
                    ->action(function ($record, $livewire) use ($dateExpr) {
                        ClientModel::query()
                            ->where('employee_id', $this->record->id)
                            ->whereIn('status', ['finished_paid', 'completed'])
                            ->whereRaw("{$dateExpr} = ?", [$record->month_year])
                            ->update(['employee_paid' => true]);
                            
                        $livewire->dispatch('refreshMonthlyEarnings');
                    }),
            ])
            ->bulkActions([]);
    }
}
