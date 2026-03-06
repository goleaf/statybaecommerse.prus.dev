<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Filament\Imports\ProductImporter;
use App\Models\User;
use App\Services\ImportExport\CsvImportProcessor;
use App\Support\ImportExport\ProgressCounter;
use App\Support\Storage\SecureStorage;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Filament\Support\ChunkIterator;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\CharsetConverter;
use League\Csv\Info;
use League\Csv\Reader as CsvReader;
use League\Csv\Statement;

final class ProductImportingFromCSV extends Command
{
    protected $signature = 'import:products
        {path : CSV file path}
        {--chunk=100 : Rows processed per chunk}';

    protected $description = 'Import products from CSV and update existing products matched by SKU.';

    public function handle(): int
    {
        $sourcePath = $this->resolveSourcePath((string) $this->argument('path'));

        if ($sourcePath === null || ! is_file($sourcePath) || ! is_readable($sourcePath)) {
            $this->error('CSV file does not exist or is not readable.');

            return self::FAILURE;
        }

        $chunkSize = (int) $this->option('chunk');
        if ($chunkSize < 1) {
            $this->error('The --chunk option must be a positive integer.');

            return self::FAILURE;
        }

        $importOwner = User::query()
            ->withoutGlobalScopes()
            ->where('is_admin', true)
            ->orderBy('id')
            ->first();

        if (! $importOwner instanceof User) {
            $this->error('No admin user found (users.is_admin = 1).');

            return self::FAILURE;
        }

        $csvReader = $this->createCsvReader($sourcePath);
        if (! $csvReader instanceof CsvReader) {
            $this->error('Unable to open CSV file.');

            return self::FAILURE;
        }

        $headers = $this->normalizeCsvHeaders($csvReader->getHeader());
        if ($headers === []) {
            $this->error('CSV header row is missing.');

            return self::FAILURE;
        }

        $columnMap = $this->guessColumnMap($headers);
        $missingRequired = $this->missingRequiredMappings($columnMap);

        if ($missingRequired !== []) {
            $this->error('Missing required CSV mappings: ' . implode(', ', $missingRequired));

            return self::FAILURE;
        }

        $csvResults = (new Statement)->process($csvReader);
        $totalRows = (int) $csvResults->count();

        $storedCsvPath = $this->storeCsvFile($sourcePath);

        if ($storedCsvPath === null) {
            $this->error('Failed to store CSV into secure import storage.');

            return self::FAILURE;
        }

        $options = [
            'should_sync'                 => true,
            'sync_keys'                   => [['field' => 'sku']],
            'require_existing_sync_match' => true,
        ];

        $import = app(Import::class);
        $import->user()->associate($importOwner);
        $import->file_name = basename($sourcePath);
        $import->file_path = $storedCsvPath;
        $import->file_disk = SecureStorage::disk();
        $import->importer = ProductImporter::class;
        $import->total_rows = $totalRows;
        $import->column_map = json_encode($columnMap);
        $import->options = json_encode($options);
        $import->save();

        if ($totalRows === 0) {
            $import->touch('completed_at');
            $this->info('No data rows found. Import created with zero rows.');
            $this->line('Import ID: ' . $import->getKey());

            return self::SUCCESS;
        }

        auth()->setUser($importOwner);

        $processor = app(CsvImportProcessor::class);
        $importer = $import->getImporter($columnMap, $options);

        $records = $this->normalizedRecordsWithRowNumbers($csvResults->getRecords());
        $chunkIterator = new ChunkIterator($records, chunkSize: $chunkSize);

        foreach ($chunkIterator->get() as $importChunk) {
            $processor->processChunk($import, $importer, $importChunk, $columnMap);
        }

        $import->touch('completed_at');
        $import->refresh();

        $summary = $this->buildSummary($import);

        $this->info(sprintf(
            'Import completed (ID: %d). Processed: %d, Successful: %d, Failed: %d, Total: %d',
            $import->getKey(),
            $summary['processed'],
            $summary['successful'],
            $summary['failed'],
            $summary['total'],
        ));

        if ($summary['failed'] > 0) {
            $this->warn('Import finished with row failures. Review failed rows in import history.');
        }

        return self::SUCCESS;
    }

    private function resolveSourcePath(string $path): ?string
    {
        $path = trim($path);

        if ($path === '') {
            return null;
        }

        if (preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1 || str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }

        return base_path($path);
    }

    private function createCsvReader(string $sourcePath): ?CsvReader
    {
        $resource = fopen($sourcePath, 'r');

        if (! is_resource($resource)) {
            return null;
        }

        $resource = $this->applyEncodingFilter($resource);

        $reader = CsvReader::createFromStream($resource);

        if (filled($csvDelimiter = $this->guessCsvDelimiter($reader))) {
            $reader->setDelimiter($csvDelimiter);
        }

        $reader->setHeaderOffset(0);

        return $reader;
    }

    /**
     * @param  resource $resource
     * @return resource
     */
    private function applyEncodingFilter($resource)
    {
        $inputEncoding = $this->detectCsvEncoding($resource);
        $outputEncoding = 'UTF-8';

        if (
            filled($inputEncoding) &&
            (Str::lower($inputEncoding) !== Str::lower($outputEncoding))
        ) {
            CharsetConverter::register();

            stream_filter_append(
                $resource,
                CharsetConverter::getFiltername($inputEncoding, $outputEncoding),
                STREAM_FILTER_READ,
            );
        }

        return $resource;
    }

    /**
     * @param resource $resource
     */
    private function detectCsvEncoding($resource): ?string
    {
        rewind($resource);

        $lineCount = 0;
        $contentSample = '';

        while ((! feof($resource)) && ($lineCount < 20)) {
            $line = fgets($resource);

            if ($line === false) {
                break;
            }

            $contentSample .= $line;
            $lineCount++;
        }

        $encodings = [
            'UTF-8',
            'SJIS-win',
            'EUC-KR',
            'ISO-8859-1',
            'GB18030',
            'Windows-1251',
            'Windows-1252',
            'EUC-JP',
        ];

        foreach ($encodings as $encoding) {
            if (! mb_check_encoding($contentSample, $encoding)) {
                continue;
            }

            return $encoding;
        }

        return null;
    }

    private function guessCsvDelimiter(CsvReader $reader): ?string
    {
        $delimiterCounts = Info::getDelimiterStats($reader, delimiters: [',', ';', '|', "\t"], limit: 10);
        $delimiter = array_search(max($delimiterCounts), $delimiterCounts, true);

        return is_string($delimiter) ? $delimiter : null;
    }

    /**
     * @return array<int, ImportColumn>
     */
    private function importerColumns(): array
    {
        return ProductImporter::getColumns();
    }

    /**
     * @param  array<string>              $headers
     * @return array<string, string|null>
     */
    private function guessColumnMap(array $headers): array
    {
        $headers = $this->normalizeCsvHeaders($headers);
        $lowercaseCsvColumnValues = array_map(Str::lower(...), $headers);
        $lowercaseCsvColumnKeys = array_combine(
            $lowercaseCsvColumnValues,
            $headers,
        );

        if (! is_array($lowercaseCsvColumnKeys)) {
            return [];
        }

        $customGuesses = [
            'sku'         => ['product code', 'artikulas', 'kodas', 'sku number', 'item number'],
            'name'        => ['title', 'pavadinimas', 'label', 'product name', 'prekė'],
            'price'       => ['kaina', 'amount', 'cost', 'retail price', 'price (inc. vat)'],
            'description' => ['aprašymas', 'body', 'content', 'long description'],
            'status'      => ['būsena', 'state', 'availability', 'is active'],
        ];

        return array_reduce($this->importerColumns(), function (array $carry, ImportColumn $column) use ($lowercaseCsvColumnKeys, $lowercaseCsvColumnValues, $customGuesses): array {
            $name = $column->getName();
            $guesses = array_unique(array_merge(
                $column->getGuesses(),
                $customGuesses[$name] ?? []
            ));

            $guess = Arr::first(
                array_intersect(
                    $lowercaseCsvColumnValues,
                    $guesses,
                ),
            );

            $carry[$name] = filled($guess) ? $lowercaseCsvColumnKeys[$guess] : null;

            return $carry;
        }, []);
    }

    /**
     * @param  array<string, string|null> $columnMap
     * @return array<int, string>
     */
    private function missingRequiredMappings(array $columnMap): array
    {
        $missing = [];

        foreach ($this->importerColumns() as $column) {
            $name = $column->getName();

            if (! $column->isMappingRequired()) {
                continue;
            }

            if (filled($columnMap[$name] ?? null)) {
                continue;
            }

            $missing[] = $column->getLabel() ?? $name;
        }

        return $missing;
    }

    private function storeCsvFile(string $sourcePath): ?string
    {
        $disk = SecureStorage::disk();
        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $filename = (string) Str::uuid();

        if ($extension !== '') {
            $filename .= '.' . $extension;
        }

        $targetPath = 'imports/csv/' . $filename;

        $stream = fopen($sourcePath, 'r');

        if (! is_resource($stream)) {
            return null;
        }

        try {
            $stored = Storage::disk($disk)->put($targetPath, $stream);
        } finally {
            fclose($stream);
        }

        return $stored ? $targetPath : null;
    }

    /**
     * @param  iterable<array<string, mixed>> $records
     * @return iterable<array<string, mixed>>
     */
    private function normalizedRecordsWithRowNumbers(iterable $records): iterable
    {
        $rowNumber = 1;

        foreach ($records as $record) {
            $normalized = $this->normalizeCsvRecord($record);

            yield array_merge(['__row_number' => $rowNumber], $normalized);
            $rowNumber++;
        }
    }

    /**
     * @param  array<string> $headers
     * @return array<string>
     */
    private function normalizeCsvHeaders(array $headers): array
    {
        if ($headers === []) {
            return $headers;
        }

        $headers[0] = $this->stripBom((string) $headers[0]);

        return $headers;
    }

    /**
     * @param  array<string, mixed> $record
     * @return array<string, mixed>
     */
    private function normalizeCsvRecord(array $record): array
    {
        $normalized = [];

        foreach ($record as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            $normalized[$this->stripBom($key)] = $value;
        }

        return $normalized !== [] ? $normalized : $record;
    }

    private function stripBom(string $value): string
    {
        return ltrim($value, "\u{FEFF}\xEF\xBB\xBF");
    }

    /**
     * @return array{processed:int, successful:int, failed:int, total:int}
     */
    private function buildSummary(Import $import): array
    {
        $total = ProgressCounter::normalizeTotal((int) ($import->total_rows ?? 0));
        $processed = ProgressCounter::normalizeProcessed((int) ($import->processed_rows ?? 0), $total);
        $successful = ProgressCounter::normalizeSuccessful((int) ($import->successful_rows ?? 0), $processed, $total);
        $failed = ProgressCounter::failedRows($processed, $successful, $total);

        return [
            'processed'  => $processed,
            'successful' => $successful,
            'failed'     => $failed,
            'total'      => $total,
        ];
    }
}
