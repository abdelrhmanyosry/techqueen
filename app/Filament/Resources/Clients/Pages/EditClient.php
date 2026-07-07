<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
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
            DeleteAction::make(),
        ];
    }
}
