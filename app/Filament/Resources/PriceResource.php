<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PriceResource\Pages;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductVariant;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class PriceResource extends BaseResource
{
    protected static ?string $model = Price::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-euro';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $activeNavigationItem = ProductResource::class;

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 15;

    public static function getNavigationLabel(): string
    {
        return __('admin.prices.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.prices.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.prices.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.prices.basic_information'))
                ->description(__('admin.prices.basic_information_description'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            MorphToSelect::make('priceable')
                                ->label(__('messages.product'))
                                ->types([
                                    MorphToSelect\Type::make(Product::class)
                                        ->titleAttribute('name')
                                        ->label(__('messages.product'))
                                        ->modifyOptionsQueryUsing(static fn (Builder $query): Builder => $query->withoutGlobalScopes())
                                        ->getOptionLabelFromRecordUsing(static fn (Product $record): string => self::resolveProductLabel($record)),
                                    MorphToSelect\Type::make(ProductVariant::class)
                                        ->titleAttribute('name')
                                        ->label(__('messages.variant'))
                                        ->modifyOptionsQueryUsing(static fn (Builder $query): Builder => $query
                                            ->withoutGlobalScopes()
                                            ->whereHas('product'))
                                        ->getOptionLabelFromRecordUsing(static fn (ProductVariant $record): string => self::resolveVariantLabel($record)),
                                ])
                                ->required()
                                ->searchable()
                                ->optionsLimit(50),
                            Hidden::make('currency_id')
                                ->default(static fn (): ?int => self::resolveEuroCurrencyId()),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('amount')
                                ->label(__('messages.amount') !== 'messages.amount' ? __('messages.amount') : 'Amount')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->step(0.0001),
                            TextInput::make('cost_amount')
                                ->label(__('messages.cost_amount') !== 'messages.cost_amount' ? __('messages.cost_amount') : 'Cost Amount')
                                ->numeric()
                                ->minValue(0)
                                ->step(0.0001),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('type')
                                ->label(__('messages.type'))
                                ->options(array_combine(Price::ALLOWED_TYPES, Price::ALLOWED_TYPES))
                                ->default('retail')
                                ->required(),
                            Toggle::make('is_enabled')
                                ->label(__('messages.enabled'))
                                ->default(true),
                        ]),
                ])
                ->columnSpanFull(),
            Section::make(__('admin.prices.validity'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            DateTimePicker::make('starts_at')
                                ->label(__('admin.prices.valid_from')),
                            DateTimePicker::make('ends_at')
                                ->label(__('admin.prices.valid_until')),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('admin.labels.id'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('priceable_type')
                    ->label(__('messages.type'))
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->badge()
                    ->sortable(),
                TextColumn::make('priceable.name')
                    ->label(__('messages.name'))
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->priceable instanceof ProductVariant) {
                            return $record->priceable->display_name;
                        }

                        return $state;
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('currency.code')
                    ->label(__('messages.currency'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('messages.amount') !== 'messages.amount' ? __('messages.amount') : 'Amount')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('messages.type'))
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_enabled')
                    ->label(__('messages.enabled'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label(__('admin.prices.valid_from'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label(__('admin.prices.valid_until'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder(__('admin.prices.no_expiry')),
                TextColumn::make('created_at')
                    ->label(__('admin.prices.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label(__('messages.enabled')),
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->label(__('messages.type'))
                    ->options(fn () => Price::query()->whereNotNull('type')->distinct()->pluck('type', 'type')->toArray()),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private static function resolveProductLabel(Product $product): string
    {
        $name = trim((string) ($product->getTranslatedName() ?? ''));

        if ($name === '') {
            $rawName = $product->getAttribute('name');
            $name = is_scalar($rawName) ? trim((string) $rawName) : '';
        }

        $sku = trim((string) ($product->getAttribute('sku') ?? ''));

        if ($name === '' && $sku === '') {
            return '#' . $product->getKey();
        }

        if ($name === '') {
            return $sku;
        }

        return $sku !== '' ? "{$name} ({$sku})" : $name;
    }

    private static function resolveVariantLabel(ProductVariant $variant): string
    {
        $productName = '';
        $product = $variant->product;

        if ($product instanceof Product) {
            $productName = trim((string) ($product->getTranslatedName() ?? $product->getAttribute('name') ?? ''));
        }

        $variantName = trim((string) ($variant->getAttribute('name') ?? ''));
        $sku = trim((string) ($variant->getAttribute('sku') ?? ''));

        $parts = array_values(array_filter([
            $productName,
            $variantName,
            $sku !== '' ? "SKU: {$sku}" : '',
        ], static fn (string $part): bool => $part !== ''));

        if ($parts !== []) {
            return implode(' | ', $parts);
        }

        return '#' . $variant->getKey();
    }

    private static function resolveEuroCurrencyId(): ?int
    {
        $currencyId = \App\Models\Currency::query()
            ->where('code', 'EUR')
            ->value('id');

        if (! is_numeric($currencyId)) {
            $currencyId = \App\Models\Currency::query()
                ->where('is_default', true)
                ->value('id');
        }

        return is_numeric($currencyId) ? (int) $currencyId : null;
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\PriceResource\RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPrices::route('/'),
            'create' => Pages\CreatePrice::route('/create'),
            'edit'   => Pages\EditPrice::route('/{record}/edit'),
        ];
    }
}
