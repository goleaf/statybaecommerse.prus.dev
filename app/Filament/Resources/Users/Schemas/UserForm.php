<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\AddressType;
use App\Enums\Industry;
use App\Models\Company;
use App\Models\CustomerGroup;
use App\Models\Partner;
use App\Models\PartnerTier;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
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
                        Repeater::make('addresses')
                            ->label(__('messages.address'))
                            ->relationship('addresses')
                            ->defaultItems(0)
                            ->addable()
                            ->addActionLabel(__('messages.add_new_address'))
                            ->collapsible()
                            ->cloneable()
                            ->reorderableWithButtons()
                            ->itemLabel(static function (array $state): ?string {
                                $name = trim(((string) ($state['first_name'] ?? '')) . ' ' . ((string) ($state['last_name'] ?? '')));
                                $street = trim((string) ($state['address_line_1'] ?? ''));

                                $label = trim($name . ($street !== '' ? " - {$street}" : ''));

                                return $label !== '' ? $label : __('messages.address');
                            })
                            ->schema([
                                Select::make('type')
                                    ->label(__('messages.type'))
                                    ->options(AddressType::options())
                                    ->default(AddressType::SHIPPING->value)
                                    ->required(),
                                TextInput::make('first_name')
                                    ->label(__('messages.first_name'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('last_name')
                                    ->label(__('messages.last_name'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('company_name')
                                    ->label(__('messages.company'))
                                    ->maxLength(255),
                                TextInput::make('company_vat')
                                    ->label('VAT code')
                                    ->maxLength(255),
                                TextInput::make('address_line_1')
                                    ->label(__('messages.address'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('address_line_2')
                                    ->label(__('messages.address_line_2'))
                                    ->maxLength(255),
                                TextInput::make('apartment')
                                    ->label(__('translations.apartment'))
                                    ->maxLength(255),
                                TextInput::make('floor')
                                    ->label(__('translations.floor'))
                                    ->maxLength(255),
                                TextInput::make('building')
                                    ->label(__('translations.building'))
                                    ->maxLength(255),
                                TextInput::make('city')
                                    ->label(__('messages.city'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('state')
                                    ->label(__('messages.state'))
                                    ->maxLength(255),
                                TextInput::make('postal_code')
                                    ->label(__('messages.postal_code'))
                                    ->required()
                                    ->maxLength(20),
                                TextInput::make('country_code')
                                    ->label(__('messages.country'))
                                    ->required()
                                    ->maxLength(2),
                                TextInput::make('phone')
                                    ->label(__('messages.phone'))
                                    ->tel()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label(__('messages.email'))
                                    ->email()
                                    ->maxLength(255),
                                TextInput::make('landmark')
                                    ->label(__('translations.landmark'))
                                    ->maxLength(255),
                                Textarea::make('notes')
                                    ->label(__('messages.notes'))
                                    ->columnSpanFull(),
                                Textarea::make('instructions')
                                    ->label(__('translations.instructions'))
                                    ->columnSpanFull(),
                                Toggle::make('is_default')
                                    ->label(__('messages.default'))
                                    ->default(false),
                                Toggle::make('is_billing')
                                    ->label(__('translations.billing_address'))
                                    ->default(false),
                                Toggle::make('is_shipping')
                                    ->label(__('translations.shipping_address'))
                                    ->default(false),
                                Toggle::make('is_active')
                                    ->label(__('messages.active'))
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
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
                            ->relationship(
                                name: 'companyRelation',
                                titleAttribute: 'name',
                                modifyQueryUsing: static fn (Builder $query): Builder => $query
                                    ->withoutGlobalScopes()
                                    ->orderBy('name'),
                            )
                            ->getOptionLabelFromRecordUsing(static fn (Model $record): string => (string) ($record->getAttribute('name') ?: ('#' . $record->getKey())))
                            ->required(fn (Get $get): bool => $get('account_type') === 'company')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(self::companyOptionSchema())
                            ->createOptionUsing(static fn (array $data): int => (int) Company::query()->create(self::prepareCompanyPayload($data))->getKey())
                            ->editOptionForm(self::companyOptionSchema())
                            ->updateOptionUsing(static function (array $data, Schema $schema): void {
                                $record = $schema->getRecord();

                                if (! $record instanceof Company) {
                                    return;
                                }

                                $record->update(self::prepareCompanyPayload($data));
                            }),
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
                            ->createOptionForm(self::customerGroupOptionSchema())
                            ->createOptionUsing(static fn (array $data): int => (int) CustomerGroup::query()->create(self::prepareCustomerGroupPayload($data))->getKey())
                            ->editOptionForm(self::customerGroupOptionSchema())
                            ->updateOptionUsing(static function (array $data, Schema $schema): void {
                                $record = $schema->getRecord();

                                if (! $record instanceof CustomerGroup) {
                                    return;
                                }

                                $record->update(self::prepareCustomerGroupPayload($data));
                            })
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
                            ->createOptionForm(self::partnerOptionSchema())
                            ->createOptionUsing(static fn (array $data): int => (int) Partner::query()->create(self::preparePartnerPayload($data))->getKey())
                            ->editOptionForm(self::partnerOptionSchema())
                            ->updateOptionUsing(static function (array $data, Schema $schema): void {
                                $record = $schema->getRecord();

                                if (! $record instanceof Partner) {
                                    return;
                                }

                                $record->update(self::preparePartnerPayload($data));
                            })
                            ->default([]),
                    ])->columns(1),
            ]);
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    private static function companyOptionSchema(): array
    {
        return [
            TextInput::make('name')
                ->label(__('messages.name'))
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->label(__('messages.email'))
                ->email()
                ->maxLength(255),
            TextInput::make('phone')
                ->label(__('messages.phone'))
                ->tel()
                ->maxLength(255),
            TextInput::make('website')
                ->label(__('users.website'))
                ->url()
                ->maxLength(255),
            TextInput::make('address')
                ->label(__('messages.address'))
                ->maxLength(255)
                ->columnSpanFull(),
            Select::make('industry')
                ->label(__('messages.industry'))
                ->options(Industry::class)
                ->searchable()
                ->preload(),
            Select::make('size')
                ->label(__('messages.company_size'))
                ->options([
                    'small'  => __('messages.company_size_small'),
                    'medium' => __('messages.company_size_medium'),
                    'large'  => __('messages.company_size_large'),
                ]),
            Textarea::make('description')
                ->label(__('messages.description'))
                ->columnSpanFull(),
            KeyValue::make('metadata')
                ->label('Metadata')
                ->nullable()
                ->columnSpanFull(),
            Toggle::make('is_active')
                ->label(__('messages.active'))
                ->default(true),
        ];
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    private static function customerGroupOptionSchema(): array
    {
        return [
            TextInput::make('name')
                ->label(__('messages.name'))
                ->required()
                ->maxLength(255),
            TextInput::make('code')
                ->label(__('messages.code'))
                ->maxLength(50),
            Select::make('type')
                ->label(__('messages.type'))
                ->options([
                    'retail'    => __('messages.retail'),
                    'wholesale' => __('messages.wholesale'),
                    'b2b'       => __('messages.b2b'),
                    'internal'  => __('messages.internal'),
                ])
                ->default('retail')
                ->required(),
            TextInput::make('color')
                ->label(__('messages.color'))
                ->maxLength(32),
            TextInput::make('icon')
                ->label(__('admin.news_images.image'))
                ->maxLength(64),
            Textarea::make('description')
                ->label(__('messages.description'))
                ->columnSpanFull(),
            TextInput::make('discount_percentage')
                ->label(__('admin.products.price_increase_percentage'))
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->suffix('%'),
            TextInput::make('discount_fixed')
                ->label(__('messages.discount_amount'))
                ->numeric()
                ->minValue(0),
            Toggle::make('has_special_pricing')
                ->label(__('ui.has_special_pricing'))
                ->default(false),
            Toggle::make('has_volume_discounts')
                ->label(__('ui.has_volume_discounts'))
                ->default(false),
            Toggle::make('can_view_prices')
                ->label(__('ui.can_view_prices'))
                ->default(true),
            Toggle::make('can_place_orders')
                ->label(__('ui.can_place_orders'))
                ->default(true),
            Toggle::make('can_view_catalog')
                ->label(__('ui.can_view_catalog'))
                ->default(true),
            Toggle::make('can_use_coupons')
                ->label(__('ui.can_use_coupons'))
                ->default(true),
            TextInput::make('minimum_order_amount')
                ->label(__('ui.minimum_order_amount'))
                ->numeric()
                ->minValue(0),
            TextInput::make('credit_limit')
                ->label(__('ui.credit_limit'))
                ->numeric()
                ->minValue(0),
            TextInput::make('payment_terms')
                ->label(__('messages.payment_method'))
                ->maxLength(255)
                ->default('net_30'),
            TextInput::make('sort_order')
                ->label(__('messages.sort'))
                ->numeric()
                ->default(0),
            KeyValue::make('metadata')
                ->label('Metadata')
                ->nullable()
                ->columnSpanFull(),
            KeyValue::make('conditions')
                ->label('Conditions')
                ->nullable()
                ->columnSpanFull(),
            Toggle::make('is_active')
                ->label(__('messages.active'))
                ->default(true),
            Toggle::make('is_enabled')
                ->label(__('messages.enabled'))
                ->default(true),
            Toggle::make('is_default')
                ->label(__('messages.default'))
                ->default(false),
        ];
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    private static function partnerOptionSchema(): array
    {
        return [
            TextInput::make('name')
                ->label(__('messages.name'))
                ->required()
                ->maxLength(255),
            TextInput::make('code')
                ->label(__('messages.code'))
                ->required()
                ->maxLength(255),
            TextInput::make('contact_email')
                ->label(__('messages.email'))
                ->email()
                ->maxLength(255),
            TextInput::make('contact_phone')
                ->label(__('messages.phone'))
                ->tel()
                ->maxLength(255),
            TextInput::make('discount_rate')
                ->label(__('messages.discount'))
                ->numeric()
                ->step(0.01)
                ->suffix('%'),
            TextInput::make('commission_rate')
                ->label(__('messages.admin_widgets.average_order_value'))
                ->numeric()
                ->step(0.01)
                ->suffix('%'),
            Select::make('tier_id')
                ->label(__('messages.partner_tiers'))
                ->options(static fn (): array => PartnerTier::query()
                    ->orderBy('priority')
                    ->pluck('name', 'id')
                    ->all())
                ->searchable()
                ->preload(),
            KeyValue::make('metadata')
                ->label('Metadata')
                ->nullable()
                ->columnSpanFull(),
            Toggle::make('is_enabled')
                ->label(__('messages.enabled'))
                ->default(true),
        ];
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

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function prepareCustomerGroupPayload(array $data): array
    {
        return [
            'name'                 => self::nullableString($data['name'] ?? null),
            'code'                 => self::nullableString($data['code'] ?? null),
            'description'          => self::nullableString($data['description'] ?? null),
            'type'                 => self::nullableString($data['type'] ?? null) ?? 'retail',
            'color'                => self::nullableString($data['color'] ?? null),
            'icon'                 => self::nullableString($data['icon'] ?? null),
            'discount_percentage'  => self::nullableFloat($data['discount_percentage'] ?? null),
            'discount_fixed'       => self::nullableFloat($data['discount_fixed'] ?? null),
            'has_special_pricing'  => self::normalizeBoolean($data['has_special_pricing'] ?? false),
            'has_volume_discounts' => self::normalizeBoolean($data['has_volume_discounts'] ?? false),
            'can_view_prices'      => self::normalizeBoolean($data['can_view_prices'] ?? true),
            'can_place_orders'     => self::normalizeBoolean($data['can_place_orders'] ?? true),
            'can_view_catalog'     => self::normalizeBoolean($data['can_view_catalog'] ?? true),
            'can_use_coupons'      => self::normalizeBoolean($data['can_use_coupons'] ?? true),
            'minimum_order_amount' => self::nullableFloat($data['minimum_order_amount'] ?? null),
            'credit_limit'         => self::nullableFloat($data['credit_limit'] ?? null),
            'payment_terms'        => self::nullableString($data['payment_terms'] ?? null),
            'sort_order'           => self::nullableInt($data['sort_order'] ?? null) ?? 0,
            'metadata'             => self::normalizeKeyValue($data['metadata'] ?? null),
            'conditions'           => self::normalizeKeyValue($data['conditions'] ?? null),
            'is_active'            => self::normalizeBoolean($data['is_active'] ?? true),
            'is_enabled'           => self::normalizeBoolean($data['is_enabled'] ?? true),
            'is_default'           => self::normalizeBoolean($data['is_default'] ?? false),
        ];
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function prepareCompanyPayload(array $data): array
    {
        return [
            'name'        => self::nullableString($data['name'] ?? null),
            'email'       => self::nullableString($data['email'] ?? null),
            'phone'       => self::nullableString($data['phone'] ?? null),
            'address'     => self::nullableString($data['address'] ?? null),
            'website'     => self::nullableString($data['website'] ?? null),
            'industry'    => self::normalizeIndustryValue($data['industry'] ?? null),
            'size'        => self::nullableString($data['size'] ?? null),
            'description' => self::nullableString($data['description'] ?? null),
            'is_active'   => self::normalizeBoolean($data['is_active'] ?? true),
            'metadata'    => self::normalizeKeyValue($data['metadata'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function preparePartnerPayload(array $data): array
    {
        return [
            'name'            => self::nullableString($data['name'] ?? null),
            'code'            => self::nullableString($data['code'] ?? null),
            'contact_email'   => self::nullableString($data['contact_email'] ?? null),
            'contact_phone'   => self::nullableString($data['contact_phone'] ?? null),
            'discount_rate'   => self::nullableFloat($data['discount_rate'] ?? null),
            'commission_rate' => self::nullableFloat($data['commission_rate'] ?? null),
            'tier_id'         => self::nullableInt($data['tier_id'] ?? null),
            'metadata'        => self::normalizeKeyValue($data['metadata'] ?? null),
            'is_enabled'      => self::normalizeBoolean($data['is_enabled'] ?? true),
        ];
    }

    private static function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $resolved = trim((string) $value);

        return $resolved !== '' ? $resolved : null;
    }

    private static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private static function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private static function normalizeBoolean(mixed $value): bool
    {
        $filtered = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        return $filtered ?? (bool) $value;
    }

    private static function normalizeIndustryValue(mixed $value): ?string
    {
        if ($value instanceof Industry) {
            return $value->value;
        }

        if (is_array($value)) {
            $value = $value['value'] ?? null;
        }

        return self::nullableString($value);
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function normalizeKeyValue(mixed $value): ?array
    {
        if (! is_array($value) || $value === []) {
            return null;
        }

        $normalized = [];

        foreach ($value as $key => $itemValue) {
            if (! is_scalar($key)) {
                continue;
            }

            $resolvedKey = trim((string) $key);
            if ($resolvedKey === '') {
                continue;
            }

            if (! is_scalar($itemValue) && $itemValue !== null) {
                continue;
            }

            $resolvedValue = $itemValue === null ? null : trim((string) $itemValue);
            $normalized[$resolvedKey] = $resolvedValue === '' ? null : $resolvedValue;
        }

        return $normalized !== [] ? $normalized : null;
    }
}
