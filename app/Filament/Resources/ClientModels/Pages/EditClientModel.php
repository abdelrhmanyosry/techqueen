<?php

namespace App\Filament\Resources\ClientModels\Pages;

use App\Filament\Resources\ClientModels\ClientModelResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditClientModel extends EditRecord
{
    protected static string $resource = ClientModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
