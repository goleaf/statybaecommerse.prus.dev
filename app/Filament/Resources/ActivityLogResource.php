<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use App\Support\Filament\Filters\DateRangeFilter;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;
    /**
     * Use the explicit union type required by Filament v4 so the resource remains compatible with the
     * framework's typed base property while still documenting the accepted icon formats.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'description';

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    public static function getNavigationGroup(): string
    {
        return __('navigation.groups.system');
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

    /**
     * Configure the table that lists activity log records along with filters and actions.
     */
    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
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
                    ->formatStateUsing(fn ($state) => class_basename((string) $state))
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
                    ->label(__('Created At')) // Make the filter caption explicit for the table header chips.
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
                    ->modalHeading(fn(ActivityLog $record) => (string) ($record->description ?? __('Activity details')))
                    ->modalSubheading(fn(ActivityLog $record) => (string) ($record->causer?->name ?? __('System')))
                    ->modalContent(fn(ActivityLog $record) => view(
                        'filament.resources.activity-log-resource.components.activity-details',
                        ['activity' => $record->loadMissing('causer', 'subject')]
                    ))
                    ->modalSubmitActionLabel(__('Close')),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Resolve distinct column values so select filters populate with real activity log data.
     *
     * @return array<string, string>
     */
    protected static function getDistinctFilterOptions(string $column): array
    {
        $values = ActivityLog::query()
            ->select($column)
            ->distinct()
            ->whereNotNull($column)
            ->orderBy($column)
            ->pluck($column)
            ->filter(static fn ($value) => $value !== '');

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
