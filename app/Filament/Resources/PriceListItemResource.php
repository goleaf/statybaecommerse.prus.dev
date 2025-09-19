<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PriceListItemResource\Pages;
use App\Models\PriceListItem;
use App\Models\PriceList;
use App\Models\Product;
use App\Enums\NavigationGroup;
use Filament\Forms;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
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
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Form;

/**
 * PriceListItemResource
 * 
 * Filament v4 resource for PriceListItem management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class PriceListItemResource extends Resource
{
    protected static ?string $model = PriceListItem::class;
    
    protected static $navigationGroup = 'Products';
    
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
        return __('price_list_items.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('price_list_items.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('price_list_items.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('price_list_id')
                                ->label(__('price_list_items.price_list'))
                                ->relationship('priceList', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('code')
                                        ->required()
                                        ->maxLength(50)
                                        ->rules(['alpha_dash']),
                                    Textarea::make('description')
                                        ->maxLength(500),
                                ]),
                            
                            Select::make('product_id')
                                ->label(__('price_list_items.product'))
                                ->relationship('product', 'name')
                                ->required()
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
                ]),
            
            Section::make(__('price_list_items.pricing'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('base_price')
                                ->label(__('price_list_items.base_price'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->required()
                                ->prefix('€')
                                ->helperText(__('price_list_items.base_price_help')),
                            
                            TextInput::make('discount_price')
                                ->label(__('price_list_items.discount_price'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->prefix('€')
                                ->helperText(__('price_list_items.discount_price_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('discount_percentage')
                                ->label(__('price_list_items.discount_percentage'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(100)
                                ->suffix('%')
                                ->helperText(__('price_list_items.discount_percentage_help')),
                            
                            TextInput::make('min_quantity')
                                ->label(__('price_list_items.min_quantity'))
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->helperText(__('price_list_items.min_quantity_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('max_quantity')
                                ->label(__('price_list_items.max_quantity'))
                                ->numeric()
                                ->minValue(1)
                                ->helperText(__('price_list_items.max_quantity_help')),
                            
                            TextInput::make('sort_order')
                                ->label(__('price_list_items.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                        ]),
                ]),
            
            Section::make(__('price_list_items.tiered_pricing'))
                ->schema([
                    Forms\Components\Repeater::make('tiered_pricing')
                        ->label(__('price_list_items.tiered_pricing'))
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('min_quantity')
                                        ->label(__('price_list_items.tier_min_quantity'))
                                        ->numeric()
                                        ->minValue(1)
                                        ->required(),
                                    
                                    TextInput::make('max_quantity')
                                        ->label(__('price_list_items.tier_max_quantity'))
                                        ->numeric()
                                        ->minValue(1),
                                    
                                    TextInput::make('price')
                                        ->label(__('price_list_items.tier_price'))
                                        ->numeric()
                                        ->step(0.01)
                                        ->minValue(0)
                                        ->required()
                                        ->prefix('€'),
                                ]),
                        ])
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['min_quantity'] ? "Qty {$state['min_quantity']}+" : null)
                        ->addActionLabel(__('price_list_items.add_tier'))
                        ->helperText(__('price_list_items.tiered_pricing_help')),
                ]),
            
            Section::make(__('price_list_items.validity'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('valid_from')
                                ->label(__('price_list_items.valid_from'))
                                ->default(now())
                                ->helperText(__('price_list_items.valid_from_help')),
                            
                            DateTimePicker::make('valid_until')
                                ->label(__('price_list_items.valid_until'))
                                ->after('valid_from')
                                ->helperText(__('price_list_items.valid_until_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('price_list_items.is_active'))
                                ->default(true),
                            
                            Toggle::make('is_featured')
                                ->label(__('price_list_items.is_featured'))
                                ->helperText(__('price_list_items.is_featured_help')),
                        ]),
                ]),
            
            Section::make(__('price_list_items.settings'))
                ->schema([
                    Textarea::make('notes')
                        ->label(__('price_list_items.notes'))
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
                TextColumn::make('priceList.name')
                    ->label(__('price_list_items.price_list'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('product.name')
                    ->label(__('price_list_items.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                TextColumn::make('product.sku')
                    ->label(__('price_list_items.product_sku'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('base_price')
                    ->label(__('price_list_items.base_price'))
                    ->formatStateUsing(fn (?float $state): string => $state ? "€{$state}" : '€0')
                    ->sortable()
                    ->alignCenter(),
                
                TextColumn::make('discount_price')
                    ->label(__('price_list_items.discount_price'))
                    ->formatStateUsing(fn (?float $state): string => $state ? "€{$state}" : '€0')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('discount_percentage')
                    ->label(__('price_list_items.discount_percentage'))
                    ->formatStateUsing(fn (?float $state): string => $state ? "{$state}%" : '0%')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('min_quantity')
                    ->label(__('price_list_items.min_quantity'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('max_quantity')
                    ->label(__('price_list_items.max_quantity'))
                    ->formatStateUsing(fn (?int $state): string => $state ? (string) $state : '∞')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_active')
                    ->label(__('price_list_items.is_active'))
                    ->boolean()
                    ->sortable(),
                
                IconColumn::make('is_featured')
                    ->label(__('price_list_items.is_featured'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('valid_from')
                    ->label(__('price_list_items.valid_from'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('valid_until')
                    ->label(__('price_list_items.valid_until'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('sort_order')
                    ->label(__('price_list_items.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label(__('price_list_items.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('price_list_items.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('price_list_id')
                    ->label(__('price_list_items.price_list'))
                    ->relationship('priceList', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('product_id')
                    ->label(__('price_list_items.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                
                TernaryFilter::make('is_active')
                    ->label(__('price_list_items.is_active'))
                    ->boolean()
                    ->trueLabel(__('price_list_items.active_only'))
                    ->falseLabel(__('price_list_items.inactive_only'))
                    ->native(false),
                
                TernaryFilter::make('is_featured')
                    ->label(__('price_list_items.is_featured'))
                    ->boolean()
                    ->trueLabel(__('price_list_items.featured_only'))
                    ->falseLabel(__('price_list_items.non_featured_only'))
                    ->native(false),
                
                Filter::make('valid_now')
                    ->label(__('price_list_items.valid_now'))
                    ->query(fn (Builder $query): Builder => $query->where('valid_from', '<=', now())->where(function (Builder $query): void {
                        $query->whereNull('valid_until')->orWhere('valid_until', '>=', now());
                    }))
                    ->toggle(),
                
                Filter::make('expired')
                    ->label(__('price_list_items.expired'))
                    ->query(fn (Builder $query): Builder => $query->where('valid_until', '<', now()))
                    ->toggle(),
                
                Filter::make('has_discount')
                    ->label(__('price_list_items.has_discount'))
                    ->query(fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                        $query->whereNotNull('discount_price')->orWhereNotNull('discount_percentage');
                    }))
                    ->toggle(),
            ])
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
                    ->action(function (PriceListItem $record): void {
                        $record->update(['is_featured' => !$record->is_featured]);
                        
                        Notification::make()
                            ->title($record->is_featured ? __('price_list_items.featured_successfully') : __('price_list_items.unfeatured_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
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
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            
                            Notification::make()
                                ->title(__('price_list_items.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('feature')
                        ->label(__('price_list_items.feature_selected'))
                        ->icon('heroicon-o-star')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => true]);
                            
                            Notification::make()
                                ->title(__('price_list_items.bulk_featured_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('unfeature')
                        ->label(__('price_list_items.unfeature_selected'))
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => false]);
                            
                            Notification::make()
                                ->title(__('price_list_items.bulk_unfeatured_success'))
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
            'index' => Pages\ListPriceListItems::route('/'),
            'create' => Pages\CreatePriceListItem::route('/create'),
            'view' => Pages\ViewPriceListItem::route('/{record}'),
            'edit' => Pages\EditPriceListItem::route('/{record}/edit'),
        ];
    }
}
