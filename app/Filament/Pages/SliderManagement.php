<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Slider;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
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
                Section::make(__('translations.basic_information'))
                    ->components([
                        Grid::make(2)->components([
                            TextInput::make('title')
                                ->label(__('translations.title'))
                                ->required()
                                ->maxLength(255)
                                ->live()
                                ->afterStateUpdated(fn($state, callable $set) => $set('slug', \Str::slug($state))),
                            TextInput::make('slug')
                                ->label(__('translations.slug'))
                                ->required()
                                ->maxLength(255)
                                ->unique(Slider::class, 'slug')
                                ->disabled()
                                ->dehydrated(),
                        ]),
                        RichEditor::make('description')
                            ->label(__('translations.description'))
                            ->maxLength(2000)
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'bulletList',
                                'orderedList',
                            ]),
                        Grid::make(2)->components([
                            TextInput::make('button_text')
                                ->label(__('translations.button_text'))
                                ->maxLength(255),
                            TextInput::make('button_url')
                                ->label(__('translations.button_url'))
                                ->url()
                                ->maxLength(255),
                        ]),
                    ])
                    ->collapsible(),
                Section::make(__('translations.media'))
                    ->components([
                        FileUpload::make('slider_image')
                            ->label(__('translations.slider_image'))
                            ->image()
                            ->directory('sliders/images')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(5120),  // 5MB
                        FileUpload::make('mobile_image')
                            ->label(__('translations.mobile_image'))
                            ->image()
                            ->directory('sliders/mobile')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(2048),  // 2MB
                    ])
                    ->collapsible(),
                Section::make(__('translations.design'))
                    ->components([
                        Grid::make(3)->components([
                            ColorPicker::make('background_color')
                                ->label(__('translations.background_color'))
                                ->default('#ffffff'),
                            ColorPicker::make('text_color')
                                ->label(__('translations.text_color'))
                                ->default('#000000'),
                            ColorPicker::make('button_color')
                                ->label(__('translations.button_color'))
                                ->default('#007bff'),
                        ]),
                        Grid::make(2)->components([
                            Select::make('text_alignment')
                                ->label(__('translations.text_alignment'))
                                ->options([
                                    'left' => __('translations.left'),
                                    'center' => __('translations.center'),
                                    'right' => __('translations.right'),
                                ])
                                ->default('center'),
                            Select::make('content_position')
                                ->label(__('translations.content_position'))
                                ->options([
                                    'top-left' => __('translations.top_left'),
                                    'top-center' => __('translations.top_center'),
                                    'top-right' => __('translations.top_right'),
                                    'center-left' => __('translations.center_left'),
                                    'center' => __('translations.center'),
                                    'center-right' => __('translations.center_right'),
                                    'bottom-left' => __('translations.bottom_left'),
                                    'bottom-center' => __('translations.bottom_center'),
                                    'bottom-right' => __('translations.bottom_right'),
                                ])
                                ->default('center'),
                        ]),
                    ])
                    ->collapsible(),
                Section::make(__('translations.animation_settings'))
                    ->components([
                        Grid::make(2)->components([
                            Select::make('animation_type')
                                ->label(__('translations.animation_type'))
                                ->options([
                                    'fade' => __('translations.fade'),
                                    'slide' => __('translations.slide'),
                                    'zoom' => __('translations.zoom'),
                                    'flip' => __('translations.flip'),
                                    'bounce' => __('translations.bounce'),
                                    'pulse' => __('translations.pulse'),
                                ])
                                ->default('fade')
                                ->live(),
                            TextInput::make('duration')
                                ->label(__('translations.duration'))
                                ->numeric()
                                ->default(5000)
                                ->suffix('ms')
                                ->minValue(1000)
                                ->maxValue(30000),
                        ]),
                        Grid::make(2)->components([
                            Toggle::make('autoplay')
                                ->label(__('translations.autoplay'))
                                ->default(true)
                                ->live(),
                            Toggle::make('pause_on_hover')
                                ->label(__('translations.pause_on_hover'))
                                ->default(true)
                                ->visible(fn(callable $get) => $get('autoplay')),
                        ]),
                        Select::make('transition_speed')
                            ->label(__('translations.transition_speed'))
                            ->options([
                                'slow' => __('translations.slow'),
                                'normal' => __('translations.normal'),
                                'fast' => __('translations.fast'),
                            ])
                            ->default('normal'),
                    ])
                    ->collapsible(),
                Section::make(__('translations.scheduling'))
                    ->components([
                        Grid::make(2)->components([
                            DateTimePicker::make('start_date')
                                ->label(__('translations.start_date'))
                                ->default(now()),
                            DateTimePicker::make('end_date')
                                ->label(__('translations.end_date'))
                                ->after('start_date'),
                        ]),
                        Toggle::make('is_scheduled')
                            ->label(__('translations.is_scheduled'))
                            ->default(false)
                            ->live(),
                    ])
                    ->collapsible(),
                Section::make(__('translations.advanced_settings'))
                    ->components([
                        Grid::make(2)->components([
                            TextInput::make('sort_order')
                                ->label(__('translations.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                            Select::make('priority')
                                ->label(__('translations.priority'))
                                ->options([
                                    'low' => __('translations.low'),
                                    'normal' => __('translations.normal'),
                                    'high' => __('translations.high'),
                                    'urgent' => __('translations.urgent'),
                                ])
                                ->default('normal'),
                        ]),
                        TagsInput::make('tags')
                            ->label(__('translations.tags'))
                            ->placeholder(__('translations.add_tags')),
                        KeyValue::make('custom_attributes')
                            ->label(__('translations.custom_attributes'))
                            ->keyLabel(__('translations.attribute_name'))
                            ->valueLabel(__('translations.attribute_value')),
                        Repeater::make('slides')
                            ->label(__('translations.additional_slides'))
                            ->components([
                                TextInput::make('title')
                                    ->label(__('translations.slide_title'))
                                    ->required(),
                                FileUpload::make('image')
                                    ->label(__('translations.slide_image'))
                                    ->image()
                                    ->directory('sliders/slides'),
                                TextInput::make('link')
                                    ->label(__('translations.slide_link'))
                                    ->url(),
                            ])
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['title'] ?? null),
                    ])
                    ->collapsible(),
                Section::make(__('translations.status'))
                    ->components([
                        Grid::make(2)->components([
                            Toggle::make('is_active')
                                ->label(__('translations.is_active'))
                                ->default(true),
                            Toggle::make('is_featured')
                                ->label(__('translations.is_featured'))
                                ->default(false),
                        ]),
                        CheckboxList::make('target_audience')
                            ->label(__('translations.target_audience'))
                            ->options([
                                'all' => __('translations.all_users'),
                                'new' => __('translations.new_users'),
                                'returning' => __('translations.returning_users'),
                                'premium' => __('translations.premium_users'),
                            ])
                            ->default(['all']),
                    ])
                    ->collapsible(),
            ])
            ->action(function (array $data): void {
                $slider = Slider::create([
                    'title' => $data['title'],
                    'slug' => $data['slug'],
                    'description' => $data['description'],
                    'button_text' => $data['button_text'] ?? null,
                    'button_url' => $data['button_url'] ?? null,
                    'background_color' => $data['background_color'] ?? '#ffffff',
                    'text_color' => $data['text_color'] ?? '#000000',
                    'button_color' => $data['button_color'] ?? '#007bff',
                    'text_alignment' => $data['text_alignment'] ?? 'center',
                    'content_position' => $data['content_position'] ?? 'center',
                    'sort_order' => $data['sort_order'] ?? 0,
                    'priority' => $data['priority'] ?? 'normal',
                    'tags' => $data['tags'] ?? [],
                    'custom_attributes' => $data['custom_attributes'] ?? [],
                    'target_audience' => $data['target_audience'] ?? ['all'],
                    'is_active' => $data['is_active'] ?? true,
                    'is_featured' => $data['is_featured'] ?? false,
                    'is_scheduled' => $data['is_scheduled'] ?? false,
                    'start_date' => $data['start_date'] ?? null,
                    'end_date' => $data['end_date'] ?? null,
                    'settings' => [
                        'animation' => $data['animation_type'] ?? 'fade',
                        'duration' => $data['duration'] ?? 5000,
                        'autoplay' => $data['autoplay'] ?? true,
                        'pause_on_hover' => $data['pause_on_hover'] ?? true,
                        'transition_speed' => $data['transition_speed'] ?? 'normal',
                    ],
                    'slides' => $data['slides'] ?? [],
                ]);

                // Handle file uploads
                if (isset($data['slider_image'])) {
                    $slider
                        ->addMediaFromDisk($data['slider_image'], 'public')
                        ->toMediaCollection('slider_images');
                }

                if (isset($data['mobile_image'])) {
                    $slider
                        ->addMediaFromDisk($data['mobile_image'], 'public')
                        ->toMediaCollection('mobile_images');
                }

                // Handle additional slides
                if (isset($data['slides']) && is_array($data['slides'])) {
                    foreach ($data['slides'] as $slideData) {
                        if (isset($slideData['image'])) {
                            $slider
                                ->addMediaFromDisk($slideData['image'], 'public')
                                ->toMediaCollection('additional_slides');
                        }
                    }
                }

                $this->loadSliders();

                Notification::make()
                    ->title(__('translations.slider_created'))
                    ->body(__('translations.slider_created_successfully'))
                    ->success()
                    ->send();
            })
            ->modalWidth(Width::SevenExtraLarge);
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

    public function bulkImportAction(): Action
    {
        return Action::make('bulkImport')
            ->label(__('translations.bulk_import'))
            ->icon('heroicon-o-arrow-up-tray')
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
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->form([
                Select::make('format')
                    ->label(__('translations.export_format'))
                    ->options([
                        'excel' => __('translations.excel'),
                        'csv' => __('translations.csv'),
                        'json' => __('translations.json'),
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

    public function analyticsAction(): Action
    {
        return Action::make('analytics')
            ->label(__('translations.analytics'))
            ->icon('heroicon-o-chart-bar')
            ->color('warning')
            ->url(route('filament.admin.pages.slider-analytics'))
            ->openUrlInNewTab();
    }

    public function settingsAction(): Action
    {
        return Action::make('settings')
            ->label(__('translations.settings'))
            ->icon('heroicon-o-cog-6-tooth')
            ->color('gray')
            ->form([
                Section::make(__('translations.global_settings'))
                    ->components([
                        Toggle::make('auto_optimize_images')
                            ->label(__('translations.auto_optimize_images'))
                            ->default(true),
                        Select::make('default_animation')
                            ->label(__('translations.default_animation'))
                            ->options([
                                'fade' => __('translations.fade'),
                                'slide' => __('translations.slide'),
                                'zoom' => __('translations.zoom'),
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

    protected function getHeaderActions(): array
    {
        return [
            $this->createSliderAction(),
            $this->bulkImportAction(),
            $this->exportSlidersAction(),
            $this->analyticsAction(),
            $this->settingsAction(),
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
