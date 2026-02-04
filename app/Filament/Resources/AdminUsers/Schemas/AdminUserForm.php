<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminUsers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class AdminUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.basic_information'))
                    ->icon('heroicon-o-user')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('messages.name'))
                                    ->required()
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
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->revealable(),
                                DateTimePicker::make('email_verified_at')
                                    ->label(__('messages.email_verified_at'))
                                    ->native(false),
                            ]),
                    ]),

                Section::make(__('messages.additional_information'))
                    ->icon('heroicon-o-document-text')
                    ->description(__('messages.manage_admin_extra_details'))
                    ->schema([
                        Textarea::make('description')
                            ->label(__('messages.description'))
                            ->rows(5)
                            ->columnSpanFull()
                            ->placeholder(__('messages.enter_description_placeholder'))
                            ->dehydrated(false),

                        Grid::make(3)
                            ->schema([
                                Placeholder::make('id')
                                    ->label(__('messages.id'))
                                    ->content(fn ($record): ?string => $record?->id ? (string) $record->id : null),

                                Placeholder::make('created_at')
                                    ->label(__('messages.created_at'))
                                    ->content(fn ($record): ?string => $record?->created_at?->diffForHumans()),

                                Placeholder::make('updated_at')
                                    ->label(__('messages.updated_at'))
                                    ->content(fn ($record): ?string => $record?->updated_at?->diffForHumans()),
                            ]),
                    ]),
            ]);
    }
}
