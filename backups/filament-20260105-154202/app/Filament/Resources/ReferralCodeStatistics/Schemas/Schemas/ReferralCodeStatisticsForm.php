<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeStatistics\Schemas;

use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReferralCodeStatisticsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('referral_code_id')
                    ->relationship('referralCode', 'title')
                    ->required(),
                SupportFlatpickr::makeDate('date')
                    ->required(),
                TextInput::make('total_views')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_clicks')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_signups')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_conversions')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_revenue')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('metadata')
                    ->columnSpanFull(),
            ]);
    }
}
