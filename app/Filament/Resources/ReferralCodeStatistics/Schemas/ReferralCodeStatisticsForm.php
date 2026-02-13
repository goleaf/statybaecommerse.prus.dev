<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeStatistics\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReferralCodeStatisticsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('referral_code_id')
                    ->relationship('referralCode', 'code')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('date')
                    ->required()
                    ->default(now()->toDateString()),
                TextInput::make('total_views')
                    ->numeric()
                    ->required()
                    ->default(0),
                TextInput::make('total_clicks')
                    ->numeric()
                    ->required()
                    ->default(0),
                TextInput::make('total_signups')
                    ->numeric()
                    ->required()
                    ->default(0),
                TextInput::make('total_conversions')
                    ->numeric()
                    ->required()
                    ->default(0),
                TextInput::make('total_revenue')
                    ->numeric()
                    ->required()
                    ->default(0),
                KeyValue::make('metadata')
                    ->columnSpanFull(),
            ]);
    }
}
