<?php

declare(strict_types=1);

namespace App\Filament\Resources\FeatureFlags\Schemas;

use App\Forms\Components\Flatpickr;
use App\Models\FeatureFlag;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Support\Filament\Components\Flatpickr;

class FeatureFlagForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('key')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->rules(['alpha_dash']),
                Textarea::make('description')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
                Textarea::make('conditions')
                    ->columnSpanFull(),
                Textarea::make('rollout_percentage')
                    ->columnSpanFull(),
                TextInput::make('environment'),
                Flatpickr::makeDateTime('starts_at'),
                Flatpickr::makeDateTime('ends_at'),
                Toggle::make('is_enabled')
                    ->required(),
                Toggle::make('is_global')
                    ->required(),
                Flatpickr::makeDateTime('start_date'),
                Flatpickr::makeDateTime('end_date'),
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
                Placeholder::make('created_by_display')
                    ->label(__('system.created_by'))
                    ->content(fn (?FeatureFlag $record): string => $record === null ? '—' : ($record->created_by_display ?? '—'))
                    ->visible(fn (?FeatureFlag $record): bool => $record !== null)
                    ->columnSpanFull(),
                Placeholder::make('updated_by_display')
                    ->label(__('system.updated_by'))
                    ->content(fn (?FeatureFlag $record): string => $record === null ? '—' : ($record->updated_by_display ?? '—'))
                    ->visible(fn (?FeatureFlag $record): bool => $record !== null)
                    ->columnSpanFull(),
                Flatpickr::makeDateTime('last_activated'),
                Flatpickr::makeDateTime('last_deactivated'),
            ]);
    }
}
