<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Filament\Resources\PriceResource\Pages;
use App\Models\Price;
use App\Models\Product;
use App\Support\Filament\SearchableInputHelper;
use App\Support\Search\ProductSearch;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use UnitEnum;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class PriceResource extends Resource
{
    use HasNav;

    protected static ?string $model = Price::class;

    /** @var string|\UnitEnum|null */
    protected static \UnitEnum|string|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 12;

    public static function form(Form $form): Form
    {
        return $schema
            ->schema([
                Section::make(__('admin.prices.priceable_association'))
                    ->description(__('admin.prices.priceable_association_description'))
                    ->schema([
                        MorphToSelect::make('priceable')
                            ->label(__('admin.prices.priceable'))
                            ->types([
                                Type::make(Product::class)
                                    ->label(__('admin.prices.priceable_types.product'))
                                    ->titleAttribute('name')
                                    ->searchColumns(['name', 'sku'])
                                    ->modifyOptionLabelUsing(static fn (Product $record): string => sprintf('%s • %s', $record->sku ?? __('admin.prices.sku_missing'), $record->name ?? '')),
                                Type::make(ProductVariant::class)
                                    ->label(__('admin.prices.priceable_types.variant'))
                                    ->titleAttribute('name')
                                    ->searchColumns(['name', 'sku'])
                                    ->modifyOptionLabelUsing(static fn (ProductVariant $record): string => sprintf('%s • %s', $record->sku ?? __('admin.prices.sku_missing'), $record->name ?? '')),
                            ])
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),
                        Select::make('currency_id')
                            ->label(__('admin.prices.currency'))
                            ->relationship('currency', 'code')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->rules(['exists:currencies,id'])
                            ->helperText(__('admin.prices.currency_helper')),
                    ])
                    ->columns(2),
                Section::make(__('admin.prices.pricing_details'))
                    ->description(__('admin.prices.pricing_details_description'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                SearchableInput::make('product_id')
                                    ->label(__('admin.prices.product'))
                                    ->placeholder('SKU / EAN / name')
                                    ->required()
                                    ->searchUsing(fn (string $search): array => ProductSearch::complex($search))
                                    ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null ? (int) $state : null)
                                    ->afterStateHydrated(function (SearchableInput $component, ?int $state): void {
                                        // Hydrate via helper to align with docs/forms/SEARCHABLE_INPUT_METADATA.md.
                                        SearchableInputHelper::hydrate(
                                            $component,
                                            $state,
                                            static function (int $value): ?array {
                                                $product = Product::query()
                                                    ->select(['id', 'sku', 'name'])
                                                    ->find($value);

                                                if (! $product instanceof Product) {
                                                    return null;
                                                }

                                                return [
                                                    'value' => $product->getKey(),
                                                    'label' => ProductSearch::label($product),
                                                ];
                                            },
                                        );
                                    })
                                    ->afterStateUpdated(function (SearchableInput $component, ?string $state, Set $set): void {
                                        if ($state === null || $state === '') {
                                            SearchableInputHelper::clear($component, $set, ['product_id' => null]);

                                            return;
                                        }

                                        $product = Product::query()
                                            ->select(['id'])
                                            ->find((int) $state);

                                        if (! $product instanceof Product) {
                                            return;
                                        }

                                        $set('product_id', $product->getKey());
                                    }),
                                TextInput::make('amount')
                                    ->label(__('admin.prices.amount'))
                                    ->numeric()
                                    ->minValue(0.0)
                                    ->step(0.0001)
                                    ->required()
                                    ->rules(['numeric', 'gte:0'])
                                    ->prefix('€')
                                    ->helperText(__('admin.prices.amount_helper')),
                                TextInput::make('compare_amount')
                                    ->label(__('admin.prices.compare_amount'))
                                    ->numeric()
                                    ->minValue(0.0)
                                    ->step(0.0001)
                                    ->rules(['nullable', 'numeric', 'gte:0'])
                                    ->helperText(__('admin.prices.compare_amount_helper')),
                                TextInput::make('cost_amount')
                                    ->label(__('admin.prices.cost_amount'))
                                    ->numeric()
                                    ->minValue(0.0)
                                    ->step(0.0001)
                                    ->rules(['nullable', 'numeric', 'gte:0'])
                                    ->helperText(__('admin.prices.cost_amount_helper')),
                            ]),
                        Select::make('type')
                            ->label(__('admin.prices.price_type'))
                            ->options([
                                'regular' => __('admin.prices.price_types.regular'),
                                'sale' => __('admin.prices.price_types.sale'),
                                'wholesale' => __('admin.prices.price_types.wholesale'),
                            ])
                            ->required()
                            ->rules(['in:regular,sale,wholesale'])
                            ->default('regular')
                            ->helperText(__('admin.prices.price_type_helper')),
                    ]),
                Section::make(__('admin.prices.lifecycle'))
                    ->description(__('admin.prices.lifecycle_description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->label(__('admin.prices.starts_at'))
                                    ->seconds(false)
                                    ->nullable()
                                    ->helperText(__('admin.prices.starts_at_helper')),
                                DateTimePicker::make('ends_at')
                                    ->label(__('admin.prices.ends_at'))
                                    ->seconds(false)
                                    ->nullable()
                                    ->helperText(__('admin.prices.ends_at_helper')),
                            ]),
                        Toggle::make('is_enabled')
                            ->label(__('admin.prices.is_enabled'))
                            ->default(true)
                            ->helperText(__('admin.prices.is_enabled_helper')),
                    ]),
                Section::make(__('admin.prices.metadata'))
                    ->description(__('admin.prices.metadata_description'))
                    ->schema([
                        KeyValue::make('metadata')
                            ->label(__('admin.prices.metadata_pairs'))
                            ->keyLabel(__('admin.prices.metadata_key'))
                            ->valueLabel(__('admin.prices.metadata_value'))
                            ->nullable()
                            ->columnSpanFull()
                            ->helperText(__('admin.prices.metadata_helper')),
                    ]),
                Section::make(__('admin.prices.sections.pricing'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('amount')
                                    ->label(__('admin.prices.fields.amount'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required(),
                                TextInput::make('compare_amount')
                                    ->label(__('admin.prices.fields.compare_amount'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01),
                            ]),
                        TextInput::make('cost_amount')
                            ->label(__('admin.prices.fields.cost_amount'))
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01),
                    ]),
                Section::make(__('admin.prices.sections.validity'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->label(__('admin.prices.fields.starts_at'))
                                    ->seconds(false),
                                DateTimePicker::make('ends_at')
                                    ->label(__('admin.prices.fields.ends_at'))
                                    ->seconds(false)
                                    ->after('starts_at'),
                            ]),
                    ]),
                Section::make(__('admin.prices.sections.metadata'))
                    ->schema([
                        KeyValue::make('metadata')
                            ->label(__('admin.prices.fields.metadata'))
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('priceable_display')
                    ->label(__('admin.prices.priceable'))
                    ->getStateUsing(static fn (Price $record): string => self::formatPriceableLabel($record))
                    ->description(static fn (Price $record): string => self::formatPriceableType($record))
                    ->searchable(query: static function (Builder $query, string $search): Builder {
                        // Allow searching across product and variant attributes when filtering prices.
                        return $query->whereHasMorph(
                            'priceable',
                            [Product::class, ProductVariant::class],
                            static function (Builder $morphQuery) use ($search): void {
                                $morphQuery->where(function (Builder $builder) use ($search): void {
                                    $builder
                                        ->where('name', 'like', "%{$search}%")
                                        ->orWhere('sku', 'like', "%{$search}%");
                                });
                            },
                        );
                    })
                    ->sortable(),
                TextColumn::make('currency.code')
                    ->label(__('admin.prices.fields.currency'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('admin.prices.amount'))
                    ->formatStateUsing(static fn ($state, Price $record): string => Number::currency((float) $state, $record->currency?->code ?? 'EUR'))
                    ->sortable(),
                TextColumn::make('currency.code')
                    ->label(__('admin.prices.currency'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('admin.prices.price_type'))
                    ->badge()
                    ->formatStateUsing(static fn (?string $state): string => __('admin.prices.price_types.'.($state ?? 'regular')))
                    ->color(static fn (?string $state): string => match ($state) {
                        'sale' => 'success',
                        'wholesale' => 'warning',
                        default => 'primary',
                    })
                    ->sortable(),
                IconColumn::make('is_enabled')
                    ->label(__('admin.prices.is_enabled'))
                    ->boolean(),
                TextColumn::make('starts_at')
                    ->label(__('admin.prices.starts_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label(__('admin.prices.ends_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.prices.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                IconColumn::make('is_enabled')
                    ->label(__('admin.prices.fields.is_enabled'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label(__('admin.prices.fields.starts_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_at')
                    ->label(__('admin.prices.fields.ends_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin.prices.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('priceable_type')
                    ->label(__('admin.prices.filters.priceable_type'))
                    ->options([
                        Product::class => __('admin.prices.priceable_types.product'),
                        ProductVariant::class => __('admin.prices.priceable_types.variant'),
                    ]),
                SelectFilter::make('currency_id')
                    ->label(__('admin.prices.filters.currency'))
                    ->relationship('currency', 'code')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_enabled')
                    ->label(__('admin.prices.filters.enabled_state')),
                Filter::make('lifecycle')
                    ->label(__('admin.prices.filters.lifecycle'))
                    ->form([
                        Select::make('stage')
                            ->label(__('admin.prices.filters.lifecycle_stage'))
                            ->options([
                                'active' => __('admin.prices.filters.lifecycle_options.active'),
                                'upcoming' => __('admin.prices.filters.lifecycle_options.upcoming'),
                                'expired' => __('admin.prices.filters.lifecycle_options.expired'),
                            ]),
                    ])
                    ->query(static function (Builder $query, array $data): Builder {
                        // Provide lifecycle snapshots without duplicating scope logic in the model.
                        return match ($data['stage'] ?? null) {
                            'active' => $query->where('is_enabled', true)
                                ->where(function (Builder $builder): void {
                                    $builder
                                        ->whereNull('starts_at')
                                        ->orWhere('starts_at', '<=', now());
                                })
                                ->where(function (Builder $builder): void {
                                    $builder
                                        ->whereNull('ends_at')
                                        ->orWhere('ends_at', '>=', now());
                                }),
                            'upcoming' => $query->where('is_enabled', true)
                                ->whereNotNull('starts_at')
                                ->where('starts_at', '>', now()),
                            'expired' => $query->where(function (Builder $builder): void {
                                $builder
                                    ->where('is_enabled', false)
                                    ->orWhere(function (Builder $inner): void {
                                        $inner
                                            ->whereNotNull('ends_at')
                                            ->where('ends_at', '<', now());
                                    });
                            }),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(static fn (Price $record): string => self::getUrl('view', ['record' => $record]))
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->with(['priceable', 'currency']))
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPrices::route('/'),
            'create' => Pages\CreatePrice::route('/create'),
            'view'   => Pages\ViewPrice::route('/{record}'),
            'edit'   => Pages\EditPrice::route('/{record}/edit'),
        ];
    }
}