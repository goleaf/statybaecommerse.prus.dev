<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RecommendationAnalyticsResource\Pages;
use App\Models\Product;
use App\Models\RecommendationAnalytics;
use App\Models\RecommendationBlock;
use App\Models\RecommendationConfig;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * RecommendationAnalyticsResource
 *
 * Filament v4 resource for RecommendationAnalytics management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class RecommendationAnalyticsResource extends Resource
{
    protected static ?string $model = RecommendationAnalytics::class;

    public static function getNavigationIcon(): \BackedEnum|\Illuminate\Contracts\Support\Htmlable|string|null
    {
        return 'heroicon-o-chart-bar';
    }

    protected static ?int $navigationSort = 8;

    protected static ?string $recordTitleAttribute = 'action';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Analytics';
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.recommendation_analytics.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.recommendation_analytics.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.recommendation_analytics.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make(__('admin.recommendation_analytics.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('block_id')
                                    ->label(__('admin.recommendation_analytics.block'))
                                    ->options(RecommendationBlock::pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                                Select::make('config_id')
                                    ->label(__('admin.recommendation_analytics.config'))
                                    ->options(RecommendationConfig::pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                                Select::make('user_id')
                                    ->label(__('admin.recommendation_analytics.user'))
                                    ->options(User::pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                                Select::make('product_id')
                                    ->label(__('admin.recommendation_analytics.product'))
                                    ->options(Product::pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                                Select::make('action')
                                    ->label(__('admin.recommendation_analytics.action'))
                                    ->options([
                                        'view' => __('admin.recommendation_analytics.actions.view'),
                                        'click' => __('admin.recommendation_analytics.actions.click'),
                                        'add_to_cart' => __('admin.recommendation_analytics.actions.add_to_cart'),
                                        'purchase' => __('admin.recommendation_analytics.actions.purchase'),
                                    ])
                                    ->required()
                                    ->default('view'),
                                DatePicker::make('date')
                                    ->label(__('admin.recommendation_analytics.date'))
                                    ->required()
                                    ->default(now()),
                            ]),
                    ]),
                SchemaSection::make(__('admin.recommendation_analytics.metrics'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('ctr')
                                    ->label(__('admin.recommendation_analytics.ctr'))
                                    ->numeric()
                                    ->step(0.0001)
                                    ->minValue(0)
                                    ->maxValue(1)
                                    ->suffix('%'),
                                TextInput::make('conversion_rate')
                                    ->label(__('admin.recommendation_analytics.conversion_rate'))
                                    ->numeric()
                                    ->step(0.0001)
                                    ->minValue(0)
                                    ->maxValue(1)
                                    ->suffix('%'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('block.name')
                    ->label(__('admin.recommendation_analytics.block'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('config.name')
                    ->label(__('admin.recommendation_analytics.config'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('admin.recommendation_analytics.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label(__('admin.recommendation_analytics.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    }),
                TextColumn::make('action')
                    ->label(__('admin.recommendation_analytics.action'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'view' => 'info',
                        'click' => 'success',
                        'add_to_cart' => 'warning',
                        'purchase' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('ctr')
                    ->label(__('admin.recommendation_analytics.ctr'))
                    ->numeric(decimalPlaces: 4)
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('conversion_rate')
                    ->label(__('admin.recommendation_analytics.conversion_rate'))
                    ->numeric(decimalPlaces: 4)
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('date')
                    ->label(__('admin.recommendation_analytics.date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('block_id')
                    ->label(__('admin.recommendation_analytics.block'))
                    ->options(RecommendationBlock::pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('config_id')
                    ->label(__('admin.recommendation_analytics.config'))
                    ->options(RecommendationConfig::pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('user_id')
                    ->label(__('admin.recommendation_analytics.user'))
                    ->options(User::pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('product_id')
                    ->label(__('admin.recommendation_analytics.product'))
                    ->options(Product::pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('action')
                    ->label(__('admin.recommendation_analytics.action'))
                    ->options([
                        'view' => __('admin.recommendation_analytics.actions.view'),
                        'click' => __('admin.recommendation_analytics.actions.click'),
                        'add_to_cart' => __('admin.recommendation_analytics.actions.add_to_cart'),
                        'purchase' => __('admin.recommendation_analytics.actions.purchase'),
                    ]),
                DateFilter::make('date')
                    ->label(__('admin.recommendation_analytics.date')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
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
            'index' => Pages\ListRecommendationAnalytics::route('/'),
            'create' => Pages\CreateRecommendationAnalytics::route('/create'),
            'view' => Pages\ViewRecommendationAnalytics::route('/{record}'),
            'edit' => Pages\EditRecommendationAnalytics::route('/{record}/edit'),
        ];
    }
}
