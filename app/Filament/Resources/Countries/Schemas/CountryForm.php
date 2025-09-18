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
                TextInput::make('code')
                    ->default(null),
                TextInput::make('iso_code')
                    ->default(null),
                TextInput::make('name')
                    ->default(null),
                TextInput::make('name_official')
                    ->default(null),
                TextInput::make('cca2')
                    ->required(),
                TextInput::make('cca3')
                    ->required(),
                TextInput::make('ccn3')
                    ->default(null),
                TextInput::make('currency_code')
                    ->default(null),
                TextInput::make('currency_symbol')
                    ->default(null),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_eu_member')
                    ->required(),
                Toggle::make('requires_vat')
                    ->required(),
                TextInput::make('vat_rate')
                    ->numeric()
                    ->default(null),
                TextInput::make('timezone')
                    ->default(null),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('metadata')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('phone_code')
                    ->tel()
                    ->default(null),
                TextInput::make('phone_calling_code')
                    ->tel()
                    ->default(null),
                TextInput::make('flag')
                    ->default(null),
                TextInput::make('svg_flag')
                    ->default(null),
                TextInput::make('region')
                    ->default(null),
                TextInput::make('subregion')
                    ->default(null),
                TextInput::make('latitude')
                    ->numeric()
                    ->default(null),
                TextInput::make('longitude')
                    ->numeric()
                    ->default(null),
                Textarea::make('currencies')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('languages')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('timezones')
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('is_enabled')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
