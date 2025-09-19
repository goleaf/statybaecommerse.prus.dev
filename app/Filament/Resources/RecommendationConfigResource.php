<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\RecommendationConfigResource\Pages;
use App\Models\Category;
use App\Models\Product;
use App\Models\RecommendationConfig;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * RecommendationConfigResource
 *
 * Filament v4 resource for RecommendationConfig management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class RecommendationConfigResource extends Resource
{
    protected static ?string $model = RecommendationConfig::class;

    /**
     * @var UnitEnum|string|null
     */
    protected static string|UnitEnum|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 12;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('recommendation_configs.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Products->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('recommendation_configs.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('recommendation_configs.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('recommendation_configs.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('recommendation_configs.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->label(__('recommendation_configs.code'))
                                ->required()
                                ->maxLength(50)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Textarea::make('description')
                        ->label(__('recommendation_configs.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('recommendation_configs.algorithm_settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('algorithm_type')
                                ->label(__('recommendation_configs.algorithm_type'))
                                ->options([
                                    'collaborative' => __('recommendation_configs.algorithm_types.collaborative'),
                                    'content_based' => __('recommendation_configs.algorithm_types.content_based'),
                                    'hybrid' => __('recommendation_configs.algorithm_types.hybrid'),
                                    'popularity' => __('recommendation_configs.algorithm_types.popularity'),
                                    'trending' => __('recommendation_configs.algorithm_types.trending'),
                                    'similarity' => __('recommendation_configs.algorithm_types.similarity'),
                                    'custom' => __('recommendation_configs.algorithm_types.custom'),
                                ])
                                ->required()
                                ->default('collaborative'),
                            TextInput::make('min_score')
                                ->label(__('recommendation_configs.min_score'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(1)
                                ->default(0.1)
                                ->helperText(__('recommendation_configs.min_score_help')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('max_results')
                                ->label(__('recommendation_configs.max_results'))
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(100)
                                ->default(10)
                                ->helperText(__('recommendation_configs.max_results_help')),
                            TextInput::make('decay_factor')
                                ->label(__('recommendation_configs.decay_factor'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(1)
                                ->default(0.9)
                                ->helperText(__('recommendation_configs.decay_factor_help')),
                        ]),
                ]),
            Section::make(__('recommendation_configs.filtering'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('products')
                                ->label(__('recommendation_configs.products'))
                                ->relationship('products', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    Textarea::make('description')
                                        ->maxLength(500),
                                ]),
                            Select::make('categories')
                                ->label(__('recommendation_configs.categories'))
                                ->relationship('categories', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    Textarea::make('description')
                                        ->maxLength(500),
                                ]),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('exclude_out_of_stock')
                                ->label(__('recommendation_configs.exclude_out_of_stock'))
                                ->default(true),
                            Toggle::make('exclude_inactive')
                                ->label(__('recommendation_configs.exclude_inactive'))
                                ->default(true),
                        ]),
                ]),
            Section::make(__('recommendation_configs.weighting'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('price_weight')
                                ->label(__('recommendation_configs.price_weight'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(1)
                                ->default(0.2)
                                ->helperText(__('recommendation_configs.price_weight_help')),
                            TextInput::make('rating_weight')
                                ->label(__('recommendation_configs.rating_weight'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(1)
                                ->default(0.3)
                                ->helperText(__('recommendation_configs.rating_weight_help')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('popularity_weight')
                                ->label(__('recommendation_configs.popularity_weight'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(1)
                                ->default(0.2)
                                ->helperText(__('recommendation_configs.popularity_weight_help')),
                            TextInput::make('recency_weight')
                                ->label(__('recommendation_configs.recency_weight'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(1)
                                ->default(0.1)
                                ->helperText(__('recommendation_configs.recency_weight_help')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('category_weight')
                                ->label(__('recommendation_configs.category_weight'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(1)
                                ->default(0.2)
                                ->helperText(__('recommendation_configs.category_weight_help')),
                            TextInput::make('custom_weight')
                                ->label(__('recommendation_configs.custom_weight'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(1)
                                ->default(0)
                                ->helperText(__('recommendation_configs.custom_weight_help')),
                        ]),
                ]),
            Section::make(__('recommendation_configs.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('recommendation_configs.is_active'))
                                ->default(true),
                            Toggle::make('is_default')
                                ->label(__('recommendation_configs.is_default')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('cache_duration')
                                ->label(__('recommendation_configs.cache_duration'))
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(1440)
                                ->default(60)
                                ->suffix('minutes')
                                ->helperText(__('recommendation_configs.cache_duration_help')),
                            TextInput::make('sort_order')
                                ->label(__('recommendation_configs.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                        ]),
                    Textarea::make('notes')
                        ->label(__('recommendation_configs.notes'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('recommendation_configs.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('code')
                    ->label(__('recommendation_configs.code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('algorithm_type')
                    ->label(__('recommendation_configs.algorithm_type'))
                    ->formatStateUsing(fn(string $state): string => __("recommendation_configs.algorithm_types.{$state}"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'collaborative' => 'blue',
                        'content_based' => 'green',
                        'hybrid' => 'purple',
                        'popularity' => 'orange',
                        'trending' => 'pink',
                        'similarity' => 'indigo',
                        'custom' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('min_score')
                    ->label(__('recommendation_configs.min_score'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('max_results')
                    ->label(__('recommendation_configs.max_results'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('decay_factor')
                    ->label(__('recommendation_configs.decay_factor'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('products_count')
                    ->label(__('recommendation_configs.products_count'))
                    ->counts('products')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('categories_count')
                    ->label(__('recommendation_configs.categories_count'))
                    ->counts('categories')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('exclude_out_of_stock')
                    ->label(__('recommendation_configs.exclude_out_of_stock'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('exclude_inactive')
                    ->label(__('recommendation_configs.exclude_inactive'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price_weight')
                    ->label(__('recommendation_configs.price_weight'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rating_weight')
                    ->label(__('recommendation_configs.rating_weight'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('popularity_weight')
                    ->label(__('recommendation_configs.popularity_weight'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('recency_weight')
                    ->label(__('recommendation_configs.recency_weight'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category_weight')
                    ->label(__('recommendation_configs.category_weight'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('custom_weight')
                    ->label(__('recommendation_configs.custom_weight'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cache_duration')
                    ->label(__('recommendation_configs.cache_duration'))
                    ->numeric()
                    ->sortable()
                    ->suffix(' min')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('recommendation_configs.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label(__('recommendation_configs.is_default'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('recommendation_configs.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('recommendation_configs.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('recommendation_configs.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('algorithm_type')
                    ->label(__('recommendation_configs.algorithm_type'))
                    ->options([
                        'collaborative' => __('recommendation_configs.algorithm_types.collaborative'),
                        'content_based' => __('recommendation_configs.algorithm_types.content_based'),
                        'hybrid' => __('recommendation_configs.algorithm_types.hybrid'),
                        'popularity' => __('recommendation_configs.algorithm_types.popularity'),
                        'trending' => __('recommendation_configs.algorithm_types.trending'),
                        'similarity' => __('recommendation_configs.algorithm_types.similarity'),
                        'custom' => __('recommendation_configs.algorithm_types.custom'),
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('recommendation_configs.is_active'))
                    ->boolean()
                    ->trueLabel(__('recommendation_configs.active_only'))
                    ->falseLabel(__('recommendation_configs.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_default')
                    ->label(__('recommendation_configs.is_default'))
                    ->boolean()
                    ->trueLabel(__('recommendation_configs.default_only'))
                    ->falseLabel(__('recommendation_configs.non_default_only'))
                    ->native(false),
                TernaryFilter::make('exclude_out_of_stock')
                    ->label(__('recommendation_configs.exclude_out_of_stock'))
                    ->boolean()
                    ->trueLabel(__('recommendation_configs.exclude_out_of_stock_only'))
                    ->falseLabel(__('recommendation_configs.include_out_of_stock_only'))
                    ->native(false),
                TernaryFilter::make('exclude_inactive')
                    ->label(__('recommendation_configs.exclude_inactive'))
                    ->boolean()
                    ->trueLabel(__('recommendation_configs.exclude_inactive_only'))
                    ->falseLabel(__('recommendation_configs.include_inactive_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn(RecommendationConfig $record): string => $record->is_active ? __('recommendation_configs.deactivate') : __('recommendation_configs.activate'))
                    ->icon(fn(RecommendationConfig $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(RecommendationConfig $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (RecommendationConfig $record): void {
                        $record->update(['is_active' => !$record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('recommendation_configs.activated_successfully') : __('recommendation_configs.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('set_default')
                    ->label(__('recommendation_configs.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn(RecommendationConfig $record): bool => !$record->is_default)
                    ->action(function (RecommendationConfig $record): void {
                        // Remove default from other recommendation configs
                        RecommendationConfig::where('is_default', true)->update(['is_default' => false]);

                        // Set this recommendation config as default
                        $record->update(['is_default' => true]);

                        Notification::make()
                            ->title(__('recommendation_configs.set_as_default_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('recommendation_configs.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->title(__('recommendation_configs.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('recommendation_configs.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title(__('recommendation_configs.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecommendationConfigs::route('/'),
            'create' => Pages\CreateRecommendationConfig::route('/create'),
            'view' => Pages\ViewRecommendationConfig::route('/{record}'),
            'edit' => Pages\EditRecommendationConfig::route('/{record}/edit'),
        ];
    }
}
