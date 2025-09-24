<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumValueResource\Pages;

use App\Filament\Resources\EnumValueResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListEnumValues extends ListRecords
{
    protected static string $resource = EnumValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Enum Values'),
            'product_status' => Tab::make('Product Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'product_status'))
                ->badge(fn () => $this->getResource()::getModel()::where('type', 'product_status')->count()),
            'order_status' => Tab::make('Order Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'order_status'))
                ->badge(fn () => $this->getResource()::getModel()::where('type', 'order_status')->count()),
            'payment_status' => Tab::make('Payment Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'payment_status'))
                ->badge(fn () => $this->getResource()::getModel()::where('type', 'payment_status')->count()),
            'shipping_status' => Tab::make('Shipping Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'shipping_status'))
                ->badge(fn () => $this->getResource()::getModel()::where('type', 'shipping_status')->count()),
            'user_role' => Tab::make('User Roles')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'user_role'))
                ->badge(fn () => $this->getResource()::getModel()::where('type', 'user_role')->count()),
            'notification_type' => Tab::make('Notification Types')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'notification_type'))
                ->badge(fn () => $this->getResource()::getModel()::where('type', 'notification_type')->count()),
            'campaign_status' => Tab::make('Campaign Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'campaign_status'))
                ->badge(fn () => $this->getResource()::getModel()::where('type', 'campaign_status')->count()),
            'discount_type' => Tab::make('Discount Types')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'discount_type'))
                ->badge(fn () => $this->getResource()::getModel()::where('type', 'discount_type')->count()),
            'inventory_status' => Tab::make('Inventory Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'inventory_status'))
                ->badge(fn () => $this->getResource()::getModel()::where('type', 'inventory_status')->count()),
            'review_status' => Tab::make('Review Status')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'review_status'))
                ->badge(fn () => $this->getResource()::getModel()::where('type', 'review_status')->count()),
        ];
    }
}
