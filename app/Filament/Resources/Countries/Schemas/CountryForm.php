<?php

declare(strict_types=1);

namespace App\Filament\Resources\Countries\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

/**
 * CountryForm
 * 
 * Filament form schema for Country management with organized sections and comprehensive field validation.
 */
final class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Basic Information Section
                Section::make(__('countries.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('countries.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true),
                                
                                TextInput::make('name_official')
                                    ->label(__('countries.name_official'))
                                    ->maxLength(255),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                TextInput::make('cca2')
                                    ->label(__('countries.cca2'))
                                    ->required()
                                    ->maxLength(2)
                                    ->uppercase()
                                    ->unique(ignoreRecord: true),
                                
                                TextInput::make('cca3')
                                    ->label(__('countries.cca3'))
                                    ->required()
                                    ->maxLength(3)
                                    ->uppercase()
                                    ->unique(ignoreRecord: true),
                                
                                TextInput::make('ccn3')
                                    ->label(__('countries.ccn3'))
                                    ->maxLength(3)
                                    ->numeric(),
                            ]),
                        
                        Textarea::make('description')
                            ->label(__('countries.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                
                // Geographic Information Section
                Section::make(__('countries.geographic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('region')
                                    ->label(__('countries.region'))
                                    ->maxLength(255),
                                
                                TextInput::make('subregion')
                                    ->label(__('countries.subregion'))
                                    ->maxLength(255),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextInput::make('latitude')
                                    ->label(__('countries.latitude'))
                                    ->numeric()
                                    ->step(0.000001),
                                
                                TextInput::make('longitude')
                                    ->label(__('countries.longitude'))
                                    ->numeric()
                                    ->step(0.000001),
                            ]),
                    ]),
                
                // Currency and Economic Information Section
                Section::make(__('countries.currency_economic'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('currency_code')
                                    ->label(__('countries.currency_code'))
                                    ->maxLength(3)
                                    ->uppercase(),
                                
                                TextInput::make('currency_symbol')
                                    ->label(__('countries.currency_symbol'))
                                    ->maxLength(10),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextInput::make('vat_rate')
                                    ->label(__('countries.vat_rate'))
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%'),
                                
                                TextInput::make('timezone')
                                    ->label(__('countries.timezone'))
                                    ->maxLength(255),
                            ]),
                        
                        Textarea::make('currencies')
                            ->label(__('countries.currencies'))
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
                
                // Contact Information Section
                Section::make(__('countries.contact_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('phone_code')
                                    ->label(__('countries.phone_code'))
                                    ->tel()
                                    ->maxLength(10),
                                
                                TextInput::make('phone_calling_code')
                                    ->label(__('countries.phone_calling_code'))
                                    ->tel()
                                    ->maxLength(10),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextInput::make('flag')
                                    ->label(__('countries.flag'))
                                    ->maxLength(255)
                                    ->url(),
                                
                                TextInput::make('svg_flag')
                                    ->label(__('countries.svg_flag'))
                                    ->maxLength(255)
                                    ->url(),
                            ]),
                    ]),
                
                // Additional Information Section
                Section::make(__('countries.additional_information'))
                    ->schema([
                        Textarea::make('languages')
                            ->label(__('countries.languages'))
                            ->rows(2)
                            ->columnSpanFull(),
                        
                        Textarea::make('timezones')
                            ->label(__('countries.timezones'))
                            ->rows(2)
                            ->columnSpanFull(),
                        
                        Textarea::make('metadata')
                            ->label(__('countries.metadata'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                
                // Status and Settings Section
                Section::make(__('countries.status_settings'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('countries.is_active'))
                                    ->default(true),
                                
                                Toggle::make('is_enabled')
                                    ->label(__('countries.is_enabled'))
                                    ->default(true),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_eu_member')
                                    ->label(__('countries.is_eu_member'))
                                    ->default(false),
                                
                                Toggle::make('requires_vat')
                                    ->label(__('countries.requires_vat'))
                                    ->default(false),
                            ]),
                        
                        TextInput::make('sort_order')
                            ->label(__('countries.sort_order'))
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ]),
            ]);
    }
}
