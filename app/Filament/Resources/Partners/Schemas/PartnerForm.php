<?php

declare(strict_types=1);

namespace App\Filament\Resources\Partners\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('logo')
                            ->collection('logo')
                            ->avatar()
                            ->alignCenter()
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Contact Details')
                    ->schema([
                        TextInput::make('contact_email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('contact_phone')
                            ->tel()
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Commercial Terms')
                    ->schema([
                        TextInput::make('discount_rate')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%'),
                        TextInput::make('commission_rate')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%'),
                        Select::make('tier_id')
                            ->relationship('tier', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(3),

                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_enabled')
                            ->required(),
                        KeyValue::make('metadata'),
                    ]),
            ]);
    }
}
