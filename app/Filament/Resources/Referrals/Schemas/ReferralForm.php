<?php

declare(strict_types=1);

namespace App\Filament\Resources\Referrals\Schemas;

use App\Models\Referral;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

final class ReferralForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('referrer_id')
                ->relationship('referrer', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('referred_id')
                ->relationship('referred', 'name')
                ->searchable()
                ->preload()
                ->required(),
            TextInput::make('referral_code')
                ->required()
                ->maxLength(20)
                ->default(static fn (): string => Referral::generateUniqueCode()),
            Select::make('status')
                ->options([
                    'pending' => 'pending',
                    'completed' => 'completed',
                    'expired' => 'expired',
                ])
                ->default('pending')
                ->required(),
            DateTimePicker::make('completed_at'),
            DateTimePicker::make('expires_at'),
            KeyValue::make('metadata')
                ->columnSpanFull(),
            TextInput::make('source')
                ->maxLength(255),
            TextInput::make('campaign')
                ->maxLength(255),
            TextInput::make('utm_source')
                ->maxLength(255),
            TextInput::make('utm_medium')
                ->maxLength(255),
            TextInput::make('utm_campaign')
                ->maxLength(255),
            TextInput::make('ip_address')
                ->maxLength(45),
            Textarea::make('user_agent')
                ->columnSpanFull(),
            TextInput::make('title')
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->columnSpanFull(),
            Textarea::make('terms_conditions')
                ->columnSpanFull(),
            Textarea::make('benefits_description')
                ->columnSpanFull(),
            Textarea::make('how_it_works')
                ->columnSpanFull(),
        ]);
    }
}
