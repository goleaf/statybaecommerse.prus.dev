<?php

declare(strict_types=1);

namespace App\Filament\Resources;


use App\Support\Concerns\HasNav;
use Filament\Schemas\Schema;
use App\Filament\Resources\ProductVariantResource\Pages;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductVariantAttributeMatrixService;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use App\Support\Forms\MatrixFactory;
use BackedEnum;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs as SchemaTabs;
use Filament\Schemas\Components\Tabs\Tab as SchemaTab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use UnitEnum;
use Pixelpeter\FilamentLanguageTabs\Forms\Components\LanguageTabs;

/**
 * ProductVariantResource
 *
 * Filament v4 resource for ProductVariant management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ProductVariantResource extends Resource
{
    use HasNav;

    protected static ?string $model = ProductVariant::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'display_name';

    /**
     * @var array<int, int|null>
     */
    private static array $matrixProductCache = [];

    public static function getNavigationLabel(): string
    {
        return __('product_variants.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('product_variants.plural');
    }

    public static function getModelLabel(): string
    {
        return __('product_variants.single');
    }

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return 'heroicon-o-squares-2x2';
    }

    

    public static function form(Schema $schema): Schema   
    {
        return $schema
            ->schema([
                SchemaTabs::make('Variant Information')
                    ->tabs([
                        SchemaTab::make('Basic Information')
                            ->schema([
                                SchemaSection::make('Variant Details')
                                    ->schema([
                                        SchemaGrid::make(2)
                                            ->schema([
                                                Select::make('product_id')
                                                    ->label(__('product_variants.fields.product'))
                                                    ->relationship('product', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload(),
                                                TextInput::make('sku')
                                                    ->label(__('product_variants.fields.sku'))
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->maxLength(255),
                                            ]),
                                        SchemaGrid::make(2)
                                            ->schema([
                                                TextInput::make('barcode')
                                                    ->label(__('product_variants.fields.barcode'))
                                                    ->maxLength(255),
                                                TextInput::make('variant_sku_suffix')
                                                    ->label(__('product_variants.fields.variant_sku_suffix'))
                                                    ->maxLength(50),
                                            ]),
                                        LanguageTabs::make([
                                            TextInput::make('name')
                                                ->label(__('product_variants.fields.name'))
                                                ->required()
                                                ->maxLength(255),
                                            Textarea::make('description')
                                                ->label(__('product_variants.fields.description'))
                                                ->rows(3),
                                        ])->columnSpanFull(),
                                    ]),
                                SchemaSection::make('Pricing')
                                    ->schema([
                                        SchemaGrid::make(3)
                                            ->schema([
                                                TextInput::make('price')
                                                    ->label(__('product_variants.fields.price'))
                                                    ->required()
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                                TextInput::make('compare_price')
                                                    ->label(__('product_variants.fields.compare_price'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                                TextInput::make('cost_price')
                                                    ->label(__('product_variants.fields.cost_price'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                            ]),
                                        SchemaGrid::make(3)
                                            ->schema([
                                                TextInput::make('wholesale_price')
                                                    ->label(__('product_variants.fields.wholesale_price'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                                TextInput::make('member_price')
                                                    ->label(__('product_variants.fields.member_price'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                                TextInput::make('promotional_price')
                                                    ->label(__('product_variants.fields.promotional_price'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                            ]),
                                    ]),
                            ]),
                        SchemaTab::make('Inventory & Stock')
                            ->schema([
                                SchemaSection::make('Stock Management')
                                    ->schema([
                                        SchemaGrid::make(2)
                                            ->schema([
                                                Toggle::make('track_inventory')
                                                    ->label(__('product_variants.fields.track_inventory'))
                                                    ->default(true),
                                                Toggle::make('is_enabled')
                                                    ->label(__('product_variants.fields.is_enabled'))
                                                    ->default(true),
                                            ]),
                                        SchemaGrid::make(3)
                                            ->schema([
                                                TextInput::make('stock_quantity')
                                                    ->label(__('product_variants.fields.stock_quantity'))
                                                    ->numeric()
                                                    ->default(0),
                                                TextInput::make('reserved_quantity')
                                                    ->label(__('product_variants.fields.reserved_quantity'))
                                                    ->numeric()
                                                    ->default(0),
                                                TextInput::make('low_stock_threshold')
                                                    ->label(__('product_variants.fields.low_stock_threshold'))
                                                    ->numeric()
                                                    ->default(5),
                                            ]),
                                    ]),
                                SchemaSection::make('Physical Properties')
                                    ->schema([
                                        SchemaGrid::make(2)
                                            ->schema([
                                                TextInput::make('weight')
                                                    ->label(__('product_variants.fields.weight'))
                                                    ->numeric()
                                                    ->suffix('kg')
                                                    ->step(0.01),
                                                TextInput::make('variant_type')
                                                    ->label(__('product_variants.fields.variant_type'))
                                                    ->maxLength(255),
                                            ]),
                                    ]),
                            ]),
                        SchemaTab::make('Marketing & Features')
                            ->schema([
                                SchemaSection::make('Marketing Settings')
                                    ->schema([
                                        SchemaGrid::make(2)
                                            ->schema([
                                                Toggle::make('is_featured')
                                                    ->label(__('product_variants.fields.is_featured')),
                                                Toggle::make('is_new')
                                                    ->label(__('product_variants.fields.is_new')),
                                            ]),
                                        SchemaGrid::make(2)
                                            ->schema([
                                                Toggle::make('is_bestseller')
                                                    ->label(__('product_variants.fields.is_bestseller')),
                                                Toggle::make('is_on_sale')
                                                    ->label(__('product_variants.fields.is_on_sale')),
                                            ]),
                                        SchemaGrid::make(2)
                                            ->schema([
                                                SupportFlatpickr::makeDateTime('sale_start_date')
                                                    ->label(__('product_variants.fields.sale_start_date')),
                                                SupportFlatpickr::makeDateTime('sale_end_date')
                                                    ->label(__('product_variants.fields.sale_end_date')),
                                            ]),
                                    ]),
                                SchemaSection::make('SEO Settings')
                                    ->schema([
                                        LanguageTabs::make([
                                            TextInput::make('seo_title')
                                                ->label(__('product_variants.fields.seo_title'))
                                                ->maxLength(255),
                                            Textarea::make('seo_description')
                                                ->label(__('product_variants.fields.seo_description'))
                                                ->rows(3),
                                        ])->columnSpanFull(),
                                    ]),
                            ]),
                        SchemaTab::make('Attributes & Variants')
                            ->schema([
                                SchemaSection::make('Variant Attribute Matrix')
                                    ->schema([
                                        MatrixFactory::radioGrid(
                                            'variant_attribute_matrix',
                                            fn (Get $get): SupportCollection => self::attributeMatrixRows(
                                                self::resolveMatrixProductId($get)
                                            ),
                                        ),
                                    ]),
                                SchemaSection::make('Additional Data')
                                    ->schema([
                                        KeyValue::make('variant_metadata')
                                            ->label(__('product_variants.fields.variant_metadata'))
                                            ->keyLabel(__('product_variants.fields.variant_metadata_key'))
                                            ->valueLabel(__('product_variants.fields.variant_metadata_value')),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return Builder<ProductVariant>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'translations',
                'product.translations',
            ]);
    }

    public static function syncVariantAttributeRelations(ProductVariant $variant, array $matrix = [], array $legacySelections = []): void
    {
        if ($legacySelections === [] && self::isLegacySelectionPayload($matrix)) {
            $legacySelections = $matrix;
            $matrix = [];
        }

        ProductVariantAttributeMatrixService::sync($variant, $matrix, $legacySelections);
    }

    private static function resolveMatrixProductId(Get $get): ?int
    {
        $productId = $get('product_id');

        if (filled($productId)) {
            return (int) $productId;
        }

        $recordId = request()?->route('record');

        if (! $recordId) {
            return null;
        }

        $recordId = (int) $recordId;

        if (! array_key_exists($recordId, self::$matrixProductCache)) {
            self::$matrixProductCache[$recordId] = ProductVariant::query()
                ->select(['id', 'product_id'])
                ->find($recordId)?->product_id;
        }

        $cached = self::$matrixProductCache[$recordId];

        return $cached !== null ? (int) $cached : null;
    }

    private static function attributeMatrixRows(?int $productId): SupportCollection
    {
        $attributes = Attribute::query()
            ->with(['values' => fn ($query) => $query->orderBy('sort_order')->orderBy('value')])
            ->when($productId, fn ($query) => $query->whereHas('products', fn ($relation) => $relation->whereKey($productId)))
            ->orderBy('sort_order')
            ->get();

        if ($attributes->isEmpty()) {
            $attributes = Attribute::query()
                ->with(['values' => fn ($query) => $query->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->take(5)
                ->get();
        }

        return $attributes
            ->map(function (Attribute $attribute): array {
                return [
                    'key'          => 'attribute_' . $attribute->getKey(),
                    'attribute_id' => $attribute->getKey(),
                    'label'        => $attribute->trans('name') ?? $attribute->name,
                    'options'      => $attribute->values
                        ->mapWithKeys(fn (AttributeValue $value): array => [
                            (string) $value->getKey() => $value->display_value ?? $value->value,
                        ])
                        ->all(),
                ];
            })
            ->filter(fn (array $row): bool => ! empty($row['options']))
            ->values();
    }

    private static function isLegacySelectionPayload(array $payload): bool
    {
        return collect($payload)
            ->every(fn ($item): bool => is_array($item) && array_key_exists('attribute_id', $item) && array_key_exists('attribute_value_id', $item));
    }

    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                ImageColumn::make('primaryImage.thumbnail_url')
                    ->label(__('product_variants.fields.image'))
                    ->getStateUsing(fn (ProductVariant $record): ?string => $record->primaryImage?->thumbnail_url)
                    ->defaultImageUrl(product_placeholder_url('thumb'))
                    ->circular()
                    ->size(50),
                TextColumn::make('product.name')
                    ->label(__('product_variants.fields.product'))
                    ->sortable()
                    ->searchable(['product.name', 'product.translations.name'])
                    ->formatStateUsing(fn ($state, ProductVariant $record): string => $record->product?->trans('name') ?? (string) $state),
                TextColumn::make('name')
                    ->label(__('product_variants.fields.name'))
                    ->searchable(['name', 'translations.name'])
                    ->sortable()
                    ->formatStateUsing(fn ($state, ProductVariant $record): string => $record->getLocalizedName()),
                TextColumn::make('sku')
                    ->label(__('product_variants.fields.sku'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('price')
                    ->label(__('product_variants.fields.price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->label(__('product_variants.fields.stock'))
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0  => 'danger',
                        $state <= 10 => 'warning',
                        default      => 'success',
                    }),
                BadgeColumn::make('stock_status')
                    ->label(__('product_variants.fields.stock_status'))
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'in_stock'     => __('product_variants.stock_status.in_stock'),
                        'low_stock'    => __('product_variants.stock_status.low_stock'),
                        'out_of_stock' => __('product_variants.stock_status.out_of_stock'),
                        'not_tracked'  => __('product_variants.stock_status.not_tracked'),
                        default        => $state,
                    }),
                IconColumn::make('is_enabled')
                    ->label(__('product_variants.fields.is_enabled'))
                    ->boolean(),
                IconColumn::make('is_default_variant')
                    ->label(__('product_variants.fields.is_default_variant'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('product_variants.fields.is_featured'))
                    ->boolean(),
                IconColumn::make('is_on_sale')
                    ->label(__('product_variants.fields.is_on_sale'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('product_variants.fields.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('product_lookup')
                    ->label(__('product_variants.filters.product'))
                    ->form([
                        SearchableInput::make('product_name')
                            ->label(__('product_variants.filters.product'))
                            ->placeholder(__('product_variants.filters.product_placeholder'))
                            ->maxLength(255)
                            ->searchUsing(fn (string $search): array => self::searchProductOptions($search)),
                    ])
                    ->indicateUsing(function (array $data): array {
                        $value = trim((string) ($data['product_name'] ?? ''));

                        if ($value === '') {
                            return [];
                        }

                        return [__('product_variants.filters.product') . ': ' . $value];
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $value = trim((string) ($data['product_name'] ?? ''));

                        if ($value === '') {
                            return $query;
                        }

                        return $query->whereHas('product', function (Builder $relationQuery) use ($value): void {
                            $relationQuery
                                ->where('name', 'like', "%{$value}%")
                                ->orWhere('sku', 'like', "%{$value}%");
                        });
                    }),
                SelectFilter::make('variant_type')
                    ->label(__('product_variants.fields.variant_type'))
                    ->options([
                        'size'     => __('product_variants.variant_types.size'),
                        'color'    => __('product_variants.variant_types.color'),
                        'material' => __('product_variants.variant_types.material'),
                        'style'    => __('product_variants.variant_types.style'),
                        'custom'   => __('product_variants.variant_types.custom'),
                    ]),
                SelectFilter::make('stock_status')
                    ->label(__('product_variants.filters.stock_status'))
                    ->options([
                        'in_stock'     => __('product_variants.stock_status.in_stock'),
                        'low_stock'    => __('product_variants.stock_status.low_stock'),
                        'out_of_stock' => __('product_variants.stock_status.out_of_stock'),
                        'not_tracked'  => __('product_variants.stock_status.not_tracked'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        $availableExpression = 'COALESCE(stock_quantity, 0) - COALESCE(reserved_quantity, 0)';

                        return match ($value) {
                            'in_stock' => $query
                                ->where('track_inventory', true)
                                ->whereRaw("{$availableExpression} > 0")
                                ->where(function (Builder $query) use ($availableExpression): void {
                                    $query
                                        ->whereNull('low_stock_threshold')
                                        ->orWhereRaw("{$availableExpression} > low_stock_threshold");
                                }),
                            'low_stock' => $query
                                ->where('track_inventory', true)
                                ->whereRaw("{$availableExpression} > 0")
                                ->whereNotNull('low_stock_threshold')
                                ->whereRaw("{$availableExpression} <= low_stock_threshold"),
                            'out_of_stock' => $query
                                ->where('track_inventory', true)
                                ->whereRaw("{$availableExpression} <= 0"),
                            'not_tracked' => $query->where('track_inventory', false),
                            default       => $query,
                        };
                    }),
                Filter::make('sku')
                    ->label(__('product_variants.fields.sku'))
                    ->form([
                        SearchableInput::make('sku')
                            ->label(__('product_variants.fields.sku'))
                            ->maxLength(255)
                            ->searchUsing(fn (string $search): array => self::variantSkuSuggestions($search))
                            ->options(fn (): array => self::variantSkuSuggestions()),
                    ])
                    ->indicateUsing(fn (array $data): array => filled($data['sku'] ?? null)
                        ? [__('product_variants.fields.sku') . ': ' . $data['sku']]
                        : [])
                    ->query(function (Builder $query, array $data): Builder {
                        $sku = $data['sku'] ?? null;

                        if (! filled($sku)) {
                            return $query;
                        }

                        return $query->where('sku', 'like', "%{$sku}%");
                    }),
                TernaryFilter::make('is_enabled')
                    ->label(__('product_variants.fields.is_enabled')),
                TernaryFilter::make('is_default_variant')
                    ->label(__('product_variants.fields.is_default_variant')),
                TernaryFilter::make('is_featured')
                    ->label(__('product_variants.fields.is_featured')),
                TernaryFilter::make('is_on_sale')
                    ->label(__('product_variants.fields.is_on_sale')),
                TernaryFilter::make('is_new')
                    ->label(__('product_variants.fields.is_new')),
                TernaryFilter::make('is_bestseller')
                    ->label(__('product_variants.fields.is_bestseller')),
            ])
            ->actions([
                Action::make('set_default')
                    ->label(__('product_variants.actions.set_default'))
                    ->icon('heroicon-o-star')
                    ->action(function (ProductVariant $record): void {
                        $record->setAsDefault();
                        Notification::make()
                            ->title(__('product_variants.messages.set_as_default_success'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (ProductVariant $record): bool => ! $record->is_default_variant),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('enable')
                        ->label(__('product_variants.actions.enable'))
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_enabled' => true]);
                            Notification::make()
                                ->title(__('product_variants.messages.bulk_enable_success'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('disable')
                        ->label(__('product_variants.actions.disable'))
                        ->icon('heroicon-o-x-circle')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_enabled' => false]);
                            Notification::make()
                                ->title(__('product_variants.messages.bulk_disable_success'))
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProductVariants::route('/'),
            'create' => Pages\CreateProductVariant::route('/create'),
            'view'   => Pages\ViewProductVariant::route('/{record}'),
            'edit'   => Pages\EditProductVariant::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function searchProductOptions(string $search): array
    {
        $term = trim($search);

        if ($term === '') {
            return [];
        }

        return Product::query()
            ->select(['id', 'name', 'sku'])
            ->with('translations')
            ->where(function (Builder $query) use ($term): void {
                $query
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhereHas('translations', static function (Builder $relation) use ($term): void {
                        $relation->where('name', 'like', "%{$term}%");
                    });
            })
            ->orderBy('name')
            ->limit(15)
            ->get()
            ->map(static function (Product $product): string {
                $sku = $product->sku;

                $name = $product->trans('name') ?? $product->name;

                return ltrim(($sku ? "[{$sku}] " : '') . $name);
            })
            ->unique()
            ->values()
            ->all();
    }
}
