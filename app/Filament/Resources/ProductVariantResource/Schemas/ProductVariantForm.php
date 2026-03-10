<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\Schemas;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductVariant;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as SchemaFacade;

class ProductVariantForm
{
    public const ATTRIBUTE_SELECTIONS_FIELD = 'attribute_selections';

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.product_variants.general_info'))
                    ->schema([
                        Select::make('product_id')
                            ->label(__('messages.product'))
                            ->relationship('product', 'name')
                            ->default(static fn (): ?int => request()->integer('product_id') ?: null)
                            ->required()
                            ->searchable(),
                        TextInput::make('sku')
                            ->label(__('messages.sku'))
                            ->required(),
                        TextInput::make('name')
                            ->label(__('messages.name'))
                            ->maxLength(255),
                        TextInput::make('barcode')
                            ->label(__('messages.barcode'))
                            ->maxLength(255),
                    ])->columns(2),

                Section::make(__('messages.attributes'))
                    ->schema([
                        Repeater::make(self::ATTRIBUTE_SELECTIONS_FIELD)
                            ->label(__('messages.attributes'))
                            ->schema([
                                Select::make('attribute_id')
                                    ->label(__('messages.attribute'))
                                    ->options(static fn (Get $get): array => self::resolveAttributeOptions($get))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(static function (Set $set): void {
                                        $set('attribute_value_id', null);
                                    }),
                                Select::make('attribute_value_id')
                                    ->label(__('messages.attribute_value'))
                                    ->options(static fn (Get $get): array => self::resolveAttributeValueOptions($get))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disabled(static fn (Get $get): bool => ! is_numeric($get('attribute_id'))),
                            ])
                            ->addActionLabel(__('admin.variant_combinations.add_attribute'))
                            ->defaultItems(0)
                            ->reorderableWithButtons()
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('admin.product_variants.pricing'))
                    ->schema([
                        TextInput::make('price')
                            ->label(__('messages.price'))
                            ->numeric()
                            ->required()
                            ->prefix('€'),
                        TextInput::make('cost_price')
                            ->label(__('admin.products.cost_price'))
                            ->numeric()
                            ->prefix('€'),
                        TextInput::make('wholesale_price')
                            ->label(__('messages.wholesale_price'))
                            ->numeric()
                            ->prefix('€'),
                        TextInput::make('member_price')
                            ->label(__('messages.member_price'))
                            ->numeric()
                            ->prefix('€'),
                        TextInput::make('promotional_price')
                            ->label(__('messages.promotional_price'))
                            ->numeric()
                            ->prefix('€'),
                    ])->columns(2),

                Section::make(__('admin.product_variants.inventory'))
                    ->schema([
                        TextInput::make('stock_quantity')
                            ->label(__('admin.products.stock_quantity'))
                            ->numeric()
                            ->default(0),
                        TextInput::make('low_stock_threshold')
                            ->label(__('admin.products.low_stock_threshold'))
                            ->numeric()
                            ->default(5),
                        Toggle::make('track_inventory')
                            ->label(__('admin.products.track_stock'))
                            ->default(true),
                        Toggle::make('allow_backorder')
                            ->label(__('admin.products.allow_backorder'))
                            ->default(false),
                    ])->columns(2),

                Section::make(__('admin.product_variants.dimensions'))
                    ->schema([
                        TextInput::make('size')
                            ->label(__('messages.size'))
                            ->maxLength(255),
                        TextInput::make('size_unit')
                            ->label(__('messages.size_unit'))
                            ->maxLength(255),
                        TextInput::make('size_display')
                            ->label(__('messages.size_display'))
                            ->maxLength(255),
                        TextInput::make('weight')
                            ->label(__('admin.products.weight'))
                            ->numeric()
                            ->suffix('kg'),
                    ])->columns(2),

                Section::make(__('admin.product_variants.status_features'))
                    ->schema([
                        Radio::make('is_enabled')
                            ->label(__('messages.is_enabled'))
                            ->boolean()
                            ->default(true)
                            ->inline()
                            ->columnSpanFull(),
                        Radio::make('is_default_variant')
                            ->label(__('messages.is_default_variant'))
                            ->boolean()
                            ->default(false)
                            ->inline()
                            ->columnSpanFull(),
                        Radio::make('is_featured')
                            ->label(__('messages.is_featured'))
                            ->boolean()
                            ->default(false)
                            ->inline()
                            ->columnSpanFull(),
                        Radio::make('is_new')
                            ->label(__('messages.is_new'))
                            ->boolean()
                            ->default(false)
                            ->inline()
                            ->columnSpanFull(),
                        Radio::make('is_bestseller')
                            ->label(__('messages.is_bestseller'))
                            ->boolean()
                            ->default(false)
                            ->inline()
                            ->columnSpanFull(),
                    ])->columns(1),

                Section::make(__('admin.product_variants.localization'))
                    ->hiddenOn('edit')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('variant_name_lt')
                                    ->label(__('admin.fields.name_lt')),
                                TextInput::make('variant_name_en')
                                    ->label(__('admin.fields.name_en')),
                                Textarea::make('description_lt')
                                    ->label(__('admin.fields.description_lt')),
                                Textarea::make('description_en')
                                    ->label(__('admin.fields.description_en')),
                            ]),
                    ]),
            ]);
    }

    /**
     * @return array<int, array{attribute_id:int, attribute_value_id:int}>
     */
    public static function normalizeAttributeSelections(mixed $state): array
    {
        if (! is_array($state)) {
            return [];
        }

        $normalized = [];

        foreach ($state as $row) {
            $attributeId = data_get($row, 'attribute_id');
            $attributeValueId = data_get($row, 'attribute_value_id');

            if (! is_numeric($attributeId) || ! is_numeric($attributeValueId)) {
                continue;
            }

            $normalized[(int) $attributeId] = [
                'attribute_id'       => (int) $attributeId,
                'attribute_value_id' => (int) $attributeValueId,
            ];
        }

        return array_values($normalized);
    }

    /**
     * @param  array<int, array{attribute_id:int, attribute_value_id:int}> $selections
     * @return array<string, int>
     */
    public static function buildAttributeMatrixPayload(array $selections): array
    {
        $matrix = [];

        foreach ($selections as $selection) {
            $attributeId = (int) ($selection['attribute_id'] ?? 0);
            $attributeValueId = (int) ($selection['attribute_value_id'] ?? 0);

            if ($attributeId < 1 || $attributeValueId < 1) {
                continue;
            }

            $matrix['attribute_' . $attributeId] = $attributeValueId;
        }

        return $matrix;
    }

    /**
     * @param  array<int, array{attribute_id:int, attribute_value_id:int}> $selections
     * @return array<string, string>
     */
    public static function buildLegacyAttributesPayload(array $selections): array
    {
        $attributeValueIds = collect($selections)
            ->pluck('attribute_value_id')
            ->filter(static fn (mixed $id): bool => is_numeric($id))
            ->map(static fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($attributeValueIds === []) {
            return [];
        }

        $attributeValues = AttributeValue::query()
            ->withoutGlobalScopes()
            ->with('attribute')
            ->whereIn('id', $attributeValueIds)
            ->get()
            ->keyBy(static fn (AttributeValue $attributeValue): int => (int) $attributeValue->getKey());

        $attributes = [];

        foreach ($selections as $selection) {
            $attributeValueId = (int) ($selection['attribute_value_id'] ?? 0);
            $attributeValue = $attributeValues->get($attributeValueId);

            if (! $attributeValue instanceof AttributeValue) {
                continue;
            }

            $attributeName = trim((string) ($attributeValue->attribute?->name ?? ''));
            $resolvedValue = trim((string) ($attributeValue->display_value ?? $attributeValue->value));

            if ($attributeName === '' || $resolvedValue === '') {
                continue;
            }

            $attributes[$attributeName] = $resolvedValue;
        }

        return $attributes;
    }

    /**
     * @return array<int, array{attribute_id:int, attribute_value_id:int}>
     */
    public static function buildSelectionStateFromRecord(ProductVariant $record): array
    {
        $variantId = (int) ($record->getKey() ?? 0);

        if ($variantId < 1) {
            return [];
        }

        if (SchemaFacade::hasTable('product_variant_attributes')) {
            $selections = DB::table('product_variant_attributes')
                ->where('variant_id', $variantId)
                ->whereNotNull('attribute_id')
                ->whereNotNull('attribute_value_id')
                ->orderBy('attribute_id')
                ->orderBy('attribute_value_id')
                ->get(['attribute_id', 'attribute_value_id'])
                ->map(static fn (object $row): array => [
                    'attribute_id'       => (int) ($row->attribute_id ?? 0),
                    'attribute_value_id' => (int) ($row->attribute_value_id ?? 0),
                ])
                ->filter(static fn (array $row): bool => $row['attribute_id'] > 0 && $row['attribute_value_id'] > 0)
                ->values()
                ->all();

            if ($selections !== []) {
                return self::normalizeAttributeSelections($selections);
            }
        }

        $matrix = $record->variant_attribute_matrix;

        return self::normalizeSelectionsFromMatrix(is_array($matrix) ? $matrix : []);
    }

    private static function resolveAttributeOptions(Get $get): array
    {
        $productId = self::resolveProductId($get);
        $selectedAttributeIds = self::resolveSelectedAttributeIds($get);
        $attributeIds = $selectedAttributeIds;

        if ($productId !== null && SchemaFacade::hasTable('product_attributes')) {
            $productAttributeIds = DB::table('product_attributes')
                ->where('product_id', $productId)
                ->whereNotNull('attribute_id')
                ->pluck('attribute_id')
                ->filter(static fn (mixed $attributeId): bool => is_numeric($attributeId))
                ->map(static fn (mixed $attributeId): int => (int) $attributeId)
                ->values()
                ->all();

            $attributeIds = array_values(array_unique(array_merge($attributeIds, $productAttributeIds)));
        }

        $query = Attribute::query()->withoutGlobalScopes()->orderBy('name');

        if ($attributeIds !== []) {
            $query->whereIn('id', $attributeIds);
        }

        return $query->pluck('name', 'id')->toArray();
    }

    private static function resolveAttributeValueOptions(Get $get): array
    {
        $attributeId = $get('attribute_id');

        if (! is_numeric($attributeId)) {
            return [];
        }

        return AttributeValue::query()
            ->withoutGlobalScopes()
            ->where('attribute_id', (int) $attributeId)
            ->orderedByName()
            ->get()
            ->mapWithKeys(static function (AttributeValue $attributeValue): array {
                $label = trim((string) ($attributeValue->display_value ?? $attributeValue->value));

                if ($label === '') {
                    $label = (string) $attributeValue->value;
                }

                return [(int) $attributeValue->getKey() => $label];
            })
            ->all();
    }

    private static function resolveProductId(Get $get): ?int
    {
        $state = $get('../../product_id') ?? $get('product_id');

        return is_numeric($state) ? (int) $state : null;
    }

    /**
     * @return array<int, int>
     */
    private static function resolveSelectedAttributeIds(Get $get): array
    {
        $selections = $get('../../' . self::ATTRIBUTE_SELECTIONS_FIELD);

        if (! is_array($selections)) {
            return [];
        }

        return collect($selections)
            ->pluck('attribute_id')
            ->filter(static fn (mixed $attributeId): bool => is_numeric($attributeId))
            ->map(static fn (mixed $attributeId): int => (int) $attributeId)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>                                        $matrix
     * @return array<int, array{attribute_id:int, attribute_value_id:int}>
     */
    private static function normalizeSelectionsFromMatrix(array $matrix): array
    {
        $selections = [];

        foreach ($matrix as $attributeKey => $value) {
            $attributeId = self::parseAttributeKey((string) $attributeKey);

            if ($attributeId === null) {
                continue;
            }

            if (is_numeric($value)) {
                $selections[] = [
                    'attribute_id'       => $attributeId,
                    'attribute_value_id' => (int) $value,
                ];

                continue;
            }

            if (! is_array($value)) {
                continue;
            }

            $selectedValues = array_values($value) === $value
                ? $value
                : collect($value)
                    ->filter(static fn (mixed $isSelected): bool => (bool) $isSelected)
                    ->keys()
                    ->all();

            foreach ($selectedValues as $selectedValueId) {
                if (! is_numeric($selectedValueId)) {
                    continue;
                }

                $selections[] = [
                    'attribute_id'       => $attributeId,
                    'attribute_value_id' => (int) $selectedValueId,
                ];
            }
        }

        return self::normalizeAttributeSelections($selections);
    }

    private static function parseAttributeKey(string $key): ?int
    {
        if (ctype_digit($key)) {
            return (int) $key;
        }

        if (preg_match('/(\d+)/', $key, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }
}
