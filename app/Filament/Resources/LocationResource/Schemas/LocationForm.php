<?php

declare(strict_types=1);

namespace App\Filament\Resources\LocationResource\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('locationTabs')
                    ->tabs(array_merge(
                        [self::generalTab()],
                        self::localeTabs(),
                    ))
                    ->columnSpanFull(),
            ]);
    }

    private static function generalTab(): Tab
    {
        return Tab::make(__('messages.General'))
            ->schema([
                Section::make(__('admin.locations_page.details_title'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('code')
                                    ->label(__('messages.code'))
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true),
                                Select::make('type')
                                    ->label(__('messages.type'))
                                    ->options(self::typeOptions())
                                    ->required(),
                                TextInput::make('sort_order')
                                    ->label(__('messages.sort_order'))
                                    ->numeric()
                                    ->default(0),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_enabled')
                                    ->label(__('messages.is_enabled'))
                                    ->default(true),
                                Toggle::make('is_default')
                                    ->label(__('messages.is_default'))
                                    ->default(false),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make(__('messages.address'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('address_line_1')
                                    ->label(__('messages.address_line_1'))
                                    ->maxLength(255),
                                TextInput::make('address_line_2')
                                    ->label(__('messages.address_line_2'))
                                    ->maxLength(255),
                                TextInput::make('city')
                                    ->label(__('messages.city'))
                                    ->maxLength(100),
                                TextInput::make('state')
                                    ->label(__('messages.state'))
                                    ->maxLength(100),
                                TextInput::make('postal_code')
                                    ->label(__('messages.postal_code'))
                                    ->maxLength(20),
                                TextInput::make('country_code')
                                    ->label(__('messages.country_code'))
                                    ->maxLength(3),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make(__('messages.contact_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('phone')
                                    ->label(__('messages.phone'))
                                    ->tel()
                                    ->maxLength(20),
                                TextInput::make('email')
                                    ->label(__('messages.email'))
                                    ->email()
                                    ->maxLength(255),
                                TextInput::make('contact_info.map_url')
                                    ->label(__('messages.map_url'))
                                    ->url()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make(__('messages.coordinates'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('latitude')
                                    ->label(__('messages.latitude'))
                                    ->numeric()
                                    ->minValue(-90)
                                    ->maxValue(90),
                                TextInput::make('longitude')
                                    ->label(__('messages.longitude'))
                                    ->numeric()
                                    ->minValue(-180)
                                    ->maxValue(180),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make(__('messages.Hours'))
                    ->schema([
                        Repeater::make('opening_hours')
                            ->label(__('messages.Hours'))
                            ->schema([
                                Select::make('day')
                                    ->label(__('messages.day'))
                                    ->options(self::dayOptions())
                                    ->required(),
                                TextInput::make('open_time')
                                    ->label(__('messages.open_time'))
                                    ->placeholder('09:00'),
                                TextInput::make('close_time')
                                    ->label(__('messages.close_time'))
                                    ->placeholder('17:00'),
                                Toggle::make('is_closed')
                                    ->label(__('messages.is_closed')),
                            ])
                            ->columns(4)
                            ->reorderable(false)
                            ->defaultItems(0),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<int, Tab>
     */
    private static function localeTabs(): array
    {
        $locales = self::resolveLocales();
        $defaultLocale = self::defaultLocale();

        return array_map(
            static fn (string $locale): Tab => Tab::make(self::localeLabel($locale))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make("name.{$locale}")
                                ->label(__('messages.name'))
                                ->required($locale === $defaultLocale)
                                ->maxLength(255),
                            TextInput::make("slug.{$locale}")
                                ->label(__('messages.slug'))
                                ->required($locale === $defaultLocale)
                                ->maxLength(255),
                        ]),
                    Textarea::make("description.{$locale}")
                        ->label(__('messages.description'))
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
            $locales,
        );
    }

    /**
     * @return array<int, string>
     */
    private static function resolveLocales(): array
    {
        $rawLocales = config('filament-language-tabs.default_locales', []);

        if (! is_array($rawLocales)) {
            $rawLocales = explode(',', (string) $rawLocales);
        }

        $normalized = array_map(
            static fn (string $locale): string => trim($locale),
            $rawLocales,
        );

        return array_values(array_filter($normalized, static fn (string $locale): bool => $locale !== ''));
    }

    private static function defaultLocale(): string
    {
        return (string) config('app.locale', 'en');
    }

    private static function localeLabel(string $locale): string
    {
        $locales = config('app.locales', []);

        if (is_array($locales) && isset($locales[$locale]['native'])) {
            return (string) $locales[$locale]['native'];
        }

        return strtoupper($locale);
    }

    /**
     * @return array<string, string>
     */
    private static function typeOptions(): array
    {
        $types = ['warehouse', 'store', 'office', 'pickup_point', 'other'];

        return array_combine(
            $types,
            array_map(static fn (string $type): string => Str::headline($type), $types),
        );
    }

    /**
     * @return array<string, string>
     */
    private static function dayOptions(): array
    {
        return [
            'monday'    => __('messages.monday'),
            'tuesday'   => __('messages.tuesday'),
            'wednesday' => __('messages.wednesday'),
            'thursday'  => __('messages.thursday'),
            'friday'    => __('messages.friday'),
            'saturday'  => __('messages.saturday'),
            'sunday'    => __('messages.sunday'),
        ];
    }
}
