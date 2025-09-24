<?php

namespace App\Filament\Resources\SystemSettingDependencies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SystemSettingDependencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('setting_id')
                    ->relationship('setting', 'name')
                    ->required(),
                TextInput::make('depends_on_setting_id')
                    ->required()
                    ->numeric(),
                Textarea::make('condition')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
