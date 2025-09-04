<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Actions\DocumentAction;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use BackedEnum;
use UnitEnum;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'Users';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('User Information')
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At'),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Profile Information')
                    ->components([
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date_of_birth'),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ]),
                        Forms\Components\Select::make('preferred_locale')
                            ->options([
                                'en' => 'English',
                                'lt' => 'Lithuanian',
                            ])
                            ->default('lt'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Roles & Permissions')
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
                Forms\Components\Section::make('Settings')
                    ->components([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Forms\Components\Toggle::make('accepts_marketing')
                            ->label('Accepts Marketing')
                            ->default(false),
                        Forms\Components\Toggle::make('two_factor_enabled')
                            ->label('Two Factor Authentication')
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
                    ->label('Verified')
                    ->getStateUsing(fn($record) => !is_null($record->email_verified_at)),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->separator(',')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('customerGroups.name')
                    ->badge()
                    ->separator(',')
                    ->label('Groups')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label('Orders')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_spent')
                    ->getStateUsing(fn($record) => $record->orders()->where('status', '!=', 'cancelled')->sum('total'))
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
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
                    ->label('Customer Groups'),
            ])
            ->actions([
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('impersonate')
                    ->icon('heroicon-o-user-circle')
                    ->color('warning')
                    ->action(function (User $record) {
                        if (auth()->user()->can('impersonate users') && $record->id !== auth()->id()) {
                            session(['impersonating' => $record->id]);
                            return redirect()
                                ->to('/')
                                ->with('success', __('Now impersonating :name', ['name' => $record->name]));
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('Impersonate User'))
                    ->modalDescription(__('You will be logged in as this user. You can return to your account anytime.'))
                    ->visible(fn(User $record) => auth()->user()->can('impersonate users') && $record->id !== auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn($records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
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
            'Email' => $record->email,
            'Phone' => $record->phone ?? __('Not provided'),
            'Status' => $record->is_active ? __('Active') : __('Inactive'),
            'Joined' => $record->created_at->format('Y-m-d'),
        ];
    }
}
