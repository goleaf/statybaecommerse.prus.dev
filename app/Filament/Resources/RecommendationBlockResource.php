<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RecommendationBlockResource\Pages;
use App\Models\RecommendationBlock;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * RecommendationBlockResource
 *
 * Filament v4 resource for RecommendationBlock management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class RecommendationBlockResource extends Resource
{
    protected static ?string $model = RecommendationBlock::class;

    protected static UnitEnum|string|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 13;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     */
    public static function getNavigationLabel(): string
    {
        return __('recommendation_blocks.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     */
    public static function getNavigationGroup(): ?string
    {
        return 'Products';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('recommendation_blocks.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('recommendation_blocks.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('recommendation_blocks.basic_information'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('recommendation_blocks.name'))
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label(__('recommendation_blocks.description'))
                        ->maxLength(1000)
                        ->rows(3),
                    Select::make('type')
                        ->label(__('recommendation_blocks.type'))
                        ->options([
                            'featured' => __('recommendation_blocks.featured'),
                            'related' => __('recommendation_blocks.related'),
                            'similar' => __('recommendation_blocks.similar'),
                            'trending' => __('recommendation_blocks.trending'),
                            'recent' => __('recommendation_blocks.recent'),
                        ])
                        ->required()
                        ->native(false),
                    Select::make('position')
                        ->label(__('recommendation_blocks.position'))
                        ->options([
                            'top' => __('recommendation_blocks.top'),
                            'bottom' => __('recommendation_blocks.bottom'),
                            'sidebar' => __('recommendation_blocks.sidebar'),
                            'inline' => __('recommendation_blocks.inline'),
                        ])
                        ->required()
                        ->native(false),
                ]),
            Section::make(__('recommendation_blocks.products'))
                ->schema([
                    Select::make('product_ids')
                        ->label(__('recommendation_blocks.products'))
                        ->multiple()
                        ->relationship('products', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('description')
                                ->maxLength(1000),
                        ]),
                    TextInput::make('max_products')
                        ->label(__('recommendation_blocks.max_products'))
                        ->numeric()
                        ->default(10)
                        ->minValue(1)
                        ->maxValue(50),
                ]),
            Section::make(__('recommendation_blocks.settings'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('recommendation_blocks.is_active'))
                        ->default(true),
                    Toggle::make('show_title')
                        ->label(__('recommendation_blocks.show_title'))
                        ->default(true),
                    Toggle::make('show_description')
                        ->label(__('recommendation_blocks.show_description'))
                        ->default(false),
                    TextInput::make('sort_order')
                        ->label(__('recommendation_blocks.sort_order'))
                        ->numeric()
                        ->default(0),
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
                    ->label(__('recommendation_blocks.name'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('type')
                    ->label(__('recommendation_blocks.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'featured' => 'success',
                        'related' => 'info',
                        'similar' => 'warning',
                        'trending' => 'danger',
                        'recent' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('position')
                    ->label(__('recommendation_blocks.position'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'top' => 'success',
                        'bottom' => 'info',
                        'sidebar' => 'warning',
                        'inline' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('products_count')
                    ->label(__('recommendation_blocks.products_count'))
                    ->counts('products')
                    ->sortable(),
                TextColumn::make('max_products')
                    ->label(__('recommendation_blocks.max_products'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('recommendation_blocks.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('sort_order')
                    ->label(__('recommendation_blocks.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('recommendation_blocks.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('recommendation_blocks.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('recommendation_blocks.type'))
                    ->options([
                        'featured' => __('recommendation_blocks.featured'),
                        'related' => __('recommendation_blocks.related'),
                        'similar' => __('recommendation_blocks.similar'),
                        'trending' => __('recommendation_blocks.trending'),
                        'recent' => __('recommendation_blocks.recent'),
                    ]),
                SelectFilter::make('position')
                    ->label(__('recommendation_blocks.position'))
                    ->options([
                        'top' => __('recommendation_blocks.top'),
                        'bottom' => __('recommendation_blocks.bottom'),
                        'sidebar' => __('recommendation_blocks.sidebar'),
                        'inline' => __('recommendation_blocks.inline'),
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('recommendation_blocks.is_active'))
                    ->placeholder(__('recommendation_blocks.all_records'))
                    ->trueLabel(__('recommendation_blocks.active_only'))
                    ->falseLabel(__('recommendation_blocks.inactive_only')),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListRecommendationBlocks::route('/'),
            'create' => Pages\CreateRecommendationBlock::route('/create'),
            'edit' => Pages\EditRecommendationBlock::route('/{record}/edit'),
        ];
    }
}
