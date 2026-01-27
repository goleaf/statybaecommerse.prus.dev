<?php

declare(strict_types=1);

namespace App\Filament\Resources\Organizations\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class OrganizationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Organization Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('type')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Description')
                    ->schema([
                        RichEditor::make('description')
                            ->columnSpanFull(),
                    ]),

                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_active')
                            ->required(),
                    ]),
            ]);
    }
}
