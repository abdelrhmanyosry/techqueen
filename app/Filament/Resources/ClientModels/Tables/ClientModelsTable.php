<?php

namespace App\Filament\Resources\ClientModels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;

class ClientModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label(__('Thumbnail Image'))
                    ->disk('public')
                    ->state(fn ($record) => $record->thumbnail ?? (!empty($record->images) && is_array($record->images) ? $record->images[0] : null))
                    ->square()
                    ->size(40)
                    ->extraImgAttributes(fn ($record) => [
                        'class' => 'cursor-zoom-in hover:scale-105 transition duration-150',
                        'x-on:click.prevent.stop' => ($path = $record->thumbnail ?? (!empty($record->images) && is_array($record->images) ? $record->images[0] : null)) 
                            ? "\$dispatch('open-lightbox', { src: '" . asset('storage/' . $path) . "' })" 
                            : "",
                    ]),
                TextColumn::make('client.name')
                    ->label(__('Client'))
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.name')
                    ->label(__('Employee'))
                    ->placeholder(__('Admin / Self'))
                    ->wrap()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('piece_name')
                    ->label(__('Piece name'))
                    ->wrap()
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'scan' => 'info',
                        'drawing' => 'warning',
                        'scan_drawing' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'scan' => __('Scan'),
                        'drawing' => __('Drawing'),
                        'scan_drawing' => __('Scan + Drawing'),
                        default => '-',
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('scan_files_list')
                    ->label(__('Scan Files'))
                    ->state(fn ($record) => $record)
                    ->html()
                    ->formatStateUsing(function ($state) {
                        $record = $state;
                        if (empty($record->scan_files) || !is_array($record->scan_files)) {
                            return '<span class="text-gray-400 dark:text-gray-600 text-xs">-</span>';
                        }
                        
                        $html = '<div class="flex flex-wrap gap-1">';
                        foreach ($record->scan_files as $index => $path) {
                            $url = asset('storage/' . $path);
                            $filename = basename($path);
                            $html .= '<a href="' . $url . '" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-800 hover:bg-blue-100 transition" onclick="event.stopPropagation();" title="' . e($filename) . '">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ' . __('File') . ' ' . ($index + 1) . '
                            </a>';
                        }
                        $html .= '</div>';
                        
                        return $html;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('solidworks_files_list')
                    ->label(__('SolidWorks Files'))
                    ->state(fn ($record) => $record)
                    ->html()
                    ->formatStateUsing(function ($state) {
                        $record = $state;
                        if (empty($record->solidworks_files) || !is_array($record->solidworks_files)) {
                            return '<span class="text-gray-400 dark:text-gray-600 text-xs">-</span>';
                        }
                        
                        $html = '<div class="flex flex-wrap gap-1">';
                        foreach ($record->solidworks_files as $index => $path) {
                            $url = asset('storage/' . $path);
                            $filename = basename($path);
                            $html .= '<a href="' . $url . '" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-violet-50 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400 border border-violet-100 dark:border-violet-800 hover:bg-violet-100 transition" onclick="event.stopPropagation();" title="' . e($filename) . '">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ' . __('File') . ' ' . ($index + 1) . '
                            </a>';
                        }
                        $html .= '</div>';
                        
                        return $html;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('receiving_date')
                    ->label(__('Receiving date'))
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('delivery_date')
                    ->label(__('Delivery date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('deposit')
                    ->label(__('Deposit'))
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->deposit)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' ' . __('EGP'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price')
                    ->label(__('Price'))
                    ->state(fn ($record) => session('hide_prices', false) ? '***' : $record->price)
                    ->formatStateUsing(fn ($state) => $state === '***' ? '***' : number_format((float)$state, 0) . ' ' . __('EGP'))
                    ->sortable(),
                ViewColumn::make('status')
                    ->label(__('Status'))
                    ->view('filament.tables.columns.status-dropdown')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('completed_at')
                    ->label(__('Completed at'))
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('employee')
                    ->label(__('Employee / Assignee'))
                    ->options(function () {
                        $options = \App\Models\Employee::pluck('name', 'id')->toArray();
                        return ['admin' => __('Admin / Self')] + $options;
                    })
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return;
                        }

                        if ($data['value'] === 'admin') {
                            $query->whereNull('employee_id');
                        } else {
                            $query->where('employee_id', $data['value']);
                        }
                    })
                    ->placeholder(__('All Assignees'))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label(__('Model Type'))
                    ->options([
                        'scan' => __('Scan'),
                        'drawing' => __('Drawing'),
                        'scan_drawing' => __('Scan + Drawing'),
                    ])
                    ->placeholder(__('All Types')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->recordUrl(null)
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
                    
                    BulkAction::make('bulkUpdateStatus')
                        ->label(__('Update Status'))
                        ->icon('heroicon-o-check-circle')
                        ->color('warning')
                        ->form([
                            Select::make('status')
                                ->label(__('Status'))
                                ->options([
                                    'in_progress' => __('In Progress'),
                                    'canceled' => __('Canceled'),
                                    'on_hold' => __('On Hold'),
                                    'finished_unpaid' => __('Finished but Unpaid'),
                                    'paid_unfinished' => __('Paid but Not Finished'),
                                    'finished_paid' => __('Finished and Paid'),
                                ])
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => $data['status'],
                                    'completed_at' => in_array($data['status'], ['finished_paid', 'completed', 'finished_unpaid']) ? now() : null,
                                ]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('bulkUpdateType')
                        ->label(__('Update Model Type'))
                        ->icon('heroicon-o-tag')
                        ->color('info')
                        ->form([
                            Select::make('type')
                                ->label(__('Model Type'))
                                ->options([
                                    'scan' => __('Scan'),
                                    'drawing' => __('Drawing'),
                                    'scan_drawing' => __('Scan + Drawing'),
                                ])
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'type' => $data['type'],
                                ]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('bulkAssignEmployee')
                        ->label(__('Assign Employee'))
                        ->icon('heroicon-o-user-group')
                        ->color('success')
                        ->form([
                            Select::make('employee_id')
                                ->label(__('Employee'))
                                ->options(function () {
                                    $options = \App\Models\Employee::pluck('name', 'id')->toArray();
                                    return ['admin' => __('Admin / Self')] + $options;
                                })
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                            $employeeId = $data['employee_id'] === 'admin' ? null : $data['employee_id'];
                            foreach ($records as $record) {
                                $record->update([
                                    'employee_id' => $employeeId,
                                ]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
