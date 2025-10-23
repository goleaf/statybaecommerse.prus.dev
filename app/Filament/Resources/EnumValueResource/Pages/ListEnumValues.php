<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumValueResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\EnumValueResource;
use App\Models\EnumValue;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Illuminate\Database\Eloquent\Builder;

class ListEnumValues extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = EnumValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all'            => Tab::make('All Enum Values'),
            'product_status' => Tab::make('Product Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'product_status'))
                ->value(fn () => $this->getResource()::getModel()::where('type', 'product_status')->count()),
            'order_status' => WidgetTab::make('Order Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'order_status'))
                ->value(fn () => $this->getResource()::getModel()::where('type', 'order_status')->count()),
            'payment_status' => WidgetTab::make('Payment Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'payment_status'))
                ->value(fn () => $this->getResource()::getModel()::where('type', 'payment_status')->count()),
            'shipping_status' => WidgetTab::make('Shipping Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'shipping_status'))
                ->value(fn () => $this->getResource()::getModel()::where('type', 'shipping_status')->count()),
            'user_role' => WidgetTab::make('User Roles')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'user_role'))
                ->value(fn () => $this->getResource()::getModel()::where('type', 'user_role')->count()),
            'notification_type' => WidgetTab::make('Notification Types')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'notification_type'))
                ->value(fn () => $this->getResource()::getModel()::where('type', 'notification_type')->count()),
            'campaign_status' => WidgetTab::make('Campaign Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'campaign_status'))
                ->value(fn () => $this->getResource()::getModel()::where('type', 'campaign_status')->count()),
            'discount_type' => WidgetTab::make('Discount Types')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'discount_type'))
                ->value(fn () => $this->getResource()::getModel()::where('type', 'discount_type')->count()),
            'inventory_status' => WidgetTab::make('Inventory Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'inventory_status'))
                ->value(fn () => $this->getResource()::getModel()::where('type', 'inventory_status')->count()),
            'review_status' => WidgetTab::make('Review Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'review_status'))
                ->value(fn () => $this->getResource()::getModel()::where('type', 'review_status')->count()),
        ];

        foreach (EnumValue::getTypes() as $type => $label) {
            $tabs[$type] = Tab::make($label)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', $type))
                ->badge(fn () => $this->getResource()::getModel()::where('type', $type)->count());
        }

        return $tabs;
    }
}
