<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\DiscountRedemptionResource;
use App\Filament\Resources\DocumentResource;
use App\Filament\Resources\OrderResource;
use App\Models\DiscountRedemption;
use App\Models\Document;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;
use Illuminate\Support\Number;
use LaraZeus\ListGroup\Entries\ListItem;
use LaraZeus\ListGroup\Infolists\ListEntry;
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

    public function infolist(Infolist $infolist): Infolist|array
    {
        return $infolist->schema([
            ListEntry::make('orderQuickLinks')
                ->heading(__('Quick links'))
                ->state(function (Order $record): array {
                    $currency = $record->currency ?? config('shared.localization.default_currency', 'EUR');

                    return [
                        ListItem::make()
                            ->id('order-customer-view-' . $record->getKey())
                            ->label(__('View customer order page'))
                            ->icon('heroicon-m-shopping-bag')
                            ->color('primary')
                            ->url(route('account.orders.detail', ['number' => $record->number]))
                            ->tooltip(__('Open the customer-facing order detail for :number', ['number' => $record->number]))
                            ->toArray(),
                        ListItem::make()
                            ->id('order-invoice-' . $record->getKey())
                            ->label(__('Download invoice'))
                            ->icon('heroicon-m-document-arrow-down')
                            ->color('info')
                            ->url(route('account.orders.invoice', ['number' => $record->number]))
                            ->tooltip(__('Download the invoice for :number (:total)', [
                                'number' => $record->number,
                                'total'  => Number::currency($record->total, $currency),
                            ]))
                            ->toArray(),
                    ];
                }),
            ListEntry::make('orderDiscountRedemptions')
                ->heading(__('discount_redemptions.title'))
                ->list()
                ->state(function (Order $record): array {
                    $record->loadMissing(['discountRedemptions.discount', 'discountRedemptions.code']);
                    $currency = $record->currency ?? config('shared.localization.default_currency', 'EUR');

                    return $record->discountRedemptions
                        ->map(function (DiscountRedemption $redemption) use ($currency): array {
                            $discount = $redemption->discount;
                            $code = $redemption->code;
                            $discountName = $discount?->getTranslation('name') ?? $discount?->name ?? __('discount_redemptions.single');
                            $codeValue = $code?->code ?? $redemption->getTranslation('status_description');

                            return ListItem::make()
                                ->id('order-discount-redemption-' . $redemption->getKey())
                                ->label(__('discount_redemptions.list_item_label', ['discount' => $discountName]))
                                ->icon('heroicon-m-ticket')
                                ->color('warning')
                                ->url(DiscountRedemptionResource::getUrl('view', ['record' => $redemption]))
                                ->tooltip(__('discount_redemptions.list_item_tooltip', [
                                    'amount' => Number::currency($redemption->amount_saved, $currency),
                                    'code'   => $codeValue,
                                ]))
                                ->toArray();
                        })
                        ->all();
                }),
            ListEntry::make('orderDocuments')
                ->heading(__('orders.documents'))
                ->list()
                ->state(function (Order $record): array {
                    $record->loadMissing(['documents.template']);

                    return $record->documents
                        ->map(function (Document $document): array {
                            $title = $document->title ?? $document->name ?? __('orders.document');

                            return ListItem::make()
                                ->id('order-document-' . $document->getKey())
                                ->label($title)
                                ->icon('heroicon-m-document-text')
                                ->color('info')
                                ->url(DocumentResource::getUrl('view', ['record' => $document]))
                                ->tooltip(__('orders.document_version_tooltip', [
                                    'version' => $document->version ?? 1,
                                ]))
                                ->toArray();
                        })
                        ->all();
                }),
            ListEntry::make('orderItemsSummary')
                ->heading(__('orders.order_items'))
                ->list()
                ->state(function (Order $record): array {
                    $record->loadMissing(['items.product']);
                    $currency = $record->currency ?? config('shared.localization.default_currency', 'EUR');

                    return $record->items
                        ->map(function (OrderItem $item) use ($currency): array {
                            $product = $item->product;
                            $productName = $product?->getTranslation('name') ?? $item->name;
                            $productUrl = $product !== null
                                ? route('frontend.products.show', $product)
                                : route('frontend.products.index');

                            return ListItem::make()
                                ->id('order-item-' . $item->getKey())
                                ->label(__('x:quantity — :product', [
                                    'quantity' => $item->quantity,
                                    'product'  => $productName,
                                ]))
                                ->icon('heroicon-m-rectangle-stack')
                                ->color('success')
                                ->url($productUrl)
                                ->tooltip(__('Line total: :total', [
                                    'total' => Number::currency($item->total, $currency),
                                ]))
                                ->toArray();
                        })
                        ->all();
                }),
            Section::make(__('orders.order_items'))
                ->schema([
                    TableRepeatableEntry::make('items')
                        ->label(__('orders.order_items'))
                        ->translateLabel()
                        ->state(function (Order $record): array {
                            $record->loadMissing(['items.product']);

                            return $record->items
                                ->map(fn (OrderItem $item): array => [
                                    'product'  => $item->product?->name ?? $item->name,
                                    'sku'      => $item->sku,
                                    'quantity' => $item->quantity,
                                    'price'    => $item->unit_price ?? $item->price,
                                    'total'    => $item->total,
                                ])
                                ->values()
                                ->all();
                        })
                        ->schema([
                            TextEntry::make('product')
                                ->label(__('orders.product'))
                                ->translateLabel(),
                            TextEntry::make('sku')
                                ->label(__('orders.sku'))
                                ->translateLabel(),
                            TextEntry::make('quantity')
                                ->label(__('orders.quantity'))
                                ->translateLabel()
                                ->numeric(),
                            TextEntry::make('price')
                                ->label(__('orders.price'))
                                ->translateLabel()
                                ->money(
                                    fn (TextEntry $component) => $component->getRecord()?->currency
                                        ?? config('shared.localization.default_currency', 'EUR')
                                ),
                            TextEntry::make('total')
                                ->label(__('orders.total'))
                                ->translateLabel()
                                ->money(
                                    fn (TextEntry $component) => $component->getRecord()?->currency
                                        ?? config('shared.localization.default_currency', 'EUR')
                                ),
                        ])
                        ->striped()
                        ->showIndex(),
                ])
                ->columns(1),
        ]);
    }
}
