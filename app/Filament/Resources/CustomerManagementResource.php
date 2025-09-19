<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerManagementResource\Pages;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * CustomerManagementResource
 *
 * Filament v4 resource for Customer management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class CustomerManagementResource extends Resource
{
    protected static ?string $model = User::class;

    /**
     * @var UnitEnum|string|null
     */
    protected static string|UnitEnum|null $navigationGroup = "Products";

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     */
    public static function getNavigationLabel(): string
    {
        return __('customers.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     */
    public static function getNavigationGroup(): ?string
    {
        return 'Customers';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('customers.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('customers.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('customers.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('name')
                                ->label(__('customers.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('email')
                                ->label(__('customers.email'))
                                ->email()
                                ->unique(ignoreRecord: true),
                        ]),
                    Grid::make(2)
                        ->components([
                            TextInput::make('phone')
                                ->label(__('customers.phone'))
                                ->tel()
                                ->maxLength(20),
                            DateTimePicker::make('email_verified_at')
                                ->label(__('customers.email_verified_at'))
                                ->displayFormat('d/m/Y H:i'),
                        ]),
                ]),
            Section::make(__('customers.account_settings'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Toggle::make('is_active')
                                ->label(__('customers.is_active'))
                                ->default(true),
                            Toggle::make('is_verified')
                                ->label(__('customers.is_verified'))
                                ->default(false),
                        ]),
                    Select::make('customer_group_id')
                        ->label(__('customers.customer_group'))
                        ->relationship('customerGroup', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Textarea::make('description')
                                ->maxLength(500),
                        ]),
                ]),
            Section::make(__('customers.personal_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('first_name')
                                ->label(__('customers.first_name')),
                            TextInput::make('last_name')
                                ->label(__('customers.last_name')),
                        ]),
                    Grid::make(2)
                        ->components([
                            DateTimePicker::make('date_of_birth')
                                ->label(__('customers.date_of_birth'))
                                ->displayFormat('Y-m-d'),
                            Select::make('gender')
                                ->label(__('customers.gender'))
                                ->options([
                                    'male' => __('customers.genders.male'),
                                    'female' => __('customers.genders.female'),
                                    'other' => __('customers.genders.other'),
                                ]),
                        ]),
                ]),
            Section::make(__('customers.address_information'))
                ->components([
                    KeyValue::make('address')
                        ->label(__('customers.address'))
                        ->keyLabel(__('customers.address_field'))
                        ->valueLabel(__('customers.address_value'))
                        ->addActionLabel(__('customers.add_address_field')),
                ]),
            Section::make(__('customers.preferences'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('preferred_language')
                                ->label(__('customers.preferred_language'))
                                ->options([
                                    'lt' => __('customers.languages.lt'),
                                    'en' => __('customers.languages.en'),
                                ])
                                ->default('lt'),
                            Select::make('preferred_currency')
                                ->label(__('customers.preferred_currency'))
                                ->options([
                                    'EUR' => 'EUR (€)',
                                    'USD' => 'USD ($)',
                                ])
                                ->default('EUR'),
                        ]),
                    Grid::make(2)
                        ->components([
                            Toggle::make('newsletter_subscription')
                                ->label(__('customers.newsletter_subscription'))
                                ->default(false),
                            Toggle::make('sms_notifications')
                                ->label(__('customers.sms_notifications'))
                                ->default(false),
                        ]),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
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
                TextColumn::make('customerGroup.name')
                    ->label(__('customers.customer_group'))
                    ->sortable(),
                BadgeColumn::make('email_verified_at')
                    ->label(__('customers.email_status'))
                    ->formatStateUsing(fn($state): string => $state ? __('customers.verified') : __('customers.unverified'))
                    ->colors([
                        'success' => fn($state): bool => (bool) $state,
                        'warning' => fn($state): bool => !$state,
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
                SelectFilter::make('customer_group_id')
                    ->relationship('customerGroup', 'name')
                    ->preload(),
                TernaryFilter::make('email_verified_at')
                    ->label(__('customers.email_verified'))
                    ->trueLabel(__('customers.verified_only'))
                    ->falseLabel(__('customers.unverified_only'))
                    ->native(false),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('customers.active_only'))
                    ->falseLabel(__('customers.inactive_only'))
                    ->native(false),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('customers.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('customers.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
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
                    ->visible(fn(User $record): bool => !$record->email_verified_at)
                    ->action(function (User $record): void {
                        $record->update(['email_verified_at' => now()]);
                        Notification::make()
                            ->title(__('customers.email_verified_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('toggle_active')
                    ->label(fn(User $record): string => $record->is_active ? __('customers.deactivate') : __('customers.activate'))
                    ->icon(fn(User $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(User $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (User $record): void {
                        $record->update(['is_active' => !$record->is_active]);
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
                            $records->each->update(['email_verified_at' => now()]);
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
                            $records->each->update(['is_active' => true]);
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
                            $records->each->update(['is_active' => false]);
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
     * Get the relations for this resource.
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
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
