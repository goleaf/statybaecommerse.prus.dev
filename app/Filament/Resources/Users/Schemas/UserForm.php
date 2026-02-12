<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\CustomerGroup;
use App\Models\Organization;
use App\Models\Partner;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('userTabs')
                    ->tabs([
                        self::profileTab(),
                        self::addressTab(),
                        self::companyTab(),
                        self::organizationsTab(),
                        self::customerGroupsTab(),
                        self::partnersTab(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function profileTab(): Tab
    {
        return Tab::make(__('messages.profile'))
            ->schema([
                Section::make(__('messages.profile'))
                    ->schema([
                        Select::make('account_type')
                            ->label(__('messages.type'))
                            ->options([
                                'private' => __('messages.private_person'),
                                'company' => __('messages.company'),
                            ])
                            ->live()
                            ->default('private')
                            ->required(),
                        TextInput::make('first_name')
                            ->label(__('messages.first_name'))
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label(__('messages.last_name'))
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('messages.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label(__('messages.password'))
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                        TextInput::make('phone_number')
                            ->label(__('messages.phone'))
                            ->tel()
                            ->maxLength(255),
                        Select::make('gender')
                            ->label(__('messages.gender'))
                            ->options([
                                'male'   => __('admin.gender.male'),
                                'female' => __('admin.gender.female'),
                                'other'  => __('admin.gender.other'),
                            ]),
                        DateTimePicker::make('date_of_birth')
                            ->label(__('messages.birth_date')),
                        Toggle::make('is_active')
                            ->label(__('messages.active'))
                            ->default(true),
                        Select::make('preferred_locale')
                            ->label(__('messages.language'))
                            ->options([
                                'en' => __('translations.english'),
                                'lt' => __('translations.lithuanian'),
                                'ru' => __('translations.russian'),
                                'de' => __('translations.german'),
                            ])
                            ->default('lt'),
                    ])->columns(2),
            ]);
    }

    private static function addressTab(): Tab
    {
        return Tab::make(__('messages.address'))
            ->schema([
                Section::make(__('messages.address'))
                    ->schema([
                        TextInput::make('address')
                            ->label(__('messages.address'))
                            ->maxLength(255),
                        TextInput::make('postal_code')
                            ->label(__('messages.postal_code'))
                            ->maxLength(20),
                        Select::make('country_id')
                            ->label(__('messages.country'))
                            ->relationship('country', 'name')
                            ->getOptionLabelFromRecordUsing(static fn (Model $record): string => (string) ($record->getAttribute('name') ?: ('#' . $record->getKey())))
                            ->searchable()
                            ->preload(),
                        Select::make('city_id')
                            ->label(__('messages.city'))
                            ->relationship('city', 'name')
                            ->getOptionLabelFromRecordUsing(static fn (Model $record): string => (string) ($record->getAttribute('name') ?: ('#' . $record->getKey())))
                            ->searchable()
                            ->preload(),
                    ])->columns(2),
            ]);
    }

    private static function companyTab(): Tab
    {
        return Tab::make(__('messages.company'))
            ->schema([
                Section::make(__('messages.company'))
                    ->schema([
                        Select::make('company_id')
                            ->label(__('messages.company'))
                            ->relationship('organization', 'name')
                            ->getOptionLabelFromRecordUsing(static fn (Model $record): string => (string) ($record->getAttribute('name') ?: ('#' . $record->getKey())))
                            ->visible(fn (Get $get): bool => $get('account_type') === 'company')
                            ->required(fn (Get $get): bool => $get('account_type') === 'company')
                            ->searchable()
                            ->preload(),
                    ])->columns(1),
            ]);
    }

    private static function organizationsTab(): Tab
    {
        return Tab::make(__('admin.navigation.organizations'))
            ->visible(fn (string $operation): bool => $operation === 'create')
            ->schema([
                Section::make(__('admin.navigation.organizations'))
                    ->schema([
                        Select::make('organization_ids')
                            ->label(__('admin.navigation.organizations'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(fn (): array => self::optionsForModel(Organization::class))
                            ->default([]),
                    ])->columns(1),
            ]);
    }

    private static function customerGroupsTab(): Tab
    {
        return Tab::make(__('admin.navigation.customer_groups'))
            ->visible(fn (string $operation): bool => $operation === 'create')
            ->schema([
                Section::make(__('admin.navigation.customer_groups'))
                    ->schema([
                        Select::make('customer_group_ids')
                            ->label(__('admin.navigation.customer_groups'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(fn (): array => self::optionsForModel(CustomerGroup::class))
                            ->default([]),
                    ])->columns(1),
            ]);
    }

    private static function partnersTab(): Tab
    {
        return Tab::make(__('messages.partners'))
            ->visible(fn (string $operation): bool => $operation === 'create')
            ->schema([
                Section::make(__('messages.partners'))
                    ->schema([
                        Select::make('partner_ids')
                            ->label(__('messages.partners'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(fn (): array => self::optionsForModel(Partner::class))
                            ->default([]),
                    ])->columns(1),
            ]);
    }

    /**
     * @param  class-string<Model> $modelClass
     * @return array<int, string>
     */
    private static function optionsForModel(string $modelClass): array
    {
        /** @var array<int, string> $options */
        $options = $modelClass::query()
            ->withoutGlobalScopes()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(static fn (Model $record): array => [
                (int) $record->getKey() => self::resolveRecordLabel($record),
            ])
            ->all();

        return $options;
    }

    private static function resolveRecordLabel(Model $record): string
    {
        $name = $record->getAttribute('name');

        if (is_array($name)) {
            $locale = (string) app()->getLocale();
            $fallbackLocale = (string) config('app.fallback_locale', 'en');

            $name = $name[$locale]
                ?? $name[$fallbackLocale]
                ?? reset($name)
                ?? null;
        }

        if (is_string($name) && trim($name) !== '') {
            return $name;
        }

        return '#' . $record->getKey();
    }
}
