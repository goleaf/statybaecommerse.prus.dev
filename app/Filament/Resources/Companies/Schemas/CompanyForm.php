<?php

declare(strict_types=1);

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.basic_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('messages.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('messages.email'))
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('messages.phone'))
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('website')
                            ->label(__('users.website'))
                            ->url()
                            ->maxLength(255),
                    ])->columns(2),

                Section::make(__('messages.address'))
                    ->schema([
                        TextInput::make('address')
                            ->label(__('messages.address'))
                            ->maxLength(255),
                    ]),

                Section::make(__('messages.Profile'))
                    ->schema([
                        TextInput::make('industry')
                            ->label('Industry')
                            ->maxLength(255),
                        Select::make('size')
                            ->label('Size')
                            ->options([
                                'small'  => 'Small (1-10)',
                                'medium' => 'Medium (11-50)',
                                'large'  => 'Large (51+)',
                            ]),
                        Textarea::make('description')
                            ->label(__('messages.description'))
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make(__('admin.navigation.settings'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('messages.active'))
                            ->default(true),
                    ]),
            ]);
    }
}
