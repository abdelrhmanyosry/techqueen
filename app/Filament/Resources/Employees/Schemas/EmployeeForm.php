<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                TextInput::make('commission_rate')
                    ->numeric()
                    ->label('Commission Rate')
                    ->helperText('Default is 50% commission per completed model')
                    ->suffix('%')
                    ->default(50)
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),
            ]);
    }
}
