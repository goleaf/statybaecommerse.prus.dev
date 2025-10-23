<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantAttributeValue;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;
use LaraZeus\ListGroup\Entries\ListItem;
use LaraZeus\ListGroup\Infolists\ListEntry;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            ListEntry::make('productQuickLinks')
                ->heading(__('Quick links'))
                ->list()
                ->state(function (Product $record): array {
                    // Resolve the locale once so translated attributes render with the expected language.
                    $locale = app()->getLocale();
                    $record->loadMissing(['brand']);

                    $items = [
                        ListItem::make()
                            ->id('product-storefront-link')
                            ->label(__('View on storefront'))
                            ->icon('heroicon-m-globe-alt')
                            ->color('primary')
                            ->url(route('frontend.products.show', $record))
                            ->tooltip(__('Open the storefront page for :product', [
                                'product' => $record->getTranslation('name', $locale),
                            ]))
                            ->toArray(),
                    ];

                    if ($record->brand !== null) {
                        $brandName = $record->brand->getTranslation('name', $locale);

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
                ->heading(__('products.related_categories'))
                ->list()
                ->state(function (Product $record): array {
                    // Eager load categories and respect translations for each rendered list item.
                    $locale = app()->getLocale();
                    $record->loadMissing(['categories']);

                    return $record->categories
                        ->map(function (Category $category) use ($locale): array {
                            $categoryName = $category->getTranslation('name', $locale);

                            return ListItem::make()
                                ->id('product-category-' . $category->getKey())
                                ->label($categoryName)
                                ->icon('heroicon-m-rectangle-stack')
                                ->color('success')
                                ->url(route('frontend.categories.show', $category))
                                ->tooltip(__('products.view_category_tooltip', ['category' => $categoryName]))
                                ->toArray();
                        })
                        ->all();
                }),
            ListEntry::make('productCollections')
                ->heading(__('products.related_collections'))
                ->list()
                ->state(function (Product $record): array {
                    // Display related collections with localized labels for the active locale.
                    $locale = app()->getLocale();
                    $record->loadMissing(['collections']);

                    return $record->collections
                        ->map(function (Collection $collection) use ($locale): array {
                            $collectionName = $collection->getTranslation('name', $locale);

                            return ListItem::make()
                                ->id('product-collection-' . $collection->getKey())
                                ->label($collectionName)
                                ->icon('heroicon-m-queue-list')
                                ->color('warning')
                                ->url(route('frontend.collections.show', $collection))
                                ->tooltip(__('products.explore_collection_tooltip', ['collection' => $collectionName]))
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