<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Pages\Imports\ImportBrands;
use App\Filament\Pages\Imports\ImportCategories;
use App\Filament\Pages\Imports\ImportCustomers;
use App\Filament\Pages\Imports\ImportDiscounts;
use App\Filament\Pages\Imports\ImportOrders;
use App\Filament\Pages\Imports\ImportOrganizations;
use App\Filament\Pages\Imports\ImportPartners;
use App\Filament\Pages\Imports\ImportPrices;
use App\Filament\Pages\Imports\ImportProducts;
use App\Filament\Pages\Imports\ImportSubscribers;
use App\Filament\Pages\Imports\ImportUsers;
use App\Filament\Widgets\DataImportExportStatsWidget;
use App\Models\AdminUser;
use App\Services\ImportExport\ProviderRegistry;
use App\Support\Storage\SecureStorage;
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
        $user = auth()->user();

        return $user instanceof AdminUser || (bool) ($user->is_admin ?? false);
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

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public function getCsvImportPages(): array
    {
        return [
            ['label' => __('translations.import') . ' ' . __('translations.products'), 'url' => ImportProducts::getUrl()],
            ['label' => __('admin.categories_import'), 'url' => ImportCategories::getUrl()],
            ['label' => __('admin.brands_import'), 'url' => ImportBrands::getUrl()],
            ['label' => __('admin.customers_import'), 'url' => ImportCustomers::getUrl()],
            ['label' => __('admin.partners_import'), 'url' => ImportPartners::getUrl()],
            ['label' => __('admin.organizations_import'), 'url' => ImportOrganizations::getUrl()],
            ['label' => __('admin.subscribers_import'), 'url' => ImportSubscribers::getUrl()],
            ['label' => __('admin.users_import'), 'url' => ImportUsers::getUrl()],
            ['label' => __('admin.discounts_import'), 'url' => ImportDiscounts::getUrl()],
            ['label' => __('admin.prices_import'), 'url' => ImportPrices::getUrl()],
            ['label' => __('admin.orders_import'), 'url' => ImportOrders::getUrl()],
        ];
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
                                ->options([
                                    'all'        => __('translations.all'),
                                    'categories' => __('translations.categories'),
                                    'products'   => __('translations.products'),
                                ])
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

    public function import(): void
    {
        $data = $this->form->getState();
        $provider = ProviderRegistry::get($data['provider'] ?? 'xml');
        if (! $provider) {
            Notification::make()
                ->title(__('translations.provider_not_found'))
                ->danger()
                ->send();

            return;
        }
        $path = $data['file'] ?? null;
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
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DataImportExportStatsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
