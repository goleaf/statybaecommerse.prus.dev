<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('userInfolistTabs')
                    ->tabs([
                        self::profileTab(),
                        self::companyTab(),
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
                        TextEntry::make('first_name')
                            ->label(__('messages.first_name')),
                        TextEntry::make('last_name')
                            ->label(__('messages.last_name')),
                        TextEntry::make('email')
                            ->label(__('messages.email')),
                        TextEntry::make('phone_number')
                            ->label(__('messages.phone')),
                        TextEntry::make('account_type')
                            ->label(__('messages.type'))
                            ->badge(),
                        TextEntry::make('gender')
                            ->label(__('messages.gender'))
                            ->formatStateUsing(static fn ($state) => match ($state) {
                                'male'   => __('admin.gender.male'),
                                'female' => __('admin.gender.female'),
                                'other'  => __('admin.gender.other'),
                                default  => $state,
                            }),
                        TextEntry::make('date_of_birth')
                            ->label(__('messages.birth_date'))
                            ->date(),
                        IconEntry::make('is_active')
                            ->label(__('messages.active'))
                            ->boolean(),
                        TextEntry::make('preferred_locale')
                            ->label(__('messages.language'))
                            ->formatStateUsing(static fn ($state) => match ($state) {
                                'en'    => __('translations.english'),
                                'lt'    => __('translations.lithuanian'),
                                'ru'    => __('translations.russian'),
                                'de'    => __('translations.german'),
                                default => $state,
                            }),
                    ])
                    ->columns(2),
            ]);
    }

    private static function companyTab(): Tab
    {
        return Tab::make(__('messages.company'))
            ->schema([
                Section::make(__('messages.company'))
                    ->schema([
                        TextEntry::make('companyRelation.name')
                            ->label(__('messages.name'))
                            ->placeholder('-'),
                        TextEntry::make('companyRelation.email')
                            ->label(__('messages.email'))
                            ->placeholder('-'),
                        TextEntry::make('companyRelation.phone')
                            ->label(__('messages.phone'))
                            ->placeholder('-'),
                        TextEntry::make('companyRelation.website')
                            ->label(__('users.website'))
                            ->placeholder('-'),
                        TextEntry::make('companyRelation.address')
                            ->label(__('messages.address'))
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    private static function addressesTab(): Tab
    {
        return Tab::make(__('messages.address'))
            ->schema([
                Section::make(__('messages.address'))
                    ->schema([
                        RepeatableEntry::make('addresses')
                            ->hiddenLabel()
                            ->getStateUsing(static function (User $record): array {
                                $record->loadMissing('addresses');

                                return $record->addresses
                                    ->sortByDesc('is_default')
                                    ->values()
                                    ->all();
                            })
                            ->schema([
                                TextEntry::make('type')
                                    ->label(__('messages.type'))
                                    ->badge(),
                                TextEntry::make('full_name')
                                    ->label(__('messages.name')),
                                TextEntry::make('address_line_1')
                                    ->label(__('messages.address')),
                                TextEntry::make('city')
                                    ->label(__('messages.city')),
                                TextEntry::make('postal_code')
                                    ->label(__('messages.postal_code')),
                                TextEntry::make('country_code')
                                    ->label(__('messages.country')),
                                IconEntry::make('is_default')
                                    ->label(__('messages.default'))
                                    ->boolean(),
                                IconEntry::make('is_active')
                                    ->label(__('messages.active'))
                                    ->boolean(),
                            ])
                            ->table([
                                TableColumn::make(__('messages.type')),
                                TableColumn::make(__('messages.name')),
                                TableColumn::make(__('messages.address')),
                                TableColumn::make(__('messages.city')),
                                TableColumn::make(__('messages.postal_code')),
                                TableColumn::make(__('messages.country')),
                                TableColumn::make(__('messages.default')),
                                TableColumn::make(__('messages.active')),
                            ]),
                    ]),
            ]);
    }

    private static function customerGroupsTab(): Tab
    {
        return Tab::make(__('admin.navigation.customer_groups'))
            ->schema([
                Section::make(__('admin.navigation.customer_groups'))
                    ->schema([
                        RepeatableEntry::make('customerGroups')
                            ->hiddenLabel()
                            ->getStateUsing(static function (User $record): array {
                                $record->loadMissing('customerGroups');

                                return $record->customerGroups
                                    ->sortBy('id')
                                    ->values()
                                    ->all();
                            })
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('messages.name'))
                                    ->formatStateUsing(static fn ($state): string => self::resolveTranslatableState($state)),
                                TextEntry::make('type')
                                    ->label(__('messages.type'))
                                    ->badge(),
                                TextEntry::make('discount_percentage')
                                    ->label(__('messages.discount'))
                                    ->suffix('%')
                                    ->placeholder('-'),
                                IconEntry::make('is_active')
                                    ->label(__('messages.active'))
                                    ->boolean(),
                                IconEntry::make('is_enabled')
                                    ->label(__('messages.enabled'))
                                    ->boolean(),
                            ])
                            ->table([
                                TableColumn::make(__('messages.name')),
                                TableColumn::make(__('messages.type')),
                                TableColumn::make(__('messages.discount')),
                                TableColumn::make(__('messages.active')),
                                TableColumn::make(__('messages.enabled')),
                            ]),
                    ]),
            ]);
    }

    private static function partnersTab(): Tab
    {
        return Tab::make(__('messages.partners'))
            ->schema([
                Section::make(__('messages.partners'))
                    ->schema([
                        RepeatableEntry::make('partners')
                            ->hiddenLabel()
                            ->getStateUsing(static function (User $record): array {
                                $record->loadMissing('partners');

                                return $record->partners
                                    ->sortBy('name')
                                    ->values()
                                    ->all();
                            })
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('messages.name')),
                                TextEntry::make('code')
                                    ->label(__('messages.code')),
                                TextEntry::make('contact_email')
                                    ->label(__('messages.email'))
                                    ->placeholder('-'),
                                TextEntry::make('discount_rate')
                                    ->label(__('messages.discount'))
                                    ->suffix('%')
                                    ->placeholder('-'),
                                IconEntry::make('is_enabled')
                                    ->label(__('messages.enabled'))
                                    ->boolean(),
                            ])
                            ->table([
                                TableColumn::make(__('messages.name')),
                                TableColumn::make(__('messages.code')),
                                TableColumn::make(__('messages.email')),
                                TableColumn::make(__('messages.discount')),
                                TableColumn::make(__('messages.enabled')),
                            ]),
                    ]),
            ]);
    }

    private static function resolveTranslatableState(mixed $state): string
    {
        if (is_string($state)) {
            return $state;
        }

        if (! is_array($state)) {
            return '-';
        }

        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale', 'en');

        return (string) ($state[$locale] ?? $state[$fallback] ?? reset($state) ?: '-');
    }
}
