<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerManagementResource\Pages;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Actions as Actions;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use BackedEnum;
use UnitEnum;

final class CustomerManagementResource extends Resource
{
    protected static ?string $model = User::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = \App\Enums\NavigationGroup::Customers;

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.customers');
    }

    public static function getModelLabel(): string
    {
        return __('Customer');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Customers');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_admin', false)
            ->withCount(['orders'])
            ->withSum('orders', 'total');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Customer Information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('Full Name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label(__('Email Address'))
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label(__('Email Verified At'))
                            ->nullable(),
                        Forms\Components\Select::make('preferred_locale')
                            ->label(__('Preferred Language'))
                            ->options([
                                'en' => __('English'),
                                'lt' => __('Lithuanian'),
                                'de' => __('German'),
                            ])
                            ->default('lt'),
                    ])
                    ->columns(2),
                Section::make(__('Account Status'))
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('Active Account'))
                            ->helperText(__('Inactive accounts cannot place orders'))
                            ->default(true),
                        Forms\Components\Select::make('timezone')
                            ->label(__('Timezone'))
                            ->options([
                                'UTC' => 'UTC',
                                'Europe/Vilnius' => 'Europe/Vilnius',
                                'Europe/London' => 'Europe/London',
                                'America/New_York' => 'America/New_York',
                            ])
                            ->default('Europe/Vilnius')
                            ->searchable(),
                        Forms\Components\DateTimePicker::make('last_login_at')
                            ->label(__('Last Login'))
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('last_login_ip')
                            ->label(__('Last Login IP'))
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
                Section::make(__('Customer Preferences'))
                    ->schema([
                        Forms\Components\KeyValue::make('preferences')
                            ->label(__('Preferences'))
                            ->keyLabel(__('Setting'))
                            ->valueLabel(__('Value'))
                            ->helperText(__('Customer preferences and settings')),
                    ]),
                Section::make(__('Password Management'))
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label(__('New Password'))
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->helperText(__('Leave blank to keep current password')),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label(__('Confirm Password'))
                            ->password()
                            ->same('password')
                            ->dehydrated(false),
                    ])
                    ->columns(2)
                    ->hiddenOn('view'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label(__('Avatar'))
                    ->circular()
                    ->defaultImageUrl(fn(User $record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=6366f1&color=fff')
                    ->size(50),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label(__('Verified'))
                    ->boolean()
                    ->getStateUsing(fn(User $record): bool => !is_null($record->email_verified_at))
                    ->sortable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label(__('Orders'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('orders_sum_total')
                    ->label(__('Total Spent'))
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('EUR')
                            ->label(__('Total Customer Value')),
                    ]),
                Tables\Columns\TextColumn::make('last_order_date')
                    ->label(__('Last Order'))
                    ->getStateUsing(fn(User $record): ?string => $record->orders()->latest()->first()?->created_at?->diffForHumans())
                    ->placeholder(__('No orders'))
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withMax('orders', 'created_at')->orderBy('orders_max_created_at', $direction);
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('preferred_locale')
                    ->label(__('Language'))
                    ->badge()
                    ->color('secondary')
                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Registered'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label(__('Email Verified'))
                    ->placeholder(__('All'))
                    ->trueLabel(__('Verified'))
                    ->falseLabel(__('Unverified'))
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('email_verified_at'),
                        false: fn(Builder $query) => $query->whereNull('email_verified_at'),
                    ),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('Account Status'))
                    ->placeholder(__('All'))
                    ->trueLabel(__('Active'))
                    ->falseLabel(__('Inactive')),
                Tables\Filters\SelectFilter::make('preferred_locale')
                    ->label(__('Language'))
                    ->options([
                        'en' => __('English'),
                        'lt' => __('Lithuanian'),
                        'de' => __('German'),
                    ]),
                Tables\Filters\Filter::make('has_orders')
                    ->label(__('Has Orders'))
                    ->query(fn(Builder $query): Builder => $query->has('orders'))
                    ->toggle(),
                Tables\Filters\Filter::make('high_value_customers')
                    ->label(__('High Value Customers'))
                    ->query(fn(Builder $query): Builder => $query->whereHas('orders', function (Builder $subQuery) {
                        $subQuery->havingRaw('SUM(total) > 1000');
                    }))
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Actions\Action::make('view_orders')
                    ->label(__('View Orders'))
                    ->icon('heroicon-o-shopping-bag')
                    ->url(fn(User $record): string => OrderResource::getUrl('index', ['tableFilters' => ['user' => ['value' => $record->id]]])),
                Actions\Action::make('send_email')
                    ->label(__('Send Email'))
                    ->icon('heroicon-o-envelope')
                    ->color('primary')
                    ->form([
                        Forms\Components\TextInput::make('subject')
                            ->label(__('Subject'))
                            ->required(),
                        Forms\Components\Textarea::make('message')
                            ->label(__('Message'))
                            ->required()
                            ->rows(5),
                    ])
                    ->action(function (User $record, array $data): void {
                        // Send email logic here
                        // Mail::to($record->email)->send(new CustomerEmail($data['subject'], $data['message']));
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    BulkAction::make('activate_accounts')
                        ->label(__('Activate Accounts'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn($records) => $records->each(fn(User $record) => $record->update(['is_active' => true])))
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate_accounts')
                        ->label(__('Deactivate Accounts'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn($records) => $records->each(fn(User $record) => $record->update(['is_active' => false])))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Tables\Grouping\Group::make('preferred_locale')
                    ->label(__('Language'))
                    ->collapsible(),
                Tables\Grouping\Group::make('is_active')
                    ->label(__('Status'))
                    ->getDescriptionFromRecordUsing(fn(User $record): string => $record->is_active ? __('Active') : __('Inactive'))
                    ->collapsible(),
            ])
            ->poll('120s');
    }

    public static function getRelations(): array
    {
        return [
            // You can add relation managers here for orders, addresses, etc.
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerManagement::route('/'),
            'create' => Pages\CreateCustomerManagement::route('/create'),
            'view' => Pages\ViewCustomerManagement::route('/{record}'),
            'edit' => Pages\EditCustomerManagement::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return (bool) (auth()->user()?->is_admin ?? false);
    }

    public static function getNavigationBadge(): ?string
    {
        $newCustomers = User::where('is_admin', false)
            ->where('created_at', '>=', now()->subWeek())
            ->count();

        return $newCustomers > 0 ? (string) $newCustomers : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
