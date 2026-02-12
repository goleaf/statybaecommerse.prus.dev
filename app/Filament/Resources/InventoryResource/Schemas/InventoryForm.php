<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryResource\Schemas;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class InventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.inventory.basic_information'))
                ->description(__('admin.inventory.basic_information_description'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('product_id')
                                ->label(__('messages.product'))
                                ->required()
                                ->searchable()
                                ->getSearchResultsUsing(static fn (string $search): array => self::searchProducts($search))
                                ->getOptionLabelUsing(static fn (mixed $value): ?string => self::resolveProductOptionLabel($value))
                                ->afterStateUpdated(static function (Set $set): void {
                                    $set('product_variant_id', null);
                                })
                                ->live(),
                            Select::make('product_variant_id')
                                ->label(__('translations.variant'))
                                ->options(static fn (Get $get): array => self::searchProductVariants((int) ($get('product_id') ?? 0)))
                                ->searchable()
                                ->preload()
                                ->visible(static fn (Get $get): bool => filled($get('product_id')))
                                ->dehydrated(static fn (Get $get): bool => filled($get('product_id'))),
                            Select::make('warehouse_id')
                                ->label(__('messages.warehouse'))
                                ->relationship(
                                    name: 'warehouse',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: static fn (Builder $query): Builder => $query
                                        ->withoutGlobalScopes()
                                        ->orderBy('name'),
                                )
                                ->getOptionLabelFromRecordUsing(static fn (Location $record): string => self::resolveWarehouseLabel($record))
                                ->required()
                                ->searchable(['name', 'code'])
                                ->preload(),
                            TextInput::make('qty')
                                ->label(__('messages.quantity'))
                                ->required()
                                ->numeric()
                                ->minValue(0),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('reserved')
                                ->label(__('admin.inventory.reserved_quantity'))
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
                            TextInput::make('threshold')
                                ->label(__('admin.inventory.low_stock_threshold'))
                                ->numeric()
                                ->minValue(0)
                                ->default(10),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private static function searchProducts(string $search): array
    {
        $search = trim($search);

        $query = Product::query()
            ->withoutGlobalScopes()
            ->select(['id', 'sku', 'name', 'barcode', 'updated_at'])
            ->orderByDesc('updated_at');

        if ($search !== '') {
            $like = "%{$search}%";

            $query->where(static function (Builder $builder) use ($like): void {
                $builder
                    ->where('sku', 'like', $like)
                    ->orWhere('barcode', 'like', $like)
                    ->orWhere('name', 'like', $like);
            });
        }

        return $query
            ->limit(50)
            ->get()
            ->mapWithKeys(static fn (Product $product): array => [
                (string) $product->getKey() => self::formatProductLabel($product),
            ])
            ->all();
    }

    private static function resolveProductOptionLabel(mixed $value): ?string
    {
        if (! is_scalar($value) || $value === '') {
            return null;
        }

        $product = Product::query()
            ->withoutGlobalScopes()
            ->select(['id', 'sku', 'name'])
            ->find((int) $value);

        if (! $product instanceof Product) {
            return null;
        }

        return self::formatProductLabel($product);
    }

    /**
     * @return array<string, string>
     */
    private static function searchProductVariants(int $productId): array
    {
        if ($productId <= 0) {
            return [];
        }

        return ProductVariant::query()
            ->withoutGlobalScopes()
            ->select(['id', 'sku', 'name'])
            ->where('product_id', $productId)
            ->orderBy('sku')
            ->limit(100)
            ->get()
            ->mapWithKeys(static fn (ProductVariant $variant): array => [
                (string) $variant->getKey() => self::formatVariantLabel($variant),
            ])
            ->all();
    }

    private static function formatProductLabel(Product $product): string
    {
        $sku = trim((string) ($product->getAttribute('sku') ?? ''));
        $name = self::normaliseLabelValue($product->getAttribute('name'));
        $identifier = $sku !== '' ? $sku : '#' . $product->getKey();

        if ($name === '') {
            return "[{$identifier}]";
        }

        return "[{$identifier}] {$name}";
    }

    private static function formatVariantLabel(ProductVariant $variant): string
    {
        $sku = trim((string) ($variant->getAttribute('sku') ?? ''));

        if ($sku !== '') {
            return $sku;
        }

        $name = self::normaliseLabelValue($variant->getAttribute('name'));

        return $name !== '' ? $name : '#' . $variant->getKey();
    }

    private static function resolveWarehouseLabel(Location $location): string
    {
        $translated = trim((string) ($location->getTranslatedName() ?? ''));
        if ($translated !== '') {
            return $translated;
        }

        $name = self::normaliseLabelValue($location->getAttribute('name'));
        if ($name !== '') {
            return $name;
        }

        $code = trim((string) ($location->getAttribute('code') ?? ''));
        if ($code !== '') {
            return $code;
        }

        return '#' . $location->getKey();
    }

    private static function normaliseLabelValue(mixed $value): string
    {
        if (is_array($value)) {
            $locale = app()->getLocale();
            $translated = $value[$locale] ?? reset($value);

            return is_scalar($translated) ? trim((string) $translated) : '';
        }

        if (! is_scalar($value)) {
            return '';
        }

        $stringValue = trim((string) $value);
        if ($stringValue === '') {
            return '';
        }

        if (! str_starts_with($stringValue, '{')) {
            return $stringValue;
        }

        $decoded = json_decode($stringValue, true);
        if (! is_array($decoded)) {
            return $stringValue;
        }

        $locale = app()->getLocale();
        $translated = $decoded[$locale] ?? reset($decoded);

        return is_scalar($translated) ? trim((string) $translated) : '';
    }
}
