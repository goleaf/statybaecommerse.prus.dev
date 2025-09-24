<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AdminUserResource\Pages;
use App\Models\AdminUser;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

final class AdminUserResource extends Resource
{
    protected static ?string $model = AdminUser::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Users';
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
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('admin.admin_users.form.sections.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
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
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('password')
                                ->label(__('admin.admin_users.form.fields.password'))
                                ->password()
                                ->required(fn (string $context): bool => $context === 'create')
                                ->minLength(8)
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
            SchemaSection::make(__('admin.admin_users.form.sections.account_details'))
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
                        'verified' => __('admin.admin_users.filters.verified'),
                        'unverified' => __('admin.admin_users.filters.unverified'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            function (Builder $query, $value): Builder {
                                return match ($value) {
                                    'verified' => $query->whereNotNull('email_verified_at'),
                                    'unverified' => $query->whereNull('email_verified_at'),
                                    default => $query,
                                };
                            }
                        );
                    }),
                Filter::make('created_at')
                    ->label(__('admin.admin_users.filters.created_at'))
                    ->form([
                        DatePicker::make('from')
                            ->label(__('admin.admin_users.filters.created_from')),
                        DatePicker::make('until')
                            ->label(__('admin.admin_users.filters.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date));
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
                            ->title(__('admin.admin_users.email_verified_successfully'))
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
                            ->title(__('admin.admin_users.verification_sent_successfully'))
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
                                ->title(__('admin.admin_users.emails_verified_successfully'))
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
                                ->title(__('admin.admin_users.verifications_sent_successfully'))
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
            'index' => Pages\ListAdminUsers::route('/'),
            'create' => Pages\CreateAdminUser::route('/create'),
            'view' => Pages\ViewAdminUser::route('/{record}'),
            'edit' => Pages\EditAdminUser::route('/{record}/edit'),
        ];
    }
}
