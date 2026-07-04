<?php

namespace App\Filament\Widgets;

use App\Models\ClientModel;
use App\Models\Employee;
use Filament\Widgets\Widget;

class DeliveryAndWorkloadReminderWidget extends Widget
{
    protected string $view = 'filament.widgets.delivery-and-workload-reminder-widget';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        // 1. Models to deliver (active, sorted by delivery date)
        $deliveries = ClientModel::query()
            ->whereNotIn('status', ['finished_paid', 'completed', 'canceled'])
            ->with('client')
            ->orderBy('delivery_date', 'asc')
            ->limit(5)
            ->get();

        // 2. Active work assigned to employees
        $employees = Employee::query()
            ->with(['models' => fn ($q) => $q->whereNotIn('status', ['finished_paid', 'completed', 'canceled'])->with('client')])
            ->get();

        // 3. Active work assigned to Admin / Self (unassigned)
        $adminModels = ClientModel::query()
            ->whereNull('employee_id')
            ->whereNotIn('status', ['finished_paid', 'completed', 'canceled'])
            ->with('client')
            ->get();

        // 4. Success rate calculations (Current Month)
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $successModelsCount = ClientModel::query()
            ->whereIn('status', ['finished_paid', 'completed', 'finished_unpaid'])
            ->where(fn ($q) => 
                $q->whereMonth('completed_at', $currentMonth)->whereYear('completed_at', $currentYear)
                  ->orWhere(fn ($sq) => 
                      $sq->whereNull('completed_at')
                         ->whereMonth('delivery_date', $currentMonth)
                         ->whereYear('delivery_date', $currentYear)
                  )
            )
            ->count();
            
        $elapsedDays = now()->day;
        $successRatePerDay = $elapsedDays > 0 ? ($successModelsCount / $elapsedDays) : 0;

        $totalModelsCount = ClientModel::query()
            ->where(fn ($q) => 
                $q->whereMonth('completed_at', $currentMonth)->whereYear('completed_at', $currentYear)
                  ->orWhere(fn ($sq) => 
                      $sq->whereMonth('delivery_date', $currentMonth)
                         ->whereYear('delivery_date', $currentYear)
                  )
            )
            ->count();

        $completionRate = $totalModelsCount > 0 ? ($successModelsCount / $totalModelsCount) * 100 : 0;

        return [
            'deliveries' => $deliveries,
            'employees' => $employees,
            'adminModels' => $adminModels,
            'successModelsCount' => $successModelsCount,
            'successRatePerDay' => $successRatePerDay,
            'totalModelsCount' => $totalModelsCount,
            'completionRate' => $completionRate,
            'elapsedDays' => $elapsedDays,
        ];
    }
}
