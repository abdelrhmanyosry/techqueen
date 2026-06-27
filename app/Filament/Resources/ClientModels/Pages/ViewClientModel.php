<?php

namespace App\Filament\Resources\ClientModels\Pages;

use App\Filament\Resources\ClientModels\ClientModelResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewClientModel extends ViewRecord
{
    protected static string $resource = ClientModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
