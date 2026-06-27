<?php

namespace App\Filament\Resources\ClientModels\Pages;

use App\Filament\Resources\ClientModels\ClientModelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClientModels extends ListRecords
{
    protected static string $resource = ClientModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
