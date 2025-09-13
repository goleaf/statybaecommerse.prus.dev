<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PriceResource\Pages;
use App\Models\Price;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tab;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class PriceResource extends Resource
{
    protected static ?string $model = Price::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-currency-euro';

    /**
     * @var UnitEnum|string|null
     */
    protected static $navigationGroup = 'Pricing';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'amount';

    public static function getNavigationLabel(): string
    {
        return __('admin.prices.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.prices.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.prices.plural_model_label');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Price Information')
                    ->tabs([
                        Tab::make(__('admin.prices.basic_information'))
                            ->icon('heroicon-o-currency-euro')
                            ->schema([
                                Section::make(__('admin.prices.price_details'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('priceable_type')
                                                    ->label(__('admin.prices.fields.priceable_type'))
                                                    ->options([
                                                        'App\Models\Product' => __('admin.prices.types.product'),
                                                        'App\Models\ProductVariant' => __('admin.prices.types.variant'),
                                                        'App\Models\Service' => __('admin.prices.types.service'),
                                                    ])
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(fn (callable $set) => $set('priceable_id', null)),
                                                Select::make('priceable_id')
                                                    ->label(__('admin.prices.fields.priceable_item'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->options(function (callable $get) {
                                                        $type = $get('priceable_type');
                                                        if (! $type) {
                                                            return [];
                                                        }

                                                        return match ($type) {
                                                            'App\Models\Product' => \App\Models\Product::pluck('name', 'id'),
                                                            'App\Models\ProductVariant' => \App\Models\ProductVariant::pluck('name', 'id'),
                                                            'App\Models\Service' => \App\Models\Service::pluck('name', 'id'),
                                                            default => [],
                                                        };
                                                    })
                                                    ->required(),
                                                Select::make('currency_id')
                                                    ->label(__('admin.prices.fields.currency'))
                                                    ->relationship('currency', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload(),
                                                Select::make('type')
                                                    ->label(__('admin.prices.fields.type'))
                                                    ->options([
                                                        'retail' => __('admin.prices.types.retail'),
                                                        'wholesale' => __('admin.prices.types.wholesale'),
                                                        'special' => __('admin.prices.types.special'),
                                                        'sale' => __('admin.prices.types.sale'),
                                                    ])
                                                    ->required()
                                                    ->default('retail'),
                                            ]),
                                    ]),
                                Section::make(__('admin.prices.amounts'))
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('amount')
                                                    ->label(__('admin.prices.fields.amount'))
                                                    ->required()
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01)
                                                    ->minValue(0),
                                                TextInput::make('compare_amount')
                                                    ->label(__('admin.prices.fields.compare_amount'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01)
                                                    ->minValue(0)
                                                    ->helperText(__('admin.prices.fields.compare_amount_help')),
                                                TextInput::make('cost_amount')
                                                    ->label(__('admin.prices.fields.cost_amount'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01)
                                                    ->minValue(0)
                                                    ->helperText(__('admin.prices.fields.cost_amount_help')),
                                            ]),
                                    ]),
                                Section::make(__('admin.prices.timing'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                DateTimePicker::make('starts_at')
                                                    ->label(__('admin.prices.fields.starts_at'))
                                                    ->displayFormat('d/m/Y H:i')
                                                    ->helperText(__('admin.prices.fields.starts_at_help')),
                                                DateTimePicker::make('ends_at')
                                                    ->label(__('admin.prices.fields.ends_at'))
                                                    ->displayFormat('d/m/Y H:i')
                                                    ->helperText(__('admin.prices.fields.ends_at_help')),
                                            ]),
                                    ]),
                                Section::make(__('admin.prices.status'))
                                    ->schema([
                                        Toggle::make('is_enabled')
                                            ->label(__('admin.prices.fields.is_enabled'))
                                            ->default(true)
                                            ->helperText(__('admin.prices.fields.is_enabled_help')),
                                    ]),
                            ]),
                        Tab::make(__('admin.prices.translations'))
                            ->icon('heroicon-o-language')
                            ->schema([
                                Section::make(__('admin.prices.lithuanian_translation'))
                                    ->schema([
                                        Grid::make(1)
                                            ->schema([
                                                TextInput::make('translations.lt.name')
                                                    ->label(__('admin.prices.fields.name_lt'))
                                                    ->maxLength(255),
                                                Textarea::make('translations.lt.description')
                                                    ->label(__('admin.prices.fields.description_lt'))
                                                    ->maxLength(1000)
                                                    ->rows(3),
                                                TextInput::make('translations.lt.notes')
                                                    ->label(__('admin.prices.fields.notes_lt'))
                                                    ->maxLength(500),
                                            ]),
                                    ]),
                                Section::make(__('admin.prices.english_translation'))
                                    ->schema([
                                        Grid::make(1)
                                            ->schema([
                                                TextInput::make('translations.en.name')
                                                    ->label(__('admin.prices.fields.name_en'))
                                                    ->maxLength(255),
                                                Textarea::make('translations.en.description')
                                                    ->label(__('admin.prices.fields.description_en'))
                                                    ->maxLength(1000)
                                                    ->rows(3),
                                                TextInput::make('translations.en.notes')
                                                    ->label(__('admin.prices.fields.notes_en'))
                                                    ->maxLength(500),
                                            ]),
                                    ]),
                            ]),
                        Tab::make(__('admin.prices.metadata'))
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make(__('admin.prices.additional_data'))
                                    ->schema([
                                        KeyValue::make('metadata')
                                            ->label(__('admin.prices.fields.metadata'))
                                            ->keyLabel(__('admin.prices.metadata_key'))
                                            ->valueLabel(__('admin.prices.metadata_value'))
                                            ->addActionLabel(__('admin.prices.add_metadata')),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('priceable_type')
                    ->label(__('admin.prices.fields.priceable_type'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'App\Models\Product' => __('admin.prices.types.product'),
                        'App\Models\ProductVariant' => __('admin.prices.types.variant'),
                        'App\Models\Service' => __('admin.prices.types.service'),
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'App\Models\Product' => 'success',
                        'App\Models\ProductVariant' => 'info',
                        'App\Models\Service' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('priceable.name')
                    ->label(__('admin.prices.fields.item_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    }),
                TextColumn::make('currency.code')
                    ->label(__('admin.prices.fields.currency'))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('amount')
                    ->label(__('admin.prices.fields.amount'))
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->color('success')
                    ->weight('bold'),
                TextColumn::make('compare_amount')
                    ->label(__('admin.prices.fields.compare_amount'))
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->placeholder(__('admin.prices.no_compare_price')),
                TextColumn::make('cost_amount')
                    ->label(__('admin.prices.fields.cost_amount'))
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->placeholder(__('admin.prices.no_cost_price'))
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('type')
                    ->label(__('admin.prices.fields.type'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'retail' => __('admin.prices.types.retail'),
                        'wholesale' => __('admin.prices.types.wholesale'),
                        'special' => __('admin.prices.types.special'),
                        'sale' => __('admin.prices.types.sale'),
                        default => $state,
                    })
                    ->colors([
                        'success' => 'retail',
                        'info' => 'wholesale',
                        'warning' => 'special',
                        'danger' => 'sale',
                    ]),
                IconColumn::make('is_enabled')
                    ->label(__('admin.prices.fields.is_enabled'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('starts_at')
                    ->label(__('admin.prices.fields.starts_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder(__('admin.prices.no_start_date'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_at')
                    ->label(__('admin.prices.fields.ends_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder(__('admin.prices.no_end_date'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin.prices.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn ($record) => $record->created_at?->format('d/m/Y H:i:s')),
            ])
            ->filters([
                SelectFilter::make('priceable_type')
                    ->label(__('admin.prices.filters.priceable_type'))
                    ->options([
                        'App\Models\Product' => __('admin.prices.types.product'),
                        'App\Models\ProductVariant' => __('admin.prices.types.variant'),
                        'App\Models\Service' => __('admin.prices.types.service'),
                    ]),
                SelectFilter::make('currency_id')
                    ->label(__('admin.prices.filters.currency'))
                    ->relationship('currency', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label(__('admin.prices.filters.type'))
                    ->options([
                        'retail' => __('admin.prices.types.retail'),
                        'wholesale' => __('admin.prices.types.wholesale'),
                        'special' => __('admin.prices.types.special'),
                        'sale' => __('admin.prices.types.sale'),
                    ]),
                TernaryFilter::make('is_enabled')
                    ->label(__('admin.prices.filters.is_enabled'))
                    ->placeholder(__('admin.prices.filters.all_prices'))
                    ->trueLabel(__('admin.prices.filters.enabled'))
                    ->falseLabel(__('admin.prices.filters.disabled')),
                DateFilter::make('starts_at')
                    ->label(__('admin.prices.filters.starts_at'))
                    ->displayFormat('d/m/Y'),
                DateFilter::make('ends_at')
                    ->label(__('admin.prices.filters.ends_at'))
                    ->displayFormat('d/m/Y'),
                DateFilter::make('created_at')
                    ->label(__('admin.prices.filters.created_at'))
                    ->displayFormat('d/m/Y'),
            ])
            ->actions([
                ViewAction::make()
                    ->label(__('admin.actions.view')),
                EditAction::make()
                    ->label(__('admin.actions.edit')),
                DeleteAction::make()
                    ->label(__('admin.actions.delete')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('admin.actions.delete_selected')),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListPrices::route('/'),
            'create' => Pages\CreatePrice::route('/create'),
            'view' => Pages\ViewPrice::route('/{record}'),
            'edit' => Pages\EditPrice::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['priceable', 'currency', 'translations']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['amount', 'type'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('admin.prices.fields.currency') => $record->currency->code,
            __('admin.prices.fields.type') => $record->type,
            __('admin.prices.fields.amount') => '€'.number_format($record->amount, 2),
        ];
    }
}
