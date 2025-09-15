<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductHistoryResource\Pages;
use App\Models\ProductHistory;
use App\Models\Product;
use App\Models\User;
use App\Enums\NavigationGroup;
use Filament\Forms;
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
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * ProductHistoryResource
 * 
 * Filament v4 resource for ProductHistory management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ProductHistoryResource extends Resource
{
    protected static ?string $model = ProductHistory::class;
    
    /** @var UnitEnum|string|null */
        protected static string | UnitEnum | null $navigationGroup = NavigationGroup::
    
    protected static ?int $navigationSort = 11;
    protected static ?string $recordTitleAttribute = 'product_name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('product_history.title');
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
        return __('product_history.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('product_history.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('product_history.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('product_id')
                                ->label(__('product_history.product'))
                                ->relationship('product', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('product_name', $product->name);
                                            $set('product_sku', $product->sku);
                                        }
                                    }
                                }),
                            
                            TextInput::make('product_name')
                                ->label(__('product_history.product_name'))
                                ->required()
                                ->maxLength(255)
                                ->disabled(),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('product_sku')
                                ->label(__('product_history.product_sku'))
                                ->maxLength(255)
                                ->disabled(),
                            
                            Select::make('user_id')
                                ->label(__('product_history.user'))
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $user = User::find($state);
                                        if ($user) {
                                            $set('user_name', $user->name);
                                            $set('user_email', $user->email);
                                        }
                                    }
                                }),
                        ]),
                ]),
            
            Section::make(__('product_history.change_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('action')
                                ->label(__('product_history.action'))
                                ->options([
                                    'created' => __('product_history.actions.created'),
                                    'updated' => __('product_history.actions.updated'),
                                    'deleted' => __('product_history.actions.deleted'),
                                    'restored' => __('product_history.actions.restored'),
                                    'price_changed' => __('product_history.actions.price_changed'),
                                    'stock_changed' => __('product_history.actions.stock_changed'),
                                    'status_changed' => __('product_history.actions.status_changed'),
                                    'category_changed' => __('product_history.actions.category_changed'),
                                    'image_changed' => __('product_history.actions.image_changed'),
                                    'custom' => __('product_history.actions.custom'),
                                ])
                                ->required()
                                ->default('updated'),
                            
                            TextInput::make('field_name')
                                ->label(__('product_history.field_name'))
                                ->maxLength(255)
                                ->helperText(__('product_history.field_name_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('old_value')
                                ->label(__('product_history.old_value'))
                                ->maxLength(500)
                                ->helperText(__('product_history.old_value_help')),
                            
                            TextInput::make('new_value')
                                ->label(__('product_history.new_value'))
                                ->maxLength(500)
                                ->helperText(__('product_history.new_value_help')),
                        ]),
                    
                    Textarea::make('description')
                        ->label(__('product_history.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->helperText(__('product_history.description_help'))
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('product_history.change_details'))
                ->schema([
                    KeyValue::make('change_details')
                        ->label(__('product_history.change_details'))
                        ->keyLabel(__('product_history.change_details_key'))
                        ->valueLabel(__('product_history.change_details_value'))
                        ->addActionLabel(__('product_history.add_change_details_field'))
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('product_history.context_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('ip_address')
                                ->label(__('product_history.ip_address'))
                                ->maxLength(45)
                                ->rules(['ip'])
                                ->helperText(__('product_history.ip_address_help')),
                            
                            TextInput::make('user_agent')
                                ->label(__('product_history.user_agent'))
                                ->maxLength(500)
                                ->helperText(__('product_history.user_agent_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('device_type')
                                ->label(__('product_history.device_type'))
                                ->maxLength(50)
                                ->helperText(__('product_history.device_type_help')),
                            
                            TextInput::make('browser')
                                ->label(__('product_history.browser'))
                                ->maxLength(100)
                                ->helperText(__('product_history.browser_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('os')
                                ->label(__('product_history.os'))
                                ->maxLength(100)
                                ->helperText(__('product_history.os_help')),
                            
                            TextInput::make('country')
                                ->label(__('product_history.country'))
                                ->maxLength(100)
                                ->helperText(__('product_history.country_help')),
                        ]),
                ]),
            
            Section::make(__('product_history.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_important')
                                ->label(__('product_history.is_important'))
                                ->default(false),
                            
                            Toggle::make('is_system')
                                ->label(__('product_history.is_system'))
                                ->default(false),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('severity')
                                ->label(__('product_history.severity'))
                                ->maxLength(20)
                                ->helperText(__('product_history.severity_help')),
                            
                            TextInput::make('category')
                                ->label(__('product_history.category'))
                                ->maxLength(50)
                                ->helperText(__('product_history.category_help')),
                        ]),
                    
                    Textarea::make('notes')
                        ->label(__('product_history.notes'))
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
                TextColumn::make('product_name')
                    ->label(__('product_history.product_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(100),
                
                TextColumn::make('product_sku')
                    ->label(__('product_history.product_sku'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                
                TextColumn::make('user.name')
                    ->label(__('product_history.user'))
                    ->sortable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('action')
                    ->label(__('product_history.action'))
                    ->formatStateUsing(fn (string $state): string => __("product_history.actions.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'green',
                        'updated' => 'blue',
                        'deleted' => 'red',
                        'restored' => 'purple',
                        'price_changed' => 'orange',
                        'stock_changed' => 'yellow',
                        'status_changed' => 'pink',
                        'category_changed' => 'indigo',
                        'image_changed' => 'teal',
                        'custom' => 'gray',
                        default => 'gray',
                    }),
                
                TextColumn::make('field_name')
                    ->label(__('product_history.field_name'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('blue')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('old_value')
                    ->label(__('product_history.old_value'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('new_value')
                    ->label(__('product_history.new_value'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('description')
                    ->label(__('product_history.description'))
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('ip_address')
                    ->label(__('product_history.ip_address'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('device_type')
                    ->label(__('product_history.device_type'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('purple')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('browser')
                    ->label(__('product_history.browser'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('os')
                    ->label(__('product_history.os'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('country')
                    ->label(__('product_history.country'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('green')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('severity')
                    ->label(__('product_history.severity'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'green',
                        'medium' => 'yellow',
                        'high' => 'orange',
                        'critical' => 'red',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('category')
                    ->label(__('product_history.category'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('blue')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_important')
                    ->label(__('product_history.is_important'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_system')
                    ->label(__('product_history.is_system'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label(__('product_history.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('product_history.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label(__('product_history.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('user_id')
                    ->label(__('product_history.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('action')
                    ->label(__('product_history.action'))
                    ->options([
                        'created' => __('product_history.actions.created'),
                        'updated' => __('product_history.actions.updated'),
                        'deleted' => __('product_history.actions.deleted'),
                        'restored' => __('product_history.actions.restored'),
                        'price_changed' => __('product_history.actions.price_changed'),
                        'stock_changed' => __('product_history.actions.stock_changed'),
                        'status_changed' => __('product_history.actions.status_changed'),
                        'category_changed' => __('product_history.actions.category_changed'),
                        'image_changed' => __('product_history.actions.image_changed'),
                        'custom' => __('product_history.actions.custom'),
                    ]),
                
                SelectFilter::make('field_name')
                    ->label(__('product_history.field_name'))
                    ->options(function () {
                        return ProductHistory::distinct('field_name')
                            ->pluck('field_name', 'field_name')
                            ->filter()
                            ->toArray();
                    })
                    ->searchable(),
                
                SelectFilter::make('severity')
                    ->label(__('product_history.severity'))
                    ->options([
                        'low' => __('product_history.severities.low'),
                        'medium' => __('product_history.severities.medium'),
                        'high' => __('product_history.severities.high'),
                        'critical' => __('product_history.severities.critical'),
                    ]),
                
                SelectFilter::make('category')
                    ->label(__('product_history.category'))
                    ->options(function () {
                        return ProductHistory::distinct('category')
                            ->pluck('category', 'category')
                            ->filter()
                            ->toArray();
                    })
                    ->searchable(),
                
                TernaryFilter::make('is_important')
                    ->label(__('product_history.is_important'))
                    ->boolean()
                    ->trueLabel(__('product_history.important_only'))
                    ->falseLabel(__('product_history.non_important_only'))
                    ->native(false),
                
                TernaryFilter::make('is_system')
                    ->label(__('product_history.is_system'))
                    ->boolean()
                    ->trueLabel(__('product_history.system_only'))
                    ->falseLabel(__('product_history.user_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                TableAction::make('mark_important')
                    ->label(__('product_history.mark_important'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (ProductHistory $record): bool => !$record->is_important)
                    ->action(function (ProductHistory $record): void {
                        $record->update(['is_important' => true]);
                        
                        Notification::make()
                            ->title(__('product_history.marked_as_important_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                TableAction::make('unmark_important')
                    ->label(__('product_history.unmark_important'))
                    ->icon('heroicon-o-star')
                    ->color('gray')
                    ->visible(fn (ProductHistory $record): bool => $record->is_important)
                    ->action(function (ProductHistory $record): void {
                        $record->update(['is_important' => false]);
                        
                        Notification::make()
                            ->title(__('product_history.unmarked_as_important_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                TableAction::make('mark_system')
                    ->label(__('product_history.mark_system'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('info')
                    ->visible(fn (ProductHistory $record): bool => !$record->is_system)
                    ->action(function (ProductHistory $record): void {
                        $record->update(['is_system' => true]);
                        
                        Notification::make()
                            ->title(__('product_history.marked_as_system_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                TableAction::make('unmark_system')
                    ->label(__('product_history.unmark_system'))
                    ->icon('heroicon-o-user')
                    ->color('gray')
                    ->visible(fn (ProductHistory $record): bool => $record->is_system)
                    ->action(function (ProductHistory $record): void {
                        $record->update(['is_system' => false]);
                        
                        Notification::make()
                            ->title(__('product_history.unmarked_as_system_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    BulkAction::make('mark_important')
                        ->label(__('product_history.mark_important_selected'))
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_important' => true]);
                            
                            Notification::make()
                                ->title(__('product_history.bulk_marked_as_important_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('unmark_important')
                        ->label(__('product_history.unmark_important_selected'))
                        ->icon('heroicon-o-star')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_important' => false]);
                            
                            Notification::make()
                                ->title(__('product_history.bulk_unmarked_as_important_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('mark_system')
                        ->label(__('product_history.mark_system_selected'))
                        ->icon('heroicon-o-cog-6-tooth')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_system' => true]);
                            
                            Notification::make()
                                ->title(__('product_history.bulk_marked_as_system_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('unmark_system')
                        ->label(__('product_history.unmark_system_selected'))
                        ->icon('heroicon-o-user')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_system' => false]);
                            
                            Notification::make()
                                ->title(__('product_history.bulk_unmarked_as_system_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListProductHistory::route('/'),
            'create' => Pages\CreateProductHistory::route('/create'),
            'view' => Pages\ViewProductHistory::route('/{record}'),
            'edit' => Pages\EditProductHistory::route('/{record}/edit'),
        ];
    }
}
