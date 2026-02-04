<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\ImportExport\CsvImportProcessor;
use Filament\Actions\Imports\Events\ImportChunkProcessed;
use Filament\Actions\Imports\Events\ImportCompleted;
use Filament\Actions\Imports\Events\ImportStarted;
use Filament\Actions\Imports\Models\Import;
use Filament\Support\ChunkIterator;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\CharsetConverter;
use League\Csv\Info;
use League\Csv\Reader as CsvReader;
use League\Csv\Statement;

final class ProcessCsvImport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param array<string, string> $columnMap
     * @param array<string, mixed>  $options
     */
    public function __construct(
        public int $importId,
        public array $columnMap,
        public array $options,
        public string $disk,
        public string $path,
        public int $chunkSize,
    ) {}

    public function handle(CsvImportProcessor $processor): void
    {
        $import = Import::query()->find($this->importId);

        if (! $import) {
            return;
        }

        $resource = Storage::disk($this->disk)->readStream($this->path);

        if (! is_resource($resource)) {
            return;
        }

        $resource = $this->applyEncodingFilter($resource);

        $csvReader = CsvReader::createFromStream($resource);
        if (filled($csvDelimiter = $this->guessCsvDelimiter($csvReader))) {
            $csvReader->setDelimiter($csvDelimiter);
        }

        $csvReader->setHeaderOffset(0);
        $csvResults = (new Statement)->process($csvReader);

        $importer = $import->getImporter(
            columnMap: $this->columnMap,
            options: $this->options,
        );

        if ($import->user instanceof Authenticatable) {
            auth()->setUser($import->user);
        }

        event(new ImportStarted($import, $this->columnMap, $this->options));

        $records = $this->withRowNumbers($csvResults->getRecords());
        $chunkIterator = new ChunkIterator($records, chunkSize: $this->chunkSize);

        foreach ($chunkIterator->get() as $importChunk) {
            $result = $processor->processChunk($import, $importer, $importChunk, $this->columnMap);

            event(new ImportChunkProcessed(
                $import,
                $this->columnMap,
                $this->options,
                $result['processedRows'],
                $result['successfulRows'],
            ));
        }

        $import->touch('completed_at');
        $import->refresh();

        event(new ImportCompleted($import, $this->columnMap, $this->options));
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

    private function withRowNumbers(iterable $records): iterable
    {
        $rowNumber = 1;
        foreach ($records as $record) {
            yield array_merge(['__row_number' => $rowNumber], $record);
            $rowNumber++;
        }
    }
}
