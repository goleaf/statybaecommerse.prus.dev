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
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkAction as TableBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
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
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bell';
    /** @var string|UnitEnum|null */
    /*protected static string | UnitEnum | null $navigationGroup = NavigationGroup::System;
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
        return "System"->value;
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.notifications.plural', [], 'Notifications');
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
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('admin.notifications.form.sections.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
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
                                ->columnSpan(1),
                        ]),
                    TextInput::make('type')
                        ->label(__('admin.notifications.form.fields.type'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('data.title')
                        ->label(__('admin.notifications.form.fields.title'))
                        ->maxLength(255),
                    Textarea::make('data.message')
                        ->label(__('admin.notifications.form.fields.message'))
                        ->rows(3),
                    Grid::make(2)
                        ->schema([
                            ColorPicker::make('data.color')
                                ->label(__('admin.notifications.form.fields.color')),
                            Toggle::make('data.urgent')
                                ->label(__('admin.notifications.form.fields.urgent')),
                        ]),
                    TagsInput::make('data.tags')
                        ->label(__('admin.notifications.form.fields.tags')),
                    TextInput::make('data.attachment')
                        ->label(__('admin.notifications.form.fields.attachment'))
                        ->url(),
                    DateTimePicker::make('read_at')
                        ->label(__('admin.notifications.form.fields.read_at')),
                ])
                ->columns(1),
            Section::make(__('admin.notifications.form.sections.raw_data'))
                ->schema([
                    KeyValue::make('data')
                        ->label(__('admin.notifications.form.fields.raw_data'))
                        ->keyLabel(__('admin.notifications.form.fields.key'))
                        ->valueLabel(__('admin.notifications.form.fields.value'))
                        ->addActionLabel(__('admin.notifications.form.fields.add_field')),
                ])
                ->collapsible(),
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
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'order' => 'blue',
                        'product' => 'green',
                        'user' => 'purple',
                        'system' => 'orange',
                        'payment' => 'yellow',
                        'shipping' => 'indigo',
                        'review' => 'pink',
                        'promotion' => 'red',
                        'newsletter' => 'cyan',
                        'support' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('data.title')
                    ->label(__('admin.notifications.form.fields.title'))
                    ->searchable()
                    ->limit(50),
                TextColumn::make('data.message')
                    ->label(__('admin.notifications.form.fields.message'))
                    ->searchable()
                    ->limit(100)
                    ->wrap(),
                TextColumn::make('notifiable_type')
                    ->label(__('admin.notifications.form.fields.notifiable_type'))
                    ->formatStateUsing(fn(string $state): string => class_basename($state))
                    ->sortable()
                    ->badge(),
                TextColumn::make('user.name')
                    ->label(__('admin.notifications.form.fields.user'))
                    ->sortable()
                    ->searchable(),
                IconColumn::make('is_read')
                    ->label(__('admin.notifications.form.fields.is_read'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('data.urgent')
                    ->label(__('admin.notifications.form.fields.urgent'))
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('danger')
                    ->falseColor('gray'),
                TextColumn::make('data.tags')
                    ->label(__('admin.notifications.form.fields.tags'))
                    ->badge()
                    ->separator(',')
                    ->limit(3),
                TextColumn::make('created_at')
                    ->label(__('admin.notifications.form.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
                TextColumn::make('read_at')
                    ->label(__('admin.notifications.form.fields.read_at'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('notifiable_type')
                    ->options([
                        User::class => 'User',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        'order' => 'Order',
                        'product' => 'Product',
                        'user' => 'User',
                        'system' => "System",
                        'payment' => 'Payment',
                        'shipping' => 'Shipping',
                        'review' => 'Review',
                        'promotion' => 'Promotion',
                        'newsletter' => 'Newsletter',
                        'support' => 'Support',
                    ]),
                TernaryFilter::make('is_read')
                    ->label(__('admin.notifications.form.fields.is_read'))
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('read_at'),
                        false: fn(Builder $query) => $query->whereNull('read_at'),
                    ),
                TernaryFilter::make('urgent')
                    ->label(__('admin.notifications.form.fields.urgent'))
                    ->queries(
                        true: fn(Builder $query) => $query->whereJsonContains('data->urgent', true),
                        false: fn(Builder $query) => $query->where(function ($q) {
                            $q->whereJsonDoesntContain('data->urgent', true)->orWhereNull('data->urgent');
                        }),
                    ),
                DateFilter::make('created_at')
                    ->label(__('admin.notifications.form.fields.created_at')),
                DateFilter::make('read_at')
                    ->label(__('admin.notifications.form.fields.read_at')),
                Filter::make('today')
                    ->label(__('admin.notifications.filters.today'))
                    ->query(fn(Builder $query): Builder => $query->whereDate('created_at', today())),
                Filter::make('this_week')
                    ->label(__('admin.notifications.filters.this_week'))
                    ->query(fn(Builder $query): Builder => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])),
                Filter::make('this_month')
                    ->label(__('admin.notifications.filters.this_month'))
                    ->query(fn(Builder $query): Builder => $query->whereMonth('created_at', now()->month)),
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
                    TableBulkAction::make('mark_as_urgent')
                        ->label(__('admin.notifications.mark_as_urgent'))
                        ->icon('heroicon-o-exclamation-triangle')
                        ->action(function (Collection $records): void {
                            $records->each(function (Notification $record): void {
                                $data = $record->data;
                                $data['urgent'] = true;
                                $record->update(['data' => $data]);
                            });
                            FilamentNotification::make()
                                ->title(__('admin.notifications.marked_as_urgent'))
                                ->success()
                                ->send();
                        }),
                    TableBulkAction::make('duplicate')
                        ->label(__('admin.notifications.duplicate'))
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (Collection $records): void {
                            $records->each(function (Notification $record): void {
                                $record->duplicate();
                            });
                            FilamentNotification::make()
                                ->title(__('admin.notifications.duplicated'))
                                ->success()
                                ->send();
                        }),
                    TableBulkAction::make('cleanup_old')
                        ->label(__('admin.notifications.cleanup_old'))
                        ->icon('heroicon-o-trash')
                        ->action(function (Collection $records): void {
                            $count = $records->count();
                            $records->each(function (Notification $record): void {
                                $record->delete();
                            });
                            FilamentNotification::make()
                                ->title(__('admin.notifications.cleanup_completed', ['count' => $count]))
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
