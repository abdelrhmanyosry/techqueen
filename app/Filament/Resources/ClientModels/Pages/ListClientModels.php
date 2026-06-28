<?php

namespace App\Filament\Resources\ClientModels\Pages;

use App\Filament\Resources\ClientModels\ClientModelResource;
use App\Models\ClientModel;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListClientModels extends ListRecords
{
    protected static string $resource = ClientModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(fn () => ClientModel::count()),
            'in_progress' => Tab::make('In Progress')
                ->badge(fn () => ClientModel::where('status', 'in_progress')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'in_progress')),
            'paid_unfinished' => Tab::make('Paid & Unfinished')
                ->badge(fn () => ClientModel::where('status', 'paid_unfinished')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'paid_unfinished')),
            'finished_unpaid' => Tab::make('Finished & Unpaid')
                ->badge(fn () => ClientModel::where('status', 'finished_unpaid')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'finished_unpaid')),
            'finished_paid' => Tab::make('Finished & Paid')
                ->badge(fn () => ClientModel::whereIn('status', ['finished_paid', 'completed'])->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['finished_paid', 'completed'])),
            'on_hold' => Tab::make('On Hold')
                ->badge(fn () => ClientModel::where('status', 'on_hold')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'on_hold')),
            'canceled' => Tab::make('Canceled')
                ->badge(fn () => ClientModel::where('status', 'canceled')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'canceled')),
        ];
    }

    public function updateStatus($recordId, string $status): void
    {
        $record = ClientModel::find($recordId);
        if ($record) {
            $record->update(['status' => $status]);
        }
    }
}
