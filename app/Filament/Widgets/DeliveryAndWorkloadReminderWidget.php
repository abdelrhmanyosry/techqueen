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

        return [
            'deliveries' => $deliveries,
            'employees' => $employees,
            'adminModels' => $adminModels,
        ];
    }
}
