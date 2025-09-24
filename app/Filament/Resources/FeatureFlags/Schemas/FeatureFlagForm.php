<?php

namespace App\Filament\Resources\FeatureFlags\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FeatureFlagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('key')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
                Textarea::make('conditions')
                    ->columnSpanFull(),
                Textarea::make('rollout_percentage')
                    ->columnSpanFull(),
                TextInput::make('environment'),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('ends_at'),
                Toggle::make('is_enabled')
                    ->required(),
                Toggle::make('is_global')
                    ->required(),
                DateTimePicker::make('start_date'),
                DateTimePicker::make('end_date'),
                Textarea::make('metadata')
                    ->columnSpanFull(),
                TextInput::make('priority'),
                TextInput::make('category'),
                TextInput::make('impact_level'),
                TextInput::make('rollout_strategy'),
                Textarea::make('rollback_plan')
                    ->columnSpanFull(),
                Textarea::make('success_metrics')
                    ->columnSpanFull(),
                TextInput::make('approval_status'),
                Textarea::make('approval_notes')
                    ->columnSpanFull(),
                TextInput::make('created_by'),
                TextInput::make('updated_by'),
                DateTimePicker::make('last_activated'),
                DateTimePicker::make('last_deactivated'),
            ]);
    }
}
