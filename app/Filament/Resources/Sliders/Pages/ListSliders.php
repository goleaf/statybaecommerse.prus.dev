<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sliders\Pages;

use App\Filament\Resources\Sliders\SliderResource;
use App\Filament\Widgets\SliderManagementWidget;
use App\Models\Slider;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
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
            $this->bulkImportAction(),
            $this->exportSlidersAction(),
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

    public function bulkImportAction(): Action
    {
        return Action::make('bulkImport')
            ->label(__('translations.bulk_import'))
            ->color('info')
            ->form([
                FileUpload::make('import_file')
                    ->label(__('translations.import_file'))
                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'])
                    ->required(),
                Toggle::make('update_existing')
                    ->label(__('translations.update_existing'))
                    ->default(false),
            ])
            ->action(function (array $data): void {
                // Handle bulk import logic here
                Notification::make()
                    ->title(__('translations.import_started'))
                    ->success()
                    ->send();
            });
    }

    public function exportSlidersAction(): Action
    {
        return Action::make('exportSliders')
            ->label(__('translations.export_sliders'))
            ->color('success')
            ->form([
                Select::make('format')
                    ->label(__('translations.export_format'))
                    ->options([
                        'excel' => __('translations.excel'),
                        'csv'   => __('translations.csv'),
                        'json'  => __('translations.json'),
                    ])
                    ->default('excel'),
                Toggle::make('include_images')
                    ->label(__('translations.include_images'))
                    ->default(false),
            ])
            ->action(function (array $data): void {
                // Handle export logic here
                Notification::make()
                    ->title(__('translations.export_started'))
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
