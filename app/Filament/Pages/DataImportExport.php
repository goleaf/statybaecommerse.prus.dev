<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Pages\Imports\ImportBrands;
use App\Filament\Pages\Imports\ImportCategories;
use App\Filament\Pages\Imports\ImportCustomers;
use App\Filament\Pages\Imports\ImportDiscounts;
use App\Filament\Pages\Imports\ImportOrders;
use App\Filament\Pages\Imports\ImportPartners;
use App\Filament\Pages\Imports\ImportPrices;
use App\Filament\Pages\Imports\ImportProducts;
use App\Filament\Pages\Imports\ImportSubscribers;
use App\Filament\Pages\Imports\ImportUsers;
use App\Filament\Widgets\DataImportExportStatsWidget;
use App\Models\AdminUser;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

final class DataImportExport extends Page
{
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
            ['label' => __('admin.subscribers_import'), 'url' => ImportSubscribers::getUrl()],
            ['label' => __('admin.users_import'), 'url' => ImportUsers::getUrl()],
            ['label' => __('admin.discounts_import'), 'url' => ImportDiscounts::getUrl()],
            ['label' => __('admin.prices_import'), 'url' => ImportPrices::getUrl()],
            ['label' => __('admin.orders_import'), 'url' => ImportOrders::getUrl()],
        ];
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
