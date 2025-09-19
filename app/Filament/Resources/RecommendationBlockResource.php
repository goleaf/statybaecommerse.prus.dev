<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Enums\NavigationGroup;
use App\Filament\Resources\RecommendationBlockResource\Pages;
use App\Models\Product;
use App\Models\RecommendationBlock;
use App\Models\RecommendationConfig;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
/**
 * RecommendationBlockResource
 *
 * Filament v4 resource for RecommendationBlock management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class RecommendationBlockResource extends Resource
{
    protected static ?string $model = RecommendationBlock::class;
    /**
    // protected static $navigationGroup = NavigationGroup::Products;
    protected static ?int $navigationSort = 13;
    protected static ?string $recordTitleAttribute = 'name';
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('recommendation_blocks.title');
    }
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
    public static function getNavigationGroup(): ?string
        return "Products";
     * Handle getPluralModelLabel functionality with proper error handling.
    public static function getPluralModelLabel(): string
        return __('recommendation_blocks.plural');
     * Handle getModelLabel functionality with proper error handling.
    public static function getModelLabel(): string
        return __('recommendation_blocks.single');
     * Configure the Filament form schema with fields and validation.
     * @param Form $schema
     * @return Form
    public static function form(Schema $schema): Schema
    {
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
                            TextInput::make('config_code')
                                ->label(__('recommendation_blocks.config_code'))
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
                                ->default('product_list'),
            Section::make(__('recommendation_blocks.display_settings'))
                            TextInput::make('title')
                                ->label(__('recommendation_blocks.title'))
                                ->helperText(__('recommendation_blocks.title_help')),
                            TextInput::make('subtitle')
                                ->label(__('recommendation_blocks.subtitle'))
                                ->helperText(__('recommendation_blocks.subtitle_help')),
                            TextInput::make('max_items')
                                ->label(__('recommendation_blocks.max_items'))
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(50)
                                ->default(10)
                                ->helperText(__('recommendation_blocks.max_items_help')),
                            TextInput::make('items_per_row')
                                ->label(__('recommendation_blocks.items_per_row'))
                                ->maxValue(12)
                                ->default(4)
                                ->helperText(__('recommendation_blocks.items_per_row_help')),
                            TextInput::make('height')
                                ->label(__('recommendation_blocks.height'))
                                ->maxLength(20)
                                ->helperText(__('recommendation_blocks.height_help')),
                            TextInput::make('width')
                                ->label(__('recommendation_blocks.width'))
                                ->helperText(__('recommendation_blocks.width_help')),
            Section::make(__('recommendation_blocks.styling'))
                            TextInput::make('css_class')
                                ->label(__('recommendation_blocks.css_class'))
                                ->helperText(__('recommendation_blocks.css_class_help')),
                            TextInput::make('css_id')
                                ->label(__('recommendation_blocks.css_id'))
                                ->helperText(__('recommendation_blocks.css_id_help')),
                    Textarea::make('custom_css')
                        ->label(__('recommendation_blocks.custom_css'))
                        ->rows(5)
                        ->maxLength(1000)
                        ->helperText(__('recommendation_blocks.custom_css_help'))
            Section::make(__('recommendation_blocks.placement'))
                            Select::make('page_type')
                                ->label(__('recommendation_blocks.page_type'))
                                    'home' => __('recommendation_blocks.page_types.home'),
                                    'product' => __('recommendation_blocks.page_types.product'),
                                    'category' => __('recommendation_blocks.page_types.category'),
                                    'cart' => __('recommendation_blocks.page_types.cart'),
                                    'checkout' => __('recommendation_blocks.page_types.checkout'),
                                    'search' => __('recommendation_blocks.page_types.search'),
                                    'profile' => __('recommendation_blocks.page_types.profile'),
                                    'custom' => __('recommendation_blocks.page_types.custom'),
                                ->default('home'),
                            TextInput::make('position')
                                ->label(__('recommendation_blocks.position'))
                                ->maxLength(100)
                                ->helperText(__('recommendation_blocks.position_help')),
                            TextInput::make('priority')
                                ->label(__('recommendation_blocks.priority'))
                                ->default(0)
                                ->minValue(0)
                                ->helperText(__('recommendation_blocks.priority_help')),
                            Toggle::make('is_sticky')
                                ->label(__('recommendation_blocks.is_sticky'))
                                ->default(false),
            Section::make(__('recommendation_blocks.settings'))
                            Toggle::make('is_active')
                                ->label(__('recommendation_blocks.is_active'))
                                ->default(true),
                            Toggle::make('is_visible')
                                ->label(__('recommendation_blocks.is_visible'))
                            Toggle::make('show_title')
                                ->label(__('recommendation_blocks.show_title'))
                            Toggle::make('show_subtitle')
                                ->label(__('recommendation_blocks.show_subtitle'))
                            Toggle::make('show_price')
                                ->label(__('recommendation_blocks.show_price'))
                            Toggle::make('show_rating')
                                ->label(__('recommendation_blocks.show_rating'))
                            Toggle::make('show_add_to_cart')
                                ->label(__('recommendation_blocks.show_add_to_cart'))
                            Toggle::make('show_wishlist')
                                ->label(__('recommendation_blocks.show_wishlist'))
                    Textarea::make('notes')
                        ->label(__('recommendation_blocks.notes'))
        ]);
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
    public static function table(Table $table): Table
    {$state}"))
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
                    ->color('blue')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('position')
                    ->label(__('recommendation_blocks.position'))
                    ->color('gray')
                TextColumn::make('max_items')
                    ->label(__('recommendation_blocks.max_items'))
                    ->numeric()
                    ->alignCenter()
                TextColumn::make('items_per_row')
                    ->label(__('recommendation_blocks.items_per_row'))
                TextColumn::make('height')
                    ->label(__('recommendation_blocks.height'))
                TextColumn::make('width')
                    ->label(__('recommendation_blocks.width'))
                TextColumn::make('css_class')
                    ->label(__('recommendation_blocks.css_class'))
                    ->limit(50)
                TextColumn::make('css_id')
                    ->label(__('recommendation_blocks.css_id'))
                TextColumn::make('priority')
                    ->label(__('recommendation_blocks.priority'))
                IconColumn::make('is_active')
                    ->label(__('recommendation_blocks.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->label(__('recommendation_blocks.is_visible'))
                IconColumn::make('is_sticky')
                    ->label(__('recommendation_blocks.is_sticky'))
                IconColumn::make('show_title')
                    ->label(__('recommendation_blocks.show_title'))
                IconColumn::make('show_subtitle')
                    ->label(__('recommendation_blocks.show_subtitle'))
                IconColumn::make('show_price')
                    ->label(__('recommendation_blocks.show_price'))
                IconColumn::make('show_rating')
                    ->label(__('recommendation_blocks.show_rating'))
                IconColumn::make('show_add_to_cart')
                    ->label(__('recommendation_blocks.show_add_to_cart'))
                IconColumn::make('show_wishlist')
                    ->label(__('recommendation_blocks.show_wishlist'))
                TextColumn::make('created_at')
                    ->label(__('recommendation_blocks.created_at'))
                    ->dateTime()
                TextColumn::make('updated_at')
                    ->label(__('recommendation_blocks.updated_at'))
            ])
            ->filters([
                SelectFilter::make('recommendation_config_id')
                    ->relationship('recommendationConfig', 'name')
                    ->preload(),
                SelectFilter::make('type')
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
                        'home' => __('recommendation_blocks.page_types.home'),
                        'product' => __('recommendation_blocks.page_types.product'),
                        'category' => __('recommendation_blocks.page_types.category'),
                        'cart' => __('recommendation_blocks.page_types.cart'),
                        'checkout' => __('recommendation_blocks.page_types.checkout'),
                        'search' => __('recommendation_blocks.page_types.search'),
                        'profile' => __('recommendation_blocks.page_types.profile'),
                        'custom' => __('recommendation_blocks.page_types.custom'),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('recommendation_blocks.active_only'))
                    ->falseLabel(__('recommendation_blocks.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_visible')
                    ->trueLabel(__('recommendation_blocks.visible_only'))
                    ->falseLabel(__('recommendation_blocks.hidden_only'))
                TernaryFilter::make('is_sticky')
                    ->trueLabel(__('recommendation_blocks.sticky_only'))
                    ->falseLabel(__('recommendation_blocks.non_sticky_only'))
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
                        $record->update(['is_visible' => !$record->is_visible]);
                            ->title($record->is_visible ? __('recommendation_blocks.shown_successfully') : __('recommendation_blocks.hidden_successfully'))
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
                            $records->each->update(['is_active' => false]);
                                ->title(__('recommendation_blocks.bulk_deactivated_success'))
                    BulkAction::make('show')
                        ->label(__('recommendation_blocks.show_selected'))
                            $records->each->update(['is_visible' => true]);
                                ->title(__('recommendation_blocks.bulk_shown_success'))
                    BulkAction::make('hide')
                        ->label(__('recommendation_blocks.hide_selected'))
                            $records->each->update(['is_visible' => false]);
                                ->title(__('recommendation_blocks.bulk_hidden_success'))
            ->defaultSort('priority', 'desc');
     * Get the relations for this resource.
     * @return array
    public static function getRelations(): array
        return [
            //
        ];
     * Get the pages for this resource.
    public static function getPages(): array
            'index' => Pages\ListRecommendationBlocks::route('/'),
            'create' => Pages\CreateRecommendationBlock::route('/create'),
            'view' => Pages\ViewRecommendationBlock::route('/{record}'),
            'edit' => Pages\EditRecommendationBlock::route('/{record}/edit'),
}
