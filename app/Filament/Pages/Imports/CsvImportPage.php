<?php

declare(strict_types=1);

namespace App\Filament\Pages\Imports;

use App\Jobs\ProcessCsvImport;
use App\Models\AdminUser;
use App\Models\ImportRowResult;
use App\Support\ImportExport\ProgressCounter;
use App\Support\Storage\SecureStorage;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
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

    public ?int $activeImportId = null;

    /**
     * @var array{processed: int, successful: int, failed: int, total: int, percent: int, status: string}|null
     */
    public ?array $importProgress = null;

    public bool $isImporting = false;

    public ?string $mappingStatusHtml = null;

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

                                            $columnMap = $page->guessColumnMap($headers);

                                            $set('columnMap', $columnMap);
                                            $page->refreshMappingStatus($columnMap);
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
                        })
                        ->schema(function (Get $get) use ($page): array {
                            $csvFile = $get('file');

                            if (is_array($csvFile)) {
                                $csvFile = head($csvFile);
                            }

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

                            $mappingSummary = Placeholder::make('mapping_summary')
                                ->content(function (Get $get) use ($page, $headers): HtmlString {
                                    if ($page->mappingStatusHtml !== null) {
                                        return new HtmlString($page->mappingStatusHtml);
                                    }

                                    return $page->getMappingStatusContent(
                                        $headers,
                                        (array) ($get('columnMap') ?? []),
                                    );
                                });

                            $schemas = [];

                            foreach ($groups as $groupLabel => $columnNames) {
                                $groupColumns = [];
                                foreach ($columnNames as $columnName) {
                                    if ($mappedColumns->has($columnName)) {
                                        $column = $mappedColumns->get($columnName);
                                        $groupColumns[] = $column->getSelect()
                                            ->options($options)
                                            ->live();
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
                                    $remainingColumns[] = $column->getSelect()
                                        ->options($options)
                                        ->live();
                                }

                                $schemas[] = Fieldset::make(__('admin.import_other_columns'))
                                    ->schema($remainingColumns)
                                    ->columns(2);
                            }

                            return [
                                $mappingSummary,
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
                            Placeholder::make('import_progress')
                                ->content(fn () => $this->getImportProgressContent()),
                            Placeholder::make('import_rows')
                                ->content(fn () => $this->getImportRowsContent()),
                        ]),
                ])
                    ->submitAction(view('filament.pages.imports.import-button'))
                    ->statePath('data'),
            ]);
    }

    public function analyze(): void
    {
        $this->lastImport = null;
        $data = $this->data;
        $csvFile = $data['file'] ?? null;

        if (is_array($csvFile)) {
            $csvFile = head($csvFile);
        }

        if (! $csvFile instanceof TemporaryUploadedFile) {
            return;
        }

        $headers = $this->getCsvHeaders($csvFile);
        $columnMap = $data['columnMap'] ?? $this->guessColumnMap($headers);

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
                    $resolvedRecord = $this->evaluateImporterRecord($importer, $this->normalizeCsvRecord($record));
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
            return new HtmlString("
                <div wire:init=\"analyze\" class='rounded-2xl border border-dashed border-primary-200 bg-gradient-to-br from-primary-50 via-white to-white p-6 text-gray-800 shadow-sm dark:border-primary-900/40 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900 dark:text-gray-100'>
                    <div class='flex flex-wrap items-center justify-between gap-4'>
                        <div>
                            <p class='text-xs font-semibold uppercase tracking-widest text-primary-600 dark:text-primary-300'>" . __('admin.import_analysis_summary') . "</p>
                            <p class='mt-2 text-base font-semibold text-gray-900 dark:text-white'>" . __('admin.import_analyzing') . "</p>
                        </div>
                        <div class='flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm dark:bg-gray-800'>
                            <x-filament::loading-indicator class='h-5 w-5' />
                        </div>
                    </div>
                </div>
            ");
        }

        $summary = $this->lastImport;

        $mappedFieldsHtml = collect($summary['mappedFields'])
            ->map(fn ($f) => "<span class='inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200'>{$f}</span>")
            ->implode(' ');

        $missingRequiredHtml = '';
        if (! empty($summary['missingRequiredFields'])) {
            $fields = collect($summary['missingRequiredFields'])
                ->map(fn ($f) => "<span class='inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-900/40 dark:text-red-200'>{$f}</span>")
                ->implode(' ');
            $missingRequiredHtml = "
                <div class='rounded-2xl border border-red-200 bg-red-50 p-6 shadow-sm dark:border-red-900/40 dark:bg-red-950/40'>
                    <p class='text-xs font-semibold uppercase tracking-widest text-red-600 dark:text-red-300'>" . __('admin.import_missing_required') . "</p>
                    <div class='mt-3 flex flex-wrap gap-2'>{$fields}</div>
                </div>
            ";
        }

        return new HtmlString("
            <div class='space-y-6'>
                <div class='relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900'>
                    <div class='pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary-500 via-primary-400 to-transparent'></div>
                    <div class='flex flex-wrap items-start justify-between gap-4'>
                        <div>
                            <p class='text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400'>" . __('admin.import_analysis_summary') . "</p>
                            <div class='mt-3 flex items-end gap-3'>
                                <span class='text-3xl font-bold text-gray-900 dark:text-white'>{$summary['total']}</span>
                                <span class='text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400'>" . __('admin.import_total_rows') . "</span>
                            </div>
                        </div>
                        <div class='inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-[11px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300'>
                            <span class='h-2 w-2 rounded-full bg-primary-500'></span>
                            " . __('admin.import_step_analysis') . "
                        </div>
                    </div>
                    <div class='mt-6 grid grid-cols-1 gap-4 md:grid-cols-3'>
                        <div class='rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-center dark:border-emerald-900/40 dark:bg-emerald-900/20'>
                            <span class='block text-4xl font-black text-emerald-700 dark:text-emerald-300'>{$summary['new']}</span>
                            <span class='mt-1 block text-xs font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400'>" . __('admin.import_new_records') . "</span>
                        </div>
                        <div class='rounded-xl border border-amber-100 bg-amber-50 p-4 text-center dark:border-amber-900/40 dark:bg-amber-900/20'>
                            <span class='block text-4xl font-black text-amber-700 dark:text-amber-300'>{$summary['updated']}</span>
                            <span class='mt-1 block text-xs font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400'>" . __('admin.import_updated_records') . '</span>
                        </div>
                        ' . (($this->data['should_sync'] ?? false) ? "
                        <div class='rounded-xl border border-red-100 bg-red-50 p-4 text-center dark:border-red-900/40 dark:bg-red-900/20'>
                            <span class='block text-4xl font-black text-red-700 dark:text-red-300'>{$summary['removed']}</span>
                            <span class='mt-1 block text-xs font-semibold uppercase tracking-widest text-red-600 dark:text-red-400'>" . __('admin.import_removed_records') . '</span>
                        </div>' : "<div class='rounded-xl border border-slate-100 bg-slate-50 p-4 text-center dark:border-slate-800 dark:bg-slate-800/40'>
                            <span class='block text-4xl font-black text-slate-300 dark:text-slate-500'>—</span>
                            <span class='mt-1 block text-xs font-semibold uppercase tracking-widest text-slate-500'>" . __('admin.import_removed_records') . '</span>
                        </div>') . "
                    </div>
                </div>

                <div class='relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900'>
                    <div class='pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 via-sky-400 to-transparent'></div>
                    <p class='text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400'>" . __('admin.import_fields_to_be_imported') . "</p>
                    <div class='mt-3 flex flex-wrap gap-2'>{$mappedFieldsHtml}</div>
                    <div class='mt-4 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900/40 dark:bg-blue-900/20'>
                        <p class='text-xs font-medium text-blue-700 dark:text-blue-300'>" . __('admin.import_analysis_note') . "</p>
                    </div>
                </div>

                {$missingRequiredHtml}
            </div>
        ");
    }

    protected function getMappingStatusContent(array $headers, array $columnMap): HtmlString
    {
        $columns = $this->getImporterColumns();
        $totalColumns = count($columns);
        $mappedCount = 0;
        $missingRequired = [];
        $invalidMappings = [];
        $headerMappings = [];

        foreach ($columns as $column) {
            $name = $column->getName();
            $label = $column->getLabel() ?? $name;
            $selected = $columnMap[$name] ?? null;

            if (filled($selected)) {
                $mappedCount++;
                $headerMappings[$selected] ??= [];
                $headerMappings[$selected][] = $label;

                if (! in_array($selected, $headers, true)) {
                    $invalidMappings[] = [
                        'field'  => $label,
                        'column' => $selected,
                    ];
                }
            } elseif ($column->isMappingRequired()) {
                $missingRequired[] = $label;
            }
        }

        $duplicateMappings = array_filter(
            $headerMappings,
            fn (array $fields): bool => count($fields) > 1,
        );

        $errorCount = count($missingRequired) + count($invalidMappings) + count($duplicateMappings);
        $badge = static fn (string $text, string $classes): string => "<span class='inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {$classes}'>" . e($text) . '</span>';

        if ($errorCount === 0) {
            $summary = __('admin.import_mapping_ok');
            $mapped = __('admin.import_mapping_mapped_count', [
                'mapped' => Number::format($mappedCount),
                'total'  => Number::format($totalColumns),
            ]);

            return new HtmlString("
                <div class='rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-700/40 dark:bg-emerald-900/20'>
                    <p class='text-sm font-semibold text-emerald-700 dark:text-emerald-300'>{$summary}</p>
                    <p class='mt-1 text-xs text-emerald-700/80 dark:text-emerald-200/80'>{$mapped}</p>
                </div>
            ");
        }

        $errorLabel = trans_choice('admin.import_mapping_errors', $errorCount, [
            'count' => Number::format($errorCount),
        ]);

        $details = [];

        if (! empty($missingRequired)) {
            $fields = collect($missingRequired)
                ->map(fn (string $field): string => $badge($field, 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200'))
                ->implode(' ');

            $details[] = "<p class='text-xs text-red-700 dark:text-red-200'>" . __('admin.import_mapping_missing_required') . " {$fields}</p>";
        }

        foreach ($duplicateMappings as $column => $fields) {
            $fieldBadges = collect($fields)
                ->map(fn (string $field): string => $badge($field, 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200'))
                ->implode(' ');

            $details[] = "<p class='text-xs text-amber-700 dark:text-amber-200'>" . __('admin.import_mapping_duplicate_column', [
                'column' => e((string) $column),
            ]) . " {$fieldBadges}</p>";
        }

        foreach ($invalidMappings as $invalid) {
            $details[] = "<p class='text-xs text-red-700 dark:text-red-200'>" . __('admin.import_mapping_invalid_column', [
                'field'  => e($invalid['field']),
                'column' => e($invalid['column']),
            ]) . '</p>';
        }

        $mapped = __('admin.import_mapping_mapped_count', [
            'mapped' => Number::format($mappedCount),
            'total'  => Number::format($totalColumns),
        ]);

        return new HtmlString("
            <div class='rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800/40 dark:bg-red-900/20'>
                <p class='text-sm font-semibold text-red-700 dark:text-red-200'>{$errorLabel}</p>
                <p class='mt-1 text-xs text-red-700/80 dark:text-red-200/80'>{$mapped}</p>
                <div class='mt-3 space-y-2'>" . implode('', $details) . '</div>
            </div>
        ');
    }

    protected function refreshMappingStatus(?array $columnMap = null): void
    {
        $csvFile = $this->data['file'] ?? null;

        if (is_array($csvFile)) {
            $csvFile = head($csvFile);
        }

        if (! $csvFile instanceof TemporaryUploadedFile) {
            $this->mappingStatusHtml = null;

            return;
        }

        $headers = $this->getCsvHeaders($csvFile);

        if ($headers === []) {
            $this->mappingStatusHtml = null;

            return;
        }

        $columnMap ??= (array) ($this->data['columnMap'] ?? $this->guessColumnMap($headers));
        $this->mappingStatusHtml = $this->getMappingStatusContent($headers, $columnMap)->toHtml();
    }

    public function updatedDataColumnMap(): void
    {
        $this->refreshMappingStatus();
    }

    public function updatedDataFile(): void
    {
        $this->refreshMappingStatus();
    }

    protected function getImportProgressContent(): HtmlString
    {
        if (! $this->activeImportId) {
            return new HtmlString('');
        }

        if (! $this->importProgress) {
            $this->refreshImportProgress();
        }

        if (! $this->importProgress) {
            return new HtmlString('');
        }

        $progress = $this->importProgress;
        $processed = Number::format($progress['processed']);
        $total = Number::format($progress['total']);
        $successful = Number::format($progress['successful']);
        $failed = Number::format($progress['failed']);
        $percent = $progress['percent'];
        $statusLabel = $progress['status'] === 'completed'
            ? __('admin.import_status_completed')
            : __('admin.import_status_running');

        return new HtmlString('
            <div wire:poll.visible.2s="tickImport" class="relative mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary-500 via-primary-400 to-transparent"></div>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">' . __('admin.import_progress') . '</p>
                        <div class="mt-2 flex items-end gap-3">
                            <span class="text-3xl font-bold text-gray-900 dark:text-white">' . $percent . '%</span>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-[11px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">' . $statusLabel . '</span>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">' . __('admin.import_processed_rows') . ': ' . $processed . ' / ' . $total . '</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">' . __('admin.import_processed_rows') . '</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">' . $processed . ' / ' . $total . '</p>
                    </div>
                </div>
                <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                    <div class="h-full rounded-full bg-gradient-to-r from-primary-500 via-primary-600 to-primary-700 transition-all duration-300" style="width: ' . $percent . '%;"></div>
                </div>
                <div class="mt-5 grid grid-cols-1 gap-3 text-xs sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-800/40">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">' . __('admin.import_processed_rows') . '</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">' . $processed . ' / ' . $total . '</p>
                    </div>
                    <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-3 dark:border-emerald-900/40 dark:bg-emerald-900/20">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">' . __('admin.import_successful_rows') . '</p>
                        <p class="mt-1 text-sm font-semibold text-emerald-700 dark:text-emerald-200">' . $successful . '</p>
                    </div>
                    <div class="rounded-lg border border-red-100 bg-red-50 p-3 dark:border-red-900/40 dark:bg-red-900/20">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-red-600 dark:text-red-300">' . __('admin.import_failed_rows') . '</p>
                        <p class="mt-1 text-sm font-semibold text-red-700 dark:text-red-200">' . $failed . '</p>
                    </div>
                    <div class="rounded-lg border border-blue-100 bg-blue-50 p-3 dark:border-blue-900/40 dark:bg-blue-900/20">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-300">' . __('admin.import_status') . '</p>
                        <p class="mt-1 text-sm font-semibold text-blue-700 dark:text-blue-200">' . $statusLabel . '</p>
                    </div>
                </div>
            </div>
        ');
    }

    protected function getImportRowsContent(): HtmlString
    {
        if (! $this->activeImportId) {
            return new HtmlString('');
        }

        $rows = ImportRowResult::query()
            ->where('import_id', $this->activeImportId)
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->reverse();

        if ($rows->isEmpty()) {
            return new HtmlString(
                "<div class='mt-6 rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-6 text-sm text-gray-600 shadow-sm dark:border-gray-800 dark:bg-gray-900/40 dark:text-gray-300'>
                    <div class='flex flex-wrap items-start justify-between gap-4'>
                        <div>
                            <p class='text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400'>" . __('admin.import_rows_title') . "</p>
                            <p class='mt-1 text-base font-semibold text-gray-900 dark:text-white'>" . __('admin.import_rows_latest') . "</p>
                            <p class='mt-1 text-xs text-gray-500 dark:text-gray-400'>" . __('admin.import_rows_latest_hint') . "</p>
                        </div>
                        <span class='inline-flex items-center rounded-full bg-white px-3 py-1 text-[11px] font-semibold text-gray-600 shadow-sm dark:bg-gray-800 dark:text-gray-300'>" . __('admin.import_chunk_size', ['count' => $this->getChunkSize()]) . "</span>
                    </div>
                    <div class='mt-4 rounded-lg border border-dashed border-gray-200 bg-white px-4 py-3 text-xs text-gray-600 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300'>"
                        . __('admin.import_rows_empty') .
                    '</div>
                </div>'
            );
        }

        $header = '
            <div class="relative mt-8 overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-slate-900/70 via-slate-400 to-transparent dark:from-slate-200/40"></div>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">' . __('admin.import_rows_title') . '</p>
                        <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">' . __('admin.import_rows_latest') . '</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">' . __('admin.import_rows_latest_hint') . '</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-[11px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">' . __('admin.import_chunk_size', ['count' => $this->getChunkSize()]) . '</span>
                </div>
            </div>
        ';

        $rowsHtml = $rows->map(function (ImportRowResult $row): string {
            $statusBadge = $this->formatStatusBadge($row->status);
            $actionBadge = $this->formatActionBadge($row->action);
            $rowNumber = $row->row_number ? (string) $row->row_number : '-';
            $message = e($row->message ?? '');
            $errorMessage = e($row->error_message ?? '');
            $changedFields = collect($row->changed_fields ?? [])->map(fn ($field) => "<span class='rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700 dark:bg-blue-400/10 dark:text-blue-300'>{$field}</span>")->implode(' ');

            $data = is_array($row->data) ? $row->data : [];
            $dataHtml = collect($data)->map(function ($value, $field) use ($row): string {
                $fieldLabel = e((string) $field);
                $valueText = e(is_scalar($value) ? (string) $value : json_encode($value));
                $fieldState = $this->resolveFieldState($row, (string) $field);
                $fieldBadge = $this->formatFieldBadge($fieldState);

                return "
                    <div class='rounded-md border border-gray-200 bg-white p-2 text-xs text-gray-700 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200'>
                        <div class='flex items-start justify-between gap-2'>
                            <div class='font-semibold text-gray-900 dark:text-white'>{$fieldLabel}</div>
                            {$fieldBadge}
                        </div>
                        <div class='mt-1 break-words text-gray-600 dark:text-gray-300'>{$valueText}</div>
                    </div>
                ";
            })->implode('');

            return "
                <tr class='odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900/40 dark:even:bg-gray-950'>
                    <td class='whitespace-nowrap px-3 py-3 text-xs text-gray-700 dark:text-gray-200'>{$rowNumber}</td>
                    <td class='px-3 py-3'>{$statusBadge}</td>
                    <td class='px-3 py-3'>{$actionBadge}</td>
                    <td class='px-3 py-3 text-xs text-gray-600 dark:text-gray-300'>{$message}</td>
                    <td class='px-3 py-3 text-xs text-gray-600 dark:text-gray-300'>{$errorMessage}</td>
                    <td class='px-3 py-3'>{$changedFields}</td>
                    <td class='px-3 py-3'>
                        <div class='grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3'>
                            {$dataHtml}
                        </div>
                    </td>
                </tr>
            ";
        })->implode('');

        return new HtmlString($header . "
            <div class='mt-4 overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-800'>
                <div class='max-h-[520px] overflow-auto'>
                    <table class='min-w-full divide-y divide-gray-200 text-left text-xs dark:divide-gray-800'>
                        <thead class='sticky top-0 bg-gray-50 text-[11px] uppercase tracking-wide text-gray-500 dark:bg-gray-950 dark:text-gray-400'>
                            <tr>
                                <th class='px-3 py-3'>" . __('admin.import_row') . "</th>
                                <th class='px-3 py-3'>" . __('admin.import_row_status') . "</th>
                                <th class='px-3 py-3'>" . __('admin.import_row_action') . "</th>
                                <th class='px-3 py-3'>" . __('admin.import_row_message') . "</th>
                                <th class='px-3 py-3'>" . __('admin.import_row_error') . "</th>
                                <th class='px-3 py-3'>" . __('admin.import_row_fields') . "</th>
                                <th class='px-3 py-3'>" . __('admin.import_row_data') . "</th>
                            </tr>
                        </thead>
                        <tbody class='divide-y divide-gray-100 dark:divide-gray-800'>
                            {$rowsHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        ");
    }

    protected function formatStatusBadge(string $status): string
    {
        return match ($status) {
            'success' => "<span class='rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-semibold text-green-700 dark:bg-green-400/10 dark:text-green-300'>" . __('admin.import_row_status_success') . '</span>',
            'failed'  => "<span class='rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-700 dark:bg-red-400/10 dark:text-red-300'>" . __('admin.import_row_status_failed') . '</span>',
            default   => "<span class='rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300'>" . __('admin.import_row_status_pending') . '</span>',
        };
    }

    protected function formatActionBadge(string $action): string
    {
        return match ($action) {
            'created' => "<span class='rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300'>" . __('admin.import_row_action_created') . '</span>',
            'updated' => "<span class='rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700 dark:bg-blue-400/10 dark:text-blue-300'>" . __('admin.import_row_action_updated') . '</span>',
            'skipped' => "<span class='rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-400/10 dark:text-amber-300'>" . __('admin.import_row_action_skipped') . '</span>',
            'error'   => "<span class='rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-700 dark:bg-red-400/10 dark:text-red-300'>" . __('admin.import_row_action_error') . '</span>',
            default   => "<span class='rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300'>" . e($action) . '</span>',
        };
    }

    protected function resolveFieldState(ImportRowResult $row, string $field): string
    {
        if ($row->status === 'failed') {
            return 'error';
        }

        if ($row->action === 'created') {
            return 'created';
        }

        if ($row->action === 'updated' && in_array($field, $row->changed_fields ?? [], true)) {
            return 'updated';
        }

        if ($row->action === 'skipped') {
            return 'unchanged';
        }

        return 'unchanged';
    }

    protected function formatFieldBadge(string $state): string
    {
        return match ($state) {
            'created' => "<span class='rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300'>" . __('admin.import_row_field_created') . '</span>',
            'updated' => "<span class='rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-400/10 dark:text-blue-300'>" . __('admin.import_row_field_updated') . '</span>',
            'error'   => "<span class='rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-700 dark:bg-red-400/10 dark:text-red-300'>" . __('admin.import_row_field_error') . '</span>',
            default   => "<span class='rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300'>" . __('admin.import_row_field_unchanged') . '</span>',
        };
    }

    public function refreshImportProgress(): void
    {
        if (! $this->activeImportId) {
            return;
        }

        $import = Import::query()->find($this->activeImportId);

        if (! $import) {
            $this->activeImportId = null;
            $this->isImporting = false;
            $this->importProgress = null;

            return;
        }

        $total = ProgressCounter::normalizeTotal((int) ($import->total_rows ?? 0));
        $processed = ProgressCounter::normalizeProcessed((int) ($import->processed_rows ?? 0), $total);
        $successful = ProgressCounter::normalizeSuccessful((int) ($import->successful_rows ?? 0), $processed, $total);
        $failed = $this->calculateFailedRowsCount($import);
        $percent = $this->calculateProgressPercent($processed, $total);
        $status = $this->resolveImportStatus($import, $processed, $total);

        $this->importProgress = [
            'processed'  => $processed,
            'total'      => $total,
            'successful' => $successful,
            'failed'     => $failed,
            'percent'    => $percent,
            'status'     => $status,
        ];

        if ($status === 'completed') {
            if ($this->isImporting) {
                $this->notifyImportCompleted($import);
            }

            $this->isImporting = false;
            $existingSummary = $this->lastImport ?? [];
            $this->lastImport = array_merge($existingSummary, [
                'new'                   => $existingSummary['new'] ?? 0,
                'updated'               => $existingSummary['updated'] ?? 0,
                'removed'               => $existingSummary['removed'] ?? 0,
                'processed'             => $processed,
                'successful'            => $successful,
                'failed'                => $failed,
                'total'                 => $total,
                'mappedFields'          => $existingSummary['mappedFields'] ?? [],
                'missingRequiredFields' => $existingSummary['missingRequiredFields'] ?? [],
            ]);
        }
    }

    public function tickImport(): void
    {
        if (! $this->activeImportId) {
            return;
        }

        if ($this->isImporting) {
            $this->processImportChunk();
        }

        $this->refreshImportProgress();
    }

    protected function processImportChunk(): void
    {
        $import = Import::query()->find($this->activeImportId);

        if (! $import) {
            $this->activeImportId = null;
            $this->isImporting = false;

            return;
        }

        if ($import->completed_at) {
            $this->isImporting = false;

            return;
        }

        $total = ProgressCounter::normalizeTotal((int) ($import->total_rows ?? 0));
        $processed = ProgressCounter::normalizeProcessed((int) ($import->processed_rows ?? 0), $total);

        if ($total > 0 && $processed >= $total) {
            $import->touch('completed_at');
            $this->isImporting = false;
            $this->notifyImportCompleted($import);

            return;
        }

        $columnMap = $this->normalizeImportPayload($import->column_map);
        $options = $this->normalizeImportPayload($import->options);
        $disk = (string) ($import->file_disk ?: SecureStorage::disk());

        $csvStream = Storage::disk($disk)->readStream($import->file_path);
        if (! $csvStream) {
            $this->isImporting = false;

            return;
        }

        $csvReader = CsvReader::createFromStream($csvStream);
        if (filled($csvDelimiter = $this->getCsvDelimiter($csvReader))) {
            $csvReader->setDelimiter($csvDelimiter);
        }

        $csvReader->setHeaderOffset($this->getHeaderOffset());

        $statement = (new Statement)
            ->offset($processed)
            ->limit($this->getChunkSize());

        $records = [];
        $rowNumber = $processed + 1;
        foreach ($statement->process($csvReader)->getRecords() as $record) {
            $record = $this->normalizeCsvRecord($record);
            $records[] = array_merge(['__row_number' => $rowNumber], $record);
            $rowNumber++;
        }

        if (! count($records)) {
            $import->touch('completed_at');
            $this->isImporting = false;
            $this->notifyImportCompleted($import);

            return;
        }

        $importer = $import->getImporter(
            columnMap: is_array($columnMap) ? $columnMap : [],
            options: is_array($options) ? $options : [],
        );

        $processor = app(\App\Services\ImportExport\CsvImportProcessor::class);
        $processor->processChunk($import, $importer, $records, is_array($columnMap) ? $columnMap : []);

        $import->refresh();

        $normalizedTotal = ProgressCounter::normalizeTotal((int) ($import->total_rows ?? 0));
        $normalizedProcessed = ProgressCounter::normalizeProcessed((int) ($import->processed_rows ?? 0), $normalizedTotal);

        if ($this->resolveImportStatus($import, $normalizedProcessed, $normalizedTotal) === 'completed') {
            $import->touch('completed_at');
            $this->isImporting = false;
            $this->notifyImportCompleted($import);
        }
    }

    protected function resolveImportStatus(Import $import, int $processedRows, int $totalRows): string
    {
        return ($import->completed_at !== null || ($totalRows > 0 && $processedRows >= $totalRows))
            ? 'completed'
            : 'running';
    }

    protected function normalizeImportPayload(mixed $payload): mixed
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (is_string($payload) && $payload !== '') {
            $decoded = json_decode($payload, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return [];
    }

    protected function notifyImportCompleted(Import $import): void
    {
        $failedRowsCount = $this->calculateFailedRowsCount($import);
        $totalRows = ProgressCounter::normalizeTotal((int) ($import->total_rows ?? 0));
        $authGuard = $this->resolveAuthGuard();

        Notification::make()
            ->title($import->importer::getCompletedNotificationTitle($import))
            ->body($import->importer::getCompletedNotificationBody($import))
            ->when(
                ! $failedRowsCount,
                fn (Notification $notification) => $notification->success(),
            )
            ->when(
                $failedRowsCount > 0 && ($totalRows === 0 || $failedRowsCount < $totalRows),
                fn (Notification $notification) => $notification->warning(),
            )
            ->when(
                $totalRows > 0 && $failedRowsCount === $totalRows,
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

    protected function calculateProgressPercent(int $processed, int $total): int
    {
        return ProgressCounter::percent($processed, $total);
    }

    protected function calculateFailedRowsCount(Import $import): int
    {
        return ProgressCounter::failedRows(
            (int) ($import->processed_rows ?? 0),
            (int) ($import->successful_rows ?? 0),
            (int) ($import->total_rows ?? 0),
        );
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
        $importUser = $this->resolveImportUser($user);

        if (! $importUser) {
            Notification::make()
                ->title(__('admin.import_user_missing'))
                ->danger()
                ->send();

            return;
        }

        $import = app(Import::class);
        $import->user()->associate($importUser);
        $import->file_name = $csvFile->getClientOriginalName();
        $import->file_path = $this->storeCsvFile($csvFile);
        $import->importer = static::getImporterClass();
        $import->total_rows = $totalRows;
        $import->save();

        $columnMap = $data['columnMap'] ?? $this->guessColumnMap($csvReader->getHeader());
        $options = Arr::except($data, ['file', 'columnMap']);

        $import->column_map = $columnMap;
        $import->options = $options;
        $import->file_disk = SecureStorage::disk();
        $import->save();

        $this->activeImportId = $import->getKey();
        $this->isImporting = true;
        $this->importProgress = null;
        $this->refreshImportProgress();

        $queueConnection = (string) config('queue.default', 'sync');
        if (! in_array($queueConnection, ['database', 'sync'], true)) {
            dispatch(new ProcessCsvImport(
                importId: $import->getKey(),
                columnMap: $columnMap,
                options: $options,
                disk: SecureStorage::disk(),
                path: $import->file_path,
                chunkSize: $this->getChunkSize(),
            ));
        }

        Notification::make()
            ->title(__('admin.import_started'))
            ->success()
            ->send();
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
        $headers = $this->normalizeCsvHeaders($headers);
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

        return $this->normalizeCsvHeaders($csvReader->getHeader());
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

    protected function storeCsvFile(TemporaryUploadedFile $file): string
    {
        $disk = SecureStorage::disk();
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid()->toString();

        if (filled($extension)) {
            $filename .= '.' . $extension;
        }

        return $file->storeAs('imports/csv', $filename, $disk);
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
            'extensions:csv',
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

    /**
     * @param array<string> $headers
     * @return array<string>
     */
    protected function normalizeCsvHeaders(array $headers): array
    {
        if ($headers === []) {
            return $headers;
        }

        $headers[0] = $this->stripBom((string) $headers[0]);

        return $headers;
    }

    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    protected function normalizeCsvRecord(array $record): array
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

    protected function stripBom(string $value): string
    {
        return ltrim($value, "\u{FEFF}\xEF\xBB\xBF");
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

    protected function resolveImportUser(?Authenticatable $user): ?Authenticatable
    {
        return $user;
    }
}
