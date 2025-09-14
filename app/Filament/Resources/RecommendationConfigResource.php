<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RecommendationConfigResource\Pages;
use App\Models\RecommendationConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use UnitEnum;

final class RecommendationConfigResource extends Resource
{
    protected static ?string $model = RecommendationConfig::class;

    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-cog-6-tooth';

    /** @var UnitEnum|string|null */
    protected static $navigationGroup = 'Recommendation System';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('translations.recommendation_config_basic_info'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('translations.recommendation_config_name'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        
                        Textarea::make('description')
                            ->label(__('translations.recommendation_config_description'))
                            ->maxLength(1000)
                            ->rows(3),
                        
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('translations.recommendation_config_is_active'))
                                    ->default(true),
                                
                                TextInput::make('priority')
                                    ->label(__('translations.recommendation_config_priority'))
                                    ->numeric()
                                    ->default(0)
                                    ->helperText(__('translations.recommendation_config_priority_help')),
                            ]),
                    ]),

                Section::make(__('translations.recommendation_config_algorithm'))
                    ->schema([
                        Select::make('type')
                            ->label(__('translations.recommendation_config_type'))
                            ->required()
                            ->options([
                                'content_based' => __('translations.recommendation_type_content_based'),
                                'collaborative' => __('translations.recommendation_type_collaborative'),
                                'hybrid' => __('translations.recommendation_type_hybrid'),
                                'popularity' => __('translations.recommendation_type_popularity'),
                                'trending' => __('translations.recommendation_type_trending'),
                                'cross_sell' => __('translations.recommendation_type_cross_sell'),
                                'up_sell' => __('translations.recommendation_type_up_sell'),
                            ])
                            ->reactive()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('config', [])),
                        
                        KeyValue::make('config')
                            ->label(__('translations.recommendation_config_config'))
                            ->keyLabel(__('translations.key'))
                            ->valueLabel(__('translations.value'))
                            ->addActionLabel(__('translations.add_config_parameter'))
                            ->helperText(__('translations.recommendation_config_config_help')),
                    ]),

                Section::make(__('translations.recommendation_config_filters'))
                    ->schema([
                        KeyValue::make('filters')
                            ->label(__('translations.recommendation_config_filters'))
                            ->keyLabel(__('translations.filter_type'))
                            ->valueLabel(__('translations.filter_value'))
                            ->addActionLabel(__('translations.add_filter'))
                            ->helperText(__('translations.recommendation_config_filters_help')),
                    ]),

                Section::make(__('translations.recommendation_config_limits'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('max_results')
                                    ->label(__('translations.recommendation_config_max_results'))
                                    ->numeric()
                                    ->default(10)
                                    ->minValue(1)
                                    ->maxValue(50),
                                
                                TextInput::make('min_score')
                                    ->label(__('translations.recommendation_config_min_score'))
                                    ->numeric()
                                    ->step(0.01)
                                    ->default(0.1)
                                    ->minValue(0)
                                    ->maxValue(1),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('translations.recommendation_config_name'))
                    ->searchable()
                    ->sortable(),
                
                BadgeColumn::make('type')
                    ->label(__('translations.recommendation_config_type'))
                    ->colors([
                        'primary' => 'content_based',
                        'success' => 'collaborative',
                        'warning' => 'hybrid',
                        'info' => 'popularity',
                        'danger' => 'trending',
                        'secondary' => 'cross_sell',
                        'gray' => 'up_sell',
                    ]),
                
                TextColumn::make('priority')
                    ->label(__('translations.recommendation_config_priority'))
                    ->sortable(),
                
                BooleanColumn::make('is_active')
                    ->label(__('translations.recommendation_config_is_active'))
                    ->sortable(),
                
                TextColumn::make('max_results')
                    ->label(__('translations.recommendation_config_max_results'))
                    ->sortable(),
                
                TextColumn::make('min_score')
                    ->label(__('translations.recommendation_config_min_score'))
                    ->formatStateUsing(fn (string $state): string => number_format((float) $state, 2)),
                
                TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('translations.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('translations.recommendation_config_type'))
                    ->options([
                        'content_based' => __('translations.recommendation_type_content_based'),
                        'collaborative' => __('translations.recommendation_type_collaborative'),
                        'hybrid' => __('translations.recommendation_type_hybrid'),
                        'popularity' => __('translations.recommendation_type_popularity'),
                        'trending' => __('translations.recommendation_type_trending'),
                        'cross_sell' => __('translations.recommendation_type_cross_sell'),
                        'up_sell' => __('translations.recommendation_type_up_sell'),
                    ]),
                
                TernaryFilter::make('is_active')
                    ->label(__('translations.recommendation_config_is_active'))
                    ->boolean()
                    ->trueLabel(__('translations.active_only'))
                    ->falseLabel(__('translations.inactive_only'))
                    ->native(false),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('priority', 'desc');
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
            'index' => Pages\ListRecommendationConfigs::route('/'),
            'create' => Pages\CreateRecommendationConfig::route('/create'),
            'edit' => Pages\EditRecommendationConfig::route('/{record}/edit'),
        ];
    }
}
