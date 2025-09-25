<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Notification;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkAction as TableBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

final class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-bell';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'System';
    }

    protected static ?int $navigationSort = 3;

    public static function getPluralModelLabel(): string
    {
        return __('admin.notifications.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.notifications.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('admin.notifications.form.sections.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('user_id')
                                ->label(__('admin.notifications.form.fields.user'))
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(1),
                            TextInput::make('type')
                                ->label(__('admin.notifications.form.fields.type'))
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),
                        ]),
                    TextInput::make('title')
                        ->label(__('admin.notifications.form.fields.title'))
                        ->required()
                        ->maxLength(255),
                    Textarea::make('body')
                        ->label(__('admin.notifications.form.fields.body'))
                        ->required()
                        ->rows(4),
                    SchemaGrid::make(2)
                        ->schema([
                            Toggle::make('is_read')
                                ->label(__('admin.notifications.form.fields.is_read'))
                                ->default(false)
                                ->columnSpan(1),
                            DateTimePicker::make('read_at')
                                ->label(__('admin.notifications.form.fields.read_at'))
                                ->columnSpan(1),
                        ]),
                ])
                ->columns(1),
            SchemaSection::make(__('admin.notifications.form.sections.metadata'))
                ->schema([
                    Placeholder::make('created_at')
                        ->label(__('admin.notifications.form.fields.created_at'))
                        ->content(fn ($record) => $record?->created_at?->format('Y-m-d H:i:s') ?? '-'),
                    Placeholder::make('updated_at')
                        ->label(__('admin.notifications.form.fields.updated_at'))
                        ->content(fn ($record) => $record?->updated_at?->format('Y-m-d H:i:s') ?? '-'),
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('admin.notifications.table.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('admin.notifications.table.type'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'info' => 'info',
                        'success' => 'success',
                        'warning' => 'warning',
                        'error' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('title')
                    ->label(__('admin.notifications.table.title'))
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),
                IconColumn::make('is_read')
                    ->label(__('admin.notifications.table.is_read'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('read_at')
                    ->label(__('admin.notifications.table.read_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin.notifications.table.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label(__('admin.notifications.filters.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label(__('admin.notifications.filters.type'))
                    ->options([
                        'info' => 'Info',
                        'success' => 'Success',
                        'warning' => 'Warning',
                        'error' => 'Error',
                    ]),
                Filter::make('is_read')
                    ->label(__('admin.notifications.filters.read'))
                    ->query(fn (Builder $query): Builder => $query->where('is_read', true)),
                Filter::make('unread')
                    ->label(__('admin.notifications.filters.unread'))
                    ->query(fn (Builder $query): Builder => $query->where('is_read', false)),
                DateFilter::make('created_at')
                    ->label(__('admin.notifications.filters.created_at')),
                Filter::make('recent')
                    ->label(__('admin.notifications.filters.recent'))
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7))),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                TableBulkAction::make('mark_as_read')
                    ->label(__('admin.notifications.actions.mark_as_read'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Notification $record): void {
                        $record->update(['is_read' => true, 'read_at' => now()]);
                        FilamentNotification::make()
                            ->title(__('admin.notifications.marked_as_read'))
                            ->success()
                            ->send();
                    }),
                TableBulkAction::make('mark_as_unread')
                    ->label(__('admin.notifications.actions.mark_as_unread'))
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->action(function (Notification $record): void {
                        $record->update(['is_read' => false, 'read_at' => null]);
                        FilamentNotification::make()
                            ->title(__('admin.notifications.marked_as_unread'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableBulkAction::make('bulk_mark_as_read')
                        ->label(__('admin.notifications.actions.bulk_mark_as_read'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(function (Notification $record): void {
                                $record->update(['is_read' => true, 'read_at' => now()]);
                            });
                            FilamentNotification::make()
                                ->title(__('admin.notifications.bulk_marked_as_read'))
                                ->success()
                                ->send();
                        }),
                    TableBulkAction::make('bulk_mark_as_unread')
                        ->label(__('admin.notifications.actions.bulk_mark_as_unread'))
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each(function (Notification $record): void {
                                $record->update(['is_read' => false, 'read_at' => null]);
                            });
                            FilamentNotification::make()
                                ->title(__('admin.notifications.bulk_marked_as_unread'))
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
