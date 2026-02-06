<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sliders\Pages;

use App\Filament\Resources\Sliders\SliderResource;
use App\Filament\Widgets\SliderManagementWidget;
use App\Models\Slider;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSliders extends ListRecords
{
    protected static string $resource = SliderResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            SliderManagementWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            $this->settingsAction(),
            $this->toggleAllSlidersAction(),
        ];
    }

    public function toggleAllSlidersAction(): Action
    {
        return Action::make('toggleAllSliders')
            ->label(__('translations.toggle_all_sliders'))
            ->color('warning')
            ->requiresConfirmation()
            ->action(function (): void {
                $activeCount = Slider::where('is_active', true)->count();
                $inactiveCount = Slider::where('is_active', false)->count();

                if ($activeCount > $inactiveCount) {
                    Slider::query()->update(['is_active' => false]);
                    $message = __('translations.all_sliders_deactivated');
                } else {
                    Slider::query()->update(['is_active' => true]);
                    $message = __('translations.all_sliders_activated');
                }

                Notification::make()
                    ->title($message)
                    ->success()
                    ->send();
            });
    }

    public function settingsAction(): Action
    {
        return Action::make('settings')
            ->label(__('translations.settings'))
            ->color('gray')
            ->form([
                Section::make(__('translations.global_settings'))
                    ->schema([
                        Toggle::make('auto_optimize_images')
                            ->label(__('translations.auto_optimize_images'))
                            ->default(true),
                        Select::make('default_animation')
                            ->label(__('translations.default_animation'))
                            ->options([
                                'fade'  => __('translations.fade'),
                                'slide' => __('translations.slide'),
                                'zoom'  => __('translations.zoom'),
                            ])
                            ->default('fade'),
                        TextInput::make('default_duration')
                            ->label(__('translations.default_duration'))
                            ->numeric()
                            ->default(5000)
                            ->suffix('ms'),
                    ]),
            ])
            ->action(function (array $data): void {
                // Handle settings save logic here
                Notification::make()
                    ->title(__('translations.settings_saved'))
                    ->success()
                    ->send();
            });
    }
}
