<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class ModelsRelationManager extends RelationManager
{
    protected static string $relationship = 'models';

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('piece_name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),

                Tables\Columns\TextColumn::make('price')
                    ->money('EGP'),

                Tables\Columns\TextColumn::make('deposit')
                    ->money('EGP'),

                Tables\Columns\TextColumn::make('receiving_date')
                    ->date(),

                Tables\Columns\TextColumn::make('delivery_date')
                    ->date(),

            ])

            ->filters([

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'delayed' => 'Delayed',
                        'canceled' => 'Canceled',
                    ])

            ]);
    }
}
