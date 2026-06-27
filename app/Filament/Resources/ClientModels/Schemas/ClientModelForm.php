<?php

namespace App\Filament\Resources\ClientModels\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ClientModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('client_id')
                    ->required()
                    ->numeric(),
                TextInput::make('piece_name')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Textarea::make('modification')
                    ->columnSpanFull(),
                DatePicker::make('receiving_date')
                    ->required(),
                DatePicker::make('delivery_date')
                    ->required(),
                TextInput::make('deposit')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('status')
                    ->required()
                    ->default('in_progress'),
                DateTimePicker::make('completed_at'),
            ]);
    }
}
