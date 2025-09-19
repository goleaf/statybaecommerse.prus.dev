<?php

declare(strict_types=1);
namespace App\Filament\Resources;
use App\Filament\Resources\PriceListItemResource\Pages;
use App\Models\PriceListItem;
use App\Models\PriceList;
use App\Models\Product;
use App\Enums\NavigationGroup;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
/**
 * PriceListItemResource
 * 
 * Filament v4 resource for PriceListItem management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class PriceListItemResource extends Resource
{
    protected static ?string $model = PriceListItem::class;
    
    // protected static $navigationGroup = NavigationGroup::Products;
    protected static ?int $navigationSort = 16;
    protected static ?string $recordTitleAttribute = 'product.name';
    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('price_list_items.title');
    }
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
    public static function getNavigationGroup(): ?string
        return "Products";
     * Handle getPluralModelLabel functionality with proper error handling.
    public static function getPluralModelLabel(): string
        return __('price_list_items.plural');
     * Handle getModelLabel functionality with proper error handling.
    public static function getModelLabel(): string
        return __('price_list_items.single');
     * Configure the Filament form schema with fields and validation.
     * @param Form $schema
     * @return Form
    public static function form(Schema $schema): Schema
    {$state['min_quantity']}+" : null)
                        ->addActionLabel(__('price_list_items.add_tier'))
                        ->helperText(__('price_list_items.tiered_pricing_help')),
            Section::make(__('price_list_items.validity'))
                            DateTimePicker::make('valid_from')
                                ->label(__('price_list_items.valid_from'))
                                ->default(now())
                                ->helperText(__('price_list_items.valid_from_help')),
                            DateTimePicker::make('valid_until')
                                ->label(__('price_list_items.valid_until'))
                                ->after('valid_from')
                                ->helperText(__('price_list_items.valid_until_help')),
                            Toggle::make('is_active')
                                ->label(__('price_list_items.is_active'))
                                ->default(true),
                            Toggle::make('is_featured')
                                ->label(__('price_list_items.is_featured'))
                                ->helperText(__('price_list_items.is_featured_help')),
            Section::make(__('price_list_items.settings'))
                    Textarea::make('notes')
                        ->label(__('price_list_items.notes'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
        ]);
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
    public static function table(Table $table): Table
    {$state}" : '€0')
                    ->alignCenter(),
                TextColumn::make('discount_price')
                    ->label(__('price_list_items.discount_price'))
                    ->alignCenter()
                TextColumn::make('discount_percentage')
                    ->label(__('price_list_items.discount_percentage'))
                    ->formatStateUsing(fn (?float $state): string => $state ? "{$state}%" : '0%')
                TextColumn::make('min_quantity')
                    ->label(__('price_list_items.min_quantity'))
                    ->numeric()
                TextColumn::make('max_quantity')
                    ->label(__('price_list_items.max_quantity'))
                    ->formatStateUsing(fn (?int $state): string => $state ? (string) $state : '∞')
                IconColumn::make('is_active')
                    ->label(__('price_list_items.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label(__('price_list_items.is_featured'))
                TextColumn::make('valid_from')
                    ->label(__('price_list_items.valid_from'))
                    ->dateTime()
                TextColumn::make('valid_until')
                    ->label(__('price_list_items.valid_until'))
                TextColumn::make('sort_order')
                    ->label(__('price_list_items.sort_order'))
                TextColumn::make('created_at')
                    ->label(__('price_list_items.created_at'))
                TextColumn::make('updated_at')
                    ->label(__('price_list_items.updated_at'))
            ])
            ->filters([
                SelectFilter::make('price_list_id')
                    ->relationship('priceList', 'name')
                    ->preload(),
                SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                TernaryFilter::make('is_active')
                    ->trueLabel(__('price_list_items.active_only'))
                    ->falseLabel(__('price_list_items.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_featured')
                    ->trueLabel(__('price_list_items.featured_only'))
                    ->falseLabel(__('price_list_items.non_featured_only'))
                Filter::make('valid_now')
                    ->label(__('price_list_items.valid_now'))
                    ->query(fn (Builder $query): Builder => $query->where('valid_from', '<=', now())->where(function (Builder $query): void {
                        $query->whereNull('valid_until')->orWhere('valid_until', '>=', now());
                    }))
                    ->toggle(),
                Filter::make('expired')
                    ->label(__('price_list_items.expired'))
                    ->query(fn (Builder $query): Builder => $query->where('valid_until', '<', now()))
                Filter::make('has_discount')
                    ->label(__('price_list_items.has_discount'))
                    ->query(fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                        $query->whereNotNull('discount_price')->orWhereNotNull('discount_percentage');
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn (PriceListItem $record): string => $record->is_active ? __('price_list_items.deactivate') : __('price_list_items.activate'))
                    ->icon(fn (PriceListItem $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (PriceListItem $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (PriceListItem $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                        
                        Notification::make()
                            ->title($record->is_active ? __('price_list_items.activated_successfully') : __('price_list_items.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('toggle_featured')
                    ->label(fn (PriceListItem $record): string => $record->is_featured ? __('price_list_items.unfeature') : __('price_list_items.feature'))
                    ->icon(fn (PriceListItem $record): string => $record->is_featured ? 'heroicon-o-star' : 'heroicon-o-star')
                    ->color(fn (PriceListItem $record): string => $record->is_featured ? 'warning' : 'success')
                        $record->update(['is_featured' => !$record->is_featured]);
                            ->title($record->is_featured ? __('price_list_items.featured_successfully') : __('price_list_items.unfeatured_successfully'))
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('price_list_items.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('price_list_items.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('price_list_items.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                            $records->each->update(['is_active' => false]);
                                ->title(__('price_list_items.bulk_deactivated_success'))
                    BulkAction::make('feature')
                        ->label(__('price_list_items.feature_selected'))
                        ->icon('heroicon-o-star')
                            $records->each->update(['is_featured' => true]);
                                ->title(__('price_list_items.bulk_featured_success'))
                    BulkAction::make('unfeature')
                        ->label(__('price_list_items.unfeature_selected'))
                            $records->each->update(['is_featured' => false]);
                                ->title(__('price_list_items.bulk_unfeatured_success'))
            ->defaultSort('sort_order');
     * Get the relations for this resource.
     * @return array
    public static function getRelations(): array
        return [
            //
        ];
     * Get the pages for this resource.
    public static function getPages(): array
            'index' => Pages\ListPriceListItems::route('/'),
            'create' => Pages\CreatePriceListItem::route('/create'),
            'view' => Pages\ViewPriceListItem::route('/{record}'),
            'edit' => Pages\EditPriceListItem::route('/{record}/edit'),
}
