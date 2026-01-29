<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subscribers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.basic_information'))
                    ->schema([
                        TextInput::make('email')
                            ->label(__('messages.email'))
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Select::make('user_id')
                            ->label(__('admin.navigation.users'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Section::make(__('messages.Profile'))
                    ->schema([
                        TextInput::make('first_name')
                            ->label(__('messages.first_name'))
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label(__('messages.last_name'))
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('messages.phone'))
                            ->tel()
                            ->maxLength(255),
                    ])->columns(3),

                Section::make(__('admin.navigation.settings'))
                    ->schema([
                        Select::make('status')
                            ->label(__('messages.status'))
                            ->options([
                                'active'       => 'Active',
                                'inactive'     => 'Inactive',
                                'unsubscribed' => 'Unsubscribed',
                            ])
                            ->required(),
                        Toggle::make('is_verified')
                            ->label(__('messages.verified'))
                            ->default(false),
                        Toggle::make('newsletter_subscription')
                            ->label(__('messages.newsletter'))
                            ->default(true),
                    ])->columns(3),
            ]);
    }
}
