<?php

namespace App\Filament\Pages;

use App\Models\ClientModel;
use App\Models\Client;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IncomeAnalytics extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected string $view = 'filament.pages.income-analytics';

    protected static ?string $navigationLabel = 'Business Analytics';

    protected static ?string $title = 'Income & Business Analytics';

    protected static ?int $navigationSort = 2;

    public int $month;
    public int $year;

    public array $yearlyIncomeChartData = [];
    public array $monthlyStatusChartData = [];

    public function mount()
    {
        $this->month = (int) request()->query('month', now()->month);
        $this->year = (int) request()->query('year', now()->year);
        $this->updateChartData();
    }

    public function updated($name)
    {
        if (in_array($name, ['month', 'year'])) {
            $this->updateChartData();
        }
    }

    public function updateChartData()
    {
        $this->yearlyIncomeChartData = $this->getYearlyIncomeChartData();
        $this->monthlyStatusChartData = $this->getMonthlyStatusChartData();
    }

    public function getMonthsProperty(): array
    {
        return [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
    }

    public function getYearsProperty(): array
    {
        $startYear = now()->subYears(4)->year;
        $endYear = now()->addYears(2)->year;
        return range($startYear, $endYear);
    }

    // Helper property to fetch monthly models
    public function getMonthlyModelsProperty()
    {
        return ClientModel::with('client')
            ->whereYear('delivery_date', $this->year)
            ->whereMonth('delivery_date', $this->month)
            ->get();
    }

    // Calculated Statistics
    public function getStatsProperty(): array
    {
        $models = $this->monthlyModels;
        
        $totalJobs = $models->count();
        
        $totalRevenue = $models->whereIn('status', ['finished_paid', 'paid_unfinished', 'completed'])->sum('price');
        
        $totalCollected = $models->sum(function ($model) {
            if (in_array($model->status, ['finished_paid', 'paid_unfinished', 'completed'])) {
                return $model->price;
            }
            return $model->deposit;
        });

        $totalOutstanding = $models->sum(function ($model) {
            if (in_array($model->status, ['finished_unpaid', 'in_progress', 'on_hold', 'delayed'])) {
                return max(0, $model->price - $model->deposit);
            }
            return 0;
        });

        $completedCount = $models->whereIn('status', ['finished_paid', 'finished_unpaid', 'completed'])->count();
        $canceledCount = $models->where('status', 'canceled')->count();
        $inProgressCount = $models->whereIn('status', ['in_progress', 'paid_unfinished'])->count();
        $onHoldCount = $models->where('status', 'on_hold')->count();

        // Delayed jobs: delivery_date is in the past and status is not finished or canceled, or status is explicitly 'delayed'
        $delayedCount = $models->filter(function ($model) {
            return $model->status === 'delayed' 
                || (Carbon::parse($model->delivery_date)->isPast() 
                    && !in_array($model->status, ['finished_paid', 'finished_unpaid', 'completed', 'canceled']));
        })->count();

        return [
            'total_jobs' => $totalJobs,
            'total_revenue' => $totalRevenue,
            'total_collected' => $totalCollected,
            'total_outstanding' => $totalOutstanding,
            'completed_count' => $completedCount,
            'canceled_count' => $canceledCount,
            'in_progress_count' => $inProgressCount,
            'on_hold_count' => $onHoldCount,
            'delayed_count' => $delayedCount,
        ];
    }

    // Business Performance Insights
    public function getInsightsProperty(): array
    {
        $stats = $this->stats;
        $models = $this->monthlyModels;

        $totalProjectValue = $stats['total_collected'] + $stats['total_outstanding'];
        $collectionRate = $totalProjectValue > 0 
            ? round(($stats['total_collected'] / $totalProjectValue) * 100, 1) 
            : 0;

        $completionRate = $stats['total_jobs'] > 0 
            ? round(($stats['completed_count'] / $stats['total_jobs']) * 100, 1) 
            : 0;

        $delayedRate = $stats['total_jobs'] > 0 
            ? round(($stats['delayed_count'] / $stats['total_jobs']) * 100, 1) 
            : 0;

        // Average Order Value (AOV) based on all non-canceled jobs
        $totalActiveJobsPrice = $models->where('status', '!=', 'canceled')->sum('price');
        $totalActiveJobsCount = $models->where('status', '!=', 'canceled')->count();
        $aov = $totalActiveJobsCount > 0 
            ? round($totalActiveJobsPrice / $totalActiveJobsCount, 2) 
            : 0;

        // Stuck value in delayed models
        $delayedValue = $models->filter(function ($model) {
            return $model->status === 'delayed'
                || (Carbon::parse($model->delivery_date)->isPast() 
                    && !in_array($model->status, ['finished_paid', 'finished_unpaid', 'completed', 'canceled']));
        })->sum(fn ($m) => $m->price - $m->deposit);

        return [
            'collection_rate' => $collectionRate,
            'completion_rate' => $completionRate,
            'delayed_rate' => $delayedRate,
            'aov' => $aov,
            'delayed_value' => $delayedValue,
        ];
    }

    // Top clients by revenue for the selected month/year
    public function getTopClientsProperty()
    {
        $models = $this->monthlyModels;
        
        return $models->groupBy('client_id')
            ->map(function ($group) {
                $client = $group->first()->client;
                return [
                    'name' => $client?->name ?? 'Walk-in Client',
                    'field' => $client?->field ?? 'N/A',
                    'jobs_count' => $group->count(),
                    'revenue' => $group->whereIn('status', ['finished_paid', 'paid_unfinished', 'completed'])->sum('price'),
                    'collected' => $group->sum(function ($model) {
                        if (in_array($model->status, ['finished_paid', 'paid_unfinished', 'completed'])) {
                            return $model->price;
                        }
                        return $model->deposit;
                    })
                ];
            })
            ->sortByDesc('revenue')
            ->take(5);
    }

    // Chart Data for the Selected Year (Month-by-Month breakdown)
    public function getYearlyIncomeChartData(): array
    {
        $yearModels = ClientModel::whereYear('delivery_date', $this->year)->get();
        
        $months = range(1, 12);
        $revenue = [];
        $collected = [];
        $outstanding = [];
        $labels = [];

        foreach ($months as $m) {
            $monthModels = $yearModels->filter(fn ($model) => Carbon::parse($model->delivery_date)->month === $m);
            
            $revenue[] = (float) $monthModels->whereIn('status', ['finished_paid', 'paid_unfinished', 'completed'])->sum('price');
            
            $collected[] = (float) $monthModels->sum(function ($model) {
                if (in_array($model->status, ['finished_paid', 'paid_unfinished', 'completed'])) {
                    return $model->price;
                }
                return $model->deposit;
            });

            $outstanding[] = (float) $monthModels->sum(function ($model) {
                if (in_array($model->status, ['finished_unpaid', 'in_progress', 'on_hold', 'delayed'])) {
                    return max(0, $model->price - $model->deposit);
                }
                return 0;
            });

            $labels[] = Carbon::create($this->year, $m, 1)->format('M');
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'collected' => $collected,
            'outstanding' => $outstanding
        ];
    }

    // Doughnut chart of job statuses for the selected month
    public function getMonthlyStatusChartData(): array
    {
        $models = $this->monthlyModels;
        
        $statuses = [
            'finished_paid' => ['label' => 'Finished and Paid', 'color' => '#10b981'],
            'completed' => ['label' => 'Completed (Paid)', 'color' => '#10b981'],
            'finished_unpaid' => ['label' => 'Finished but Unpaid', 'color' => '#f59e0b'],
            'paid_unfinished' => ['label' => 'Paid but Not Finished', 'color' => '#0ea5e9'],
            'in_progress' => ['label' => 'In Progress', 'color' => '#3b82f6'],
            'canceled' => ['label' => 'Canceled', 'color' => '#f43f5e'],
            'on_hold' => ['label' => 'On Hold', 'color' => '#6b7280'],
            'delayed' => ['label' => 'Delayed', 'color' => '#ef4444'],
        ];

        $labels = [];
        $values = [];
        $colors = [];

        foreach ($statuses as $key => $meta) {
            $count = $models->where('status', $key)->count();
            if ($count > 0) {
                $labels[] = $meta['label'];
                $values[] = $count;
                $colors[] = $meta['color'];
            }
        }

        // Handle case where month is empty
        if (empty($values)) {
            $labels[] = 'No Jobs';
            $values[] = 1;
            $colors[] = '#e5e7eb';
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors
        ];
    }

    // Export Monthly Report as CSV
    public function exportCSV()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=income-report-{$this->year}-{$this->month}.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $models = $this->monthlyModels;

        $callback = function() use($models) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Client Name', 'Piece Name', 'Receiving Date', 'Delivery Date', 'Price', 'Deposit', 'Remaining Balance', 'Status', 'Completed At']);

            foreach ($models as $model) {
                fputcsv($file, [
                    $model->id,
                    $model->client?->name ?? 'Walk-in Client',
                    $model->piece_name,
                    $model->receiving_date,
                    $model->delivery_date,
                    $model->price,
                    $model->deposit,
                    max(0, $model->price - $model->deposit),
                    str_replace('_', ' ', ucfirst($model->status)),
                    $model->completed_at ? Carbon::parse($model->completed_at)->toDateTimeString() : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
