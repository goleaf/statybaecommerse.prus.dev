<?php

declare(strict_types=1);

namespace App\Filament\Resources;


use App\Support\Concerns\HasNav;
use Filament\Schemas\Schema;
use App\Enums\NavigationGroup;
use App\Filament\Resources\RecommendationBlockResource\Pages;
use App\Models\RecommendationBlock;
use App\Models\Scopes\ActiveScope;
use App\Support\Recommendations\RecommendationBlockOptions;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section as SchemaSection;
use Novadaemon\FilamentCombobox\Combobox;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * RecommendationBlockResource
 *
 * Filament v4 resource for RecommendationBlock management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class RecommendationBlockResource extends Resource
{
    use HasNav;

    protected static ?string $model = RecommendationBlock::class;

    /**
     * @var string|BackedEnum|null Tracks the navigation group while remaining Filament compatible.
     */
    protected static \UnitEnum|string|null $navigationGroup = NavigationGroup::Products;

    protected static ?int $navigationSort = 13;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     */
    public static function getNavigationLabel(): string
    {
        return __('recommendation_blocks.title');
    }

    public static function getNavigationGroup(): BackedEnum|string|null
    {
        // Delegates to the enum value so Filament keeps grouping consistent while still
        // accepting plain strings when Filament expects a literal label.
        return self::$navigationGroup instanceof BackedEnum
            ? self::$navigationGroup->value
            : self::$navigationGroup;
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
            SchemaSection::make(__('recommendation_blocks.basic_information'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('recommendation_blocks.fields.name'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    TextInput::make('title')
                        ->label(__('recommendation_blocks.block_title'))
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label(__('recommendation_blocks.fields.description'))
                        ->maxLength(1000)
                        ->rows(3),
                    Select::make('type')
                        ->label(__('recommendation_blocks.type'))
                        // Ensure the UI values stay in sync with domain helpers.
                        ->options(RecommendationBlockOptions::types())
                        ->required()
                        ->native(false),
                    Select::make('position')
                        ->label(__('recommendation_blocks.position'))
                        // Position options rely on the same helper for filters and selects.
                        ->options(RecommendationBlockOptions::positions())
                        ->required()
                        ->native(false),
                ]),
            SchemaSection::make(__('recommendation_blocks.sections.products'))
                ->schema([
                    Combobox::make('products')
                        ->label(__('recommendation_blocks.products'))
                        ->relationship('products', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                        ->height('320px')
                        ->afterStateHydrated(function ($state, callable $set): void {
                            $set('products', collect($state)->sort()->values()->all());
                        })
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('description')
                                ->maxLength(1000),
                        ]),
                    TextInput::make('max_products')
                        ->label(__('recommendation_blocks.fields.max_products'))
                        ->numeric()
                        ->default(10)
                        ->minValue(1)
                        ->maxValue(50),
                ]),
            SchemaSection::make(__('recommendation_blocks.sections.settings'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('recommendation_blocks.fields.is_active'))
                        ->default(true),
                    Toggle::make('show_title')
                        ->label(__('recommendation_blocks.fields.show_title'))
                        ->default(true),
                    Toggle::make('show_description')
                        ->label(__('recommendation_blocks.fields.show_description'))
                        ->default(false),
                    TextInput::make('sort_order')
                        ->label(__('recommendation_blocks.fields.sort_order'))
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
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('recommendation_blocks.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('type')
                    ->label(__('recommendation_blocks.fields.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'featured' => 'success',
                        'related'  => 'info',
                        'similar'  => 'warning',
                        'trending' => 'danger',
                        'recent'   => 'gray',
                        default    => 'gray',
                    }),
                TextColumn::make('position')
                    ->label(__('recommendation_blocks.fields.position'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'top'     => 'success',
                        'bottom'  => 'info',
                        'sidebar' => 'warning',
                        'inline'  => 'danger',
                        default   => 'gray',
                    }),
                TextColumn::make('products_count')
                    ->label(__('recommendation_blocks.fields.products_count'))
                    ->counts('products')
                    ->sortable(),
                TextColumn::make('max_products')
                    ->label(__('recommendation_blocks.fields.max_products'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('recommendation_blocks.fields.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('sort_order')
                    ->label(__('recommendation_blocks.fields.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('recommendation_blocks.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('recommendation_blocks.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('recommendation_blocks.type'))
                    ->options(RecommendationBlockOptions::types()),
                SelectFilter::make('position')
                    ->label(__('recommendation_blocks.position'))
                    ->options(RecommendationBlockOptions::positions()),
                TernaryFilter::make('is_active')
                    ->label(__('recommendation_blocks.is_active'))
                    ->placeholder(__('recommendation_blocks.filters.all_records'))
                    ->trueLabel(__('recommendation_blocks.filters.active_only'))
                    ->falseLabel(__('recommendation_blocks.filters.inactive_only')),
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

    /**
     * Get available recommendation block types mapped to their translations.
     *
     * @return array<string, string>
     */
    protected static function getTypeOptions(): array
    {
        return [
            'featured' => __('recommendation_blocks.types.featured'),
            'related' => __('recommendation_blocks.types.related'),
            'similar' => __('recommendation_blocks.types.similar'),
            'trending' => __('recommendation_blocks.types.trending'),
            'recent' => __('recommendation_blocks.types.recent'),
        ];
    }

    /**
     * Get available recommendation block positions mapped to their translations.
     *
     * @return array<string, string>
     */
    protected static function getPositionOptions(): array
    {
        return [
            'top' => __('recommendation_blocks.positions.top'),
            'bottom' => __('recommendation_blocks.positions.bottom'),
            'sidebar' => __('recommendation_blocks.positions.sidebar'),
            'inline' => __('recommendation_blocks.positions.inline'),
        ];
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
            'index'  => Pages\ListRecommendationBlocks::route('/'),
            'create' => Pages\CreateRecommendationBlock::route('/create'),
            'edit'   => Pages\EditRecommendationBlock::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScope(ActiveScope::class);
    }
}
