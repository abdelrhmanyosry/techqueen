<?php

namespace App\Filament\Resources\ClientModels;

use App\Filament\Resources\ClientModels\Pages\CreateClientModel;
use App\Filament\Resources\ClientModels\Pages\EditClientModel;
use App\Filament\Resources\ClientModels\Pages\ListClientModels;
use App\Filament\Resources\ClientModels\Pages\ViewClientModel;
use App\Filament\Resources\ClientModels\Schemas\ClientModelForm;
use App\Filament\Resources\ClientModels\Schemas\ClientModelInfolist;
use App\Filament\Resources\ClientModels\Tables\ClientModelsTable;
use App\Models\ClientModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClientModelResource extends Resource
{
    protected static ?string $model = ClientModel::class;

    protected static ?string $modelLabel = 'Model';

    protected static ?string $pluralModelLabel = 'Models';

    protected static ?string $navigationLabel = 'Models';

    protected static ?string $slug = 'models';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'piece_name';

    public static function form(Schema $schema): Schema
    {
        return ClientModelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClientModelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientModelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClientModels::route('/'),
            'create' => CreateClientModel::route('/create'),
            'view' => ViewClientModel::route('/{record}'),
            'edit' => EditClientModel::route('/{record}/edit'),
        ];
    }
}
