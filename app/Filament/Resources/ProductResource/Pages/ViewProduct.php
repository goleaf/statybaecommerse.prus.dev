<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantAttributeValue;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
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
        $product = $this->record->loadMissing(['categories.translations']);

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

        $productName = $resolveTranslation($product, 'name');
        $productSlug = $resolveTranslation($product, 'slug') ?? $product->slug;

        $quickLinks = [
            ListItem::make()
                ->id('storefront-product-'.$product->getKey())
                ->label(__('View on storefront'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('primary')
                ->url(route('products.show', $productSlug))
                ->tooltip(__('Open the storefront page for :name', ['name' => $productName ?? $product->name])),
        ];

        $categoryItems = $product->categories
            ->map(function ($category) use ($resolveTranslation) {
                $name = $resolveTranslation($category, 'name');
                $slug = $resolveTranslation($category, 'slug') ?? $category->slug;

                if (blank($slug)) {
                    return null;
                }

                return ListItem::make()
                    ->id('category-'.$category->getKey())
                    ->label($name ?? __('Unnamed category'))
                    ->icon('heroicon-o-tag')
                    ->color('warning')
                    ->url(route('categories.show', $slug))
                    ->tooltip(__('View the :name category', ['name' => $name ?? __('category')]));
            })
            ->filter()
            ->values()
            ->all();

        return $infolist->schema([
            ListEntry::make('product_quick_links')
                ->heading(__('Quick links'))
                ->state(fn () => $quickLinks),
            ListEntry::make('product_categories')
                ->heading(__('Related categories'))
                ->list()
                ->state(fn () => $categoryItems),
        ]);
    }
}
