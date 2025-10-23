<?php

declare(strict_types=1);

namespace App\Filament\Resources\FeatureFlags\Schemas;

use App\Models\FeatureFlag;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;

class FeatureFlagForm
{
    public static function configure(Form $form): Form
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
                Flatpickr::make('starts_at')
                    ->time(true)
                    ->time24hr(true)
                    ->seconds(false)
                    ->format('Y-m-d H:i')
                    ->rangePicker(),
                Flatpickr::make('ends_at')
                    ->time(true)
                    ->time24hr(true)
                    ->seconds(false)
                    ->format('Y-m-d H:i')
                    ->rangePicker(),
                Toggle::make('is_enabled')
                    ->required(),
                Toggle::make('is_global')
                    ->required(),
                Flatpickr::make('start_date')
                    ->time(true)
                    ->time24hr(true)
                    ->seconds(false)
                    ->format('Y-m-d H:i')
                    ->rangePicker(),
                Flatpickr::make('end_date')
                    ->time(true)
                    ->time24hr(true)
                    ->seconds(false)
                    ->format('Y-m-d H:i')
                    ->rangePicker(),
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
                Flatpickr::make('last_activated')
                    ->time(true)
                    ->time24hr(true)
                    ->seconds(false)
                    ->format('Y-m-d H:i'),
                Flatpickr::make('last_deactivated')
                    ->time(true)
                    ->time24hr(true)
                    ->seconds(false)
                    ->format('Y-m-d H:i'),
            ]);
    }
}
