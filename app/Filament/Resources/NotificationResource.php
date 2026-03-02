<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages\CreateNotification;
use App\Filament\Resources\NotificationResource\Pages\EditNotification;
use App\Filament\Resources\NotificationResource\Pages\ListNotifications;
use App\Filament\Resources\NotificationResource\Pages\ViewNotification;
use App\Models\Notification;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bell';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'name',
                        modifyQueryUsing: static fn (Builder $query): Builder => $query
                            ->withoutGlobalScopes()
                            ->orderBy('name'),
                    )
                    ->searchable()
                    ->preload()
                    ->default(static fn (): ?int => request()->integer('user_id') ?: null)
                    ->required(),
                TextInput::make('type')
                    ->maxLength(255)
                    ->default('App\\Notifications\\DatabaseNotification')
                    ->required(),
                KeyValue::make('data')
                    ->label(__('admin.labels.data'))
                    ->columnSpanFull(),
                DateTimePicker::make('read_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('admin.navigation.users'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(static fn (string $state): string => class_basename($state)),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('read_at')
                    ->dateTime()
                    ->sortable(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListNotifications::route('/'),
            'create' => CreateNotification::route('/create'),
            'view'   => ViewNotification::route('/{record}'),
            'edit'   => EditNotification::route('/{record}/edit'),
        ];
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function normalizePayload(array $data): array
    {
        $userId = is_numeric($data['user_id'] ?? null) ? (int) $data['user_id'] : null;

        if ($userId !== null && $userId > 0) {
            $data['user_id'] = $userId;
            $data['notifiable_id'] = $userId;
            $data['notifiable_type'] = User::class;
        }

        $type = trim((string) ($data['type'] ?? ''));
        $data['type'] = $type !== '' ? $type : 'App\\Notifications\\DatabaseNotification';
        $data['data'] = is_array($data['data'] ?? null) ? $data['data'] : [];

        return $data;
    }
}
