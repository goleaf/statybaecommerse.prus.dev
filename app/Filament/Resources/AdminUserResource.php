<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Forms\Components\Flatpickr;
use App\Filament\Resources\AdminUserResource\Pages;
use App\Models\AdminUser;
use App\Support\Filament\Components\Flatpickr;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
use App\Support\Filament\Components\Flatpickr;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class AdminUserResource extends Resource
{
    use HasNav;

    protected static ?string $model = AdminUser::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): BackedEnum|\UnitEnum|Htmlable|string|null
    {
        return 'heroicon-o-document-text';
    }

    

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.admin_users.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('admin.admin_users.single');
    }

    /**
     * Configure the Filament form schema with fields and validation on the provided Form instance for v4 compliance.
     */
    public static function form(Schema $form): Schema
    {
        return $schema->schema([
            Section::make(__('admin.admin_users.form.sections.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('admin.admin_users.form.fields.name'))
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('email')
                                ->label(__('admin.admin_users.form.fields.email'))
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255)
                                ->columnSpan(1),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('password')
                                ->label(__('admin.admin_users.form.fields.password'))
                                ->password()
                                ->required(fn (string $context): bool => $context === 'create')
                                ->minLength(8)
                                ->dehydrated(fn ($state) => filled($state))
                                ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                                ->columnSpan(1),
                            TextInput::make('password_confirmation')
                                ->label(__('admin.admin_users.form.fields.password_confirmation'))
                                ->password()
                                ->required(fn (string $context): bool => $context === 'create')
                                ->same('password')
                                ->columnSpan(1),
                        ]),
                ])
                ->columns(1),
            Section::make(__('admin.admin_users.form.sections.account_details'))
                ->schema([
                    Placeholder::make('email_verified_at')
                        ->label(__('admin.admin_users.form.fields.email_verified_at'))
                        ->content(fn ($record) => $record?->email_verified_at?->format('Y-m-d H:i:s') ?? '-'),
                    Placeholder::make('created_at')
                        ->label(__('admin.admin_users.form.fields.created_at'))
                        ->content(fn ($record) => $record?->created_at?->format('Y-m-d H:i:s') ?? '-'),
                    Placeholder::make('updated_at')
                        ->label(__('admin.admin_users.form.fields.updated_at'))
                        ->content(fn ($record) => $record?->updated_at?->format('Y-m-d H:i:s') ?? '-'),
                ])
                ->columns(2),
            SchemaSection::make(__('admin.admin_users.form.sections.roles_permissions'))
                ->schema([
                    Select::make('roles')
                        ->label(__('admin.admin_users.form.fields.roles'))
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->helperText(__('admin.admin_users.form.helpers.roles')),
                    Textarea::make('audit_reason')
                        ->label(__('admin.admin_users.form.fields.audit_reason'))
                        ->helperText(__('admin.admin_users.form.helpers.audit_reason'))
                        ->visible(fn (?AdminUser $record): bool => (bool) ($record?->exists))
                        ->columnSpanFull(),
                ])
                ->columns(1),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions while preserving the Table return contract.
     */
    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            // Returning the configured Table keeps action and bulk workflows type-safe in Filament v4.
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.admin_users.form.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('admin.admin_users.form.fields.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                IconColumn::make('email_verified_at')
                    ->label(__('admin.admin_users.form.fields.email_verified'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label(__('admin.admin_users.form.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('email_verified')
                    ->label(__('admin.admin_users.filters.email_verified'))
                    ->options([
                        'verified'   => __('admin.admin_users.filters.verified'),
                        'unverified' => __('admin.admin_users.filters.unverified'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] ?? null,
                            function (Builder $query, $value): Builder {
                                return match ($value) {
                                    'verified'   => $query->whereNotNull('email_verified_at'),
                                    'unverified' => $query->whereNull('email_verified_at'),
                                    default      => $query,
                                };
                            }
                        );
                    }),
                Filter::make('created_at')
                    ->label(__('admin.admin_users.filters.created_at'))
                    ->form([
                        Flatpickr::makeDate('from')
                            ->label(__('admin.admin_users.filters.created_from')),
                        Flatpickr::makeDate('until')
                            ->label(__('admin.admin_users.filters.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date)
                            );
                    }),
                Filter::make('recent')
                    ->label(__('admin.admin_users.filters.recent'))
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('verify_email')
                    ->label(__('admin.admin_users.actions.verify_email'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (AdminUser $record): void {
                        $record->update(['email_verified_at' => now()]);
                        FilamentNotification::make()
                            ->title(__('admin.admin_users.notifications.email_verified_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('send_verification')
                    ->label(__('admin.admin_users.actions.send_verification'))
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->action(function (AdminUser $record): void {
                        // Send verification email logic here
                        FilamentNotification::make()
                            ->title(__('admin.admin_users.notifications.verification_sent_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    Action::make('verify_emails')
                        ->label(__('admin.admin_users.actions.verify_emails'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(function (AdminUser $record): void {
                                $record->update(['email_verified_at' => now()]);
                            });
                            FilamentNotification::make()
                                ->title(__('admin.admin_users.notifications.emails_verified_successfully'))
                                ->success()
                                ->send();
                        }),
                    Action::make('send_verifications')
                        ->label(__('admin.admin_users.actions.send_verifications'))
                        ->icon('heroicon-o-envelope')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            // Send verification emails logic here
                            FilamentNotification::make()
                                ->title(__('admin.admin_users.notifications.verifications_sent_successfully'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAdminUsers::route('/'),
            'create' => Pages\CreateAdminUser::route('/create'),
            'view'   => Pages\ViewAdminUser::route('/{record}'),
            'edit'   => Pages\EditAdminUser::route('/{record}/edit'),
        ];
    }
}