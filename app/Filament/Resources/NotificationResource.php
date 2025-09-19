<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Notification;
use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkAction as TableBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;
use UnitEnum;

/**
 * NotificationResource
 *
 * Filament v4 resource for Notification management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    /** @var UnitEnum|string|null */    /** @var UnitEnum|string|null */
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::System;

    protected static ?int $navigationSort = 50;

    protected static ?string $recordTitleAttribute = 'type';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.notifications.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'System';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.notifications.title');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.notifications.single', [], 'Notification');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.notifications.form.sections.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('notifiable_type')
                                ->label(__('admin.notifications.form.fields.notifiable_type'))
                                ->options([
                                    User::class => 'User',
                                ])
                                ->required()
                                ->columnSpan(1),
                            Select::make('notifiable_id')
                                ->label(__('admin.notifications.form.fields.notifiable_id'))
                                ->relationship('notifiable', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(1),
                        ]),
                    TextInput::make('type')
                        ->label(__('admin.notifications.form.fields.type'))
                        ->required()
                        ->maxLength(255),
                    Textarea::make('data')
                        ->label(__('admin.notifications.form.fields.data'))
                        ->rows(4)
                        ->columnSpanFull(),
                    DateTimePicker::make('read_at')
                        ->label(__('admin.notifications.form.fields.read_at'))
                        ->columnSpan(1),
                ])
                ->columns(1),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label(__('admin.notifications.form.fields.type'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('notifiable_type')
                    ->label(__('admin.notifications.form.fields.notifiable_type'))
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('admin.notifications.form.fields.user'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_read')
                    ->label(__('admin.notifications.form.fields.is_read'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label(__('admin.notifications.form.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('read_at')
                    ->label(__('admin.notifications.form.fields.read_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('notifiable_type')
                    ->label(__('admin.notifications.form.fields.notifiable_type'))
                    ->options([
                        User::class => 'User',
                    ]),
                TernaryFilter::make('is_read')
                    ->label(__('admin.notifications.form.fields.is_read'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('read_at'),
                        false: fn (Builder $query) => $query->whereNull('read_at'),
                    ),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableBulkAction::make('mark_as_read')
                        ->label(__('admin.notifications.mark_as_read'))
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records): void {
                            $records->each(function (Notification $record): void {
                                $record->update(['read_at' => now()]);
                            });

                            FilamentNotification::make()
                                ->title(__('admin.notifications.marked_as_read'))
                                ->success()
                                ->send();
                        }),
                    TableBulkAction::make('mark_as_unread')
                        ->label(__('admin.notifications.mark_as_unread'))
                        ->icon('heroicon-o-x-circle')
                        ->action(function (Collection $records): void {
                            $records->each(function (Notification $record): void {
                                $record->update(['read_at' => null]);
                            });

                            FilamentNotification::make()
                                ->title(__('admin.notifications.marked_as_unread'))
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
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'view' => Pages\ViewNotification::route('/{record}'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}
