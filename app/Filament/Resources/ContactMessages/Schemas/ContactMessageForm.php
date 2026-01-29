<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageForm
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
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('messages.phone'))
                            ->tel()
                            ->maxLength(255),
                    ])->columns(3),

                Section::make(__('messages.message'))
                    ->schema([
                        TextInput::make('subject')
                            ->label(__('messages.subject'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('order_number')
                            ->label(__('messages.order_number'))
                            ->maxLength(255),
                        Textarea::make('message')
                            ->label(__('messages.message'))
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make(__('admin.navigation.settings'))
                    ->schema([
                        TextInput::make('ip_address')
                            ->label('IP Address')
                            ->disabled(),
                        TextInput::make('user_agent')
                            ->label('User Agent')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
