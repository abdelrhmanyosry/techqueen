<?php

namespace App\Filament\Resources\ClientModels\Pages;

use App\Filament\Resources\ClientModels\ClientModelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClientModel extends CreateRecord
{
    protected static string $resource = ClientModelResource::class;
}
