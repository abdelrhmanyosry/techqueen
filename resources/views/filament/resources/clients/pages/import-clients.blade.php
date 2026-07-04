<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6" @if($isImporting) wire:poll.500ms="processNextBatch" @endif>
        
        <!-- Upload Card -->
        <div class="p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Upload Client CSV File') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('Upload a CSV file containing your client records. The CSV must contain headers matching the Egyptian formatting columns: اسم العميل, رقم, شغلانة, عربون, باقي, تاريخ تسليم, تاريخ استلام, ملاحظات, تعديل.') }}
            </p>
            
            <form wire:submit.prevent="startImport" class="space-y-4">
                <div class="border border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-6 bg-gray-50 dark:bg-gray-950/30 flex flex-col items-center justify-center relative">
                    <input type="file" wire:model="csvFile" id="csvFile" class="opacity-0 absolute inset-0 cursor-pointer w-full h-full" @disabled($isImporting)>
                    
                    <div class="flex flex-col items-center justify-center space-y-2 pointer-events-none">
                        <span class="p-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                            <x-heroicon-o-document-text class="w-8 h-8" />
                        </span>
                        @if($csvFile)
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $csvFile->getClientOriginalName() }}</span>
                            <span class="text-xs text-gray-400">{{ number_format($csvFile->getSize() / 1024, 1) }} KB</span>
                        @else
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Click or drag CSV file to upload') }}</span>
                            <span class="text-xs text-gray-400">{{ __('Supports CSV or TXT formats') }}</span>
                        @endif
                    </div>
                </div>

                @error('csvFile')
                    <p class="text-sm text-danger-600 dark:text-danger-400 font-semibold">{{ $message }}</p>
                @enderror

                <div class="flex justify-end gap-3">
                    <a href="{{ static::getResource()::getUrl('index') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition" @disabled($isImporting)>
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-info-600 hover:bg-info-500 rounded-xl shadow-sm transition flex items-center gap-2" @disabled($isImporting || !$csvFile)>
                        <x-heroicon-m-arrow-up-tray class="w-4 h-4" />
                        {{ __('Start Import') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Progress and Logs Section -->
        @if($isImporting || $currentIdx > 0)
            <div class="p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm space-y-6">
                <!-- Progress Bar -->
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-3 flex items-center justify-between">
                        <span>{{ __('Import Progress') }}</span>
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            {{ $currentIdx }} / {{ $totalRows }} {{ __('rows processed') }}
                        </span>
                    </h3>
                    <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-4 overflow-hidden shadow-inner border border-gray-200/50 dark:border-gray-700/50">
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-full rounded-full transition-all duration-300 shadow-md flex items-center justify-end px-2" style="width: {{ $progress }}%">
                            @if($progress > 5)
                                <span class="text-[9px] font-extrabold text-white">{{ $progress }}%</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Logs Box -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-2">{{ __('Import Logs') }}</h3>
                    <div x-data x-init="$watch('$wire.logs', () => { $nextTick(() => { $el.scrollTop = $el.scrollHeight }) })" class="bg-gray-950 text-emerald-400 font-mono text-xs p-4 rounded-xl shadow-inner h-64 overflow-y-auto space-y-1.5 scroll-smooth border border-gray-800 leading-relaxed select-all">
                        @foreach($logs as $log)
                            <div>{{ $log }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
