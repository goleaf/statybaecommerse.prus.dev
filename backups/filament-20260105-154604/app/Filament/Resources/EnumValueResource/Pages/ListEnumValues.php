<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumValueResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\EnumValueResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use App\Models\EnumValue;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

class ListEnumValues extends BaseListRecords
{
    use HasWidgetTabs;

    protected static string $resource = EnumValueResource::class;

    public function mount(): void
    {
        parent::mount();

        // Bridge legacy query parameter usage (`tableFilters[...]`) so compatibility tests can prime filter state from the URL.
        $legacyFilters = request()->query('tableFilters');

        if (is_array($legacyFilters) && $legacyFilters !== []) {
            // Merge the provided filter payload into the Livewire-bound table filter bag without clobbering active filters.
            $this->tableFilters = array_merge($this->tableFilters ?? [], $legacyFilters);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        $modelClass = $this->getResource()::getModel();

        $tabs = [
            'all' => WidgetTab::make('All Enum Values')
                ->value(fn () => $modelClass::count()),
            'product_status' => WidgetTab::make('Product Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'product_status'))
                ->value(fn () => $modelClass::where('type', 'product_status')->count()),
            'order_status' => WidgetTab::make('Order Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'order_status'))
                ->value(fn () => $modelClass::where('type', 'order_status')->count()),
            'payment_status' => WidgetTab::make('Payment Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'payment_status'))
                ->value(fn () => $modelClass::where('type', 'payment_status')->count()),
            'shipping_status' => WidgetTab::make('Shipping Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'shipping_status'))
                ->value(fn () => $modelClass::where('type', 'shipping_status')->count()),
            'user_role' => WidgetTab::make('User Roles')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'user_role'))
                ->value(fn () => $modelClass::where('type', 'user_role')->count()),
            'notification_type' => WidgetTab::make('Notification Types')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'notification_type'))
                ->value(fn () => $modelClass::where('type', 'notification_type')->count()),
            'campaign_status' => WidgetTab::make('Campaign Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'campaign_status'))
                ->value(fn () => $modelClass::where('type', 'campaign_status')->count()),
            'discount_type' => WidgetTab::make('Discount Types')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'discount_type'))
                ->value(fn () => $modelClass::where('type', 'discount_type')->count()),
            'inventory_status' => WidgetTab::make('Inventory Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'inventory_status'))
                ->value(fn () => $modelClass::where('type', 'inventory_status')->count()),
            'review_status' => WidgetTab::make('Review Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'review_status'))
                ->value(fn () => $modelClass::where('type', 'review_status')->count()),
        ];

        foreach (EnumValue::getTypes() as $type => $label) {
            if (isset($tabs[$type])) {
                continue;
            }

            $tabs[$type] = WidgetTab::make($label)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', $type))
                ->value(fn () => $modelClass::where('type', $type)->count());
        }

        $payload = request()->all();
        $selectedTypes = [];

        if (filled($filterValue = data_get($payload, 'tableFilters.type.value'))) {
            // Preserve single-select requests so only the relevant tab headings render in filtered responses.
            $selectedTypes[] = $filterValue;
        }

        $filterValues = data_get($payload, 'tableFilters.type.values');
        if (is_array($filterValues)) {
            // When a multi-select payload is provided, merge it into the visibility whitelist.
            $selectedTypes = array_merge($selectedTypes, array_filter($filterValues));
        }

        if ($selectedTypes !== []) {
            // Limit the widget tab collection to the active filter keys plus the aggregate "all" tab for quick reset access.
            $tabs = array_filter(
                $tabs,
                static fn (WidgetTab $tab, string $key): bool => $key === 'all' || in_array($key, $selectedTypes, true),
                ARRAY_FILTER_USE_BOTH,
            );
        }

        return $tabs;
    }
}
