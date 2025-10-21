<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Slider;
use App\Support\Filament\SearchableComponentHelper;
use App\Support\Search\ContentLinkSearch;
use App\Support\Search\SearchResultPayload;

use function collect;

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
use Filament\Forms\Set;
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
                    ->afterStateHydrated(function (SearchableInput $component, ?string $state, Set $set): void {
                        // Reset any cached payload before hydrating the component with helper-driven data.
                        $set('button_url_payload', []);

                        SearchableComponentHelper::hydrate(
                            $component,
                            $state,
                            static function (?string $url): ?array {
                                if (! is_string($url) || trim($url) === '') {
                                    return null;
                                }

                                $result = collect(ContentLinkSearch::results($url))
                                    ->first(static fn ($candidate): bool => $candidate->value() === $url);

                                if ($result !== null) {
                                    $normalised = SearchResultPayload::hydrate($result);

                                    return [
                                        'value'   => $normalised['id'],
                                        'label'   => $normalised['label'],
                                        'payload' => $normalised['payload'],
                                    ];
                                }

                                // Fall back to a bare payload when the lookup cannot resolve metadata from search services.
                                return [
                                    'value'   => $url,
                                    'label'   => $url,
                                    'payload' => [
                                        'id'    => $url,
                                        'label' => $url,
                                        'type'  => 'custom',
                                    ],
                                ];
                            },
                            static function (array $record) use ($set): array {
                                $payload = $record['payload'] ?? [];

                                $set('button_url_payload', $payload);

                                return [
                                    'value'   => $record['value'] ?? null,
                                    'label'   => $record['label'] ?? null,
                                    'payload' => $payload,
                                ];
                            },
                        );

                        // See docs/filament/searchable-inputs.md for helper expectations.
                    })
                    ->afterStateUpdated(function (SearchableInput $component, ?string $state, Set $set): void {
                        if (is_string($state) && trim($state) !== '') {
                            return;
                        }

                        // Ensure both the component and any dependent payload caches are cleared together.
                        SearchableComponentHelper::clear(
                            $component,
                            static function () use ($set): void {
                                $set('button_url_payload', []);
                            },
                        );
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
