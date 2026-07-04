<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('whatsappReminder')
                ->label(__('WhatsApp Reminder'))
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->url(function ($record) {
                    $models = \App\Models\ClientModel::where('employee_id', $record->id)
                        ->whereNotIn('status', ['finished_paid', 'completed', 'canceled'])
                        ->orderBy('delivery_date', 'asc')
                        ->limit(2)
                        ->get();
                    
                    if ($models->isEmpty()) {
                        return '#';
                    }
                    
                    $message = "Hi {$record->name},\n\nThis is a reminder of your upcoming model deliveries:\n\n";
                    foreach ($models as $idx => $model) {
                        $dueDate = $model->delivery_date ? \Carbon\Carbon::parse($model->delivery_date)->format('d M Y') : 'No Date';
                        $message .= ($idx + 1) . ". {$model->piece_name} (Due: {$dueDate})\n";
                    }
                    $message .= "\nPlease make sure they are delivered on time. Thank you!";
                    
                    $phone = preg_replace('/[^0-9]/', '', $record->phone);
                    return "https://wa.me/{$phone}?text=" . rawurlencode($message);
                })
                ->openUrlInNewTab()
                ->visible(fn ($record) => !empty($record->phone))
                ->disabled(fn ($record) => \App\Models\ClientModel::where('employee_id', $record->id)
                    ->whereNotIn('status', ['finished_paid', 'completed', 'canceled'])
                    ->count() === 0
                ),
            DeleteAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Resources\Employees\Widgets\MonthlyEarningsWidget::class,
        ];
    }
}
