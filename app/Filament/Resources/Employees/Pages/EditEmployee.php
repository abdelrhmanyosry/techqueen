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
                    $phone = preg_replace('/[^0-9]/', '', $record->phone);
                    if (str_starts_with($phone, '00')) {
                        $phone = substr($phone, 2);
                    } elseif (str_starts_with($phone, '0')) {
                        $phone = '20' . substr($phone, 1);
                    }

                    $models = \App\Models\ClientModel::where('employee_id', $record->id)
                        ->whereNotIn('status', ['finished_paid', 'completed', 'canceled'])
                        ->orderBy('delivery_date', 'asc')
                        ->limit(2)
                        ->get();
                    
                    if ($models->isEmpty()) {
                        $message = "Hi {$record->name},\n\nThis is TechQueen Workshop.";
                    } else {
                        $message = "Hi {$record->name},\n\nThis is a reminder of your upcoming model deliveries:\n\n";
                        foreach ($models as $idx => $model) {
                            $dueDate = $model->delivery_date ? \Carbon\Carbon::parse($model->delivery_date)->format('d M Y') : 'No Date';
                            $message .= ($idx + 1) . ". {$model->piece_name} (Due: {$dueDate})\n";
                        }
                        $message .= "\nPlease make sure they are delivered on time. Thank you!";
                    }
                    
                    return "https://wa.me/{$phone}?text=" . rawurlencode($message);
                })
                ->openUrlInNewTab()
                ->visible(fn ($record) => !empty($record->phone)),
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
