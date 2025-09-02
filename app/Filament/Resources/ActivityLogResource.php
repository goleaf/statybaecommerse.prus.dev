<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;
use BackedEnum;

final class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('Activity Log');
    }

    public static function getModelLabel(): string
    {
        return __('Activity');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Activities');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->label(__('Log Name'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'product' => 'success',
                        'order' => 'warning',
                        'user' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('Description'))
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label(__('Subject Type'))
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('subject_id')
                    ->label(__('Subject ID'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label(__('User'))
                    ->searchable()
                    ->sortable()
                    ->default(__('System')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->label(__('Log Type'))
                    ->options([
                        'product' => __('Product'),
                        'order' => __('Order'),
                        'user' => __('User'),
                        'category' => __('Category'),
                        'brand' => __('Brand'),
                    ]),
                Tables\Filters\SelectFilter::make('subject_type')
                    ->label(__('Subject Type'))
                    ->options([
                        'App\Models\Product' => __('Product'),
                        'App\Models\Order' => __('Order'),
                        'App\Models\User' => __('User'),
                        'App\Models\Category' => __('Category'),
                        'App\Models\Brand' => __('Brand'),
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Tables\Filters\Indicators\DatePicker::make('created_from'),
                        Tables\Filters\Indicators\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalContent(function (Activity $record) {
                        return view('filament.activity-log.view-modal', [
                            'activity' => $record,
                            'properties' => $record->properties->toArray(),
                        ]);
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['description', 'log_name'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Log Type' => $record->log_name,
            'Subject' => $record->subject_type ? class_basename($record->subject_type) . ' #' . $record->subject_id : null,
            'User' => $record->causer?->name ?? __('System'),
            'Date' => $record->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
