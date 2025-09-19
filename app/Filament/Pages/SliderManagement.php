<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Slider;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;
use UnitEnum;

class SliderManagement extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Slider Management';
    protected static ?string $title = 'Slider Management';
    protected static ?string $slug = 'slider-management';
    protected static ?int $navigationSort = 1;
    protected static string|UnitEnum|null $navigationGroup = 'Content';

    public Collection $sliders;

    public function mount(): void
    {
        $this->loadSliders();
    }

    public function loadSliders(): void
    {
        $this->sliders = Slider::with('translations')
            ->orderBy('sort_order')
            ->get();
    }

    public function createSliderAction(): Action
    {
        return Action::make('createSlider')
            ->label(__('translations.create_slider'))
            ->icon('heroicon-o-plus')
            ->color('primary')
            ->size(Size::Large)
            ->form([
                TextInput::make('title')
                    ->label(__('translations.title'))
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(__('translations.description'))
                    ->maxLength(1000)
                    ->rows(3),
                TextInput::make('button_text')
                    ->label(__('translations.button_text'))
                    ->maxLength(255),
                TextInput::make('button_url')
                    ->label(__('translations.button_url'))
                    ->url()
                    ->maxLength(255),
                FileUpload::make('slider_image')
                    ->label(__('translations.slider_image'))
                    ->image()
                    ->directory('sliders/images')
                    ->visibility('public')
                    ->imageEditor(),
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
                Select::make('animation_type')
                    ->label(__('translations.animation_type'))
                    ->options([
                        'fade' => __('translations.fade'),
                        'slide' => __('translations.slide'),
                        'zoom' => __('translations.zoom'),
                        'flip' => __('translations.flip'),
                    ])
                    ->default('fade'),
                TextInput::make('duration')
                    ->label(__('translations.duration'))
                    ->numeric()
                    ->default(5000)
                    ->suffix('ms'),
                Toggle::make('autoplay')
                    ->label(__('translations.autoplay'))
                    ->default(true),
            ])
            ->action(function (array $data): void {
                $slider = Slider::create([
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'button_text' => $data['button_text'],
                    'button_url' => $data['button_url'],
                    'background_color' => $data['background_color'],
                    'text_color' => $data['text_color'],
                    'sort_order' => $data['sort_order'],
                    'is_active' => $data['is_active'],
                    'settings' => [
                        'animation' => $data['animation_type'],
                        'duration' => $data['duration'],
                        'autoplay' => $data['autoplay'],
                    ],
                ]);

                // Handle file upload
                if (isset($data['slider_image'])) {
                    $slider
                        ->addMediaFromDisk($data['slider_image'], 'public')
                        ->toMediaCollection('slider_images');
                }

                $this->loadSliders();

                Notification::make()
                    ->title(__('translations.slider_created'))
                    ->success()
                    ->send();
            })
            ->modalWidth(MaxWidth::SevenExtraLarge);
    }

    public function toggleAllSlidersAction(): Action
    {
        return Action::make('toggleAllSliders')
            ->label(__('translations.toggle_all_sliders'))
            ->icon('heroicon-o-power')
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

                $this->loadSliders();

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
            ->icon('heroicon-o-arrows-up-down')
            ->color('info')
            ->url(route('filament.admin.resources.sliders.index'))
            ->openUrlInNewTab();
    }

    public function duplicateSliderAction(Slider $slider): Action
    {
        return Action::make('duplicateSlider')
            ->label(__('translations.duplicate'))
            ->icon('heroicon-o-document-duplicate')
            ->color('info')
            ->action(function () use ($slider): void {
                $newSlider = $slider->replicate();
                $newSlider->title = $slider->title . ' (Copy)';
                $newSlider->sort_order = Slider::max('sort_order') + 1;
                $newSlider->save();

                // Copy translations
                foreach ($slider->translations as $translation) {
                    $newTranslation = $translation->replicate();
                    $newTranslation->slider_id = $newSlider->id;
                    $newTranslation->save();
                }

                // Copy media
                if ($slider->hasImage()) {
                    $media = $slider->getFirstMedia('slider_images');
                    $media->copy($newSlider, 'slider_images');
                }

                $this->loadSliders();

                Notification::make()
                    ->title(__('translations.slider_duplicated'))
                    ->success()
                    ->send();
            });
    }

    public function toggleSliderAction(Slider $slider): Action
    {
        return Action::make('toggleSlider')
            ->label($slider->is_active ? __('translations.deactivate') : __('translations.activate'))
            ->icon($slider->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
            ->color($slider->is_active ? 'danger' : 'success')
            ->action(function () use ($slider): void {
                $slider->update(['is_active' => !$slider->is_active]);
                $this->loadSliders();

                Notification::make()
                    ->title($slider->is_active
                        ? __('translations.slider_activated')
                        : __('translations.slider_deactivated'))
                    ->success()
                    ->send();
            });
    }

    public function deleteSliderAction(Slider $slider): Action
    {
        return Action::make('deleteSlider')
            ->label(__('translations.delete'))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function () use ($slider): void {
                $slider->delete();
                $this->loadSliders();

                Notification::make()
                    ->title(__('translations.slider_deleted'))
                    ->success()
                    ->send();
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->createSliderAction(),
            $this->toggleAllSlidersAction(),
            $this->reorderSlidersAction(),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'sliders' => $this->sliders,
        ];
    }

    public function getView(): string
    {
        return 'filament.pages.slider-management';
    }
}
