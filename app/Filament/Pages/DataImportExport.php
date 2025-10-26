<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Schemas\Schema;
use App\Services\ImportExport\ProviderRegistry;
use App\Support\Storage\SecureStorage;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

final class DataImportExport extends Page
{
    protected string $view = 'filament.pages.data-import-export';

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations while conveying the
     * accepted union types for maintainers via PHPDoc.
     *
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
                        Notification::make()
                            ->title(__('translations.provider_not_found'))
                            ->danger()
                            ->send();

                        return;
                    }
                    $path = $this->file;
                    if (is_array($path)) {
                        $path = $path[0] ?? null;
                    }
                    if (! $path) {
                        Notification::make()
                            ->title(__('translations.file_missing'))
                            ->danger()
                            ->send();

                        return;
                    }
                    $abs = Storage::disk(SecureStorage::disk())->path($path);
                    $res = $provider->import($abs, ['only' => $this->only ?? 'all', 'download_images' => (bool) $this->downloadImages]);
                    Notification::make()
                        ->title(__('translations.import_finished'))
                        ->success()
                        ->send();
                    $this->dispatch('imported', created: $res['categories']['created'] + $res['products']['created']);
                }),
            Action::make('export')
                ->label(__('translations.export'))
                ->action(function (): void {
                    $provider = ProviderRegistry::get($this->provider ?? 'xml');
                    if (! $provider) {
                        Notification::make()
                            ->title(__('translations.provider_not_found'))
                            ->danger()
                            ->send();

                        return;
                    }
                    $targetPath = $this->exportPath ?? 'catalog-export.xml';
                    $provider->export(
                        Storage::disk(SecureStorage::disk())->path($targetPath),
                        ['only' => $this->only ?? 'all']
                    );
                    Notification::make()
                        ->title(__('translations.export_finished'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
