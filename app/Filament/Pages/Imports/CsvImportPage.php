<?php

declare(strict_types=1);

namespace App\Filament\Pages\Imports;

use App\Models\AdminUser;
use App\Services\ImportExport\CsvImportProcessor;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\Imports\Events\ImportChunkProcessed;
use Filament\Actions\Imports\Events\ImportCompleted;
use Filament\Actions\Imports\Events\ImportStarted;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\ChunkIterator;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use League\Csv\Bom;
use League\Csv\CharsetConverter;
use League\Csv\Info;
use League\Csv\Reader as CsvReader;
use League\Csv\Statement;
use League\Csv\Writer;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use SplTempFileObject;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class CsvImportPage extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    /**
     * @var array{processed: int, successful: int, failed: int, total: int}|null
     */
    public ?array $lastImport = null;

    protected string $view = 'filament.pages.imports.csv-import';

    protected static bool $shouldRegisterNavigation = false;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    abstract protected static function getImporterClass(): string;

    abstract protected static function getImportLabel(): string;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof AdminUser || (bool) ($user->is_admin ?? false);
    }

    public static function getImporter(): string
    {
        return static::getImporterClass();
    }

    public static function getNavigationLabel(): string
    {
        return static::getImportLabel();
    }

    public function getTitle(): string|Htmlable
    {
        return static::getImportLabel();
    }

    public function form(Form $form): Form
    {
        $page = $this;

        return $form
            ->schema([
                Section::make(__('translations.import'))
                    ->schema([
                        FileUpload::make('file')
                            ->label(__('filament-actions::import.modal.form.file.label'))
                            ->placeholder(__('filament-actions::import.modal.form.file.placeholder'))
                            ->acceptedFileTypes([
                                'text/csv',
                                'text/x-csv',
                                'application/csv',
                                'application/x-csv',
                                'text/comma-separated-values',
                                'text/x-comma-separated-values',
                                'text/plain',
                                'application/vnd.ms-excel',
                            ])
                            ->rules($this->getFileValidationRules())
                            ->afterStateUpdated(function (FileUpload $component, Component $livewire, Set $set, ?TemporaryUploadedFile $state) use ($page): void {
                                if (! $state instanceof TemporaryUploadedFile) {
                                    return;
                                }

                                try {
                                    $livewire->validateOnly($component->getStatePath());
                                } catch (ValidationException $exception) {
                                    $component->state([]);

                                    throw $exception;
                                }

                                $headers = $page->getCsvHeaders($state);

                                if ($headers === []) {
                                    return;
                                }

                                $set('columnMap', $page->guessColumnMap($headers));
                            })
                            ->storeFiles(false)
                            ->visibility('private')
                            ->required(),
                        Fieldset::make(__('filament-actions::import.modal.form.columns.label'))
                            ->columns(1)
                            ->inlineLabel()
                            ->schema(function (Get $get) use ($page): array {
                                $csvFile = $get('file');

                                if (! $csvFile instanceof TemporaryUploadedFile) {
                                    return [];
                                }

                                $headers = $page->getCsvHeaders($csvFile);

                                if ($headers === []) {
                                    return [];
                                }

                                $options = array_combine($headers, $headers);

                                return array_map(
                                    fn (ImportColumn $column) => $column->getSelect()->options($options),
                                    $page->getImporterColumns(),
                                );
                            })
                            ->statePath('columnMap')
                            ->visible(fn (Get $get): bool => $get('file') instanceof TemporaryUploadedFile),
                        ...$this->getOptionsFormComponents(),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        $data = $this->form->getState();
        $csvFile = $data['file'] ?? null;

        if (! $csvFile instanceof TemporaryUploadedFile) {
            Notification::make()
                ->title(__('translations.file_missing'))
                ->danger()
                ->send();

            return;
        }

        $csvStream = $this->getUploadedFileStream($csvFile);

        if (! $csvStream) {
            return;
        }

        $csvReader = CsvReader::createFromStream($csvStream);

        if (filled($csvDelimiter = $this->getCsvDelimiter($csvReader))) {
            $csvReader->setDelimiter($csvDelimiter);
        }

        $csvReader->setHeaderOffset($this->getHeaderOffset());
        $csvResults = (new Statement)->process($csvReader);

        $totalRows = $csvResults->count();
        $maxRows = $this->getMaxRows() ?? $totalRows;

        if ($maxRows < $totalRows) {
            Notification::make()
                ->title(__('filament-actions::import.notifications.max_rows.title'))
                ->body(trans_choice('filament-actions::import.notifications.max_rows.body', $maxRows, [
                    'count' => Number::format($maxRows),
                ]))
                ->danger()
                ->send();

            return;
        }

        $authGuard = $this->resolveAuthGuard();
        $user = auth($authGuard)->user();

        $import = app(Import::class);
        $import->user()->associate($user);
        $import->file_name = $csvFile->getClientOriginalName();
        $import->file_path = $csvFile->getRealPath();
        $import->importer = static::getImporterClass();
        $import->total_rows = $totalRows;
        $import->save();

        $columnMap = $data['columnMap'] ?? $this->guessColumnMap($csvReader->getHeader());
        $options = Arr::except($data, ['file', 'columnMap']);

        $importer = $import->getImporter(
            columnMap: $columnMap,
            options: $options,
        );

        event(new ImportStarted($import, $columnMap, $options));

        /** @var Authenticatable $user */
        auth()->setUser($user);

        $processor = app(CsvImportProcessor::class);
        $chunkIterator = new ChunkIterator($csvResults->getRecords(), chunkSize: $this->getChunkSize());

        foreach ($chunkIterator->get() as $importChunk) {
            $result = $processor->processChunk($import, $importer, $importChunk, $columnMap);

            event(new ImportChunkProcessed(
                $import,
                $columnMap,
                $options,
                $result['processedRows'],
                $result['successfulRows'],
            ));
        }

        $import->touch('completed_at');
        $import->refresh();

        event(new ImportCompleted($import, $columnMap, $options));

        $this->lastImport = [
            'processed'  => $import->processed_rows,
            'successful' => $import->successful_rows,
            'failed'     => $import->getFailedRowsCount(),
            'total'      => $import->total_rows,
        ];

        if ($import->user instanceof Authenticatable) { /** @phpstan-ignore instanceof.alwaysTrue */
            $failedRowsCount = $import->getFailedRowsCount();

            Notification::make()
                ->title($import->importer::getCompletedNotificationTitle($import))
                ->body($import->importer::getCompletedNotificationBody($import))
                ->when(
                    ! $failedRowsCount,
                    fn (Notification $notification) => $notification->success(),
                )
                ->when(
                    $failedRowsCount && ($failedRowsCount < $import->total_rows),
                    fn (Notification $notification) => $notification->warning(),
                )
                ->when(
                    $failedRowsCount === $import->total_rows,
                    fn (Notification $notification) => $notification->danger(),
                )
                ->when(
                    $failedRowsCount,
                    fn (Notification $notification) => $notification->actions([
                        Action::make('downloadFailedRowsCsv')
                            ->label(trans_choice('filament-actions::import.notifications.completed.actions.download_failed_rows_csv.label', $failedRowsCount, [
                                'count' => Number::format($failedRowsCount),
                            ]))
                            ->color('danger')
                            ->url(URL::signedRoute('filament.imports.failed-rows.download', ['authGuard' => $authGuard, 'import' => $import], absolute: false), shouldOpenInNewTab: true)
                            ->markAsRead(),
                    ]),
                )
                ->persistent()
                ->send();
        }
    }

    public function downloadExample(): StreamedResponse
    {
        $columns = $this->getImporterColumns();

        $csv = Writer::createFromFileObject(new SplTempFileObject);

        if (filled($csvDelimiter = $this->getCsvDelimiter())) {
            $csv->setDelimiter($csvDelimiter);
        }

        $csv->insertOne(array_map(
            fn (ImportColumn $column): string => $column->getExampleHeader(),
            $columns,
        ));

        $columnExamples = array_map(
            fn (ImportColumn $column): array => $column->getExamples(),
            $columns,
        );

        $exampleRowsCount = array_reduce(
            $columnExamples,
            fn (int $count, array $exampleData): int => max($count, count($exampleData)),
            initial: 0,
        );

        $exampleRows = [];

        foreach ($columnExamples as $exampleData) {
            for ($i = 0; $i < $exampleRowsCount; $i++) {
                $exampleRows[$i][] = $exampleData[$i] ?? '';
            }
        }

        $csv->insertAll($exampleRows);

        return response()->streamDownload(function () use ($csv): void {
            $csv->setOutputBOM(Bom::Utf8);

            echo $csv->toString();
        }, __('filament-actions::import.example_csv.file_name', [
            'importer' => (string) str(static::getImporterClass())->classBasename()->kebab(),
        ]), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array<int, ImportColumn>
     */
    protected function getImporterColumns(): array
    {
        return static::getImporterClass()::getColumns();
    }

    /**
     * @return array<int, mixed>
     */
    protected function getOptionsFormComponents(): array
    {
        return static::getImporterClass()::getOptionsFormComponents();
    }

    /**
     * @return array<string, string|null>
     */
    protected function guessColumnMap(array $headers): array
    {
        $lowercaseCsvColumnValues = array_map(Str::lower(...), $headers);
        $lowercaseCsvColumnKeys = array_combine(
            $lowercaseCsvColumnValues,
            $headers,
        );

        return array_reduce($this->getImporterColumns(), function (array $carry, ImportColumn $column) use ($lowercaseCsvColumnKeys, $lowercaseCsvColumnValues): array {
            $guess = Arr::first(
                array_intersect(
                    $lowercaseCsvColumnValues,
                    $column->getGuesses(),
                ),
            );

            $carry[$column->getName()] = filled($guess) ? $lowercaseCsvColumnKeys[$guess] : null;

            return $carry;
        }, []);
    }

    /**
     * @return array<string>
     */
    protected function getCsvHeaders(TemporaryUploadedFile $file): array
    {
        $csvStream = $this->getUploadedFileStream($file);

        if (! $csvStream) {
            return [];
        }

        $csvReader = CsvReader::createFromStream($csvStream);

        if (filled($csvDelimiter = $this->getCsvDelimiter($csvReader))) {
            $csvReader->setDelimiter($csvDelimiter);
        }

        $csvReader->setHeaderOffset($this->getHeaderOffset());

        return $csvReader->getHeader();
    }

    /**
     * @return resource|false
     */
    protected function getUploadedFileStream(TemporaryUploadedFile $file)
    {
        $fileDisk = invade($file)->disk; /** @phpstan-ignore-line */
        if (config("filesystems.disks.{$fileDisk}.driver") !== 's3') {
            $resource = $file->readStream();
        } else {
            /** @var AwsS3V3Adapter $s3Adapter */
            $s3Adapter = Storage::disk($fileDisk)->getAdapter();

            invade($s3Adapter)->client->registerStreamWrapper(); /** @phpstan-ignore-line */
            $fileS3Path = (string) str('s3://' . config("filesystems.disks.{$fileDisk}.bucket") . '/' . $file->getRealPath())->replace('\\', '/');

            $resource = fopen($fileS3Path, mode: 'r', context: stream_context_create([
                's3' => [
                    'seekable' => true,
                ],
            ]));
        }

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

    protected function detectCsvEncoding(mixed $resource): ?string
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

    protected function getChunkSize(): int
    {
        return 100;
    }

    protected function getMaxRows(): ?int
    {
        return null;
    }

    protected function getHeaderOffset(): int
    {
        return 0;
    }

    protected function getCsvDelimiter(?CsvReader $reader = null): ?string
    {
        return $this->guessCsvDelimiter($reader);
    }

    protected function guessCsvDelimiter(?CsvReader $reader = null): ?string
    {
        if (! $reader) {
            return null;
        }

        $delimiterCounts = Info::getDelimiterStats($reader, delimiters: [',', ';', '|', "\t"], limit: 10);
        $delimiter = array_search(max($delimiterCounts), $delimiterCounts, true);

        return is_string($delimiter) ? $delimiter : null;
    }

    /**
     * @return array<mixed>
     */
    protected function getFileValidationRules(): array
    {
        return [
            'extensions:csv,txt',
            fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                $csvStream = $this->getUploadedFileStream($value);

                if (! $csvStream) {
                    return;
                }

                $csvReader = CsvReader::createFromStream($csvStream);

                if (filled($csvDelimiter = $this->getCsvDelimiter($csvReader))) {
                    $csvReader->setDelimiter($csvDelimiter);
                }

                $csvReader->setHeaderOffset($this->getHeaderOffset());

                $csvColumns = $csvReader->getHeader();

                $duplicateCsvColumns = [];

                foreach (array_count_values($csvColumns) as $header => $count) {
                    if ($count <= 1) {
                        continue;
                    }

                    $duplicateCsvColumns[] = $header;
                }

                if (empty($duplicateCsvColumns)) {
                    return;
                }

                $filledDuplicateCsvColumns = array_filter($duplicateCsvColumns, fn ($value): bool => filled($value));

                $fail(trans_choice('filament-actions::import.modal.form.file.rules.duplicate_columns', count($filledDuplicateCsvColumns), [
                    'columns' => implode(', ', $filledDuplicateCsvColumns),
                ]));
            },
        ];
    }

    protected function resolveAuthGuard(): string
    {
        if (class_exists(Filament::class) && Filament::isServing()) {
            return Filament::getAuthGuard();
        }

        $authGuard = auth();

        if (! property_exists($authGuard, 'name')) {
            return config('auth.defaults.guard') ?? 'web';
        }

        return $authGuard->name;
    }
}
