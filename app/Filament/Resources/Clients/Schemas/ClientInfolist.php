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
                Section::make('Client Details')
                    ->icon('heroicon-o-user')
                    ->components([
                        Grid::make(3)
                            ->components([
                                TextEntry::make('name')
                                    ->weight(FontWeight::Bold)
                                    ->color('primary')
                                    ->icon('heroicon-m-user')
                                    ->iconColor('primary'),
                                
                                TextEntry::make('field')
                                    ->placeholder('-')
                                    ->weight(FontWeight::SemiBold)
                                    ->icon('heroicon-m-briefcase'),
                                
                                TextEntry::make('phone')
                                    ->weight(FontWeight::SemiBold)
                                    ->icon('heroicon-m-phone')
                                    ->iconColor('success')
                                    ->copyable(),
                            ]),
                    ]),

                Section::make('Additional Notes')
                    ->icon('heroicon-o-document-text')
                    ->components([
                        TextEntry::make('notes')
                            ->placeholder('No notes recorded for this client.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('System Information')
                    ->icon('heroicon-o-cog')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextEntry::make('created_at')
                                    ->date()
                                    ->icon('heroicon-m-calendar')
                                    ->color('gray'),
                                
                                TextEntry::make('updated_at')
                                    ->date()
                                    ->icon('heroicon-m-arrow-path')
                                    ->color('gray'),
                            ]),
                    ])
                    ->collapsed(),
            ]);
    }
}
