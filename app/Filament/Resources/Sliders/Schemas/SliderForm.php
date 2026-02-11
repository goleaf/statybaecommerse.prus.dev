<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sliders\Schemas;

use App\Models\Slider;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use App\Support\Filament\Components\SearchableInput;
use App\Support\Filament\Forms\Components\SortOrderInput;
use App\Support\Filament\SearchableInputHelper;
use App\Support\Search\ContentLinkSearch;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SliderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('translations.title'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label(__('translations.title'))
                                ->required()
                                ->maxLength(255)
                                ->live()
                                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                            TextInput::make('slug')
                                ->label(__('messages.slug'))
                                ->required()
                                ->maxLength(255)
                                ->unique(Slider::class, 'slug', ignoreRecord: true),
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
                                'grid',
                                'gridDelete',
                                'textColor',
                            ])
                            ->textColors([
                                'primary' => '#1d4ed8',
                                'emerald' => '#047857',
                                'amber'   => '#f59e0b',
                                'slate'   => '#475569',
                            ]),
                        Grid::make(3)->schema([
                            TextInput::make('button_text')
                                ->label(__('translations.button_text'))
                                ->maxLength(255),
                            SearchableInput::make('button_url')
                                ->label(__('translations.button_url'))
                                ->placeholder(__('translations.button_url'))
                                ->searchUsing(fn (string $value): array => ContentLinkSearch::results($value))
                                ->dehydrateStateUsing(fn (?string $state): ?string => $state !== null && $state !== '' ? $state : null)
                                ->afterStateHydrated(function (SearchableInput $component, ?string $state): void {
                                    SearchableInputHelper::hydrate(
                                        $component,
                                        $state,
                                        static fn (int|string $value): ?array => ['value' => $value, 'label' => $value],
                                    );
                                })
                                ->afterStateUpdated(function (SearchableInput $component, ?string $state, callable $set): void {
                                    if ($state !== null && $state !== '') {
                                        return;
                                    }
                                    SearchableInputHelper::clear($component, $set, ['button_url' => null]);
                                }),
                        ]),
                    ])
                    ->columnSpanFull(),

                Section::make(__('translations.media'))
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('slider_image')
                            ->label(__('translations.slider_image'))
                            ->collection('slider_images')
                            ->image()
                            ->visibility('private')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(5120),  // 5MB
                        SpatieMediaLibraryFileUpload::make('mobile_image')
                            ->label(__('translations.mobile_image'))
                            ->collection('mobile_images')
                            ->image()
                            ->visibility('private')
                            ->imageEditor()
                            ->maxSize(2048),  // 2MB
                    ])
                    ->columnSpanFull(),

                Section::make(__('translations.design'))
                    ->schema([
                        Grid::make(3)->schema([
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
                        Grid::make(2)->schema([
                            Select::make('text_alignment')
                                ->label(__('translations.text_alignment'))
                                ->options([
                                    'left'   => __('translations.left'),
                                    'center' => __('translations.center'),
                                    'right'  => __('translations.right'),
                                ])
                                ->default('center'),
                            Select::make('content_position')
                                ->label(__('translations.content_position'))
                                ->options([
                                    'top-left'      => __('translations.top_left'),
                                    'top-center'    => __('translations.top_center'),
                                    'top-right'     => __('translations.top_right'),
                                    'center-left'   => __('translations.center_left'),
                                    'center'        => __('translations.center'),
                                    'center-right'  => __('translations.center_right'),
                                    'bottom-left'   => __('translations.bottom_left'),
                                    'bottom-center' => __('translations.bottom_center'),
                                    'bottom-right'  => __('translations.bottom_right'),
                                ])
                                ->default('center'),
                        ]),
                    ])
                    ->columnSpanFull(),

                Section::make(__('translations.animation_settings'))
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('settings.animation')
                                ->label(__('translations.animation_type'))
                                ->options([
                                    'fade'   => __('translations.fade'),
                                    'slide'  => __('translations.slide'),
                                    'zoom'   => __('translations.zoom'),
                                    'flip'   => __('translations.flip'),
                                    'bounce' => __('translations.bounce'),
                                    'pulse'  => __('translations.pulse'),
                                ])
                                ->default('fade'),
                            TextInput::make('settings.duration')
                                ->label(__('translations.duration'))
                                ->numeric()
                                ->default(5000)
                                ->suffix('ms')
                                ->minValue(1000)
                                ->maxValue(30000),
                        ]),
                        Grid::make(2)->schema([
                            Toggle::make('settings.autoplay')
                                ->label(__('translations.autoplay'))
                                ->default(true)
                                ->live(),
                            Toggle::make('settings.pause_on_hover')
                                ->label(__('translations.pause_on_hover'))
                                ->default(true)
                                ->visible(fn (callable $get) => $get('settings.autoplay')),
                        ]),
                        Select::make('settings.transition_speed')
                            ->label(__('translations.transition_speed'))
                            ->options([
                                'slow'   => __('translations.slow'),
                                'normal' => __('translations.normal'),
                                'fast'   => __('translations.fast'),
                            ])
                            ->default('normal'),
                    ])
                    ->columnSpanFull(),

                Section::make(__('translations.scheduling'))
                    ->schema([
                        Grid::make(2)->schema([
                            SupportFlatpickr::makeDateTime('start_date')
                                ->label(__('translations.start_date'))
                                ->default(now()),
                            SupportFlatpickr::makeDateTime('end_date')
                                ->label(__('translations.end_date'))
                                ->after('start_date'),
                        ]),
                        Toggle::make('is_scheduled')
                            ->label(__('translations.is_scheduled'))
                            ->default(false)
                            ->live(),
                    ])
                    ->columnSpanFull(),

                Section::make(__('translations.all_sliders_activated'))
                    ->schema([
                        Grid::make(2)->schema([
                            SortOrderInput::make(label: __('translations.reorder_sliders')),
                            Select::make('priority')
                                ->label(__('translations.priority'))
                                ->options([
                                    'low'    => __('translations.low'),
                                    'normal' => __('translations.normal'),
                                    'high'   => __('translations.high'),
                                    'urgent' => __('translations.urgent'),
                                ])
                                ->default('normal'),
                        ]),
                        TagsInput::make('tags')
                            ->label(__('translations.tags'))
                            ->placeholder(__('translations.add_tags')),
                        KeyValue::make('custom_attributes')
                            ->label(__('translations.custom_attributes'))
                            ->keyLabel(__('translations.title'))
                            ->valueLabel(__('translations.description')),
                        Repeater::make('slides')
                            ->label(__('translations.additional_slides'))
                            ->schema([
                                TextInput::make('title')
                                    ->label(__('translations.slide_title'))
                                    ->required(),
                                FileUpload::make('image')
                                    ->label(__('translations.slide_image'))
                                    ->image()
                                    ->directory('sliders/slides'),
                                SearchableInput::make('link')
                                    ->label(__('translations.button_url'))
                                    ->placeholder(__('translations.button_url'))
                                    ->searchUsing(fn (string $value): array => ContentLinkSearch::results($value))
                                    ->dehydrateStateUsing(fn (?string $state): ?string => $state !== null && $state !== '' ? $state : null)
                                    ->afterStateHydrated(function (SearchableInput $component, ?string $state): void {
                                        SearchableInputHelper::hydrate(
                                            $component,
                                            $state,
                                            static fn (int|string $value): ?array => ['value' => $value, 'label' => $value],
                                        );
                                    })
                                    ->afterStateUpdated(function (SearchableInput $component, ?string $state, callable $set): void {
                                        if ($state !== null && $state !== '') {
                                            return;
                                        }
                                        SearchableInputHelper::clear($component, $set, ['link' => null]);
                                    }),
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                    ])
                    ->columnSpanFull(),

                Section::make(__('translations.settings'))
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_active')
                                ->label(__('translations.is_active'))
                                ->default(true),
                            Toggle::make('is_featured')
                                ->label(__('messages.featured'))
                                ->default(false),
                        ]),
                        CheckboxList::make('target_audience')
                            ->label(__('translations.target_audience'))
                            ->options([
                                'all'       => __('translations.all_users'),
                                'new'       => __('translations.new_users'),
                                'returning' => __('translations.returning_users'),
                                'premium'   => __('translations.premium_users'),
                            ])
                            ->default(['all']),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
