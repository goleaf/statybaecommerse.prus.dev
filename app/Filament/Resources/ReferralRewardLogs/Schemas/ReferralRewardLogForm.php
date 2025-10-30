<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewardLogs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class ReferralRewardLogForm
{
    public static function configure(Schema $schema): Schema
    {
        // Return the configured schema instance so Filament can build the component tree for v4 resources.
        return $schema
            ->schema([
                Select::make('referral_reward_id')
                    ->relationship('referralReward', 'title')
                    ->searchable()
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->nullable(),
                Select::make('action')
                    ->options([
                        'earned'    => 'Earned',
                        'redeemed'  => 'Redeemed',
                        'expired'   => 'Expired',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('earned')
                    ->required(),
                Textarea::make('data')
                    ->columnSpanFull(),
                TextInput::make('ip_address')
                    ->ip()
                    ->maxLength(45),
                Textarea::make('user_agent')
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }
}
