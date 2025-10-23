<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Forms\Components\Flatpickr;
use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Notification;
use App\Support\Filament\Components\Flatpickr;
use App\Support\Filament\Filters\SingleDateFilter;
use BackedEnum;
use UnitEnum;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
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
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class NotificationResource extends Resource
{
    use HasNav;

    protected static ?string $model = Notification::class;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-bell';

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static UnitEnum|string|null $navigationGroup = 'System';

    protected static bool $shouldRegisterNavigation = false;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-bell';

    protected static UnitEnum|string|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    public static function getPluralModelLabel(): string
    {
        return __('admin.notifications.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.notifications.single');
    }

    public static function form(Form $form): Form|array
    {
        return $form
            ->schema([
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
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, bool $state): void {
                                        $set('read_at', $state ? now() : null);
                                    })
                                    ->columnSpan(1),
                                Flatpickr::makeDateTime('read_at')
                                    ->label(__('admin.notifications.form.fields.read_at'))
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                                        $set('is_read', filled($state));
                                    })
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

    public static function table(Table $table): Table|array
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('admin.notifications.table.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('notification_type')
                    ->label(__('admin.notifications.table.notification_type'))
                    ->badge()
                    ->icon(fn (Notification $record): string => $record->getNotificationTypeIcon())
                    ->color(fn (Notification $record): string => $record->getNotificationTypeColor())
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('data->type', $direction))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('data->type', 'like', "%{$search}%")),
                TextColumn::make('type')
                    ->label(__('admin.notifications.table.type'))
                    ->formatStateUsing(function (?string $state): string {
                        if (! is_string($state)) {
                            return '';
                        }

                        return Str::of($state)->afterLast('\\')->value();
                    })
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'info'    => 'info',
                        'success' => 'success',
                        'warning' => 'warning',
                        'error'   => 'danger',
                        default   => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('admin.notifications.table.title'))
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return is_string($state) && strlen($state) > 50 ? $state : null;
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
                SelectFilter::make('notification_type')
                    ->label(__('admin.notifications.filters.type'))
                    ->options([
                        'info'    => 'Info',
                        'success' => 'Success',
                        'warning' => 'Warning',
                        'error' => 'Error',
                    ])
                    ->query(function (Builder $query, ?string $value): Builder {
                        if (! filled($value)) {
                            return $query;
                        }

                        return $query->where('data->type', $value);
                    }),
                TernaryFilter::make('is_read')
                    ->label(__('admin.notifications.filters.read_state'))
                    ->nullable()
                    ->placeholder(__('admin.notifications.filters.any_state'))
                    ->trueLabel(__('admin.notifications.filters.read'))
                    ->falseLabel(__('admin.notifications.filters.unread'))
                    ->queries(
                        true: fn (Builder $query): Builder => $query->where('is_read', true),
                        false: fn (Builder $query): Builder => $query->where('is_read', false),
                        blank: fn (Builder $query): Builder => $query,
                    ),
                Filter::make('created_at')
                    ->label(__('admin.notifications.filters.created_at'))
                    ->form([
                        Flatpickr::make('value')->asDate()
                            ->label(__('admin.notifications.filters.created_at'))
                            ->format('Y-m-d')
                            ->displayFormat('Y-m-d'),
                    ])
                    ->modifyQueryUsing(fn (Builder $query, array $data): Builder => SingleDateFilter::apply(
                        $query,
                        $data['value'] ?? null,
                        'created_at',
                    )),
                Filter::make('recent')
                    ->label(__('admin.notifications.filters.recent'))
                    ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7))),
            ])
            ->recordActions([
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
                    })
                    ->successNotificationTitle(__('admin.notifications.marked_as_read')),
                Action::make('mark_as_unread')
                    ->label(__('admin.notifications.actions.mark_as_unread'))
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (Notification $record): bool => (bool) $record->is_read)
                    ->action(function (Notification $record): void {
                        $record->forceFill([
                            'is_read' => false,
                            'read_at' => null,
                        ])->save();
                    })
                    ->successNotificationTitle(__('admin.notifications.marked_as_unread')),
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
                        })
                        ->successNotificationTitle(__('admin.notifications.bulk_marked_as_read')),
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
                        })
                        ->successNotificationTitle(__('admin.notifications.bulk_marked_as_unread')),
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

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function mutateReadState(array $data): array
    {
        $readState = $data['read_state'] ?? null;

        unset($data['read_state']);

        if ($readState === self::READ_STATE_READ) {
            $data['read_at'] = $data['read_at'] ?? now();
        } elseif ($readState === self::READ_STATE_UNREAD) {
            $data['read_at'] = null;
        }

        return $data;
    }

    /**
     * @return Builder<Notification>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('user')
            ->latest('created_at');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'view'   => Pages\ViewNotification::route('/{record}'),
            'edit'   => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}
