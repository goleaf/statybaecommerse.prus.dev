<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralStatistics\Schemas;

use App\Support\Filament\Components\Flatpickr;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReferralStatisticsForm
{
    public static function configure(Schema $form): Schema
    {
        return $schema
            ->schema([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Flatpickr::makeDate('date')
                    ->required(),
                TextInput::make('total_referrals')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('completed_referrals')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('pending_referrals')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_rewards_earned')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_discounts_given')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('metadata')
                    ->columnSpanFull(),
            ]);
    }
}
