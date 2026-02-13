<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralStatistics\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReferralStatisticsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('date')
                    ->required()
                    ->default(now()->toDateString()),
                TextInput::make('total_referrals')
                    ->numeric()
                    ->required()
                    ->default(0),
                TextInput::make('completed_referrals')
                    ->numeric()
                    ->required()
                    ->default(0),
                TextInput::make('pending_referrals')
                    ->numeric()
                    ->required()
                    ->default(0),
                TextInput::make('total_rewards_earned')
                    ->numeric()
                    ->required()
                    ->default(0),
                TextInput::make('total_discounts_given')
                    ->numeric()
                    ->required()
                    ->default(0),
                KeyValue::make('metadata')
                    ->columnSpanFull(),
            ]);
    }
}
