<?php

namespace App\Filament\Resources\ReferralCampaigns\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReferralCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('name')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
                DateTimePicker::make('start_date'),
                DateTimePicker::make('end_date'),
                TextInput::make('reward_amount')
                    ->numeric(),
                TextInput::make('reward_type'),
                TextInput::make('max_referrals_per_user')
                    ->numeric(),
                TextInput::make('max_total_referrals')
                    ->numeric(),
                Textarea::make('conditions')
                    ->columnSpanFull(),
                Textarea::make('metadata')
                    ->columnSpanFull(),
                DateTimePicker::make('deprecated_at'),
            ]);
    }
}
