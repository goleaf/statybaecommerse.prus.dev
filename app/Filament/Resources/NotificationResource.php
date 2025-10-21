<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Notification;
use App\Support\Filament\Components\Flatpickr;
use App\Support\Filament\Filters\SingleDateFilter;
use BackedEnum;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
use Filament\Schemas\Schema;

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

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make(__('admin.notifications.form.sections.basic_information'))
                ->schema([
                    Grid::make(2)
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
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_read')
                                ->label(__('admin.notifications.form.fields.is_read'))
                                ->default(false)
                                ->columnSpan(1),
                            Flatpickr::makeDateTime('read_at')
                                ->label(__('admin.notifications.form.fields.read_at'))
                                ->columnSpan(1),
                        ]),
                ])
                ->columns(1),
            Section::make(__('admin.notifications.form.sections.metadata'))
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
                TernaryFilter::make('is_read')
                    ->label(__('admin.notifications.filters.read'))
                    ->trueLabel(__('admin.notifications.filters.read'))
                    ->falseLabel(__('admin.notifications.filters.unread'))
                    ->queries(
                        true: fn (Builder $query): Builder => $query->where('is_read', true),
                        false: fn (Builder $query): Builder => $query->where('is_read', false),
                    ),
                Filter::make('created_at')
                    ->label(__('admin.notifications.filters.created_at'))
                    ->form([
                        Flatpickr::make('value')
                            ->label(__('admin.notifications.filters.created_at'))
                            ->format('Y-m-d')
                            ->displayFormat('Y-m-d'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => SingleDateFilter::apply(
                        $query,
                        $data['value'] ?? null,
                        'created_at',
                    )),
                Filter::make('recent')
                    ->label(__('admin.notifications.filters.recent'))
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7))),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('mark_as_read')
                    ->label(__('admin.notifications.actions.mark_as_read'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Notification $record): bool => ! $record->is_read)
                    ->action(function (Notification $record): void {
                        $record->forceFill([
                            'is_read' => true,
                            'read_at' => now(),
                        ])->save();

                        FilamentNotification::make()
                            ->title(__('admin.notifications.marked_as_read'))
                            ->success()
                            ->send();
                    }),
                Action::make('mark_as_unread')
                    ->label(__('admin.notifications.actions.mark_as_unread'))
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (Notification $record): bool => $record->is_read)
                    ->action(function (Notification $record): void {
                        $record->forceFill([
                            'is_read' => false,
                            'read_at' => null,
                        ])->save();

                        FilamentNotification::make()
                            ->title(__('admin.notifications.marked_as_unread'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('bulk_mark_as_read')
                        ->label(__('admin.notifications.actions.bulk_mark_as_read'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(function (Notification $record): void {
                                $record->forceFill([
                                    'is_read' => true,
                                    'read_at' => now(),
                                ])->save();
                            });

                            FilamentNotification::make()
                                ->title(__('admin.notifications.bulk_marked_as_read'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('bulk_mark_as_unread')
                        ->label(__('admin.notifications.actions.bulk_mark_as_unread'))
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each(function (Notification $record): void {
                                $record->forceFill([
                                    'is_read' => false,
                                    'read_at' => null,
                                ])->save();
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
