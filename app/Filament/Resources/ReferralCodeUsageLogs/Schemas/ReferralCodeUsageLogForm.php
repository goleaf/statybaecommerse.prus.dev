<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeUsageLogs\Schemas;

use App\Models\ReferralCode;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;

final class ReferralCodeUsageLogForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                SchemaSection::make(__('admin.referral_code_usage_logs.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('referral_code_id')
                                    ->label(__('admin.referral_code_usage_logs.referral_code'))
                                    ->options(ReferralCode::pluck('code', 'id'))
                                    ->required()
                                    ->searchable(),
                                Select::make('user_id')
                                    ->label(__('admin.referral_code_usage_logs.user'))
                                    ->options(User::pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                                TextInput::make('ip_address')
                                    ->label(__('admin.referral_code_usage_logs.ip_address'))
                                    ->ip()
                                    ->maxLength(45),
                                TextInput::make('referrer')
                                    ->label(__('admin.referral_code_usage_logs.referrer'))
                                    ->url()
                                    ->maxLength(255),
                                TextInput::make('user_agent')
                                    ->label(__('admin.referral_code_usage_logs.user_agent'))
                                    ->maxLength(500),
                            ]),
                        Textarea::make('metadata')
                            ->label(__('admin.referral_code_usage_logs.metadata'))
                            ->rows(5)
                            ->helperText(__('admin.referral_code_usage_logs.metadata_help')),
                    ]),
            ]);
    }
}
