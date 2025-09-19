<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\RecommendationBlockResource\Pages;
use App\Models\Product;
use App\Models\RecommendationBlock;
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
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
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
 * RecommendationBlockResource
 *
 * Filament v4 resource for RecommendationBlock management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class RecommendationBlockResource extends Resource
{
    protected static ?string $model = RecommendationBlock::class;

    /**
     * @var UnitEnum|string|null
     */
    protected static string|UnitEnum|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 13;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('recommendation_blocks.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'Products'->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('recommendation_blocks.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('recommendation_blocks.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('recommendation_blocks.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('recommendation_blocks.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->label(__('recommendation_blocks.code'))
                                ->required()
                                ->maxLength(50)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Textarea::make('description')
                        ->label(__('recommendation_blocks.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('recommendation_blocks.configuration'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('recommendation_config_id')
                                ->label(__('recommendation_blocks.recommendation_config'))
                                ->relationship('recommendationConfig', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $config = RecommendationConfig::find($state);
                                        if ($config) {
                                            $set('config_name', $config->name);
                                            $set('config_code', $config->code);
                                        }
                                    }
                                }),
                            TextInput::make('config_name')
                                ->label(__('recommendation_blocks.config_name'))
                                ->maxLength(255)
                                ->disabled(),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('config_code')
                                ->label(__('recommendation_blocks.config_code'))
                                ->maxLength(50)
                                ->disabled(),
                            Select::make('type')
                                ->label(__('recommendation_blocks.type'))
                                ->options([
                                    'product_list' => __('recommendation_blocks.types.product_list'),
                                    'carousel' => __('recommendation_blocks.types.carousel'),
                                    'grid' => __('recommendation_blocks.types.grid'),
                                    'sidebar' => __('recommendation_blocks.types.sidebar'),
                                    'banner' => __('recommendation_blocks.types.banner'),
                                    'popup' => __('recommendation_blocks.types.popup'),
                                    'custom' => __('recommendation_blocks.types.custom'),
                                ])
                                ->required()
                                ->default('product_list'),
                        ]),
                ]),
            Section::make(__('recommendation_blocks.display_settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('title')
                                ->label(__('recommendation_blocks.title'))
                                ->maxLength(255)
                                ->helperText(__('recommendation_blocks.title_help')),
                            TextInput::make('subtitle')
                                ->label(__('recommendation_blocks.subtitle'))
                                ->maxLength(255)
                                ->helperText(__('recommendation_blocks.subtitle_help')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('max_items')
                                ->label(__('recommendation_blocks.max_items'))
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(50)
                                ->default(10)
                                ->helperText(__('recommendation_blocks.max_items_help')),
                            TextInput::make('items_per_row')
                                ->label(__('recommendation_blocks.items_per_row'))
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(12)
                                ->default(4)
                                ->helperText(__('recommendation_blocks.items_per_row_help')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('height')
                                ->label(__('recommendation_blocks.height'))
                                ->maxLength(20)
                                ->helperText(__('recommendation_blocks.height_help')),
                            TextInput::make('width')
                                ->label(__('recommendation_blocks.width'))
                                ->maxLength(20)
                                ->helperText(__('recommendation_blocks.width_help')),
                        ]),
                ]),
            Section::make(__('recommendation_blocks.styling'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('css_class')
                                ->label(__('recommendation_blocks.css_class'))
                                ->maxLength(255)
                                ->helperText(__('recommendation_blocks.css_class_help')),
                            TextInput::make('css_id')
                                ->label(__('recommendation_blocks.css_id'))
                                ->maxLength(255)
                                ->helperText(__('recommendation_blocks.css_id_help')),
                        ]),
                    Textarea::make('custom_css')
                        ->label(__('recommendation_blocks.custom_css'))
                        ->rows(5)
                        ->maxLength(1000)
                        ->helperText(__('recommendation_blocks.custom_css_help'))
                        ->columnSpanFull(),
                ]),
            Section::make(__('recommendation_blocks.placement'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('page_type')
                                ->label(__('recommendation_blocks.page_type'))
                                ->options([
                                    'home' => __('recommendation_blocks.page_types.home'),
                                    'product' => __('recommendation_blocks.page_types.product'),
                                    'category' => __('recommendation_blocks.page_types.category'),
                                    'cart' => __('recommendation_blocks.page_types.cart'),
                                    'checkout' => __('recommendation_blocks.page_types.checkout'),
                                    'search' => __('recommendation_blocks.page_types.search'),
                                    'profile' => __('recommendation_blocks.page_types.profile'),
                                    'custom' => __('recommendation_blocks.page_types.custom'),
                                ])
                                ->required()
                                ->default('home'),
                            TextInput::make('position')
                                ->label(__('recommendation_blocks.position'))
                                ->maxLength(100)
                                ->helperText(__('recommendation_blocks.position_help')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('priority')
                                ->label(__('recommendation_blocks.priority'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText(__('recommendation_blocks.priority_help')),
                            Toggle::make('is_sticky')
                                ->label(__('recommendation_blocks.is_sticky'))
                                ->default(false),
                        ]),
                ]),
            Section::make(__('recommendation_blocks.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('recommendation_blocks.is_active'))
                                ->default(true),
                            Toggle::make('is_visible')
                                ->label(__('recommendation_blocks.is_visible'))
                                ->default(true),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('show_title')
                                ->label(__('recommendation_blocks.show_title'))
                                ->default(true),
                            Toggle::make('show_subtitle')
                                ->label(__('recommendation_blocks.show_subtitle'))
                                ->default(false),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('show_price')
                                ->label(__('recommendation_blocks.show_price'))
                                ->default(true),
                            Toggle::make('show_rating')
                                ->label(__('recommendation_blocks.show_rating'))
                                ->default(true),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('show_add_to_cart')
                                ->label(__('recommendation_blocks.show_add_to_cart'))
                                ->default(true),
                            Toggle::make('show_wishlist')
                                ->label(__('recommendation_blocks.show_wishlist'))
                                ->default(false),
                        ]),
                    Textarea::make('notes')
                        ->label(__('recommendation_blocks.notes'))
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
                    ->label(__('recommendation_blocks.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('code')
                    ->label(__('recommendation_blocks.code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('recommendationConfig.name')
                    ->label(__('recommendation_blocks.recommendation_config'))
                    ->sortable()
                    ->limit(50),
                TextColumn::make('type')
                    ->label(__('recommendation_blocks.type'))
                    ->formatStateUsing(fn(string $state): string => __("recommendation_blocks.types.{$state}"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'product_list' => 'blue',
                        'carousel' => 'green',
                        'grid' => 'purple',
                        'sidebar' => 'orange',
                        'banner' => 'pink',
                        'popup' => 'indigo',
                        'custom' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('page_type')
                    ->label(__('recommendation_blocks.page_type'))
                    ->formatStateUsing(fn(string $state): string => __("recommendation_blocks.page_types.{$state}"))
                    ->badge()
                    ->color('blue')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('position')
                    ->label(__('recommendation_blocks.position'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('max_items')
                    ->label(__('recommendation_blocks.max_items'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('items_per_row')
                    ->label(__('recommendation_blocks.items_per_row'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('height')
                    ->label(__('recommendation_blocks.height'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('width')
                    ->label(__('recommendation_blocks.width'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('css_class')
                    ->label(__('recommendation_blocks.css_class'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('css_id')
                    ->label(__('recommendation_blocks.css_id'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('priority')
                    ->label(__('recommendation_blocks.priority'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('recommendation_blocks.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->label(__('recommendation_blocks.is_visible'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_sticky')
                    ->label(__('recommendation_blocks.is_sticky'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('show_title')
                    ->label(__('recommendation_blocks.show_title'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('show_subtitle')
                    ->label(__('recommendation_blocks.show_subtitle'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('show_price')
                    ->label(__('recommendation_blocks.show_price'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('show_rating')
                    ->label(__('recommendation_blocks.show_rating'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('show_add_to_cart')
                    ->label(__('recommendation_blocks.show_add_to_cart'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('show_wishlist')
                    ->label(__('recommendation_blocks.show_wishlist'))
                    ->boolean()
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
                SelectFilter::make('recommendation_config_id')
                    ->label(__('recommendation_blocks.recommendation_config'))
                    ->relationship('recommendationConfig', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label(__('recommendation_blocks.type'))
                    ->options([
                        'product_list' => __('recommendation_blocks.types.product_list'),
                        'carousel' => __('recommendation_blocks.types.carousel'),
                        'grid' => __('recommendation_blocks.types.grid'),
                        'sidebar' => __('recommendation_blocks.types.sidebar'),
                        'banner' => __('recommendation_blocks.types.banner'),
                        'popup' => __('recommendation_blocks.types.popup'),
                        'custom' => __('recommendation_blocks.types.custom'),
                    ]),
                SelectFilter::make('page_type')
                    ->label(__('recommendation_blocks.page_type'))
                    ->options([
                        'home' => __('recommendation_blocks.page_types.home'),
                        'product' => __('recommendation_blocks.page_types.product'),
                        'category' => __('recommendation_blocks.page_types.category'),
                        'cart' => __('recommendation_blocks.page_types.cart'),
                        'checkout' => __('recommendation_blocks.page_types.checkout'),
                        'search' => __('recommendation_blocks.page_types.search'),
                        'profile' => __('recommendation_blocks.page_types.profile'),
                        'custom' => __('recommendation_blocks.page_types.custom'),
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('recommendation_blocks.is_active'))
                    ->boolean()
                    ->trueLabel(__('recommendation_blocks.active_only'))
                    ->falseLabel(__('recommendation_blocks.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_visible')
                    ->label(__('recommendation_blocks.is_visible'))
                    ->boolean()
                    ->trueLabel(__('recommendation_blocks.visible_only'))
                    ->falseLabel(__('recommendation_blocks.hidden_only'))
                    ->native(false),
                TernaryFilter::make('is_sticky')
                    ->label(__('recommendation_blocks.is_sticky'))
                    ->boolean()
                    ->trueLabel(__('recommendation_blocks.sticky_only'))
                    ->falseLabel(__('recommendation_blocks.non_sticky_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn(RecommendationBlock $record): string => $record->is_active ? __('recommendation_blocks.deactivate') : __('recommendation_blocks.activate'))
                    ->icon(fn(RecommendationBlock $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(RecommendationBlock $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (RecommendationBlock $record): void {
                        $record->update(['is_active' => !$record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('recommendation_blocks.activated_successfully') : __('recommendation_blocks.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('toggle_visible')
                    ->label(fn(RecommendationBlock $record): string => $record->is_visible ? __('recommendation_blocks.hide') : __('recommendation_blocks.show'))
                    ->icon(fn(RecommendationBlock $record): string => $record->is_visible ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(RecommendationBlock $record): string => $record->is_visible ? 'warning' : 'success')
                    ->action(function (RecommendationBlock $record): void {
                        $record->update(['is_visible' => !$record->is_visible]);

                        Notification::make()
                            ->title($record->is_visible ? __('recommendation_blocks.shown_successfully') : __('recommendation_blocks.hidden_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('recommendation_blocks.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->title(__('recommendation_blocks.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('recommendation_blocks.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title(__('recommendation_blocks.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('show')
                        ->label(__('recommendation_blocks.show_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_visible' => true]);

                            Notification::make()
                                ->title(__('recommendation_blocks.bulk_shown_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('hide')
                        ->label(__('recommendation_blocks.hide_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_visible' => false]);

                            Notification::make()
                                ->title(__('recommendation_blocks.bulk_hidden_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('priority', 'desc');
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
            'index' => Pages\ListRecommendationBlocks::route('/'),
            'create' => Pages\CreateRecommendationBlock::route('/create'),
            'view' => Pages\ViewRecommendationBlock::route('/{record}'),
            'edit' => Pages\EditRecommendationBlock::route('/{record}/edit'),
        ];
    }
}
