<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\DiscountRedemptionResource;
use App\Filament\Resources\DocumentResource;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;

final class ViewOrder extends ViewRecord
{
    use Translatable;

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $order = $this->record->loadMissing(['items.product.translations']);

        $locale = app()->getLocale();

        $resolveTranslation = static function (mixed $model, string $field) use ($locale): mixed {
            if (method_exists($model, 'getTranslation')) {
                $value = $model->getTranslation($field, $locale);
                if (filled($value)) {
                    return $value;
                }
            }

            if (method_exists($model, 'trans')) {
                $value = $model->trans($field, $locale);
                if (filled($value)) {
                    return $value;
                }
            }

            return $model->{$field} ?? null;
        };

        $quickLinks = [
            ListItem::make()
                ->id('storefront-order-'.$order->getKey())
                ->label(__('View order on storefront'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('primary')
                ->url(route('frontend.orders.show', ['order' => $order->getKey()]))
                ->tooltip(__('Open the storefront order page for order #:number', ['number' => $order->number])),
        ];

        $productItems = $order->items
            ->map(function ($item) use ($resolveTranslation) {
                $product = $item->product;
                $productName = $product ? $resolveTranslation($product, 'name') : null;
                $productSlug = $product ? ($resolveTranslation($product, 'slug') ?? $product->slug) : null;

                $label = __(':name × :quantity', [
                    'name' => $productName ?? $item->name ?? __('Unknown product'),
                    'quantity' => $item->quantity,
                ]);

                $url = $productSlug ? route('products.show', $productSlug) : null;

                return ListItem::make()
                    ->id('order-item-'.$item->getKey())
                    ->label($label)
                    ->icon('heroicon-o-cube')
                    ->color('info')
                    ->url($url ?? '#')
                    ->badge(number_format((float) ($item->total ?? 0), 2))
                    ->tooltip(__('Open the storefront page for :name', ['name' => $productName ?? $item->name ?? __('this product')]));
            })
            ->filter()
            ->values()
            ->all();

        return $infolist->schema([
            ListEntry::make('order_quick_links')
                ->heading(__('Quick links'))
                ->state(fn () => $quickLinks),
            ListEntry::make('order_items')
                ->heading(__('Ordered products'))
                ->list()
                ->state(fn () => $productItems),
        ]);
    }
}
