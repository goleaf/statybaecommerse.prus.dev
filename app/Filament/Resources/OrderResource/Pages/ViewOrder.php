<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\DiscountRedemptionResource;
use App\Filament\Resources\DocumentResource;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;

final class ViewOrder extends ViewRecord
{
    use SpatieTranslatableViewRecord; // Keep the detail view synchronized with the active locale.

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(), // Allow locale switching while reviewing record details.
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('orders.order_items'))
                ->schema([
                    TableRepeatableEntry::make('items')
                        ->label(__('orders.order_items'))
                        ->translateLabel()
                        ->state(function (Order $record): array {
                            $record->loadMissing(['items.product']);

                            return $record->items
                                ->map(fn (OrderItem $item): array => [
                                    'product' => $item->product?->name ?? $item->name,
                                    'sku' => $item->sku,
                                    'quantity' => $item->quantity,
                                    'price' => $item->unit_price ?? $item->price,
                                    'total' => $item->total,
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
