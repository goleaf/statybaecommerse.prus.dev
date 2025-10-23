<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\ImportExport\ProviderRegistry;
use App\Support\Storage\SecureStorage;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

final class DataImportExport extends Page
{
    protected string $view = 'filament.pages.data-import-export';

    /**
     * @var string|\BackedEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-arrow-down-tray';

    public ?string $provider = 'xml';

    public ?string $only = 'all';

    public ?bool $downloadImages = true;

    public ?string $exportPath = 'catalog-export.xml';

    public array|string|null $file = null;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('translations.data_import_export'))
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\Select::make('provider')
                                ->label(__('translations.provider'))
                                ->options(collect(ProviderRegistry::providers())->mapWithKeys(fn ($p, $k) => [$k => $p->label()])->all())
                                ->required(),
                            Forms\Components\Select::make('only')
                                ->label(__('translations.scope'))
                                ->options(['all' => 'all', 'categories' => 'categories', 'products' => 'products'])
                                ->required(),
                        ]),
                        Fieldset::make(__('translations.import'))
                            ->schema([
                                Forms\Components\FileUpload::make('file')
                                    ->label(__('translations.xml_file'))
                                    ->acceptedFileTypes(['application/xml', 'text/xml'])
                                    ->required(),
                                Forms\Components\Toggle::make('downloadImages')
                                    ->label(__('translations.download_images'))
                                    ->default(true),
                            ]),
                        Fieldset::make(__('translations.export'))
                            ->schema([
                                Forms\Components\TextInput::make('exportPath')
                                    ->label(__('translations.export_path'))
                                    ->default('storage/catalog-export.xml')
                                    ->required(),
                            ]),
                    ])
                    ->columns(1),
            ]);
    }

    protected function getActions(): array
    {
        return [
            Action::make('import')
                ->label(__('translations.import'))
                ->action(function (): void {
                    $provider = ProviderRegistry::get($this->provider ?? 'xml');
                    if (! $provider) {
                        $this->notify('danger', __('translations.provider_not_found'));

                        return;
                    }
                    $path = $this->file;
                    if (is_array($path)) {
                        $path = $path[0] ?? null;
                    }
                    if (! $path) {
                        $this->notify('danger', __('translations.file_missing'));

                        return;
                    }
                    $abs = Storage::disk(SecureStorage::disk())->path($path);
                    $res = $provider->import($abs, ['only' => $this->only ?? 'all', 'download_images' => (bool) $this->downloadImages]);
                    $this->notify('success', __('translations.import_finished'));
                    $this->dispatch('imported', created: $res['categories']['created'] + $res['products']['created']);
                }),
            Action::make('export')
                ->label(__('translations.export'))
                ->action(function (): void {
                    $provider = ProviderRegistry::get($this->provider ?? 'xml');
                    if (! $provider) {
                        $this->notify('danger', __('translations.provider_not_found'));

                        return;
                    }
                    $targetPath = $this->exportPath ?? 'catalog-export.xml';
                    $provider->export(
                        Storage::disk(SecureStorage::disk())->path($targetPath),
                        ['only' => $this->only ?? 'all']
                    );
                    $this->notify('success', __('translations.export_finished'));
                }),
        ];
    }
}
