<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use Filament\Resources\Pages\Page;
use Livewire\WithFileUploads;
use App\Models\Client;
use App\Models\ClientModel;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class ImportClients extends Page
{
    use WithFileUploads;

    protected static string $resource = ClientResource::class;

    protected string $view = 'filament.resources.clients.pages.import-clients';

    public $csvFile;
    public $isImporting = false;
    public $progress = 0;
    public $logs = [];
    public $importedClients = 0;
    public $importedModels = 0;
    public $totalRows = 0;
    public $rowsToProcess = [];
    public $currentIdx = 0;

    public function mount(): void
    {
        $this->logs[] = __('Please upload a CSV file to start the import process.');
    }

    public function getTitle(): string
    {
        return __('Import CSV');
    }

    public function startImport()
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt',
        ]);

        $this->isImporting = true;
        $this->progress = 0;
        $this->logs = [];
        $this->logs[] = __('Parsing CSV file...');
        $this->importedClients = 0;
        $this->importedModels = 0;

        $path = $this->csvFile->getRealPath();
        $file = fopen($path, 'r');
        
        // Read first line to detect delimiter
        $firstLine = fgets($file);
        rewind($file);
        $delimiter = ',';
        if ($firstLine !== false) {
            $commas = substr_count($firstLine, ',');
            $semicolons = substr_count($firstLine, ';');
            if ($semicolons > $commas) {
                $delimiter = ';';
            }
        }

        // Read headers
        $headers = fgetcsv($file, 0, $delimiter);
        if (!$headers) {
            fclose($file);
            $this->logs[] = __('Error: Headers not found in CSV.');
            $this->isImporting = false;
            return;
        }

        $cleanEncoding = function ($str) {
            if (empty($str)) return '';
            $str = trim($str);
            if (!mb_check_encoding($str, 'UTF-8')) {
                $str = mb_convert_encoding($str, 'UTF-8', 'CP1256');
            }
            $str = preg_replace('/^\xEF\xBB\xBF/', '', $str);
            return trim($str);
        };

        $headers = array_map($cleanEncoding, $headers);

        $nameIdx = array_search('اسم العميل', $headers);
        $phoneIdx = array_search('رقم', $headers);
        $fieldIdx = array_search('مجال', $headers);
        $jobIdx = array_search('شغلانة', $headers);
        $notesIdx = array_search('ملاحظات', $headers);
        $modIdx = array_search('تعديل', $headers);
        $recIdx = array_search('تاريخ استلام', $headers);
        $delIdx = array_search('تاريخ تسليم', $headers);
        $depIdx = array_search('عربون', $headers);
        $balIdx = array_search('باقي', $headers);

        if ($nameIdx === false) {
            $nameIdx = 0;
        }

        $this->rowsToProcess = [];
        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            $row = array_map($cleanEncoding, $row);
            if (empty($row) || count($row) < 1 || empty(implode('', $row))) {
                continue;
            }
            $this->rowsToProcess[] = [
                'row' => $row,
                'nameIdx' => $nameIdx,
                'phoneIdx' => $phoneIdx,
                'fieldIdx' => $fieldIdx,
                'jobIdx' => $jobIdx,
                'notesIdx' => $notesIdx,
                'modIdx' => $modIdx,
                'recIdx' => $recIdx,
                'delIdx' => $delIdx,
                'depIdx' => $depIdx,
                'balIdx' => $balIdx,
            ];
        }
        fclose($file);

        $this->totalRows = count($this->rowsToProcess);
        if ($this->totalRows === 0) {
            $this->logs[] = __('No valid rows found in the uploaded file.');
            $this->isImporting = false;
            return;
        }

        $this->logs[] = __('Found :count rows. Starting import...', ['count' => $this->totalRows]);
        $this->currentIdx = 0;
    }

    public function processNextBatch()
    {
        if (!$this->isImporting) {
            return;
        }

        $batchSize = 2;
        $end = min($this->currentIdx + $batchSize, $this->totalRows);

        for ($i = $this->currentIdx; $i < $end; $i++) {
            $item = $this->rowsToProcess[$i];
            $row = $item['row'];
            
            $clientName = isset($row[$item['nameIdx']]) ? $row[$item['nameIdx']] : '';
            if (empty($clientName) || $clientName === 'اسم العميل' || $clientName === 'Client Name') {
                continue;
            }

            $phone = $item['phoneIdx'] !== false && isset($row[$item['phoneIdx']]) ? $row[$item['phoneIdx']] : '';
            $field = $item['fieldIdx'] !== false && isset($row[$item['fieldIdx']]) ? $row[$item['fieldIdx']] : '';
            $pieceName = $item['jobIdx'] !== false && isset($row[$item['jobIdx']]) ? $row[$item['jobIdx']] : '';
            $notes = $item['notesIdx'] !== false && isset($row[$item['notesIdx']]) ? $row[$item['notesIdx']] : '';
            $modification = $item['modIdx'] !== false && isset($row[$item['modIdx']]) ? $row[$item['modIdx']] : '';
            $recDateStr = $item['recIdx'] !== false && isset($row[$item['recIdx']]) ? $row[$item['recIdx']] : '';
            $delDateStr = $item['delIdx'] !== false && isset($row[$item['delIdx']]) ? $row[$item['delIdx']] : '';
            $depositStr = $item['depIdx'] !== false && isset($row[$item['depIdx']]) ? $row[$item['depIdx']] : '';
            $balanceStr = $item['balIdx'] !== false && isset($row[$item['balIdx']]) ? $row[$item['balIdx']] : '';

            // Find or create client
            $client = Client::where('name', $clientName)->first();
            if (!$client) {
                $client = Client::create([
                    'name' => $clientName,
                    'phone' => $phone ?: null,
                    'field' => $field ?: null,
                ]);
                $this->importedClients++;
                $this->logs[] = "✅ " . __('Imported Client: :name', ['name' => $clientName]);
            } else {
                $this->logs[] = "ℹ️ " . __('Client already exists: :name', ['name' => $clientName]);
            }

            if (!empty($pieceName) || !empty($depositStr) || !empty($balanceStr)) {
                // Parse prices
                $deposit = (int)preg_replace('/[^0-9]/', '', $depositStr);
                $balance = (int)preg_replace('/[^0-9]/', '', $balanceStr);
                $price = $deposit + $balance;

                // Parse dates
                $receivingDate = now()->format('Y-m-d');
                if (!empty($recDateStr)) {
                    try {
                        $parsedRec = Carbon::parse($recDateStr);
                        $receivingDate = $parsedRec->format('Y-m-d');
                    } catch (\Exception $e) {
                    }
                }

                $deliveryDate = Carbon::parse($receivingDate)->addDays(7)->format('Y-m-d');
                if (!empty($delDateStr)) {
                    if (str_contains($delDateStr, 'يوم') || str_contains($delDateStr, 'ايام') || str_contains($delDateStr, 'أيام')) {
                        preg_match('/[0-9]+/', $delDateStr, $matches);
                        if (!empty($matches[0])) {
                            $days = (int)$matches[0];
                            $deliveryDate = Carbon::parse($receivingDate)->addDays($days)->format('Y-m-d');
                        }
                    } elseif ($delDateStr === 'مفتوح') {
                        $deliveryDate = Carbon::parse($receivingDate)->addDays(30)->format('Y-m-d');
                    } else {
                        try {
                            $parsedDel = Carbon::parse($delDateStr);
                            $deliveryDate = $parsedDel->format('Y-m-d');
                        } catch (\Exception $e) {
                        }
                    }
                }

                ClientModel::create([
                    'client_id' => $client->id,
                    'piece_name' => $pieceName ?: __('New Job'),
                    'notes' => $notes ?: null,
                    'modification' => $modification ?: null,
                    'receiving_date' => $receivingDate,
                    'delivery_date' => $deliveryDate,
                    'deposit' => $deposit,
                    'price' => $price,
                    'status' => 'in_progress',
                ]);
                $this->importedModels++;
                $this->logs[] = "⚙️ " . __('Added Job: :job for client :client', ['job' => $pieceName ?: __('New Job'), 'client' => $clientName]);
            }
        }

        $this->currentIdx = $end;
        $this->progress = $this->totalRows > 0 ? (int)(($this->currentIdx / $this->totalRows) * 100) : 100;

        if ($this->currentIdx >= $this->totalRows) {
            $this->isImporting = false;
            $this->logs[] = "🎉 " . __('Import completed! Successfully imported :clients clients and :models models.', [
                'clients' => $this->importedClients,
                'models' => $this->importedModels
            ]);
            
            Notification::make()
                ->title(__('Import Completed'))
                ->body(__('Successfully imported :clients clients and :models models.', [
                    'clients' => $this->importedClients,
                    'models' => $this->importedModels
                ]))
                ->success()
                ->send();
        }
    }
}
