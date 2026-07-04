<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('whatsapp')
                ->label(__('WhatsApp'))
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->url(function ($record) {
                    if (!$record->phone) {
                        return null;
                    }
                    $phone = preg_replace('/[^0-9]/', '', $record->phone);
                    $message = "Hi {$record->name},\n\nThis is TechQueen Workshop.";
                    return "https://wa.me/{$phone}?text=" . rawurlencode($message);
                })
                ->openUrlInNewTab()
                ->visible(fn ($record) => !empty($record->phone)),
            EditAction::make(),
        ];
    }
}
