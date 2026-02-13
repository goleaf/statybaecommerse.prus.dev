<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewards\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class ReferralRewardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('referral_id')
                ->relationship('referral', 'referral_code')
                ->searchable()
                ->preload(),
            Select::make('user_id')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('order_id')
                ->relationship('order', 'id')
                ->searchable()
                ->preload(),
            Select::make('type')
                ->options([
                    'discount'          => 'discount',
                    'credit'            => 'credit',
                    'referrer_bonus'    => 'referrer_bonus',
                    'referred_discount' => 'referred_discount',
                ])
                ->required(),
            TextInput::make('amount')
                ->required()
                ->numeric(),
            TextInput::make('currency_code')
                ->required()
                ->maxLength(3)
                ->default('EUR'),
            Select::make('status')
                ->options([
                    'pending' => 'pending',
                    'applied' => 'applied',
                    'expired' => 'expired',
                ])
                ->required()
                ->default('pending'),
            DateTimePicker::make('applied_at'),
            DateTimePicker::make('expires_at'),
            KeyValue::make('metadata')
                ->columnSpanFull(),
            TextInput::make('title')
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->columnSpanFull(),
            Toggle::make('is_active')
                ->default(true),
            TextInput::make('priority')
                ->required()
                ->numeric()
                ->default(0),
            KeyValue::make('conditions')
                ->columnSpanFull(),
            KeyValue::make('reward_data')
                ->columnSpanFull(),
        ]);
    }
}
