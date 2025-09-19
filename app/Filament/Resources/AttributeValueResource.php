<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeValueResource\Pages;
use App\Models\AttributeValue;
use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductVariant;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Form;

/**
 * AttributeValueResource
 * 
 * Filament v4 resource for AttributeValue management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class AttributeValueResource extends Resource
{
    protected static ?string $model = AttributeValue::class;
    
    /** @var UnitEnum|string|null */
        protected static $navigationGroup = NavigationGroup::
    
    ;
    protected static ?int $navigationSort = 9;
    protected static ?string $recordTitleAttribute = 'value';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('attribute_values.title');
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
        return __('attribute_values.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('attribute_values.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('attribute_values.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('attribute_id')
                                ->label(__('attribute_values.attribute'))
                                ->relationship('attribute', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $attribute = Attribute::find($state);
                                        if ($attribute) {
                                            $set('attribute_name', $attribute->name);
                                            $set('attribute_type', $attribute->type);
                                        }
                                    }
                                }),
                            
                            TextInput::make('attribute_name')
                                ->label(__('attribute_values.attribute_name'))
                                ->required()
                                ->maxLength(255)
                                ->disabled(),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            Select::make('valueable_type')
                                ->label(__('attribute_values.valueable_type'))
                                ->options([
                                    'product' => __('attribute_values.types.product'),
                                    'product_variant' => __('attribute_values.types.product_variant'),
                                ])
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    $set('valueable_id', null);
                                }),
                            
                            Select::make('valueable_id')
                                ->label(__('attribute_values.valueable_item'))
                                ->options(function (Forms\Get $get) {
                                    $type = $get('valueable_type');
                                    if ($type === 'product') {
                                        return Product::pluck('name', 'id');
                                    } elseif ($type === 'product_variant') {
                                        return ProductVariant::pluck('name', 'id');
                                    }
                                    return [];
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live(),
                        ]),
                ]),
            
            Section::make(__('attribute_values.value_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('value')
                                ->label(__('attribute_values.value'))
                                ->required()
                                ->maxLength(255),
                            
                            TextInput::make('display_value')
                                ->label(__('attribute_values.display_value'))
                                ->maxLength(255)
                                ->helperText(__('attribute_values.display_value_help')),
                        ]),
                    
                    Textarea::make('description')
                        ->label(__('attribute_values.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('attribute_values.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('attribute_values.is_active'))
                                ->default(true),
                            
                            Toggle::make('is_default')
                                ->label(__('attribute_values.is_default')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('sort_order')
                                ->label(__('attribute_values.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                            
                            Toggle::make('is_searchable')
                                ->label(__('attribute_values.is_searchable'))
                                ->default(false),
                        ]),
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
                TextColumn::make('attribute.name')
                    ->label(__('attribute_values.attribute'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('blue'),
                
                TextColumn::make('valueable_type')
                    ->label(__('attribute_values.type'))
                    ->formatStateUsing(fn (string $state): string => __("attribute_values.types.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'product' => 'green',
                        'product_variant' => 'purple',
                        default => 'gray',
                    }),
                
                TextColumn::make('valueable.name')
                    ->label(__('attribute_values.item'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                TextColumn::make('value')
                    ->label(__('attribute_values.value'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->weight('bold'),
                
                TextColumn::make('display_value')
                    ->label(__('attribute_values.display_value'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('description')
                    ->label(__('attribute_values.description'))
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_active')
                    ->label(__('attribute_values.is_active'))
                    ->boolean()
                    ->sortable(),
                
                IconColumn::make('is_default')
                    ->label(__('attribute_values.is_default'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_searchable')
                    ->label(__('attribute_values.is_searchable'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('sort_order')
                    ->label(__('attribute_values.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label(__('attribute_values.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('attribute_values.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('attribute_id')
                    ->label(__('attribute_values.attribute'))
                    ->relationship('attribute', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('valueable_type')
                    ->label(__('attribute_values.valueable_type'))
                    ->options([
                        'product' => __('attribute_values.types.product'),
                        'product_variant' => __('attribute_values.types.product_variant'),
                    ]),
                
                TernaryFilter::make('is_active')
                    ->label(__('attribute_values.is_active'))
                    ->boolean()
                    ->trueLabel(__('attribute_values.active_only'))
                    ->falseLabel(__('attribute_values.inactive_only'))
                    ->native(false),
                
                TernaryFilter::make('is_default')
                    ->label(__('attribute_values.is_default'))
                    ->boolean()
                    ->trueLabel(__('attribute_values.default_only'))
                    ->falseLabel(__('attribute_values.non_default_only'))
                    ->native(false),
                
                TernaryFilter::make('is_searchable')
                    ->label(__('attribute_values.is_searchable'))
                    ->boolean()
                    ->trueLabel(__('attribute_values.searchable_only'))
                    ->falseLabel(__('attribute_values.not_searchable'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                
                Action::make('toggle_active')
                    ->label(fn (AttributeValue $record): string => $record->is_active ? __('attribute_values.deactivate') : __('attribute_values.activate'))
                    ->icon(fn (AttributeValue $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (AttributeValue $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (AttributeValue $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                        
                        Notification::make()
                            ->title($record->is_active ? __('attribute_values.activated_successfully') : __('attribute_values.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                Action::make('set_default')
                    ->label(__('attribute_values.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (AttributeValue $record): bool => !$record->is_default)
                    ->action(function (AttributeValue $record): void {
                        // Remove default from other values for the same attribute and item
                        AttributeValue::where('attribute_id', $record->attribute_id)
                            ->where('valueable_type', $record->valueable_type)
                            ->where('valueable_id', $record->valueable_id)
                            ->where('is_default', true)
                            ->update(['is_default' => false]);
                        
                        // Set this value as default
                        $record->update(['is_default' => true]);
                        
                        Notification::make()
                            ->title(__('attribute_values.set_as_default_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    
                    BulkAction::make('activate')
                        ->label(__('attribute_values.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            
                            Notification::make()
                                ->title(__('attribute_values.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('deactivate')
                        ->label(__('attribute_values.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            
                            Notification::make()
                                ->title(__('attribute_values.bulk_deactivated_success'))
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
            'index' => Pages\ListAttributeValues::route('/'),
            'create' => Pages\CreateAttributeValue::route('/create'),
            'view' => Pages\ViewAttributeValue::route('/{record}'),
            'edit' => Pages\EditAttributeValue::route('/{record}/edit'),
        ];
    }
}
