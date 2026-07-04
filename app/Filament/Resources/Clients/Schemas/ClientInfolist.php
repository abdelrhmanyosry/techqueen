<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class ClientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Client Details'))
                    ->icon('heroicon-o-user')
                    ->components([
                        Grid::make(3)
                            ->components([
                                TextEntry::make('name')
                                    ->label(__('Name'))
                                    ->weight(FontWeight::Bold)
                                    ->color('primary')
                                    ->icon('heroicon-m-user')
                                    ->iconColor('primary'),
                                
                                TextEntry::make('field')
                                    ->label(__('Field'))
                                    ->placeholder('-')
                                    ->weight(FontWeight::SemiBold)
                                    ->icon('heroicon-m-briefcase'),
                                
                                TextEntry::make('phone')
                                    ->label(__('Phone'))
                                    ->weight(FontWeight::SemiBold)
                                    ->icon('heroicon-m-phone')
                                    ->iconColor('success')
                                    ->copyable(),
                            ]),
                    ]),

                Section::make(__('Additional Notes'))
                    ->icon('heroicon-o-document-text')
                    ->components([
                        TextEntry::make('notes')
                            ->label(__('Notes'))
                            ->placeholder(__('No notes recorded for this client.'))
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make(__('System Information'))
                    ->icon('heroicon-o-cog')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextEntry::make('created_at')
                                    ->label(__('Created At'))
                                    ->date()
                                    ->icon('heroicon-m-calendar')
                                    ->color('gray'),
                                
                                TextEntry::make('updated_at')
                                    ->label(__('Updated At'))
                                    ->date()
                                    ->icon('heroicon-m-arrow-path')
                                    ->color('gray'),
                            ]),
                    ])
                    ->collapsed(),
            ]);
    }
}
