<?php

namespace App\Filament\Resources\ClientModels\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ClientModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('client_id')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->searchDebounce(100)
                    ->preload()
                    ->native(false)
                    ->getOptionLabelFromRecordUsing(fn ($record) => '<span class="flex items-center gap-2 py-1"><span class="p-1 rounded bg-amber-50 dark:bg-amber-500/10 text-amber-600"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></span><span class="font-semibold text-gray-900 dark:text-gray-200 text-xs">' . e($record->name) . '</span> <span class="text-[10px] text-gray-400">(' . e($record->field ?? 'Client') . ')</span></span>')
                    ->allowHtml()
                    ->required()
                    ->label('Client'),
                TextInput::make('piece_name')
                    ->required(),
                Select::make('type')
                    ->options([
                        'scan' => '<span class="flex items-center gap-2 py-1"><span class="inline-block w-2.5 h-2.5 rounded-full bg-sky-500"></span><span class="font-semibold text-gray-900 dark:text-gray-200 text-xs">Scan</span></span>',
                        'drawing' => '<span class="flex items-center gap-2 py-1"><span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500"></span><span class="font-semibold text-gray-900 dark:text-gray-200 text-xs">Drawing</span></span>',
                        'scan_drawing' => '<span class="flex items-center gap-2 py-1"><span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span><span class="font-semibold text-gray-900 dark:text-gray-200 text-xs">Scan + Drawing</span></span>',
                    ])
                    ->allowHtml()
                    ->native(false)
                    ->required()
                    ->label('Model Type'),
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
                    ->minValue(0)
                    ->default(0.0),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->prefix('EGP'),
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->label('Assign to Employee')
                    ->placeholder('Admin / Self (Unassigned)')
                    ->searchable()
                    ->searchDebounce(100)
                    ->preload()
                    ->native(false)
                    ->getOptionLabelFromRecordUsing(fn ($record) => '<span class="flex items-center gap-2 py-1"><span class="p-1 rounded bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg></span><span class="font-semibold text-gray-900 dark:text-gray-200 text-xs">' . e($record->name) . '</span> <span class="text-[10px] text-gray-400">(' . e($record->commission_rate) . '% Comm.)</span></span>')
                    ->allowHtml(),
                Select::make('status')
                    ->options([
                        'in_progress' => '<span class="flex items-center gap-2 py-1"><span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500"></span><span class="font-semibold text-blue-700 dark:text-blue-400 text-xs">In Progress</span></span>',
                        'canceled' => '<span class="flex items-center gap-2 py-1"><span class="inline-block w-2.5 h-2.5 rounded-full bg-rose-500"></span><span class="font-semibold text-rose-700 dark:text-rose-400 text-xs">Canceled</span></span>',
                        'on_hold' => '<span class="flex items-center gap-2 py-1"><span class="inline-block w-2.5 h-2.5 rounded-full bg-gray-400"></span><span class="font-semibold text-gray-700 dark:text-gray-400 text-xs">On Hold</span></span>',
                        'finished_unpaid' => '<span class="flex items-center gap-2 py-1"><span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500"></span><span class="font-semibold text-amber-700 dark:text-amber-400 text-xs">Finished but Unpaid</span></span>',
                        'paid_unfinished' => '<span class="flex items-center gap-2 py-1"><span class="inline-block w-2.5 h-2.5 rounded-full bg-sky-500"></span><span class="font-semibold text-sky-700 dark:text-sky-400 text-xs">Paid but Not Finished</span></span>',
                        'finished_paid' => '<span class="flex items-center gap-2 py-1"><span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span><span class="font-semibold text-emerald-700 dark:text-emerald-400 text-xs">Finished and Paid</span></span>',
                    ])
                    ->allowHtml()
                    ->native(false)
                    ->required()
                    ->default('in_progress'),
                DatePicker::make('completed_at'),
                FileUpload::make('images')
                    ->label('Model Images')
                    ->disk('public')
                    ->multiple()
                    ->image()
                    ->directory('model-images')
                    ->reorderable()
                    ->columnSpanFull(),
            ]);
    }
}
