<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RecommendationConfigResourceSimple\Pages;
use App\Models\RecommendationConfigSimple;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

final class RecommendationConfigResourceSimple extends Resource
{
    protected static ?string $model = RecommendationConfigSimple::class;

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('recommendation_configs_simple.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('recommendation_configs_simple.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('recommendation_configs_simple.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('name')
                                ->label(__('recommendation_configs_simple.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->label(__('recommendation_configs_simple.code'))
                                ->required()
                                ->maxLength(50)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Textarea::make('description')
                        ->label(__('recommendation_configs_simple.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('recommendation_configs_simple.algorithm_settings'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('algorithm_type')
                                ->label(__('recommendation_configs_simple.algorithm_type'))
                                ->options([
                                    'collaborative' => __('recommendation_configs_simple.algorithm_types.collaborative'),
                                    'content_based' => __('recommendation_configs_simple.algorithm_types.content_based'),
                                    'hybrid' => __('recommendation_configs_simple.algorithm_types.hybrid'),
                                    'popularity' => __('recommendation_configs_simple.algorithm_types.popularity'),
                                    'trending' => __('recommendation_configs_simple.algorithm_types.trending'),
                                    'similarity' => __('recommendation_configs_simple.algorithm_types.similarity'),
                                    'custom' => __('recommendation_configs_simple.algorithm_types.custom'),
                                ])
                                ->required()
                                ->default('collaborative'),
                            TextInput::make('min_score')
                                ->label(__('recommendation_configs_simple.min_score'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(1)
                                ->default(0.1)
                                ->helperText(__('recommendation_configs_simple.min_score_help')),
                        ]),
                    Grid::make(2)
                        ->components([
                            TextInput::make('max_results')
                                ->label(__('recommendation_configs_simple.max_results'))
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(100)
                                ->default(10)
                                ->helperText(__('recommendation_configs_simple.max_results_help')),
                            TextInput::make('decay_factor')
                                ->label(__('recommendation_configs_simple.decay_factor'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(1)
                                ->default(0.9)
                                ->helperText(__('recommendation_configs_simple.decay_factor_help')),
                        ]),
                ]),
            Section::make(__('recommendation_configs_simple.filtering'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('products')
                                ->label(__('recommendation_configs_simple.products'))
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
                                ->label(__('recommendation_configs_simple.categories'))
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
                        ->components([
                            Toggle::make('exclude_out_of_stock')
                                ->label(__('recommendation_configs_simple.exclude_out_of_stock'))
                                ->default(true),
                            Toggle::make('exclude_inactive')
                                ->label(__('recommendation_configs_simple.exclude_inactive'))
                                ->default(true),
                        ]),
                ]),
            Section::make(__('recommendation_configs_simple.weighting'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('price_weight')
                                ->label(__('recommendation_configs_simple.price_weight'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(1)
                                ->default(0.2)
                                ->helperText(__('recommendation_configs_simple.price_weight_help')),
                            TextInput::make('rating_weight')
                                ->label(__('recommendation_configs_simple.rating_weight'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(1)
                                ->default(0.3)
                                ->helperText(__('recommendation_configs_simple.rating_weight_help')),
                        ]),
                    Grid::make(2)
                        ->components([
                            TextInput::make('popularity_weight')
                                ->label(__('recommendation_configs_simple.popularity_weight'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(1)
                                ->default(0.2)
                                ->helperText(__('recommendation_configs_simple.popularity_weight_help')),
                            TextInput::make('recency_weight')
                                ->label(__('recommendation_configs_simple.recency_weight'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(1)
                                ->default(0.1)
                                ->helperText(__('recommendation_configs_simple.recency_weight_help')),
                        ]),
                    Grid::make(2)
                        ->components([
                            TextInput::make('category_weight')
                                ->label(__('recommendation_configs_simple.category_weight'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(1)
                                ->default(0.2)
                                ->helperText(__('recommendation_configs_simple.category_weight_help')),
                            TextInput::make('custom_weight')
                                ->label(__('recommendation_configs_simple.custom_weight'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(1)
                                ->default(0)
                                ->helperText(__('recommendation_configs_simple.custom_weight_help')),
                        ]),
                ]),
            Section::make(__('recommendation_configs_simple.settings'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Toggle::make('is_active')
                                ->label(__('recommendation_configs_simple.is_active'))
                                ->default(true),
                            Toggle::make('is_default')
                                ->label(__('recommendation_configs_simple.is_default')),
                        ]),
                    Grid::make(2)
                        ->components([
                            TextInput::make('cache_duration')
                                ->label(__('recommendation_configs_simple.cache_duration'))
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(1440)
                                ->default(60)
                                ->suffix('minutes')
                                ->helperText(__('recommendation_configs_simple.cache_duration_help')),
                            TextInput::make('sort_order')
                                ->label(__('recommendation_configs_simple.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                        ]),
                    Textarea::make('notes')
                        ->label(__('recommendation_configs_simple.notes'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('recommendation_configs_simple.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('code')
                    ->label(__('recommendation_configs_simple.code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('algorithm_type')
                    ->label(__('recommendation_configs_simple.algorithm_type'))
                    ->formatStateUsing(fn (string $state): string => __("recommendation_configs_simple.algorithm_types.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
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
                    ->label(__('recommendation_configs_simple.min_score'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('max_results')
                    ->label(__('recommendation_configs_simple.max_results'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('decay_factor')
                    ->label(__('recommendation_configs_simple.decay_factor'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('products_count')
                    ->label(__('recommendation_configs_simple.products_count'))
                    ->counts('products')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('categories_count')
                    ->label(__('recommendation_configs_simple.categories_count'))
                    ->counts('categories')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('exclude_out_of_stock')
                    ->label(__('recommendation_configs_simple.exclude_out_of_stock'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('exclude_inactive')
                    ->label(__('recommendation_configs_simple.exclude_inactive'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price_weight')
                    ->label(__('recommendation_configs_simple.price_weight'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rating_weight')
                    ->label(__('recommendation_configs_simple.rating_weight'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('popularity_weight')
                    ->label(__('recommendation_configs_simple.popularity_weight'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('recency_weight')
                    ->label(__('recommendation_configs_simple.recency_weight'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category_weight')
                    ->label(__('recommendation_configs_simple.category_weight'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('custom_weight')
                    ->label(__('recommendation_configs_simple.custom_weight'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cache_duration')
                    ->label(__('recommendation_configs_simple.cache_duration'))
                    ->numeric()
                    ->sortable()
                    ->suffix(' min')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('recommendation_configs_simple.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label(__('recommendation_configs_simple.is_default'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('recommendation_configs_simple.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('recommendation_configs_simple.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('recommendation_configs_simple.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('algorithm_type')
                    ->label(__('recommendation_configs_simple.algorithm_type'))
                    ->options([
                        'collaborative' => __('recommendation_configs_simple.algorithm_types.collaborative'),
                        'content_based' => __('recommendation_configs_simple.algorithm_types.content_based'),
                        'hybrid' => __('recommendation_configs_simple.algorithm_types.hybrid'),
                        'popularity' => __('recommendation_configs_simple.algorithm_types.popularity'),
                        'trending' => __('recommendation_configs_simple.algorithm_types.trending'),
                        'similarity' => __('recommendation_configs_simple.algorithm_types.similarity'),
                        'custom' => __('recommendation_configs_simple.algorithm_types.custom'),
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('recommendation_configs_simple.is_active'))
                    ->boolean()
                    ->trueLabel(__('recommendation_configs_simple.active_only'))
                    ->falseLabel(__('recommendation_configs_simple.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_default')
                    ->label(__('recommendation_configs_simple.is_default'))
                    ->boolean()
                    ->trueLabel(__('recommendation_configs_simple.default_only'))
                    ->falseLabel(__('recommendation_configs_simple.non_default_only'))
                    ->native(false),
                TernaryFilter::make('exclude_out_of_stock')
                    ->label(__('recommendation_configs_simple.exclude_out_of_stock'))
                    ->boolean()
                    ->trueLabel(__('recommendation_configs_simple.exclude_out_of_stock_only'))
                    ->falseLabel(__('recommendation_configs_simple.include_out_of_stock_only'))
                    ->native(false),
                TernaryFilter::make('exclude_inactive')
                    ->label(__('recommendation_configs_simple.exclude_inactive'))
                    ->boolean()
                    ->trueLabel(__('recommendation_configs_simple.exclude_inactive_only'))
                    ->falseLabel(__('recommendation_configs_simple.include_inactive_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn (RecommendationConfigSimple $record): string => $record->is_active ? __('recommendation_configs_simple.deactivate') : __('recommendation_configs_simple.activate'))
                    ->icon(fn (RecommendationConfigSimple $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (RecommendationConfigSimple $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (RecommendationConfigSimple $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('recommendation_configs_simple.activated_successfully') : __('recommendation_configs_simple.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('set_default')
                    ->label(__('recommendation_configs_simple.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (RecommendationConfigSimple $record): bool => ! $record->is_default)
                    ->action(function (RecommendationConfigSimple $record): void {
                        // Remove default from other recommendation configs
                        RecommendationConfigSimple::where('is_default', true)->update(['is_default' => false]);

                        // Set this recommendation config as default
                        $record->update(['is_default' => true]);

                        Notification::make()
                            ->title(__('recommendation_configs_simple.set_as_default_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('recommendation_configs_simple.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->title(__('recommendation_configs_simple.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('recommendation_configs_simple.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title(__('recommendation_configs_simple.bulk_deactivated_success'))
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
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecommendationConfigSimples::route('/'),
            'create' => Pages\CreateRecommendationConfigSimple::route('/create'),
            'view' => Pages\ViewRecommendationConfigSimple::route('/{record}'),
            'edit' => Pages\EditRecommendationConfigSimple::route('/{record}/edit'),
        ];
    }
}
