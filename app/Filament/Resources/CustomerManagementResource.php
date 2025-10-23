<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerManagementResource\Pages;
use App\Filament\Resources\CustomerManagementResource\RelationManagers\AddressesRelationManager;
use App\Filament\Resources\CustomerManagementResource\RelationManagers\CartItemsRelationManager;
use App\Filament\Resources\CustomerManagementResource\RelationManagers\DiscountRedemptionsRelationManager;
use App\Filament\Resources\CustomerManagementResource\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\CustomerManagementResource\RelationManagers\ReviewsRelationManager;
use App\Models\User;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Scopes\ActiveScope;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable as SpatieTranslatableResource;
use Tapp\FilamentValueRangeFilter\Filters\ValueRangeFilter;

/**
 * CustomerManagementResource
 *
 * Central Filament resource that drives the back-office customer management
 * experience.  The resource exposes form components for CRUD operations and
 * table utilities for quick moderation actions, mirroring the behaviour
 * expected by the feature tests in {@see \Tests\Feature\CustomerManagementResourceTest}.
 */
final class CustomerManagementResource extends Resource
{
    use SpatieTranslatableResource; // Keep parity with other translated resources.

    /** @var string|\BackedEnum|null */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $model = User::class;

    /**
     * Build the Filament form used on the create and edit pages.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('customers.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('customers.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('email')
                                ->label(__('customers.email'))
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('phone')
                                ->label(__('customers.phone'))
                                ->tel()
                                ->maxLength(20),
                            SupportFlatpickr::makeDateTime('email_verified_at')
                                ->label(__('customers.email_verified_at'))
                                ->displayFormat('Y-m-d H:i'),
                        ]),
                ]),
            Section::make(__('customers.account_settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('customers.is_active'))
                                ->default(true),
                            Toggle::make('is_verified')
                                ->label(__('customers.is_verified'))
                                ->default(false),
                        ]),
                    Select::make('customerGroups')
                        ->label(__('customers.customer_group'))
                        ->relationship('customerGroups', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label(__('customer_groups.name'))
                                ->required()
                                ->maxLength(255),
                            Textarea::make('description')
                                ->label(__('customer_groups.description'))
                                ->maxLength(500),
                        ]),
                ]),
            Section::make(__('customers.personal_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('first_name')
                                ->label(__('customers.first_name')),
                            TextInput::make('last_name')
                                ->label(__('customers.last_name')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            SupportFlatpickr::makeDateTime('date_of_birth')
                                ->label(__('customers.date_of_birth'))
                                ->displayFormat('Y-m-d'),
                            Select::make('gender')
                                ->label(__('customers.gender'))
                                ->options([
                                    'male' => __('customers.genders.male'),
                                    'female' => __('customers.genders.female'),
                                    'other' => __('customers.genders.other'),
                                ])
                                ->nullable(),
                        ]),
                ]),
            Section::make(__('customers.preferences'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('preferred_locale')
                                ->label(__('customers.preferred_language'))
                                ->options([
                                    'lt' => __('customers.languages.lt'),
                                    'en' => __('customers.languages.en'),
                                ])
                                ->default('lt'),
                            Select::make('preferences->preferred_currency')
                                ->label(__('customers.preferred_currency'))
                                ->options([
                                    'EUR' => 'EUR (€)',
                                    'USD' => 'USD ($)',
                                ])
                                ->default('EUR'),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('notification_preferences->newsletter_subscription')
                                ->label(__('customers.newsletter_subscription'))
                                ->default(false),
                            Toggle::make('notification_preferences->sms_notifications')
                                ->label(__('customers.sms_notifications'))
                                ->default(false),
                        ]),
                ]),
        ]);
    }

    /**
     * Configure the table displayed on the list page.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('customers.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('email')
                    ->label(__('customers.email'))
                    ->copyable(),
                TextColumn::make('phone')
                    ->label(__('customers.phone'))
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('customerGroups.name')
                    ->label(__('customers.customer_group'))
                    ->sortable(),
                TextColumn::make('email_verified_at')
                    ->label(__('customers.email_status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? __('customers.verified') : __('customers.unverified'))
                    ->colors([
                        'success' => fn (?string $state): bool => filled($state),
                        'warning' => fn (?string $state): bool => blank($state),
                    ]),
                IconColumn::make('is_active')
                    ->label(__('customers.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('orders_count')
                    ->label(__('customers.orders_count'))
                    ->counts('orders')
                    ->sortable(),
                TextColumn::make('total_spent')
                    ->label(__('customers.total_spent'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('customers.created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('customers.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('customerGroups')
                    ->relationship('customerGroups', 'name')
                    ->label(__('customers.customer_group'))
                    ->preload(),
                SelectFilter::make('email_verified_at')
                    ->label(__('customers.email_verified'))
                    ->options([
                        '1' => __('customers.verified_only'),
                        '0' => __('customers.unverified_only'),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        $state = $data['value'] ?? null;

                        // Translate simple string values from the UI into database constraints.
                        if ($state === '1') {
                            $query->whereNotNull('email_verified_at');

                            return;
                        }

                        if ($state === '0') {
                            $query->whereNull('email_verified_at');
                        }
                    }),
                SelectFilter::make('is_active')
                    ->label(__('customers.is_active'))
                    ->options([
                        '1' => __('customers.active_only'),
                        '0' => __('customers.inactive_only'),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        $state = $data['value'] ?? null;

                        // Keep a predictable boolean comparison for Livewire powered table tests.
                        if ($state === '1') {
                            $query->where('is_active', true);

                            return;
                        }

                        if ($state === '0') {
                            $query->where('is_active', false);
                        }
                    }),
                ValueRangeFilter::make('orders_count')
                    ->label(__('customers.orders_count')),
                Filter::make('created_at')
                    ->form([
                        SupportFlatpickr::makeDate('created_from')
                            ->label(__('customers.created_from')),
                        SupportFlatpickr::makeDate('created_until')
                            ->label(__('customers.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $createdFrom = $data['created_from'] ?? null;
                        $createdUntil = $data['created_until'] ?? null;

                        return $query
                            ->when(
                                $createdFrom,
                                fn (Builder $innerQuery, string $date): Builder => $innerQuery->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $createdUntil,
                                fn (Builder $innerQuery, string $date): Builder => $innerQuery->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('verify_email')
                    ->label(__('customers.verify_email'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record): bool => $record->email_verified_at === null)
                    ->action(function (User $record): void {
                        // Instantly confirm the email to keep parity with the bulk action below.
                        $record->update(['email_verified_at' => now()]);

                        Notification::make()
                            ->title(__('customers.email_verified_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('toggle_active')
                    ->label(fn (User $record): string => $record->is_active ? __('customers.deactivate') : __('customers.activate'))
                    ->icon(fn (User $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (User $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (User $record): void {
                        // Flip the active flag and immediately reflect the change in the UI table.
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('customers.activated_successfully') : __('customers.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('verify_emails')
                    ->label(__('customers.verify_emails'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Collection $records): void {
                        $ids = $records->modelKeys();

                        if ($ids !== []) {
                            file_put_contents(storage_path('logs/bulk.log'), json_encode(['action' => 'verify', 'ids' => $ids]).PHP_EOL, FILE_APPEND);
                            User::query()->withoutGlobalScopes()->whereIn('id', $ids)->toBase()->update(['email_verified_at' => now()]);
                        }

                        Notification::make()
                            ->title(__('customers.bulk_verified_success'))
                            ->success()
                            ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('activate')
                    ->label(__('customers.activate_selected'))
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->action(function (Collection $records): void {
                        $ids = $records->modelKeys();

                        if ($ids !== []) {
                            User::query()->withoutGlobalScopes()->whereIn('id', $ids)->toBase()->update(['is_active' => true]);
                        }

                        Notification::make()
                            ->title(__('customers.bulk_activated_success'))
                            ->success()
                            ->send();
                    })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                    ->label(__('customers.deactivate_selected'))
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->action(function (Collection $records): void {
                        $ids = $records->modelKeys();

                        if ($ids !== []) {
                            file_put_contents(storage_path('logs/bulk.log'), json_encode(['action' => 'deactivate', 'ids' => $ids]).PHP_EOL, FILE_APPEND);
                            User::query()->withoutGlobalScopes()->whereIn('id', $ids)->toBase()->update(['is_active' => false]);
                        }

                        Notification::make()
                            ->title(__('customers.bulk_deactivated_success'))
                            ->success()
                            ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Register the relation managers displayed on the customer view.
     */
    public static function getRelations(): array
    {
        return [
            OrdersRelationManager::class,
            AddressesRelationManager::class,
            ReviewsRelationManager::class,
            CartItemsRelationManager::class,
            DiscountRedemptionsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Configure the page routes used by this resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
