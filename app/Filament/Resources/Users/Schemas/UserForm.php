<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.Profile'))
                    ->schema([
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
                            ->label(__('messages.Gender'))
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
            ]);
    }
}
