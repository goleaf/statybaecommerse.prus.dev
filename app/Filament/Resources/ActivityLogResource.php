<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup as AppNavigationGroup;
use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use App\Support\Filament\Filters\DateRangeFilter;
use App\Support\Nav;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

final class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'description';

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static BackedEnum|string|null $navigationIcon = null;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return Nav::iconForResource(self::class) ?? self::$navigationIcon;
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return Nav::groupForResource(self::class) ?? AppNavigationGroup::System;
    }

    public static function getNavigationLabel(): string
    {
        return __('activity_logs.plural');
    }

    public static function getModelLabel(): string
    {
        return __('activity_logs.single');
    }

    public static function getPluralModelLabel(): string
    {
        return __('activity_logs.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('ID'))
                    ->sortable(),
                TextColumn::make('log_name')
                    ->label(__('Log Name'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(50),
                TextColumn::make('causer.name')
                    ->label(__('Causer'))
                    ->default(__('System'))
                    ->sortable(),
                TextColumn::make('subject_type')
                    ->label(__('Subject Type'))
                    ->formatStateUsing(fn ($state): string => class_basename((string) $state))
                    ->sortable(),
                TextColumn::make('event')
                    ->label(__('Event'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('log_name')
                    ->label(__('Log Name'))
                    ->options(fn (): array => self::getDistinctFilterOptions('log_name')),
                SelectFilter::make('subject_type')
                    ->label(__('Subject Type'))
                    ->options(fn (): array => self::getDistinctFilterOptions('subject_type')),
                Filter::make('created_at')
                    ->label(__('Created At'))
                    ->form([
                        SupportFlatpickr::makeRange('range', withTime: false, displayFormat: 'Y-m-d', format: 'Y-m-d'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => DateRangeFilter::apply(
                        $query,
                        $data['range'] ?? null,
                        'created_at',
                    )),
            ])
            ->actions([
                Action::make('view_details')
                    ->label(__('View details'))
                    ->modalHeading(fn (ActivityLog $record): string => (string) ($record->description ?? __('Activity details')))
                    ->modalSubheading(fn (ActivityLog $record): string => (string) ($record->causer?->name ?? __('System')))
                    ->modalContent(fn (ActivityLog $record): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View => view(
                        'filament.resources.activity-log-resource.components.activity-details',
                        ['activity' => $record->loadMissing('causer', 'subject')]
                    ))
                    ->modalSubmitActionLabel(__('Close')),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected static function getDistinctFilterOptions(string $column): array
    {
        $values = ActivityLog::query()
            ->select($column)
            ->distinct()
            ->whereNotNull($column)
            ->orderBy($column)
            ->pluck($column)
            ->filter(static fn ($value): bool => $value !== '');

        if ($column === 'subject_type') {
            return $values
                ->mapWithKeys(static fn ($value): array => [
                    (string) $value => class_basename((string) $value),
                ])
                ->all();
        }

        return $values
            ->mapWithKeys(static fn ($value): array => [
                (string) $value => (string) $value,
            ])
            ->all();
    }

    public static function getRecordTitle(?Model $record): string
    {
        if ($record instanceof ActivityLog) {
            $title = (string) ($record->description ?? '');

            if ($title !== '') {
                return $title;
            }

            $key = $record->getKey();

            if ($key !== null) {
                return __('activity_logs.single') . ' #' . $key;
            }
        }

        return __('activity_logs.single');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
