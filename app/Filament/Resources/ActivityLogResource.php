<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Forms\Components\Flatpickr;
use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use App\Support\Filament\Filters\DateRangeFilter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
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
     * Icon used in the navigation menu. Type: string|\BackedEnum|null.
     */
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'description';

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static ?string $recordTitleAttribute = 'description';

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

    /**
     * Define the create/edit form schema using the Filament v4 Form API.
     * The resource remains read-only for now, so we return an empty schema
     * while keeping the hook available for future enhancements.
     */
    public static function form(Form $form): Form
    {
        // Configure the Filament resource form schema using the v4 Schema API.
        return $schema->schema([]);
    }

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
                    ->form([
                        Flatpickr::makeRange('range')
                            ->label(__('Created At'))

                            ->format('Y-m-d')
                            ->displayFormat('Y-m-d'),
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