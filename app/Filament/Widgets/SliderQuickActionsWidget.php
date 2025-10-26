<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Slider;
use App\Support\Filament\SearchableInputHelper;
use App\Support\Search\ContentLinkSearch;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

final class SliderQuickActionsWidget extends Widget implements HasActions, HasForms
{
    use InteractsWithActions, InteractsWithForms;

    protected string $view = 'filament.widgets.slider-quick-actions';

    protected int|string|array $columnSpan = 'full';

    public function createSliderAction(): Action
    {
        return Action::make('createSlider')
            ->label(__('translations.create_slider'))
            ->icon('heroicon-m-plus')
            ->color('primary')
            ->form([
                TextInput::make('title')
                    ->label(__('translations.title'))
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(__('translations.description'))
                    ->maxLength(1000)
                    ->columnSpanFull(),
                TextInput::make('button_text')
                    ->label(__('translations.button_text'))
                    ->maxLength(255),
                SearchableInput::make('button_url')
                    ->label(__('translations.button_url'))
                    ->placeholder(__('translations.slider_link_placeholder'))
                    ->maxLength(255)
                    ->searchUsing(fn (string $value): array => ContentLinkSearch::results($value))
                    ->dehydrateStateUsing(fn (?string $state): ?string => $state !== null && $state !== '' ? $state : null)
                    ->afterStateHydrated(function (SearchableInput $component, ?string $state): void {
                        // Hydrate via helper per docs/forms/SEARCHABLE_INPUT_METADATA.md expectations.
                        SearchableInputHelper::hydrate(
                            $component,
                            $state,
                            static fn (string $value): ?array => ['value' => $value, 'label' => $value],
                        );
                    })
                    ->afterStateUpdated(function (SearchableInput $component, ?string $state, callable $set): void {
                        if ($state !== null && $state !== '') {
                            return;
                        }

                        // Clear persisted URLs when the lookup resets to avoid stale metadata.
                        SearchableInputHelper::clear($component, $set, ['button_url' => null]);
                    }),
                ColorPicker::make('background_color')
                    ->label(__('translations.background_color'))
                    ->default('#ffffff'),
                ColorPicker::make('text_color')
                    ->label(__('translations.text_color'))
                    ->default('#000000'),
                TextInput::make('sort_order')
                    ->label(__('translations.sort_order'))
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label(__('translations.is_active'))
                    ->default(true),
            ])
            ->action(function (array $data): void {
                $slider = Slider::create($data);

                Notification::make()
                    ->title(__('translations.slider_created'))
                    ->success()
                    ->send();
            });
    }

    public function toggleAllSlidersAction(): Action
    {
        return Action::make('toggleAllSliders')
            ->label(__('translations.toggle_all_sliders'))
            ->icon('heroicon-m-power')
            ->color('warning')
            ->requiresConfirmation()
            ->action(function (): void {
                $activeCount = Slider::where('is_active', true)->count();
                $inactiveCount = Slider::where('is_active', false)->count();

                if ($activeCount > $inactiveCount) {
                    // Deactivate all
                    Slider::query()->update(['is_active' => false]);
                    $message = __('translations.all_sliders_deactivated');
                } else {
                    // Activate all
                    Slider::query()->update(['is_active' => true]);
                    $message = __('translations.all_sliders_activated');
                }

                Notification::make()
                    ->title($message)
                    ->success()
                    ->send();
            });
    }

    public function reorderSlidersAction(): Action
    {
        return Action::make('reorderSliders')
            ->label(__('translations.reorder_sliders'))
            ->icon('heroicon-m-arrows-up-down')
            ->color('info')
            ->url(function () {
                try {
                    return route('filament.admin.resources.sliders.index');
                } catch (Exception $e) {
                    return '#';
                }
            })
            ->openUrlInNewTab();
    }
}
