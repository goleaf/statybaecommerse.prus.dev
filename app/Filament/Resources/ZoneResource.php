<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ZoneResource\Pages;
use App\Models\Zone;
use App\Models\Country;
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
use Filament\Forms\Components\Repeater;
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
 * ZoneResource
 * 
 * Filament v4 resource for Zone management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ZoneResource extends Resource
{
    protected static ?string $model = Zone::class;
    
    /** @var UnitEnum|string|null */
        protected static string | UnitEnum | null $navigationGroup = NavigationGroup::
    
    ;
    protected static ?int $navigationSort = 6;
    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('zones.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::System->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('zones.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('zones.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('zones.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('zones.name'))
                                ->required()
                                ->maxLength(255),
                            
                            TextInput::make('code')
                                ->label(__('zones.code'))
                                ->required()
                                ->maxLength(10)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    
                    Textarea::make('description')
                        ->label(__('zones.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('zones.countries'))
                ->schema([
                    Select::make('countries')
                        ->label(__('zones.countries'))
                        ->relationship('countries', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('zones.shipping_settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('shipping_cost')
                                ->label(__('zones.shipping_cost'))
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01)
                                ->minValue(0),
                            
                            TextInput::make('free_shipping_threshold')
                                ->label(__('zones.free_shipping_threshold'))
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01)
                                ->minValue(0),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('estimated_delivery_days')
                                ->label(__('zones.estimated_delivery_days'))
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(365),
                            
                            Toggle::make('supports_express_shipping')
                                ->label(__('zones.supports_express_shipping')),
                        ]),
                ]),
            
            Section::make(__('zones.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('zones.is_active'))
                                ->default(true),
                            
                            Toggle::make('is_default')
                                ->label(__('zones.is_default')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('sort_order')
                                ->label(__('zones.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                            
                            Select::make('type')
                                ->label(__('zones.type'))
                                ->options([
                                    'domestic' => __('zones.types.domestic'),
                                    'international' => __('zones.types.international'),
                                    'regional' => __('zones.types.regional'),
                                ])
                                ->default('domestic'),
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
                TextColumn::make('name')
                    ->label(__('zones.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('code')
                    ->label(__('zones.code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                
                TextColumn::make('type')
                    ->label(__('zones.type'))
                    ->formatStateUsing(fn (string $state): string => __("zones.types.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'domestic' => 'green',
                        'international' => 'blue',
                        'regional' => 'purple',
                        default => 'gray',
                    }),
                
                TextColumn::make('countries_count')
                    ->label(__('zones.countries_count'))
                    ->counts('countries')
                    ->sortable(),
                
                TextColumn::make('shipping_cost')
                    ->label(__('zones.shipping_cost'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('free_shipping_threshold')
                    ->label(__('zones.free_shipping_threshold'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('estimated_delivery_days')
                    ->label(__('zones.estimated_delivery_days'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('supports_express_shipping')
                    ->label(__('zones.supports_express_shipping'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_active')
                    ->label(__('zones.is_active'))
                    ->boolean()
                    ->sortable(),
                
                IconColumn::make('is_default')
                    ->label(__('zones.is_default'))
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('sort_order')
                    ->label(__('zones.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label(__('zones.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('zones.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('zones.type'))
                    ->options([
                        'domestic' => __('zones.types.domestic'),
                        'international' => __('zones.types.international'),
                        'regional' => __('zones.types.regional'),
                    ]),
                
                TernaryFilter::make('is_active')
                    ->label(__('zones.is_active'))
                    ->boolean()
                    ->trueLabel(__('zones.active_only'))
                    ->falseLabel(__('zones.inactive_only'))
                    ->native(false),
                
                TernaryFilter::make('is_default')
                    ->label(__('zones.is_default'))
                    ->boolean()
                    ->trueLabel(__('zones.default_only'))
                    ->falseLabel(__('zones.non_default_only'))
                    ->native(false),
                
                TernaryFilter::make('supports_express_shipping')
                    ->label(__('zones.supports_express_shipping'))
                    ->boolean()
                    ->trueLabel(__('zones.express_shipping_only'))
                    ->falseLabel(__('zones.standard_shipping_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                
                Action::make('toggle_active')
                    ->label(fn (Zone $record): string => $record->is_active ? __('zones.deactivate') : __('zones.activate'))
                    ->icon(fn (Zone $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Zone $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Zone $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                        
                        Notification::make()
                            ->title($record->is_active ? __('zones.activated_successfully') : __('zones.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                Action::make('set_default')
                    ->label(__('zones.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (Zone $record): bool => !$record->is_default)
                    ->action(function (Zone $record): void {
                        // Remove default from other zones
                        Zone::where('is_default', true)->update(['is_default' => false]);
                        
                        // Set this zone as default
                        $record->update(['is_default' => true]);
                        
                        Notification::make()
                            ->title(__('zones.set_as_default_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    
                    BulkAction::make('activate')
                        ->label(__('zones.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            
                            Notification::make()
                                ->title(__('zones.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('deactivate')
                        ->label(__('zones.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            
                            Notification::make()
                                ->title(__('zones.bulk_deactivated_success'))
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
            'index' => Pages\ListZones::route('/'),
            'create' => Pages\CreateZone::route('/create'),
            'view' => Pages\ViewZone::route('/{record}'),
            'edit' => Pages\EditZone::route('/{record}/edit'),
        ];
    }
}
