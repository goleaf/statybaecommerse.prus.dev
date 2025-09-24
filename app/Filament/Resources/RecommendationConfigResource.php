<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RecommendationConfigResource\Pages;
use App\Models\RecommendationConfig;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
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
                            TextInput::make('key')
                                ->label(__('recommendation_config.fields.key'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('value')
                                ->label(__('recommendation_config.fields.value'))
                                ->maxLength(255),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(__('recommendation_config.fields.key'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('value')
                    ->label(__('recommendation_config.fields.value'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('recommendation_config.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
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
