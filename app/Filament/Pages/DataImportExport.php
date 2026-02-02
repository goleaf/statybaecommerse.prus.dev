<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\ImportExport\ProviderRegistry;
use App\Support\Storage\SecureStorage;
use App\Filament\Imports\BrandImporter;
use App\Filament\Imports\CategoryImporter;
use App\Filament\Imports\ProductImporter;
use Filament\Actions\Action;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Storage;

final class DataImportExport extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.data-import-export';

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return 'heroicon-o-arrow-up-tray';
    }

    public static function getNavigationLabel(): string
    {
        return __('translations.import');
    }

    public function getTitle(): string|Htmlable
    {
        return __('translations.import');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->is_admin ?? false;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'provider'       => 'xml',
            'only'           => 'all',
            'downloadImages' => true,
        ]);
    }

    public function importProductsAction(): ImportAction
    {
        return ImportAction::make('importProducts')
            ->label(__('admin.products_import') ?? 'Import Products')
            ->importer(ProductImporter::class);
    }

    public function importCategoriesAction(): ImportAction
    {
        return ImportAction::make('importCategories')
            ->label(__('admin.categories_import') ?? 'Import Categories')
            ->importer(CategoryImporter::class);
    }

    public function importBrandsAction(): ImportAction
    {
        return ImportAction::make('importBrands')
            ->label(__('admin.brands_import') ?? 'Import Brands')
            ->importer(BrandImporter::class);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('translations.import'))
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('provider')
                                ->label(__('translations.provider'))
                                ->options(collect(ProviderRegistry::providers())->mapWithKeys(fn ($p, $k) => [$k => $p->label()])->all())
                                ->required()
                                ->live(),
                            Select::make('only')
                                ->label(__('translations.scope'))
                                ->options(['all' => 'all', 'categories' => 'categories', 'products' => 'products'])
                                ->required(),
                        ]),
                        FileUpload::make('file')
                            ->label(__('translations.xml_file'))
                            ->acceptedFileTypes(['application/xml', 'text/xml'])
                            ->disk(SecureStorage::disk())
                            ->directory('imports')
                            ->required(),
                        Toggle::make('downloadImages')
                            ->label(__('translations.download_images'))
                            ->default(true),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function importAction(): Action
    {
        return Action::make('import')
            ->label(__('translations.import'))
            ->action(function (): void {
                $data = $this->form->getState();
                $provider = ProviderRegistry::get($data['provider'] ?? 'xml');
                if (! $provider) {
                    Notification::make()
                        ->title(__('translations.provider_not_found'))
                        ->danger()
                        ->send();

                    return;
                }
                $path = $data['file'];
                if (! $path) {
                    Notification::make()
                        ->title(__('translations.file_missing'))
                        ->danger()
                        ->send();

                    return;
                }
                $abs = Storage::disk(SecureStorage::disk())->path($path);
                $res = $provider->import($abs, ['only' => $data['only'] ?? 'all', 'download_images' => (bool) ($data['downloadImages'] ?? true)]);

                Notification::make()
                    ->title(__('translations.import_finished'))
                    ->body(__('messages.created') . ': ' . ($res['categories']['created'] + $res['products']['created']))
                    ->success()
                    ->send();
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->importProductsAction(),
            $this->importCategoriesAction(),
            $this->importBrandsAction(),
            $this->importAction(),
        ];
    }
}
