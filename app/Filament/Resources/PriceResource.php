<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Filament\Resources\PriceResource\Pages;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type as MorphToSelectType;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use UnitEnum;

final class PriceResource extends Resource
{
    use HasNav;

    protected static ?string $model = Price::class;

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static UnitEnum|string|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 12;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('admin.prices.sections.basic_information'))
                    ->schema([
                        MorphToSelect::make('priceable')
                            ->label(__('admin.prices.fields.priceable'))
                            ->types([
                                MorphToSelectType::make(Product::class)
                                    ->title(__('admin.prices.priceable_types.product'))
                                    ->getRecordTitleAttribute('name'),
                                MorphToSelectType::make(ProductVariant::class)
                                    ->title(__('admin.prices.priceable_types.variant'))
                                    ->getRecordTitleAttribute('name'),
                            ])
                            ->searchable()
                            ->required(),
                        Grid::make(2)
                            ->schema([
                                Select::make('currency_id')
                                    ->label(__('admin.prices.fields.currency'))
                                    ->relationship('currency', 'code')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('type')
                                    ->label(__('admin.prices.fields.type'))
                                    ->options(self::getPriceTypeOptions())
                                    ->default('retail')
                                    ->required(),
                            ]),
                        Toggle::make('is_enabled')
                            ->label(__('admin.prices.fields.is_enabled'))
                            ->default(true),
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
        return $table
            ->columns([
                TextColumn::make('priceable_type')
                    ->label(__('admin.prices.fields.priceable_type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::getPriceableTypeLabels()[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('priceable.name')
                    ->label(__('admin.prices.fields.priceable_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('currency.code')
                    ->label(__('admin.prices.fields.currency'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('admin.prices.fields.amount'))
                    ->sortable()
                    ->formatStateUsing(fn ($state, Price $record): string => Number::currency((float) $state, $record->currency?->code ?? 'EUR', locale: app()->getLocale())),
                TextColumn::make('compare_amount')
                    ->label(__('admin.prices.fields.compare_amount'))
                    ->sortable()
                    ->formatStateUsing(fn ($state, Price $record): ?string => blank($state)
                        ? null
                        : Number::currency((float) $state, $record->currency?->code ?? 'EUR', locale: app()->getLocale()))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cost_amount')
                    ->label(__('admin.prices.fields.cost_amount'))
                    ->sortable()
                    ->formatStateUsing(fn ($state, Price $record): ?string => blank($state)
                        ? null
                        : Number::currency((float) $state, $record->currency?->code ?? 'EUR', locale: app()->getLocale()))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('type')
                    ->label(__('admin.prices.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::getPriceTypeOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'sale' => 'success',
                        'wholesale' => 'warning',
                        'special' => 'info',
                        default => 'gray',
                    })
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
                    ->options(self::getPriceableTypeLabels()),
                SelectFilter::make('currency_id')
                    ->label(__('admin.prices.filters.currency'))
                    ->relationship('currency', 'code')
                    ->searchable(),
                SelectFilter::make('type')
                    ->label(__('admin.prices.filters.type'))
                    ->options(self::getPriceTypeOptions()),
                TernaryFilter::make('is_enabled')
                    ->label(__('admin.prices.filters.is_enabled')),
                Filter::make('active')
                    ->label(__('admin.prices.filters.active'))
                    ->query(fn (Builder $query): Builder => $query
                        ->where('is_enabled', true)
                        ->where(function (Builder $builder): void {
                            $builder
                                ->whereNull('starts_at')
                                ->orWhere('starts_at', '<=', now());
                        })
                        ->where(function (Builder $builder): void {
                            $builder
                                ->whereNull('ends_at')
                                ->orWhere('ends_at', '>=', now());
                        })),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPrices::route('/'),
            'create' => Pages\CreatePrice::route('/create'),
            'view' => Pages\ViewPrice::route('/{record}'),
            'edit' => Pages\EditPrice::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function getPriceableTypeLabels(): array
    {
        return [
            Product::class => __('admin.prices.priceable_types.product'),
            ProductVariant::class => __('admin.prices.priceable_types.variant'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function getPriceTypeOptions(): array
    {
        return [
            'retail' => __('admin.prices.types.retail'),
            'wholesale' => __('admin.prices.types.wholesale'),
            'special' => __('admin.prices.types.special'),
            'sale' => __('admin.prices.types.sale'),
        ];
    }
}
