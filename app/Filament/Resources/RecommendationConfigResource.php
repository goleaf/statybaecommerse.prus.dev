<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RecommendationConfigResource\Pages;
use App\Models\RecommendationConfig;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

final class RecommendationConfigResource extends Resource
{
    protected static ?string $model = RecommendationConfig::class;

    protected static UnitEnum|string|null $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 11;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('recommendation_configs.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('recommendation_configs.plural');
    }

    public static function getModelLabel(): string
    {
        return __('recommendation_configs.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('recommendation_config.sections.basic_info'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('recommendation_config.fields.name'))
                                ->required()
                                ->maxLength(255),
                            Select::make('type')
                                ->label(__('recommendation_config.fields.type'))
                                ->options([
                                    'collaborative' => 'collaborative',
                                    'content_based' => 'content_based',
                                    'hybrid' => 'hybrid',
                                    'popularity' => 'popularity',
                                    'trending' => 'trending',
                                    'cross_sell' => 'cross_sell',
                                    'up_sell' => 'up_sell',
                                ])
                                ->required()
                                ->native(false),
                        ]),
                    Textarea::make('description')
                        ->label(__('recommendation_config.fields.description')),
                ]),
            Section::make(__('recommendation_config.sections.parameters'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('min_score')
                                ->label(__('recommendation_config.fields.min_score'))
                                ->numeric(),
                            TextInput::make('max_results')
                                ->label(__('recommendation_config.fields.max_results'))
                                ->numeric(),
                            TextInput::make('decay_factor')
                                ->label(__('recommendation_config.fields.decay_factor'))
                                ->numeric(),
                            TextInput::make('priority')
                                ->label(__('recommendation_config.fields.priority'))
                                ->numeric(),
                            TextInput::make('cache_ttl')
                                ->label(__('recommendation_config.fields.cache_ttl'))
                                ->numeric(),
                            TextInput::make('sort_order')
                                ->label(__('recommendation_config.fields.sort_order'))
                                ->numeric(),
                        ]),
                ]),
            Section::make(__('recommendation_config.sections.flags'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('recommendation_config.fields.is_active')),
                            Toggle::make('is_default')
                                ->label(__('recommendation_config.fields.is_default')),
                        ]),
                ]),
            Section::make(__('recommendation_config.sections.relationships'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('products')
                                ->label(__('recommendation_config.fields.products'))
                                ->relationship('products', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->formatStateUsing(fn ($state) => is_array($state) ? array_values(collect($state)->sort()->all()) : $state)
                                ->native(false),
                            Select::make('categories')
                                ->label(__('recommendation_config.fields.categories'))
                                ->relationship('categories', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->formatStateUsing(fn ($state) => is_array($state) ? array_values(collect($state)->sort()->all()) : $state)
                                ->native(false),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('recommendation_config.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('recommendation_config.fields.type'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('recommendation_config.fields.is_active'))
                    ->boolean(),
                IconColumn::make('is_default')
                    ->label(__('recommendation_config.fields.is_default'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('recommendation_config.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('recommendation_config.fields.type'))
                    ->options([
                        'collaborative' => 'collaborative',
                        'content_based' => 'content_based',
                        'hybrid' => 'hybrid',
                        'popularity' => 'popularity',
                        'trending' => 'trending',
                        'cross_sell' => 'cross_sell',
                        'up_sell' => 'up_sell',
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('recommendation_config.fields.is_active')),
                TernaryFilter::make('is_default')
                    ->label(__('recommendation_config.fields.is_default')),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
                BulkAction::make('activate')
                    ->label(__('recommendation_config.actions.activate'))
                    ->requiresConfirmation()
                    ->action(fn ($records) => $records->each->update(['is_active' => true])),
                BulkAction::make('deactivate')
                    ->label(__('recommendation_config.actions.deactivate'))
                    ->requiresConfirmation()
                    ->action(fn ($records) => $records->each->update(['is_active' => false])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecommendationConfigs::route('/'),
        ];
    }
}
