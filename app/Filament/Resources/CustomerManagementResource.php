<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerManagementResource\Pages;
use App\Filament\Resources\CustomerManagementResource\RelationManagers;
use App\Filament\Resources\CustomerManagementResource\Widgets;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tab;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class CustomerManagementResource extends Resource
{
    protected static ?string $model = User::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-users';

    /** @var string|\BackedEnum|null */

    /**
     * @var UnitEnum|string|null
     */
    protected static $navigationGroup = 'Customer Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.customers');
    }

    public static function getModelLabel(): string
    {
        return __('admin.customers.customer');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.customers.customers');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.customer_management');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['customerGroups', 'partners', 'addresses'])
            ->withCount(['orders', 'cartItems', 'reviews', 'addresses']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->components([
                Tabs::make(__('admin.customers.customer_information'))
                    ->tabs([
                        Tab::make(__('admin.customers.personal_information'))
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make(__('admin.customers.basic_information'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label(__('admin.customers.name'))
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->live()
                                                    ->afterStateUpdated(fn (callable $set, ?string $state) => $set('slug', \Str::slug($state ?? ''))),
                                                TextInput::make('email')
                                                    ->label(__('admin.customers.email'))
                                                    ->email()
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->maxLength(255),
                                                TextInput::make('phone')
                                                    ->label(__('admin.customers.phone'))
                                                    ->tel()
                                                    ->maxLength(20),
                                                Select::make('preferred_locale')
                                                    ->label(__('admin.customers.preferred_language'))
                                                    ->options([
                                                        'lt' => __('admin.locales.lithuanian'),
                                                        'en' => __('admin.locales.english'),
                                                        'de' => __('admin.locales.german'),
                                                    ])
                                                    ->default('lt')
                                                    ->required(),
                                                DateTimePicker::make('email_verified_at')
                                                    ->label(__('admin.customers.email_verified_at'))
                                                    ->displayFormat('d/m/Y H:i'),
                                                DateTimePicker::make('last_login_at')
                                                    ->label(__('admin.customers.last_login_at'))
                                                    ->displayFormat('d/m/Y H:i')
                                                    ->disabled(),
                                            ]),
                                    ]),
                                Section::make(__('admin.customers.account_status'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('is_active')
                                                    ->label(__('admin.customers.is_active'))
                                                    ->default(true)
                                                    ->helperText(__('admin.customers.is_active_help')),
                                                Toggle::make('email_notifications')
                                                    ->label(__('admin.customers.email_notifications'))
                                                    ->default(true)
                                                    ->helperText(__('admin.customers.email_notifications_help')),
                                                Toggle::make('sms_notifications')
                                                    ->label(__('admin.customers.sms_notifications'))
                                                    ->default(false)
                                                    ->helperText(__('admin.customers.sms_notifications_help')),
                                                Toggle::make('marketing_consent')
                                                    ->label(__('admin.customers.marketing_consent'))
                                                    ->default(false)
                                                    ->helperText(__('admin.customers.marketing_consent_help')),
                                            ]),
                                    ]),
                            ]),
                        Tab::make(__('admin.customers.customer_groups'))
                            ->icon('heroicon-o-user-group')
                            ->schema([
                                Section::make(__('admin.customers.group_membership'))
                                    ->schema([
                                        Select::make('customerGroups')
                                            ->label(__('admin.customers.customer_groups'))
                                            ->relationship('customerGroups', 'name')
                                            ->multiple()
                                            ->preload()
                                            ->searchable()
                                            ->createOptionForm([
                                                TextInput::make('name')
                                                    ->label(__('admin.customer_groups.name'))
                                                    ->required()
                                                    ->maxLength(255),
                                                TextInput::make('slug')
                                                    ->label(__('admin.customer_groups.slug'))
                                                    ->required()
                                                    ->maxLength(255),
                                                TextInput::make('description')
                                                    ->label(__('admin.customer_groups.description'))
                                                    ->maxLength(1000),
                                                TextInput::make('discount_percentage')
                                                    ->label(__('admin.customer_groups.discount_percentage'))
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->maxValue(100)
                                                    ->suffix('%'),
                                                Toggle::make('is_enabled')
                                                    ->label(__('admin.customer_groups.is_enabled'))
                                                    ->default(true),
                                            ])
                                            ->helperText(__('admin.customers.customer_groups_help')),
                                    ]),
                            ]),
                        Tab::make(__('admin.customers.partner_information'))
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Section::make(__('admin.customers.partner_details'))
                                    ->schema([
                                        Select::make('partners')
                                            ->label(__('admin.customers.partners'))
                                            ->relationship('partners', 'name')
                                            ->multiple()
                                            ->preload()
                                            ->searchable()
                                            ->helperText(__('admin.customers.partners_help')),
                                        Placeholder::make('partner_discount_rate')
                                            ->label(__('admin.customers.partner_discount_rate'))
                                            ->content(fn ($record) => $record?->partner_discount_rate
                                                ? number_format($record->partner_discount_rate, 2).'%'
                                                : __('admin.customers.no_partner_discount')),
                                    ]),
                            ]),
                        Tab::make(__('admin.customers.addresses'))
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Section::make(__('admin.customers.address_information'))
                                    ->schema([
                                        Placeholder::make('addresses_count')
                                            ->label(__('admin.customers.total_addresses'))
                                            ->content(fn ($record) => $record?->addresses_count ?? 0),
                                        Placeholder::make('default_address')
                                            ->label(__('admin.customers.default_address'))
                                            ->content(fn ($record) => $record?->default_address
                                                ? $record->default_address->full_address
                                                : __('admin.customers.no_default_address')),
                                        Placeholder::make('billing_address')
                                            ->label(__('admin.customers.billing_address'))
                                            ->content(fn ($record) => $record?->billing_address
                                                ? $record->billing_address->full_address
                                                : __('admin.customers.no_billing_address')),
                                        Placeholder::make('shipping_address')
                                            ->label(__('admin.customers.shipping_address'))
                                            ->content(fn ($record) => $record?->shipping_address
                                                ? $record->shipping_address->full_address
                                                : __('admin.customers.no_shipping_address')),
                                    ]),
                            ]),
                        Tab::make(__('admin.customers.activity_summary'))
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Section::make(__('admin.customers.order_statistics'))
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Placeholder::make('orders_count')
                                                    ->label(__('admin.customers.total_orders'))
                                                    ->content(fn ($record) => $record?->orders_count ?? 0),
                                                Placeholder::make('total_spent')
                                                    ->label(__('admin.customers.total_spent'))
                                                    ->content(fn ($record) => $record?->orders()->sum('total')
                                                        ? '€'.number_format($record->orders()->sum('total'), 2)
                                                        : '€0.00'),
                                                Placeholder::make('average_order_value')
                                                    ->label(__('admin.customers.average_order_value'))
                                                    ->content(fn ($record) => $record?->orders()->avg('total')
                                                        ? '€'.number_format($record->orders()->avg('total'), 2)
                                                        : '€0.00'),
                                            ]),
                                    ]),
                                Section::make(__('admin.customers.engagement_metrics'))
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Placeholder::make('cart_items_count')
                                                    ->label(__('admin.customers.cart_items'))
                                                    ->content(fn ($record) => $record?->cart_items_count ?? 0),
                                                Placeholder::make('reviews_count')
                                                    ->label(__('admin.customers.reviews_written'))
                                                    ->content(fn ($record) => $record?->reviews_count ?? 0),
                                                Placeholder::make('wishlist_count')
                                                    ->label(__('admin.customers.wishlist_items'))
                                                    ->content(fn ($record) => $record?->wishlist()->count() ?? 0),
                                            ]),
                                    ]),
                            ]),
                        Tab::make(__('admin.customers.additional_information'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make(__('admin.customers.metadata'))
                                    ->schema([
                                        KeyValue::make('metadata')
                                            ->label(__('admin.customers.metadata'))
                                            ->keyLabel(__('admin.customers.key'))
                                            ->valueLabel(__('admin.customers.value'))
                                            ->addActionLabel(__('admin.customers.add_metadata'))
                                            ->helperText(__('admin.customers.metadata_help')),
                                    ]),
                                Section::make(__('admin.customers.system_information'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Placeholder::make('created_at')
                                                    ->label(__('admin.customers.created_at'))
                                                    ->content(fn ($record) => $record?->created_at?->format('d/m/Y H:i')),
                                                Placeholder::make('updated_at')
                                                    ->label(__('admin.customers.updated_at'))
                                                    ->content(fn ($record) => $record?->updated_at?->format('d/m/Y H:i')),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.customers.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage(__('admin.customers.name_copied')),
                TextColumn::make('email')
                    ->label(__('admin.customers.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(__('admin.customers.email_copied'))
                    ->icon('heroicon-m-envelope'),
                TextColumn::make('phone')
                    ->label(__('admin.customers.phone'))
                    ->searchable()
                    ->sortable()
                    ->placeholder(__('admin.customers.no_phone'))
                    ->icon('heroicon-m-phone'),
                BadgeColumn::make('preferred_locale')
                    ->label(__('admin.customers.language'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'lt' => __('admin.locales.lithuanian'),
                        'en' => __('admin.locales.english'),
                        'de' => __('admin.locales.german'),
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'lt',
                        'success' => 'en',
                        'warning' => 'de',
                    ]),
                TextColumn::make('customerGroups.name')
                    ->label(__('admin.customers.customer_groups'))
                    ->badge()
                    ->separator(', ')
                    ->color('info')
                    ->limit(2)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (count($state) <= 2) {
                            return null;
                        }

                        return collect($state)->pluck('name')->join(', ');
                    }),
                TextColumn::make('orders_count')
                    ->label(__('admin.customers.orders'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->icon('heroicon-m-shopping-bag'),
                TextColumn::make('total_spent')
                    ->label(__('admin.customers.total_spent'))
                    ->getStateUsing(function ($record) {
                        $total = $record->orders()->sum('total');

                        return $total ? '€'.number_format($total, 2) : '€0.00';
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->withSum('orders', 'total')
                            ->orderBy('orders_sum_total', $direction);
                    })
                    ->alignEnd()
                    ->color('success'),
                IconColumn::make('is_active')
                    ->label(__('admin.customers.status'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('email_verified_at')
                    ->label(__('admin.customers.email_verified'))
                    ->boolean()
                    ->getStateUsing(fn ($record) => ! is_null($record->email_verified_at))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('warning'),
                TextColumn::make('last_login_at')
                    ->label(__('admin.customers.last_login'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder(__('admin.customers.never_logged_in'))
                    ->since()
                    ->tooltip(fn ($record) => $record->last_login_at?->format('d/m/Y H:i:s')),
                TextColumn::make('created_at')
                    ->label(__('admin.customers.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.customers.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label(__('admin.customers.status'))
                    ->options([
                        1 => __('admin.customers.active'),
                        0 => __('admin.customers.inactive'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] !== null,
                            fn (Builder $query, $value): Builder => $query->where('is_active', $value),
                        );
                    }),
                SelectFilter::make('preferred_locale')
                    ->label(__('admin.customers.language'))
                    ->options([
                        'lt' => __('admin.locales.lithuanian'),
                        'en' => __('admin.locales.english'),
                        'de' => __('admin.locales.german'),
                    ]),
                SelectFilter::make('customerGroups')
                    ->label(__('admin.customers.customer_groups'))
                    ->relationship('customerGroups', 'name')
                    ->multiple()
                    ->preload(),
                TernaryFilter::make('email_verified_at')
                    ->label(__('admin.customers.email_verified'))
                    ->nullable()
                    ->trueLabel(__('admin.customers.verified'))
                    ->falseLabel(__('admin.customers.unverified'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('email_verified_at'),
                        false: fn (Builder $query) => $query->whereNull('email_verified_at'),
                        blank: fn (Builder $query) => $query,
                    ),
                TernaryFilter::make('has_orders')
                    ->label(__('admin.customers.has_orders'))
                    ->nullable()
                    ->trueLabel(__('admin.customers.with_orders'))
                    ->falseLabel(__('admin.customers.without_orders'))
                    ->queries(
                        true: fn (Builder $query) => $query->has('orders'),
                        false: fn (Builder $query) => $query->doesntHave('orders'),
                        blank: fn (Builder $query) => $query,
                    ),
                TernaryFilter::make('has_partners')
                    ->label(__('admin.customers.has_partners'))
                    ->nullable()
                    ->trueLabel(__('admin.customers.with_partners'))
                    ->falseLabel(__('admin.customers.without_partners'))
                    ->queries(
                        true: fn (Builder $query) => $query->has('partners'),
                        false: fn (Builder $query) => $query->doesntHave('partners'),
                        blank: fn (Builder $query) => $query,
                    ),
                DateFilter::make('created_at')
                    ->label(__('admin.customers.created_at'))
                    ->displayFormat('d/m/Y'),
                DateFilter::make('last_login_at')
                    ->label(__('admin.customers.last_login_at'))
                    ->displayFormat('d/m/Y'),
                Filter::make('high_value_customers')
                    ->label(__('admin.customers.high_value_customers'))
                    ->query(fn (Builder $query): Builder => $query
                        ->withSum('orders', 'total')
                        ->having('orders_sum_total', '>', 1000)),
                Filter::make('recent_customers')
                    ->label(__('admin.customers.recent_customers'))
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30))),
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('name')
                            ->label(__('admin.customers.name')),
                        TextConstraint::make('email')
                            ->label(__('admin.customers.email')),
                        TextConstraint::make('phone')
                            ->label(__('admin.customers.phone')),
                        RelationshipConstraint::make('customerGroups')
                            ->label(__('admin.customers.customer_groups'))
                            ->multiple(),
                        DateConstraint::make('created_at')
                            ->label(__('admin.customers.created_at')),
                        DateConstraint::make('last_login_at')
                            ->label(__('admin.customers.last_login_at')),
                        NumberConstraint::make('orders_count')
                            ->label(__('admin.customers.orders_count')),
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('admin.actions.view')),
                Tables\Actions\EditAction::make()
                    ->label(__('admin.actions.edit')),
                Tables\Actions\DeleteAction::make()
                    ->label(__('admin.actions.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('admin.actions.delete_selected')),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label(__('admin.actions.force_delete_selected')),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label(__('admin.actions.restore_selected')),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('60s');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
            RelationManagers\AddressesRelationManager::class,
            RelationManagers\CartItemsRelationManager::class,
            RelationManagers\ReviewsRelationManager::class,
            RelationManagers\WishlistRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\CustomerStatsWidget::class,
            Widgets\CustomerActivityWidget::class,
            Widgets\CustomerSegmentationWidget::class,
            Widgets\RecentCustomersWidget::class,
        ];
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->name.' ('.$record->email.')';
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('admin.customers.email') => $record->email,
            __('admin.customers.phone') => $record->phone ?? __('admin.customers.no_phone'),
            __('admin.customers.customer_groups') => $record->customerGroups->pluck('name')->join(', ') ?: __('admin.customers.no_groups'),
        ];
    }

    public static function getGlobalSearchResultUrl($record): string
    {
        return self::getUrl('view', ['record' => $record]);
    }
}
