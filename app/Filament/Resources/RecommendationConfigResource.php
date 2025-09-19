<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Enums\NavigationGroup;
use App\Filament\Resources\RecommendationConfigResource\Pages;
use App\Models\Category;
use App\Models\Product;
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
 * RecommendationConfigResource
 *
 * Filament v4 resource for RecommendationConfig management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class RecommendationConfigResource extends Resource
{
    protected static ?string $model = RecommendationConfig::class;
    /**
    // protected static $navigationGroup = NavigationGroup::Products;
    protected static ?int $navigationSort = 12;
    protected static ?string $recordTitleAttribute = 'name';
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('recommendation_configs.title');
    }
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
    public static function getNavigationGroup(): ?string
        return "Products";
     * Handle getPluralModelLabel functionality with proper error handling.
    public static function getPluralModelLabel(): string
        return __('recommendation_configs.plural');
     * Handle getModelLabel functionality with proper error handling.
    public static function getModelLabel(): string
        return __('recommendation_configs.single');
     * Configure the Filament form schema with fields and validation.
     * @param Form $schema
     * @return Form
    public static function form(Schema $schema): Schema
    {$state}"))
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
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('max_results')
                    ->label(__('recommendation_configs.max_results'))
                TextColumn::make('decay_factor')
                    ->label(__('recommendation_configs.decay_factor'))
                TextColumn::make('products_count')
                    ->label(__('recommendation_configs.products_count'))
                    ->counts('products')
                TextColumn::make('categories_count')
                    ->label(__('recommendation_configs.categories_count'))
                    ->counts('categories')
                IconColumn::make('exclude_out_of_stock')
                    ->label(__('recommendation_configs.exclude_out_of_stock'))
                    ->boolean()
                IconColumn::make('exclude_inactive')
                    ->label(__('recommendation_configs.exclude_inactive'))
                TextColumn::make('price_weight')
                    ->label(__('recommendation_configs.price_weight'))
                TextColumn::make('rating_weight')
                    ->label(__('recommendation_configs.rating_weight'))
                TextColumn::make('popularity_weight')
                    ->label(__('recommendation_configs.popularity_weight'))
                TextColumn::make('recency_weight')
                    ->label(__('recommendation_configs.recency_weight'))
                TextColumn::make('category_weight')
                    ->label(__('recommendation_configs.category_weight'))
                TextColumn::make('custom_weight')
                    ->label(__('recommendation_configs.custom_weight'))
                TextColumn::make('cache_duration')
                    ->label(__('recommendation_configs.cache_duration'))
                    ->suffix(' min')
                IconColumn::make('is_active')
                    ->label(__('recommendation_configs.is_active'))
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label(__('recommendation_configs.is_default'))
                TextColumn::make('sort_order')
                    ->label(__('recommendation_configs.sort_order'))
                TextColumn::make('created_at')
                    ->label(__('recommendation_configs.created_at'))
                    ->dateTime()
                TextColumn::make('updated_at')
                    ->label(__('recommendation_configs.updated_at'))
            ])
            ->filters([
                SelectFilter::make('algorithm_type')
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
                    ->trueLabel(__('recommendation_configs.active_only'))
                    ->falseLabel(__('recommendation_configs.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_default')
                    ->trueLabel(__('recommendation_configs.default_only'))
                    ->falseLabel(__('recommendation_configs.non_default_only'))
                TernaryFilter::make('exclude_out_of_stock')
                    ->trueLabel(__('recommendation_configs.exclude_out_of_stock_only'))
                    ->falseLabel(__('recommendation_configs.include_out_of_stock_only'))
                TernaryFilter::make('exclude_inactive')
                    ->trueLabel(__('recommendation_configs.exclude_inactive_only'))
                    ->falseLabel(__('recommendation_configs.include_inactive_only'))
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
                        // Remove default from other recommendation configs
                        RecommendationConfig::where('is_default', true)->update(['is_default' => false]);
                        // Set this recommendation config as default
                        $record->update(['is_default' => true]);
                            ->title(__('recommendation_configs.set_as_default_successfully'))
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
                            $records->each->update(['is_active' => false]);
                                ->title(__('recommendation_configs.bulk_deactivated_success'))
            ->defaultSort('sort_order');
     * Get the relations for this resource.
     * @return array
    public static function getRelations(): array
        return [
            //
        ];
     * Get the pages for this resource.
    public static function getPages(): array
            'index' => Pages\ListRecommendationConfigs::route('/'),
            'create' => Pages\CreateRecommendationConfig::route('/create'),
            'view' => Pages\ViewRecommendationConfig::route('/{record}'),
            'edit' => Pages\EditRecommendationConfig::route('/{record}/edit'),
}
