<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.profile'))
                    ->schema([
                        Select::make('account_type')
                            ->label(__('messages.type'))
                            ->options([
                                'private' => __('messages.private_person'),
                                'company' => __('messages.company'),
                            ])
                            ->live()
                            ->default('private')
                            ->required(),
                        Select::make('company_id')
                            ->label(__('messages.company'))
                            ->relationship('organization', 'name')
                            ->visible(fn (Get $get) => $get('account_type') === 'company')
                            ->required(fn (Get $get) => $get('account_type') === 'company')
                            ->searchable()
                            ->preload(),
                        TextInput::make('first_name')
                            ->label(__('messages.first_name'))
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label(__('messages.last_name'))
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('messages.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label(__('messages.password'))
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                        TextInput::make('phone_number')
                            ->label(__('messages.phone'))
                            ->tel()
                            ->maxLength(255),
                        Select::make('gender')
                            ->label(__('messages.gender'))
                            ->options([
                                'male'   => __('admin.gender.male'),
                                'female' => __('admin.gender.female'),
                                'other'  => __('admin.gender.other'),
                            ]),
                        DateTimePicker::make('date_of_birth')
                            ->label(__('messages.birth_date')),
                        Toggle::make('is_active')
                            ->label(__('messages.active'))
                            ->default(true),
                        Select::make('preferred_locale')
                            ->label(__('messages.language'))
                            ->options([
                                'en' => __('translations.english'),
                                'lt' => __('translations.lithuanian'),
                                'ru' => __('translations.russian'),
                                'de' => __('translations.german'),
                            ])
                            ->default('lt'),
                    ])->columns(2)
                    ->columnSpanFull(),

                Section::make(__('messages.address'))
                    ->schema([
                        TextInput::make('address')
                            ->label(__('messages.address'))
                            ->maxLength(255),
                        TextInput::make('postal_code')
                            ->label(__('messages.postal_code'))
                            ->maxLength(20),
                        Select::make('country_id')
                            ->label(__('messages.country'))
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('city_id')
                            ->label(__('messages.city'))
                            ->relationship('city', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
