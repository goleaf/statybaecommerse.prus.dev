<?php

declare(strict_types=1);

namespace App\Filament\Resources\Partners\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.General'))
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('logo')
                            ->label(__('messages.logo'))
                            ->collection('logo')
                            ->avatar()
                            ->alignCenter()
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label(__('messages.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label(__('messages.code'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ])->columns(2)
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
                    ])->columns(2)
                    ->columnSpanFull(),

                Section::make(__('messages.financials'))
                    ->schema([
                        TextInput::make('discount_rate')
                            ->label(__('messages.discount'))
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%'),
                        TextInput::make('commission_rate')
                            ->label(__('messages.admin.widgets.average_order_value'))
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%'),
                        Select::make('tier_id')
                            ->label(__('messages.partner_tiers'))
                            ->relationship('partnerTier', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(3)
                    ->columnSpanFull(),

                Section::make(__('admin.navigation.settings'))
                    ->schema([
                        Toggle::make('is_enabled')
                            ->label(__('messages.enabled'))
                            ->required(),
                        KeyValue::make('metadata')
                            ->label(__('messages.Value')),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
