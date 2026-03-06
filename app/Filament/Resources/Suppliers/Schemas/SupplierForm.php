<?php

declare(strict_types=1);

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

final class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.general'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('messages.name'))
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(static function (Set $set, Get $get, ?string $state): void {
                                if (filled((string) $get('code'))) {
                                    return;
                                }

                                $set('code', Str::slug((string) $state));
                            })
                            ->maxLength(255),
                        TextInput::make('company_code')
                            ->label(__('messages.company_code'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label(__('messages.system_code'))
                            ->placeholder(__('admin.suppliers.code_placeholder'))
                            ->helperText(__('admin.suppliers.code_help'))
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(static function (mixed $state): ?string {
                                if (! is_string($state) || trim($state) === '') {
                                    return null;
                                }

                                $slug = Str::slug($state);

                                return $slug !== '' ? $slug : null;
                            })
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('messages.company'))
                    ->schema([
                        TextInput::make('vat_code')
                            ->label(__('admin.labels.vat_code'))
                            ->maxLength(255),
                        TextInput::make('website')
                            ->label(__('messages.website'))
                            ->url()
                            ->maxLength(255),
                        Textarea::make('address')
                            ->label(__('messages.address'))
                            ->rows(2)
                            ->columnSpanFull(),
                        TextInput::make('city')
                            ->label(__('messages.city'))
                            ->maxLength(255),
                        TextInput::make('postal_code')
                            ->label(__('messages.postal_code'))
                            ->maxLength(255),
                        TextInput::make('country')
                            ->label(__('messages.country'))
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('messages.footer_contact'))
                    ->schema([
                        TextInput::make('contact_person')
                            ->label(__('messages.contact_person'))
                            ->maxLength(255),
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
