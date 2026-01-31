<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.navigation.users'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('messages.name')),
                        TextEntry::make('email')
                            ->label(__('messages.email')),
                    ])->columns(2),

                Section::make(__('messages.Profile'))
                    ->schema([
                        TextEntry::make('first_name')
                            ->label(__('messages.first_name')),
                        TextEntry::make('last_name')
                            ->label(__('messages.last_name')),
                        TextEntry::make('phone_number')
                            ->label(__('messages.phone')),
                        TextEntry::make('gender')
                            ->label(__('messages.Gender'))
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'male'   => __('admin.gender.male'),
                                'female' => __('admin.gender.female'),
                                'other'  => __('admin.gender.other'),
                                default  => $state,
                            }),
                        TextEntry::make('date_of_birth')
                            ->label(__('messages.birth_date'))
                            ->date(),
                    ])->columns(2),

                Section::make(__('admin.navigation.settings'))
                    ->schema([
                        IconEntry::make('is_active')
                            ->label(__('messages.active'))
                            ->boolean(),
                        IconEntry::make('is_admin')
                            ->label(__('admin.user_status.admin'))
                            ->boolean(),
                        TextEntry::make('preferred_locale')
                            ->label(__('messages.language'))
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'en'    => __('translations.english'),
                                'lt'    => __('translations.lithuanian'),
                                'ru'    => __('translations.russian'),
                                'de'    => __('translations.german'),
                                default => $state,
                            }),
                    ])->columns(3),
            ]);
    }
}
