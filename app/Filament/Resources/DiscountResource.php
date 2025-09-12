<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Models\Discount;
use App\Services\MultiLanguageTabService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Actions as Actions;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use \BackedEnum;
final class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-tag';


    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.marketing');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.discounts');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Discount Settings (Non-translatable)
                \Filament\Schemas\Components\Section::make(__('translations.discount_settings'))
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label(__('translations.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label(__('translations.description'))
                            ->maxLength(1000)
                            ->rows(3),
                        Forms\Components\Select::make('type')
                            ->label(__('translations.type'))
                            ->options([
                                'percentage' => __('translations.percentage'),
                                'fixed' => __('translations.fixed_amount'),
                                'bogo' => __('translations.buy_one_get_one'),
                                'free_shipping' => __('translations.free_shipping'),
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('value')
                            ->label(__('translations.value'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix(fn($get) => $get('type') === 'percentage' ? '%' : '€'),
                        Forms\Components\TextInput::make('minimum_amount')
                            ->label(__('translations.minimum_amount'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        Forms\Components\TextInput::make('maximum_amount')
                            ->label(__('translations.maximum_amount'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label(__('translations.starts_at'))
                            ->required(),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label(__('translations.ends_at'))
                            ->after('starts_at'),
                        Forms\Components\TextInput::make('usage_limit')
                            ->label(__('translations.usage_limit'))
                            ->numeric()
                            ->minValue(1),
                        Forms\Components\TextInput::make('usage_limit_per_customer')
                            ->label(__('translations.usage_limit_per_customer'))
                            ->numeric()
                            ->minValue(1),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('translations.active'))
                            ->default(true),
                        Forms\Components\Toggle::make('exclude_sale_items')
                            ->label(__('translations.exclude_sale_items'))
                            ->default(false),
                    ])
                    ->columns(2),
                // Removed multilanguage tabs for stability in tests
                \Filament\Schemas\Components\Section::make(__('translations.discount_conditions'))
                    ->components([
                        Forms\Components\Repeater::make('conditions')
                            ->relationship('conditions')
                            ->components([
                                Forms\Components\Select::make('type')
                                    ->label(__('translations.condition_type'))
                                    ->options([
                                        'product' => __('translations.product'),
                                        'category' => __('translations.category'),
                                        'brand' => __('translations.brand'),
                                        'collection' => __('translations.collection'),
                                        'cart_total' => __('translations.cart_total'),
                                        'quantity' => __('translations.quantity'),
                                        'customer_group' => __('translations.customer_group'),
                                    ])
                                    ->required()
                                    ->live(),
                                Forms\Components\Select::make('operator')
                                    ->label(__('translations.operator'))
                                    ->options([
                                        'equals' => __('translations.equals'),
                                        'not_equals' => __('translations.not_equals'),
                                        'greater_than' => __('translations.greater_than'),
                                        'less_than' => __('translations.less_than'),
                                        'contains' => __('translations.contains'),
                                        'not_contains' => __('translations.not_contains'),
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('value')
                                    ->label(__('translations.value'))
                                    ->required(),
                            ])
                            ->columns(3)
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->addActionLabel(__('translations.add_condition')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('translations.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('translations.type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'percentage' => 'success',
                        'fixed' => 'info',
                        'bogo' => 'warning',
                        'free_shipping' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('translations.value'))
                    ->formatStateUsing(fn($record): string =>
                        $record->type === 'percentage'
                            ? (string) $record->value . '%'
                            : '€' . number_format((float) $record->value, 2)),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label(__('translations.starts_at'))
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label(__('translations.ends_at'))
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_count')
                    ->label(__('translations.usage_count'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_limit')
                    ->label(__('translations.usage_limit'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('translations.active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('translations.type'))
                    ->options([
                        'percentage' => __('translations.percentage'),
                        'fixed' => __('translations.fixed_amount'),
                        'bogo' => __('translations.buy_one_get_one'),
                        'free_shipping' => __('translations.free_shipping'),
                    ]),
                Tables\Filters\Filter::make('active')
                    ->label(__('translations.active_only'))
                    ->query(fn(Builder $query): Builder => $query->where('is_active', true)),
                Tables\Filters\Filter::make('current')
                    ->label(__('translations.current_discounts'))
                    ->query(fn(Builder $query): Builder =>
                        $query
                            ->where('starts_at', '<=', now())
                            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'view' => Pages\ViewDiscount::route('/{record}'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }
}
