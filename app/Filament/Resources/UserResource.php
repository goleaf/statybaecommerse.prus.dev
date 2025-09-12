<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Actions\DocumentAction;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use \BackedEnum;
final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.customers');
    }

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.users.sections.user_information'))
                    ->components([
                        Forms\Components\TextInput::make('first_name')
                            ->label(__('admin.users.fields.first_name'))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label(__('admin.users.fields.last_name'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label(__('admin.users.fields.email_verified_at')),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make(__('admin.users.sections.profile_information'))
                    ->components([
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date_of_birth'),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => __('admin.users.gender.male'),
                                'female' => __('admin.users.gender.female'),
                                'other' => __('admin.users.gender.other'),
                            ]),
                        Forms\Components\Select::make('preferred_locale')
                            ->options([
                                'en' => __('admin.language_switcher.english'),
                                'lt' => __('admin.language_switcher.lithuanian'),
                            ])
                            ->default('lt'),
                    ])
                    ->columns(2),
                Section::make(__('admin.users.sections.roles_permissions'))
                    ->components([
                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('customerGroups')
                            ->relationship('customerGroups', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])
                    ->columns(2),
                Section::make(__('admin.users.sections.settings'))
                    ->components([
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('admin.users.fields.is_active'))
                            ->default(true),
                        Forms\Components\Toggle::make('accepts_marketing')
                            ->label(__('admin.users.fields.accepts_marketing'))
                            ->default(false),
                        Forms\Components\Toggle::make('two_factor_enabled')
                            ->label(__('admin.users.fields.two_factor_enabled'))
                            ->default(false),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->boolean()
                    ->label(__('admin.users.fields.email_verified_at'))
                    ->getStateUsing(fn($record) => !is_null($record->email_verified_at)),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->separator(',')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('customerGroups.name')
                    ->badge()
                    ->separator(',')
                    ->label(__('admin.users.fields.customer_groups'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label(__('admin.users.fields.orders_count'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_spent')
                    ->getStateUsing(fn($record) => $record->orders()->where('status', '!=', 'cancelled')->sum('total'))
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->deferLoading(false)
            ->filters([
                Tables\Filters\Filter::make('verified')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('email_verified_at')),
                Tables\Filters\Filter::make('active')
                    ->query(fn(Builder $query): Builder => $query->where('is_active', true)),
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple(),
                Tables\Filters\SelectFilter::make('customerGroups')
                    ->relationship('customerGroups', 'name')
                    ->multiple()
                    ->label(__('admin.users.fields.customer_groups')),
            ])
            ->recordActions([
                DocumentAction::make()
                    ->variables(fn(User $record) => [
                        '$CUSTOMER_NAME' => $record->name,
                        '$CUSTOMER_FIRST_NAME' => $record->first_name ?? '',
                        '$CUSTOMER_LAST_NAME' => $record->last_name ?? '',
                        '$CUSTOMER_EMAIL' => $record->email,
                        '$CUSTOMER_PHONE' => $record->phone_number ?? '',
                        '$CUSTOMER_COMPANY' => $record->company ?? '',
                        '$CUSTOMER_GROUP' => $record->customerGroups->pluck('name')->implode(', '),
                    ]),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('impersonate')
                    ->icon('heroicon-o-user-circle')
                    ->color('warning')
                    ->action(function (User $record) {
                        if (auth()->user()->can('impersonate users') && $record->id !== auth()->id()) {
                            session(['impersonating' => $record->id]);
                            return redirect()
                                ->to('/')
                                ->with('success', __('admin.notifications.impersonating_user', ['name' => $record->name]));
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.modals.impersonate_user'))
                    ->modalDescription(__('admin.modals.impersonate_description'))
                    ->visible(fn(User $record) => auth()->user()->can('impersonate users') && $record->id !== auth()->id()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('admin.actions.activate_selected'))
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records): void {
                            $ids = collect($records)
                                ->map(fn($record) => is_object($record) ? $record->getKey() : $record)
                                ->all();
                            if (!empty($ids)) {
                                \App\Models\User::whereIn('id', $ids)->update(['is_active' => true]);
                            }
                        }),
                    BulkAction::make('deactivate')
                        ->label(__('admin.actions.deactivate_selected'))
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(fn($records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
            RelationManagers\AddressesRelationManager::class,
            RelationManagers\ReviewsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            __('admin.users.fields.email') => $record->email,
            __('admin.users.fields.phone') => $record->phone ?? __('admin.messages.not_provided'),
            __('admin.fields.status') => $record->is_active ? __('admin.products.status.active') : __('admin.products.status.inactive'),
            __('admin.users.joined') => $record->created_at->format('Y-m-d'),
        ];
    }
}
