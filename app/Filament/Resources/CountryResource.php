<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
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
 * CountryResource
 * 
 * Filament v4 resource for Country management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class CountryResource extends Resource
{
    protected static ?string $model = Country::class;
    
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = 'Products';
    protected static ?int $navigationSort = 4;
    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('countries.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'System'->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('countries.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('countries.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('countries.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('countries.name'))
                                ->required()
                                ->maxLength(255),
                            
                            TextInput::make('code')
                                ->label(__('countries.code'))
                                ->required()
                                ->maxLength(3)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha'])
                                ->helperText(__('countries.code_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('iso_code')
                                ->label(__('countries.iso_code'))
                                ->required()
                                ->maxLength(3)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha'])
                                ->helperText(__('countries.iso_code_help')),
                            
                            TextInput::make('phone_code')
                                ->label(__('countries.phone_code'))
                                ->maxLength(10)
                                ->helperText(__('countries.phone_code_help')),
                        ]),
                    
                    Textarea::make('description')
                        ->label(__('countries.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('countries.currency_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('currency_code')
                                ->label(__('countries.currency_code'))
                                ->maxLength(3)
                                ->rules(['alpha'])
                                ->helperText(__('countries.currency_code_help')),
                            
                            TextInput::make('currency_symbol')
                                ->label(__('countries.currency_symbol'))
                                ->maxLength(10)
                                ->helperText(__('countries.currency_symbol_help')),
                        ]),
                ]),
            
            Section::make(__('countries.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('countries.is_active'))
                                ->default(true),
                            
                            Toggle::make('is_default')
                                ->label(__('countries.is_default')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('sort_order')
                                ->label(__('countries.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                            
                            Select::make('region')
                                ->label(__('countries.region'))
                                ->options([
                                    'europe' => __('countries.regions.europe'),
                                    'asia' => __('countries.regions.asia'),
                                    'africa' => __('countries.regions.africa'),
                                    'north_america' => __('countries.regions.north_america'),
                                    'south_america' => __('countries.regions.south_america'),
                                    'oceania' => __('countries.regions.oceania'),
                                    'antarctica' => __('countries.regions.antarctica'),
                                ]),
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
                    ->label(__('countries.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('code')
                    ->label(__('countries.code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                
                TextColumn::make('iso_code')
                    ->label(__('countries.iso_code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('blue'),
                
                TextColumn::make('phone_code')
                    ->label(__('countries.phone_code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('currency_code')
                    ->label(__('countries.currency_code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('currency_symbol')
                    ->label(__('countries.currency_symbol'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('region')
                    ->label(__('countries.region'))
                    ->formatStateUsing(fn (string $state): string => __("countries.regions.{$state}"))
                    ->badge()
                    ->color('purple')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('cities_count')
                    ->label(__('countries.cities_count'))
                    ->counts('cities')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_active')
                    ->label(__('countries.is_active'))
                    ->boolean()
                    ->sortable(),
                
                IconColumn::make('is_default')
                    ->label(__('countries.is_default'))
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('sort_order')
                    ->label(__('countries.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label(__('countries.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('countries.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('countries.is_active'))
                    ->boolean()
                    ->trueLabel(__('countries.active_only'))
                    ->falseLabel(__('countries.inactive_only'))
                    ->native(false),
                
                TernaryFilter::make('is_default')
                    ->label(__('countries.is_default'))
                    ->boolean()
                    ->trueLabel(__('countries.default_only'))
                    ->falseLabel(__('countries.non_default_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                
                Action::make('toggle_active')
                    ->label(fn (Country $record): string => $record->is_active ? __('countries.deactivate') : __('countries.activate'))
                    ->icon(fn (Country $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Country $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Country $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                        
                        Notification::make()
                            ->title($record->is_active ? __('countries.activated_successfully') : __('countries.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                Action::make('set_default')
                    ->label(__('countries.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (Country $record): bool => !$record->is_default)
                    ->action(function (Country $record): void {
                        // Remove default from other countries
                        Country::where('is_default', true)->update(['is_default' => false]);
                        
                        // Set this country as default
                        $record->update(['is_default' => true]);
                        
                        Notification::make()
                            ->title(__('countries.set_as_default_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    
                    BulkAction::make('activate')
                        ->label(__('countries.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            
                            Notification::make()
                                ->title(__('countries.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('deactivate')
                        ->label(__('countries.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            
                            Notification::make()
                                ->title(__('countries.bulk_deactivated_success'))
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
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'view' => Pages\ViewCountry::route('/{record}'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}
