<?php

namespace App\Filament\Pages;

use App\Models\ClientModel;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class Calendar extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected string $view = 'filament.pages.calendar';

    public static function getNavigationLabel(): string
    {
        return __('Calendar');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Delivery Calendar');
    }

    public int $month;
    public int $year;
    public ?string $selectedDate = null;

    public function mount()
    {
        $this->month = (int) request()->query('month', now()->month);
        $this->year = (int) request()->query('year', now()->year);
        $this->selectedDate = request()->query('date', now()->format('Y-m-d'));
    }

    public function selectDate(string $date)
    {
        $this->selectedDate = $date;
    }

    public function nextMonth()
    {
        if ($this->month === 12) {
            $this->month = 1;
            $this->year++;
        } else {
            $this->month++;
        }
        
        $this->selectedDate = Carbon::create($this->year, $this->month, 1)->format('Y-m-d');
    }

    public function previousMonth()
    {
        if ($this->month === 1) {
            $this->month = 12;
            $this->year--;
        } else {
            $this->month--;
        }

        $this->selectedDate = Carbon::create($this->year, $this->month, 1)->format('Y-m-d');
    }

    public function goToToday()
    {
        $this->month = now()->month;
        $this->year = now()->year;
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function getCalendarWeeksProperty(): array
    {
        $startOfMonth = Carbon::create($this->year, $this->month, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        
        $startOfWeekDay = $startOfMonth->dayOfWeek; // 0 (Sunday) to 6 (Saturday)
        
        $days = [];
        
        // Add blank days for the start of the week
        for ($i = 0; $i < $startOfWeekDay; $i++) {
            $days[] = null;
        }
        
        // Add days of the month
        for ($day = 1; $day <= $endOfMonth->day; $day++) {
            $days[] = Carbon::create($this->year, $this->month, $day);
        }
        
        // Pad the end to complete a week grid (multiple of 7)
        while (count($days) % 7 !== 0) {
            $days[] = null;
        }
        
        return array_chunk($days, 7);
    }

    public function getDeliveriesCountProperty(): array
    {
        return ClientModel::whereYear('delivery_date', $this->year)
            ->whereMonth('delivery_date', $this->month)
            ->get()
            ->groupBy(fn ($model) => Carbon::parse($model->delivery_date)->format('Y-m-d'))
            ->map(fn ($group) => $group->count())
            ->toArray();
    }

    public function getSelectedDateDeliveriesProperty()
    {
        if (!$this->selectedDate) {
            return collect();
        }

        return ClientModel::with('client')
            ->whereDate('delivery_date', $this->selectedDate)
            ->get();
    }

    public function getMonthNameProperty(): string
    {
        return Carbon::create($this->year, $this->month, 1)->translatedFormat('F Y');
    }

    public function updateDeliveryStatus(int $id, string $status)
    {
        $model = ClientModel::findOrFail($id);
        $model->update(['status' => $status]);

        \Filament\Notifications\Notification::make()
            ->title(__('Status Updated'))
            ->body(__('Model :name status has been updated successfully.', ['name' => $model->piece_name]))
            ->success()
            ->send();
    }
}
