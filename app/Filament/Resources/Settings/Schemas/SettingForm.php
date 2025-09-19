<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->required(),
                Textarea::make('value')
                    ->columnSpanFull(),
                TextInput::make('type')
                    ->required()
                    ->default('string'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Toggle::make('is_public')
                    ->required(),
                TextInput::make('display_name'),
                TextInput::make('group'),
                Toggle::make('is_required')
                    ->required(),
                Toggle::make('is_encrypted')
                    ->required(),
            ]);
    }
}
