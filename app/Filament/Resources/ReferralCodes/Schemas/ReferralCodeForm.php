<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodes\Schemas;

use App\Models\ReferralCampaign;
use App\Models\ReferralCode;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class ReferralCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->default(static fn (): ?int => request()->integer('user_id') ?: null)
                ->required(),
            TextInput::make('code')
                ->required()
                ->maxLength(20)
                ->default(static fn (): string => ReferralCode::generateUniqueCode()),
            Toggle::make('is_active')
                ->default(true),
            DateTimePicker::make('expires_at'),
            KeyValue::make('metadata')
                ->columnSpanFull(),
            KeyValue::make('title')
                ->columnSpanFull(),
            KeyValue::make('description')
                ->columnSpanFull(),
            TextInput::make('usage_limit')
                ->numeric(),
            TextInput::make('usage_count')
                ->required()
                ->numeric()
                ->default(0),
            TextInput::make('reward_amount')
                ->numeric(),
            TextInput::make('reward_type')
                ->maxLength(255),
            KeyValue::make('conditions')
                ->columnSpanFull(),
            Select::make('campaign_id')
                ->relationship('campaign', 'id')
                ->getOptionLabelFromRecordUsing(static fn (ReferralCampaign $record): string => $record->localized_name)
                ->searchable()
                ->preload(),
            TextInput::make('source')
                ->maxLength(255),
            KeyValue::make('tags')
                ->columnSpanFull(),
        ]);
    }
}
