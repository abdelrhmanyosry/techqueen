<?php

namespace App\Filament\Resources\Clients\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\TextInput;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->wrap()
                    ->searchable(),
                TextColumn::make('field')
                    ->label(__('Field'))
                    ->wrap()
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->wrap()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('whatsapp')
                    ->label(__('WhatsApp'))
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(function ($record) {
                        if (!$record->phone) {
                            return null;
                        }
                        $phone = preg_replace('/[^0-9]/', '', $record->phone);
                        if (str_starts_with($phone, '00')) {
                            $phone = substr($phone, 2);
                        } elseif (str_starts_with($phone, '0')) {
                            $phone = '20' . substr($phone, 1);
                        }
                        $message = "Hi {$record->name},\n\nThis is TechQueen Workshop.";
                        return "https://wa.me/{$phone}?text=" . rawurlencode($message);
                    })
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->phone)),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\Action::make('fullscreen')
                    ->label(__('Full Screen'))
                    ->icon('heroicon-m-arrows-pointing-out')
                    ->color('gray')
                    ->extraAttributes([
                        'x-on:click.prevent.stop' => "document.body.classList.toggle('table-fullscreen-active')",
                    ]),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    
                    BulkAction::make('bulkUpdateField')
                        ->label(__('Update Field'))
                        ->icon('heroicon-o-briefcase')
                        ->color('info')
                        ->form([
                            TextInput::make('field')
                                ->label(__('Field'))
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'field' => $data['field'],
                                ]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
