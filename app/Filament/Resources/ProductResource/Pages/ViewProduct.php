<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantAttributeValue;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;
use LaraZeus\ListGroup\Entries\ListItem;
use LaraZeus\ListGroup\Infolists\ListEntry;

final class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            ListEntry::make('productQuickLinks')
                ->heading(__('Quick links'))
                ->state(function (Product $record): array {
                    $record->loadMissing(['brand']);

                    $items = [
                        ListItem::make()
                            ->id('product-storefront-link')
                            ->label(__('View on storefront'))
                            ->icon('heroicon-m-globe-alt')
                            ->color('primary')
                            ->url(route('frontend.products.show', $record))
                            ->tooltip(__('Open the storefront page for :product', [
                                'product' => $record->getTranslation('name'),
                            ]))
                            ->toArray(),
                    ];

                    if ($record->brand !== null) {
                        $brandName = $record->brand->getTranslation('name');

                        $items[] = ListItem::make()
                            ->id('product-brand-' . $record->brand->getKey())
                            ->label(__('View brand :brand', ['brand' => $brandName]))
                            ->icon('heroicon-m-tag')
                            ->color('info')
                            ->url(route('frontend.brands.show', $record->brand))
                            ->tooltip(__('Browse all products from :brand', ['brand' => $brandName]))
                            ->toArray();
                    }

                    return $items;
                }),
            ListEntry::make('productCategories')
                ->heading(__('Related categories'))
                ->list()
                ->state(function (Product $record): array {
                    $record->loadMissing(['categories']);

                    return $record->categories
                        ->map(function (Category $category): array {
                            $categoryName = $category->getTranslation('name');

                            return ListItem::make()
                                ->id('product-category-' . $category->getKey())
                                ->label($categoryName)
                                ->icon('heroicon-m-rectangle-stack')
                                ->color('success')
                                ->url(route('frontend.categories.show', $category))
                                ->tooltip(__('View the :category category', ['category' => $categoryName]))
                                ->toArray();
                        })
                        ->all();
                }),
            Section::make(__('ecommerce.variants'))
                ->schema([
                    TableRepeatableEntry::make('variants')
                        ->label(__('ecommerce.variants'))
                        ->translateLabel()
                        ->state(function (Product $record): array {
                            $record->loadMissing(['variants.variantAttributeValues']);

                            return $record->variants
                                ->map(fn (ProductVariant $variant): array => [
                                    'name'       => $variant->display_name,
                                    'sku'        => $variant->sku,
                                    'price'      => $variant->price,
                                    'stock'      => $variant->available_quantity ?? $variant->stock_quantity,
                                    'attributes' => $variant->variantAttributeValues
                                        ->map(fn (VariantAttributeValue $value): string => sprintf('%s: %s', $value->attribute_name, $value->display_value))
                                        ->filter()
                                        ->implode(', '),
                                ])
                                ->values()
                                ->all();
                        })
                        ->schema([
                            TextEntry::make('name')
                                ->label(__('ecommerce.name'))
                                ->translateLabel(),
                            TextEntry::make('sku')
                                ->label(__('ecommerce.sku'))
                                ->translateLabel(),
                            TextEntry::make('price')
                                ->label(__('ecommerce.price'))
                                ->translateLabel()
                                ->money(fn () => config('shared.localization.default_currency', 'EUR'), decimalPlaces: 2),
                            TextEntry::make('stock')
                                ->label(__('ecommerce.stock'))
                                ->translateLabel()
                                ->numeric(),
                            TextEntry::make('attributes')
                                ->label(__('ecommerce.attributes'))
                                ->translateLabel(),
                        ])
                        ->striped()
                        ->showIndex(),
                ])
                ->columns(1),
        ]);
    }
}
