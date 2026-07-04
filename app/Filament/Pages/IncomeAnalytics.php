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

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('Business Analytics');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Income & Business Analytics');
    }

    public int $month;
    public int $year;

    public array $yearlyIncomeChartData = [];
    public array $monthlyStatusChartData = [];

    // Memoized properties to prevent N+1 and duplicate queries
    private $memoizedMonthlyModels = null;
    private $memoizedStats = null;
    private $memoizedInsights = null;
    private $memoizedTopClients = null;

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
        // Reset memoized properties when month or year changes
        $this->memoizedMonthlyModels = null;
        $this->memoizedStats = null;
        $this->memoizedInsights = null;
        $this->memoizedTopClients = null;

        $this->yearlyIncomeChartData = $this->getYearlyIncomeChartData();
        $this->monthlyStatusChartData = $this->getMonthlyStatusChartData();
    }

    public function getMonthsProperty(): array
    {
        return [
            1 => __('January'), 2 => __('February'), 3 => __('March'), 4 => __('April'),
            5 => __('May'), 6 => __('June'), 7 => __('July'), 8 => __('August'),
            9 => __('September'), 10 => __('October'), 11 => __('November'), 12 => __('December')
        ];
    }

    public function getYearsProperty(): array
    {
        $startYear = now()->subYears(4)->year;
        $endYear = now()->addYears(2)->year;
        return range($startYear, $endYear);
    }

    // Helper property to fetch monthly models (optimised to load only necessary columns)
    public function getMonthlyModelsProperty()
    {
        if ($this->memoizedMonthlyModels === null) {
            $this->memoizedMonthlyModels = ClientModel::with('client')
                ->select([
                    'id',
                    'client_id',
                    'piece_name',
                    'receiving_date',
                    'delivery_date',
                    'price',
                    'deposit',
                    'status',
                    'completed_at'
                ])
                ->whereYear('delivery_date', $this->year)
                ->whereMonth('delivery_date', $this->month)
                ->get();
        }
        return $this->memoizedMonthlyModels;
    }

    // Calculated Statistics
    public function getStatsProperty(): array
    {
        if ($this->memoizedStats === null) {
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

            $this->memoizedStats = [
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

        return $this->memoizedStats;
    }

    // Business Performance Insights
    public function getInsightsProperty(): array
    {
        if ($this->memoizedInsights === null) {
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

            $this->memoizedInsights = [
                'collection_rate' => $collectionRate,
                'completion_rate' => $completionRate,
                'delayed_rate' => $delayedRate,
                'aov' => $aov,
                'delayed_value' => $delayedValue,
            ];
        }

        return $this->memoizedInsights;
    }

    // Top clients by revenue for the selected month/year
    public function getTopClientsProperty()
    {
        if ($this->memoizedTopClients === null) {
            $models = $this->monthlyModels;
            
            $this->memoizedTopClients = $models->groupBy('client_id')
                ->map(function ($group) {
                    $client = $group->first()->client;
                    return [
                        'name' => $client?->name ?? __('Walk-in Client'),
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

        return $this->memoizedTopClients;
    }

    // Chart Data for the Selected Year (Month-by-Month breakdown - optimized query)
    public function getYearlyIncomeChartData(): array
    {
        $yearModels = ClientModel::whereYear('delivery_date', $this->year)
            ->select(['id', 'status', 'price', 'deposit', 'delivery_date'])
            ->get();
        
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

            $labels[] = Carbon::create($this->year, $m, 1)->translatedFormat('M');
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
            'finished_paid' => ['label' => __('Finished and Paid'), 'color' => '#10b981'],
            'completed' => ['label' => __('Completed (Paid)'), 'color' => '#10b981'],
            'finished_unpaid' => ['label' => __('Finished but Unpaid'), 'color' => '#f59e0b'],
            'paid_unfinished' => ['label' => __('Paid but Not Finished'), 'color' => '#0ea5e9'],
            'in_progress' => ['label' => __('In Progress'), 'color' => '#3b82f6'],
            'canceled' => ['label' => __('Canceled'), 'color' => '#f43f5e'],
            'on_hold' => ['label' => __('On Hold'), 'color' => '#6b7280'],
            'delayed' => ['label' => __('Delayed'), 'color' => '#ef4444'],
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
            $labels[] = __('No Jobs');
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
                    $model->client?->name ?? __('Walk-in Client'),
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
