<?php

namespace App\Filament\Resources\ReferralCodeUsageLogs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ReferralCodeUsageLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('referral_code_id')
                    ->relationship('referralCode', 'title')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                TextInput::make('ip_address')
                    ->required(),
                Textarea::make('user_agent')
                    ->columnSpanFull(),
                TextInput::make('referrer'),
                Textarea::make('metadata')
                    ->columnSpanFull(),
            ]);
    }
}
