<?php

declare(strict_types=1);

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.general'))
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('logo')
                            ->label(__('messages.logo'))
                            ->collection('logo')
                            ->disk('public')
                            ->visibility('public')
                            ->avatar()
                            ->alignCenter()
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label(__('messages.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label(__('messages.code'))
                            ->placeholder(__('admin.suppliers.code_placeholder'))
                            ->helperText(__('admin.suppliers.code_help'))
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->dehydrateStateUsing(static function (mixed $state): ?string {
                                $value = is_string($state) ? trim($state) : '';

                                return $value !== '' ? $value : null;
                            }),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('messages.footer_contact'))
                    ->schema([
                        TextInput::make('contact_email')
                            ->label(__('messages.email'))
                            ->email()
                            ->maxLength(255),
                        TextInput::make('contact_phone')
                            ->label(__('messages.phone'))
                            ->tel()
                            ->maxLength(255),
                        Textarea::make('notes')
                            ->label(__('messages.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('admin.navigation.settings'))
                    ->schema([
                        Toggle::make('is_enabled')
                            ->label(__('messages.enabled'))
                            ->default(true)
                            ->required(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
