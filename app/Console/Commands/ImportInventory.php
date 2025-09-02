<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ImportInventoryChunk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class ImportInventory extends Command
{
    protected $signature = 'import:inventory {path : CSV path} {--chunk=500}';
    protected $description = 'Import inventory from CSV in queued chunks';

    public function handle(): int
    {
        $path = (string) $this->argument('path');
        if (!is_file($path)) {
            $this->error("File not found: {$path}");
            return 1;
        }
        $chunkSize = max(50, (int) $this->option('chunk'));

        $fh = fopen($path, 'rb');
        if (!$fh) {
            $this->error('Cannot open file');
            return 1;
        }
        $headers = fgetcsv($fh) ?: [];
        $headers = array_map(fn($h) => strtolower(trim((string) $h)), $headers);

        $rows = [];
        $batch = [];
        $count = 0;
        while (($line = fgetcsv($fh)) !== false) {
            $row = [];
            foreach ($headers as $idx => $key) {
                $row[$key] = $line[$idx] ?? null;
            }
            $rows[] = $row;
            if (count($rows) >= $chunkSize) {
                $batch[] = new ImportInventoryChunk($rows);
                $count += count($rows);
                $rows = [];
                if (count($batch) >= 5) {
                    Bus::batch($batch)->name('Import Inventory')->dispatch();
                    $batch = [];
                }
            }
        }
        if (!empty($rows)) {
            $batch[] = new ImportInventoryChunk($rows);
            $count += count($rows);
        }
        if (!empty($batch)) {
            $pending = Bus::batch($batch)->name('Import Inventory')->dispatch();
            $this->info('Batch ID: ' . $pending->id);
        }
        fclose($fh);

        $this->info("Queued import for {$count} rows");
        return 0;
    }
}
