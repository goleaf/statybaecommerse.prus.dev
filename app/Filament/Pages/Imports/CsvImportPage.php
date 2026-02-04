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
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\ChunkIterator;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;
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
use ReflectionClass;
use SplTempFileObject;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

abstract class CsvImportPage extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    /**
     * @var array{processed: int, successful: int, failed: int, total: int, new: int, updated: int, removed: int, mappedFields: array<string>, missingRequiredFields: array<string>}|null
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
                Wizard::make([
                    Step::make(__('admin.import_step_source'))
                        ->description(__('admin.import_step_source_description'))
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

                                    Toggle::make('should_sync')
                                        ->label(__('admin.import_should_sync'))
                                        ->helperText(__('admin.import_should_sync_description'))
                                        ->default(false),

                                    ...$this->getOptionsFormComponents(),
                                ]),
                        ]),

                    Step::make(__('admin.import_step_mapping'))
                        ->description(__('admin.import_step_mapping_description'))
                        ->afterValidation(function (Get $get) {
                            $mapping = $get('columnMap') ?? [];
                            $this->data['columnMap'] = $mapping;
                            $this->runAnalysis($mapping);
                        })
                        ->schema(function (Get $get) use ($page): array {
                            $csvFile = $get('file');

                            if (! $csvFile instanceof TemporaryUploadedFile) {
                                return [
                                    Placeholder::make('no_file')
                                        ->content(__('admin.import_please_upload_file')),
                                ];
                            }

                            $headers = $page->getCsvHeaders($csvFile);

                            if ($headers === []) {
                                return [];
                            }

                            $options = array_combine($headers, $headers);
                            $importerClass = static::getImporterClass();
                            $groups = $importerClass::getColumnGroups();
                            $allColumns = $page->getImporterColumns();
                            $mappedColumns = collect($allColumns)->keyBy->getName();

                            $schemas = [];

                            foreach ($groups as $groupLabel => $columnNames) {
                                $groupColumns = [];
                                foreach ($columnNames as $columnName) {
                                    if ($mappedColumns->has($columnName)) {
                                        $column = $mappedColumns->get($columnName);
                                        $groupColumns[] = $column->getSelect()->options($options);
                                        $mappedColumns->forget($columnName);
                                    }
                                }

                                if (! empty($groupColumns)) {
                                    $schemas[] = Fieldset::make($groupLabel)
                                        ->schema($groupColumns)
                                        ->columns(2);
                                }
                            }

                            // Remaining columns
                            if ($mappedColumns->isNotEmpty()) {
                                $remainingColumns = [];
                                foreach ($mappedColumns as $column) {
                                    $remainingColumns[] = $column->getSelect()->options($options);
                                }

                                $schemas[] = Fieldset::make(__('admin.import_other_columns'))
                                    ->schema($remainingColumns)
                                    ->columns(2);
                            }

                            return [
                                Grid::make(1)
                                    ->schema($schemas)
                                    ->statePath('columnMap'),
                            ];
                        }),

                    Step::make(__('admin.import_step_analysis'))
                        ->description(__('admin.import_step_analysis_description'))
                        ->schema([
                            Placeholder::make('analysis_summary')
                                ->content(fn () => $this->getAnalysisContent()),
                        ]),
                ])
                    ->submitAction(view('filament.pages.imports.import-button'))
                    ->statePath('data'),
            ]);
    }

    protected function runAnalysis(?array $columnMap = null): void
    {
        $this->lastImport = null;
        $data = $this->data;
        $csvFile = $data['file'] ?? null;

        if (! $csvFile instanceof TemporaryUploadedFile) {
            return;
        }

        $headers = $this->getCsvHeaders($csvFile);
        $columnMap ??= $data['columnMap'] ?? $this->guessColumnMap($headers);

        $csvStream = $this->getUploadedFileStream($csvFile);
        if (! $csvStream) {
            return;
        }
        $csvReader = CsvReader::createFromStream($csvStream);

        if (filled($csvDelimiter = $this->getCsvDelimiter($csvReader))) {
            $csvReader->setDelimiter($csvDelimiter);
        }

        $csvReader->setHeaderOffset($this->getHeaderOffset());
        $records = $csvReader->getRecords();

        $importerClass = static::getImporterClass();
        $modelClass = $importerClass::getModel();
        $importer = new $importerClass(new Import, $columnMap, Arr::except($data, ['file', 'columnMap']));

        $newCount = 0;
        $updatedCount = 0;
        $totalCount = 0;
        $mappedFields = [];
        $missingRequiredFields = [];

        // Check required fields
        foreach ($this->getImporterColumns() as $column) {
            $name = $column->getName();
            if (isset($columnMap[$name]) && $columnMap[$name] !== null) {
                $mappedFields[] = $column->getLabel() ?? $name;
            } elseif ($column->isMappingRequired()) {
                $missingRequiredFields[] = $column->getLabel() ?? $name;
            }
        }

        $limit = 1000;
        $processedForAnalysis = 0;

        foreach ($records as $record) {
            $totalCount++;
            if ($processedForAnalysis < $limit) {
                try {
                    $resolvedRecord = $this->evaluateImporterRecord($importer, $record);
                    if ($resolvedRecord->exists) {
                        $updatedCount++;
                    } else {
                        $newCount++;
                    }
                } catch (Throwable $e) {
                    // Skip failures
                }
                $processedForAnalysis++;
            }
        }

        if ($totalCount > $processedForAnalysis && $processedForAnalysis > 0) {
            $ratio = $totalCount / $processedForAnalysis;
            $newCount = (int) ($newCount * $ratio);
            $updatedCount = (int) ($updatedCount * $ratio);
        }

        $removedCount = 0;
        if ($data['should_sync'] ?? false) {
            $currentCount = $modelClass::count();
            $removedCount = max(0, $currentCount - $updatedCount);
        }

        $this->lastImport = [
            'total'                 => $totalCount,
            'new'                   => $newCount,
            'updated'               => $updatedCount,
            'removed'               => $removedCount,
            'mappedFields'          => $mappedFields,
            'missingRequiredFields' => $missingRequiredFields,
        ];
    }

    protected function evaluateImporterRecord($importer, array $data): Model
    {
        $reflection = new ReflectionClass($importer);
        $property = $reflection->getProperty('data');
        $property->setAccessible(true);
        $property->setValue($importer, $data);

        return $importer->resolveRecord();
    }

    protected function getAnalysisContent(): HtmlString
    {
        if (! $this->lastImport) {
            return new HtmlString('<div class="flex items-center gap-3 text-warning-600 p-4 bg-warning-50 rounded-lg border border-warning-200"><x-filament::loading-indicator class="h-5 w-5" /><p>' . __('admin.import_analyzing') . '</p></div>');
        }

        $summary = $this->lastImport;

        $mappedFieldsHtml = collect($summary['mappedFields'])
            ->map(fn ($f) => "<span class='inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/30'>{$f}</span>")
            ->implode(' ');

        $missingRequiredHtml = '';
        if (! empty($summary['missingRequiredFields'])) {
            $fields = collect($summary['missingRequiredFields'])
                ->map(fn ($f) => "<span class='inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-700/10 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/30'>{$f}</span>")
                ->implode(' ');
            $missingRequiredHtml = "
                <div class='mt-4 p-4 border border-red-200 bg-red-50 dark:bg-red-900/20 rounded-lg'>
                    <p class='text-sm font-bold text-red-700 dark:text-red-400 mb-2'>" . __('admin.import_missing_required') . ":</p>
                    <div class='flex flex-wrap gap-2'>{$fields}</div>
                </div>
            ";
        }

        return new HtmlString("
            <div class='space-y-6'>
                <div class='p-6 border rounded-xl bg-white dark:bg-gray-900 shadow-sm'>
                    <p class='text-xl font-bold mb-6'>" . __('admin.import_total_rows') . ": {$summary['total']}</p>
                    <div class='grid grid-cols-1 md:grid-cols-3 gap-6'>
                        <div class='p-4 bg-green-50 dark:bg-green-900/20 rounded-xl text-center border-2 border-green-100 dark:border-green-800 shadow-sm'>
                            <span class='block text-4xl font-black text-green-700 dark:text-green-400 mb-1'>{$summary['new']}</span>
                            <span class='text-xs font-bold uppercase tracking-widest text-green-600 dark:text-green-500'>" . __('admin.import_new_records') . "</span>
                        </div>
                        <div class='p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl text-center border-2 border-amber-100 dark:border-amber-800 shadow-sm'>
                            <span class='block text-4xl font-black text-amber-700 dark:text-amber-400 mb-1'>{$summary['updated']}</span>
                            <span class='text-xs font-bold uppercase tracking-widest text-amber-600 dark:text-amber-500'>" . __('admin.import_updated_records') . '</span>
                        </div>
                        ' . (($this->data['should_sync'] ?? false) ? "
                        <div class='p-4 bg-red-50 dark:bg-red-900/20 rounded-xl text-center border-2 border-red-100 dark:border-red-800 shadow-sm'>
                            <span class='block text-4xl font-black text-red-700 dark:text-red-400 mb-1'>{$summary['removed']}</span>
                            <span class='text-xs font-bold uppercase tracking-widest text-red-600 dark:text-red-500'>" . __('admin.import_removed_records') . '</span>
                        </div>' : '') . "
                    </div>
                </div>

                <div>
                    <p class='text-sm font-bold text-gray-700 dark:text-gray-300 mb-3'>" . __('admin.import_fields_to_be_imported') . ":</p>
                    <div class='flex flex-wrap gap-2'>{$mappedFieldsHtml}</div>
                </div>

                <div class='mt-4 p-4 border rounded-lg bg-blue-50 dark:bg-blue-900/20'>
                    <p class='text-sm text-blue-700 dark:text-blue-300 font-medium'>" . __('admin.import_analysis_note') . "</p>
                </div>

                {$missingRequiredHtml}
            </div>
        ");
    }

    public function import(): void
    {
        $state = $this->form->getState();
        $data = $state['data'] ?? $state;
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

        $this->lastImport = array_merge($this->lastImport ?? [], [
            'processed'  => $import->processed_rows,
            'successful' => $import->successful_rows,
            'failed'     => $import->getFailedRowsCount(),
            'total'      => $import->total_rows,
        ]);

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

    protected function guessColumnMap(array $headers): array
    {
        $lowercaseCsvColumnValues = array_map(Str::lower(...), $headers);
        $lowercaseCsvColumnKeys = array_combine(
            $lowercaseCsvColumnValues,
            $headers,
        );

        $customGuesses = [
            'sku'         => ['product code', 'artikulas', 'kodas', 'sku number', 'item number'],
            'name'        => ['title', 'pavadinimas', 'label', 'product name', 'prekė'],
            'price'       => ['kaina', 'amount', 'cost', 'retail price', 'price (inc. vat)'],
            'description' => ['aprašymas', 'body', 'content', 'long description'],
            'status'      => ['būsena', 'state', 'availability', 'is active'],
        ];

        return array_reduce($this->getImporterColumns(), function (array $carry, ImportColumn $column) use ($lowercaseCsvColumnKeys, $lowercaseCsvColumnValues, $customGuesses): array {
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
