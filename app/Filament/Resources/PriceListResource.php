<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PriceListResource\Pages;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\CustomerGroup;
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
use Filament\Forms\Components\Repeater;
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
use Filament\Forms\Form;

/**
 * PriceListResource
 * 
 * Filament v4 resource for PriceList management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class PriceListResource extends Resource
{
    protected static ?string $model = PriceList::class;
    
    protected static string | UnitEnum | null $navigationGroup = 'Products';
    
    protected static ?int $navigationSort = 15;
    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('price_lists.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Products->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('price_lists.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('price_lists.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('price_lists.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('price_lists.name'))
                                ->required()
                                ->maxLength(255),
                            
                            TextInput::make('code')
                                ->label(__('price_lists.code'))
                                ->required()
                                ->maxLength(50)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    
                    Textarea::make('description')
                        ->label(__('price_lists.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('price_lists.pricing_settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('pricing_type')
                                ->label(__('price_lists.pricing_type'))
                                ->options([
                                    'fixed' => __('price_lists.pricing_types.fixed'),
                                    'percentage' => __('price_lists.pricing_types.percentage'),
                                    'tiered' => __('price_lists.pricing_types.tiered'),
                                    'volume' => __('price_lists.pricing_types.volume'),
                                    'custom' => __('price_lists.pricing_types.custom'),
                                ])
                                ->required()
                                ->default('fixed'),
                            
                            TextInput::make('base_discount')
                                ->label(__('price_lists.base_discount'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(100)
                                ->default(0)
                                ->suffix('%')
                                ->helperText(__('price_lists.base_discount_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('min_order_value')
                                ->label(__('price_lists.min_order_value'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->default(0)
                                ->prefix('€')
                                ->helperText(__('price_lists.min_order_value_help')),
                            
                            TextInput::make('max_order_value')
                                ->label(__('price_lists.max_order_value'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->default(0)
                                ->prefix('€')
                                ->helperText(__('price_lists.max_order_value_help')),
                        ]),
                ]),
            
            Section::make(__('price_lists.targeting'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('customer_groups')
                                ->label(__('price_lists.customer_groups'))
                                ->relationship('customerGroups', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    Textarea::make('description')
                                        ->maxLength(500),
                                ]),
                            
                            Select::make('products')
                                ->label(__('price_lists.products'))
                                ->relationship('products', 'name')
                                ->multiple()
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
                    
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_public')
                                ->label(__('price_lists.is_public'))
                                ->default(false)
                                ->helperText(__('price_lists.is_public_help')),
                            
                            Toggle::make('is_default')
                                ->label(__('price_lists.is_default'))
                                ->helperText(__('price_lists.is_default_help')),
                        ]),
                ]),
            
            Section::make(__('price_lists.validity'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('valid_from')
                                ->label(__('price_lists.valid_from'))
                                ->default(now())
                                ->helperText(__('price_lists.valid_from_help')),
                            
                            DateTimePicker::make('valid_until')
                                ->label(__('price_lists.valid_until'))
                                ->after('valid_from')
                                ->helperText(__('price_lists.valid_until_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('price_lists.is_active'))
                                ->default(true),
                            
                            TextInput::make('sort_order')
                                ->label(__('price_lists.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                        ]),
                ]),
            
            Section::make(__('price_lists.tiered_pricing'))
                ->schema([
                    Repeater::make('tiered_pricing')
                        ->label(__('price_lists.tiered_pricing'))
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('min_quantity')
                                        ->label(__('price_lists.min_quantity'))
                                        ->numeric()
                                        ->minValue(1)
                                        ->required(),
                                    
                                    TextInput::make('max_quantity')
                                        ->label(__('price_lists.max_quantity'))
                                        ->numeric()
                                        ->minValue(1),
                                    
                                    TextInput::make('discount')
                                        ->label(__('price_lists.discount'))
                                        ->numeric()
                                        ->step(0.01)
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->required()
                                        ->suffix('%'),
                                ]),
                        ])
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['min_quantity'] ? "Qty {$state['min_quantity']}+" : null)
                        ->addActionLabel(__('price_lists.add_tier'))
                        ->visible(fn (Forms\Get $get): bool => $get('pricing_type') === 'tiered'),
                ]),
            
            Section::make(__('price_lists.volume_pricing'))
                ->schema([
                    Repeater::make('volume_pricing')
                        ->label(__('price_lists.volume_pricing'))
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('min_volume')
                                        ->label(__('price_lists.min_volume'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->prefix('€'),
                                    
                                    TextInput::make('max_volume')
                                        ->label(__('price_lists.max_volume'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->prefix('€'),
                                    
                                    TextInput::make('discount')
                                        ->label(__('price_lists.discount'))
                                        ->numeric()
                                        ->step(0.01)
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->required()
                                        ->suffix('%'),
                                ]),
                        ])
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['min_volume'] ? "€{$state['min_volume']}+" : null)
                        ->addActionLabel(__('price_lists.add_volume_tier'))
                        ->visible(fn (Forms\Get $get): bool => $get('pricing_type') === 'volume'),
                ]),
            
            Section::make(__('price_lists.settings'))
                ->schema([
                    Textarea::make('notes')
                        ->label(__('price_lists.notes'))
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
                    ->label(__('price_lists.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('code')
                    ->label(__('price_lists.code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                
                TextColumn::make('pricing_type')
                    ->label(__('price_lists.pricing_type'))
                    ->formatStateUsing(fn (string $state): string => __("price_lists.pricing_types.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fixed' => 'blue',
                        'percentage' => 'green',
                        'tiered' => 'purple',
                        'volume' => 'orange',
                        'custom' => 'gray',
                        default => 'gray',
                    }),
                
                TextColumn::make('base_discount')
                    ->label(__('price_lists.base_discount'))
                    ->formatStateUsing(fn (?float $state): string => $state ? "{$state}%" : '0%')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('min_order_value')
                    ->label(__('price_lists.min_order_value'))
                    ->formatStateUsing(fn (?float $state): string => $state ? "€{$state}" : '€0')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('max_order_value')
                    ->label(__('price_lists.max_order_value'))
                    ->formatStateUsing(fn (?float $state): string => $state ? "€{$state}" : '€∞')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('customer_groups_count')
                    ->label(__('price_lists.customer_groups_count'))
                    ->counts('customerGroups')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('products_count')
                    ->label(__('price_lists.products_count'))
                    ->counts('products')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_public')
                    ->label(__('price_lists.is_public'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_default')
                    ->label(__('price_lists.is_default'))
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('valid_from')
                    ->label(__('price_lists.valid_from'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('valid_until')
                    ->label(__('price_lists.valid_until'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_active')
                    ->label(__('price_lists.is_active'))
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('sort_order')
                    ->label(__('price_lists.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label(__('price_lists.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('price_lists.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('pricing_type')
                    ->label(__('price_lists.pricing_type'))
                    ->options([
                        'fixed' => __('price_lists.pricing_types.fixed'),
                        'percentage' => __('price_lists.pricing_types.percentage'),
                        'tiered' => __('price_lists.pricing_types.tiered'),
                        'volume' => __('price_lists.pricing_types.volume'),
                        'custom' => __('price_lists.pricing_types.custom'),
                    ]),
                
                TernaryFilter::make('is_active')
                    ->label(__('price_lists.is_active'))
                    ->boolean()
                    ->trueLabel(__('price_lists.active_only'))
                    ->falseLabel(__('price_lists.inactive_only'))
                    ->native(false),
                
                TernaryFilter::make('is_public')
                    ->label(__('price_lists.is_public'))
                    ->boolean()
                    ->trueLabel(__('price_lists.public_only'))
                    ->falseLabel(__('price_lists.private_only'))
                    ->native(false),
                
                TernaryFilter::make('is_default')
                    ->label(__('price_lists.is_default'))
                    ->boolean()
                    ->trueLabel(__('price_lists.default_only'))
                    ->falseLabel(__('price_lists.non_default_only'))
                    ->native(false),
                
                Filter::make('valid_now')
                    ->label(__('price_lists.valid_now'))
                    ->query(fn (Builder $query): Builder => $query->where('valid_from', '<=', now())->where(function (Builder $query): void {
                        $query->whereNull('valid_until')->orWhere('valid_until', '>=', now());
                    }))
                    ->toggle(),
                
                Filter::make('expired')
                    ->label(__('price_lists.expired'))
                    ->query(fn (Builder $query): Builder => $query->where('valid_until', '<', now()))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                
                Action::make('toggle_active')
                    ->label(fn (PriceList $record): string => $record->is_active ? __('price_lists.deactivate') : __('price_lists.activate'))
                    ->icon(fn (PriceList $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (PriceList $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (PriceList $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                        
                        Notification::make()
                            ->title($record->is_active ? __('price_lists.activated_successfully') : __('price_lists.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                Action::make('set_default')
                    ->label(__('price_lists.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (PriceList $record): bool => !$record->is_default)
                    ->action(function (PriceList $record): void {
                        // Remove default from other price lists
                        PriceList::where('is_default', true)->update(['is_default' => false]);
                        
                        // Set this price list as default
                        $record->update(['is_default' => true]);
                        
                        Notification::make()
                            ->title(__('price_lists.set_as_default_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    
                    BulkAction::make('activate')
                        ->label(__('price_lists.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            
                            Notification::make()
                                ->title(__('price_lists.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('deactivate')
                        ->label(__('price_lists.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            
                            Notification::make()
                                ->title(__('price_lists.bulk_deactivated_success'))
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
            'index' => Pages\ListPriceLists::route('/'),
            'create' => Pages\CreatePriceList::route('/create'),
            'view' => Pages\ViewPriceList::route('/{record}'),
            'edit' => Pages\EditPriceList::route('/{record}/edit'),
        ];
    }
}
