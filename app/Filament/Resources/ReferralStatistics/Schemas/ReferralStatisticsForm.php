<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralStatistics\Schemas;

use App\Forms\Components\Flatpickr;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;

class ReferralStatisticsForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Flatpickr::make('date')->datePicker()
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
