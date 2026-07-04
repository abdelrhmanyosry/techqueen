<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use App\Models\Client;
use App\Models\ClientModel;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('importCsv')
                ->label(__('Import CSV'))
                ->icon('heroicon-m-arrow-up-tray')
                ->color('info')
                ->url(fn (): string => static::getResource()::getUrl('import')),
        ];
    }
}
