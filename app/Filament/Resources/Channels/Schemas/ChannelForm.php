<?php

namespace App\Filament\Resources\Channels\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ChannelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('timezone'),
                TextInput::make('url')
                    ->url(),
                Toggle::make('is_enabled')
                    ->required(),
                Toggle::make('is_default')
                    ->required(),
                Textarea::make('metadata')
                    ->columnSpanFull(),
                TextInput::make('code')
                    ->required()
                    ->default(''),
                TextInput::make('type')
                    ->required()
                    ->default('web'),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('configuration')
                    ->columnSpanFull(),
                TextInput::make('domain'),
                Toggle::make('ssl_enabled')
                    ->required(),
                TextInput::make('meta_title'),
                Textarea::make('meta_description')
                    ->columnSpanFull(),
                Textarea::make('meta_keywords')
                    ->columnSpanFull(),
                TextInput::make('analytics_tracking_id'),
                Toggle::make('analytics_enabled')
                    ->required(),
                Textarea::make('payment_methods')
                    ->columnSpanFull(),
                TextInput::make('default_payment_method'),
                Textarea::make('shipping_methods')
                    ->columnSpanFull(),
                TextInput::make('default_shipping_method'),
                TextInput::make('free_shipping_threshold')
                    ->numeric(),
                TextInput::make('currency_code')
                    ->required()
                    ->default('EUR'),
                TextInput::make('currency_symbol')
                    ->required()
                    ->default('€'),
                TextInput::make('currency_position')
                    ->required()
                    ->default('after'),
                TextInput::make('default_language')
                    ->required()
                    ->default('lt'),
                Textarea::make('supported_languages')
                    ->columnSpanFull(),
                TextInput::make('contact_email')
                    ->email(),
                TextInput::make('contact_phone')
                    ->tel(),
                Textarea::make('contact_address')
                    ->columnSpanFull(),
                Textarea::make('social_media')
                    ->columnSpanFull(),
                Textarea::make('legal_documents')
                    ->columnSpanFull(),
            ]);
    }
}
