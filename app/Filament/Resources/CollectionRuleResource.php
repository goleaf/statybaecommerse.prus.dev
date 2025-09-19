<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CollectionRuleResource\Pages;
use App\Models\Collection;
use App\Models\CollectionRule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
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
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use BackedEnum;

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.collection_rules.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.collection_rules.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('collection_rule_tabs')
                ->tabs([
                    Tab::make(__('admin.collection_rules.form.tabs.basic_information'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            SchemaSection::make(__('admin.collection_rules.form.sections.basic_information'))
                                ->schema([
                                    SchemaGrid::make(2)
                                        ->schema([
                                            Select::make('collection_id')
                                                ->label(__('admin.collection_rules.form.fields.collection'))
                                                ->relationship('collection', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name}")
                                                ->columnSpan(1),
                                            TextInput::make('field')
                                                ->label(__('admin.collection_rules.form.fields.field'))
                                                ->required()
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                        ]),
                                    SchemaGrid::make(3)
                                        ->schema([
                                            Select::make('operator')
                                                ->label(__('admin.collection_rules.form.fields.operator'))
                                                ->options([
                                                    'equals' => __('admin.collection_rules.operators.equals'),
                                                    'not_equals' => __('admin.collection_rules.operators.not_equals'),
                                                    'contains' => __('admin.collection_rules.operators.contains'),
                                                    'not_contains' => __('admin.collection_rules.operators.not_contains'),
                                                    'starts_with' => __('admin.collection_rules.operators.starts_with'),
                                                    'ends_with' => __('admin.collection_rules.operators.ends_with'),
                                                    'greater_than' => __('admin.collection_rules.operators.greater_than'),
                                                    'less_than' => __('admin.collection_rules.operators.less_than'),
                                                    'greater_than_or_equal' => __('admin.collection_rules.operators.greater_than_or_equal'),
                                                    'less_than_or_equal' => __('admin.collection_rules.operators.less_than_or_equal'),
                                                ])
                                                ->required()
                                                ->columnSpan(1),
                                            TextInput::make('value')
                                                ->label(__('admin.collection_rules.form.fields.value'))
                                                ->required()
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                            TextInput::make('position')
                                                ->label(__('admin.collection_rules.form.fields.position'))
                                                ->numeric()
                                                ->default(0)
                                                ->columnSpan(1),
                                        ])
                                ])
                                ->columns(1),
                        ]),
                    Tab::make(__('admin.collection_rules.form.tabs.rule_details'))
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            SchemaSection::make(__('admin.collection_rules.form.sections.rule_details'))
                                ->schema([
                                    Placeholder::make('collection_name')
                                        ->label(__('admin.collection_rules.form.fields.collection_name'))
                                        ->content(fn($record) => $record?->collection?->name ?? '-'),
                                    Placeholder::make('rule_description')
                                        ->label(__('admin.collection_rules.form.fields.rule_description'))
                                        ->content(fn($record) => $record
                                            ? "{$record->field} {$record->operator} {$record->value}"
                                            : '-'),
                                ])
                                ->columns(2),
                        ]),
                ])
                ->columnSpanFull(),
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
                TextColumn::make('collection.name')
                    ->label(__('admin.collection_rules.form.fields.collection'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('field')
                    ->label(__('admin.collection_rules.form.fields.field'))
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('operator')
                    ->label(__('admin.collection_rules.form.fields.operator'))
                    ->formatStateUsing(fn(string $state): string =>
                        match ($state) {
                            'equals' => __('admin.collection_rules.operators.equals'),
                            'not_equals' => __('admin.collection_rules.operators.not_equals'),
                            'contains' => __('admin.collection_rules.operators.contains'),
                            'not_contains' => __('admin.collection_rules.operators.not_contains'),
                            'starts_with' => __('admin.collection_rules.operators.starts_with'),
                            'ends_with' => __('admin.collection_rules.operators.ends_with'),
                            'greater_than' => __('admin.collection_rules.operators.greater_than'),
                            'less_than' => __('admin.collection_rules.operators.less_than'),
                            'greater_than_or_equal' => __('admin.collection_rules.operators.greater_than_or_equal'),
                            'less_than_or_equal' => __('admin.collection_rules.operators.less_than_or_equal'),
                            default => $state,
                        })
                    ->colors([
                        'primary' => 'equals',
                        'success' => 'contains',
                        'warning' => 'starts_with',
                        'danger' => 'not_equals',
                    ]),
                TextColumn::make('value')
                    ->label(__('admin.collection_rules.form.fields.value'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position')
                    ->label(__('admin.collection_rules.form.fields.position'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.collection_rules.form.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('collection_id')
                    ->label(__('admin.collection_rules.filters.collection'))
                    ->relationship('collection', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('operator')
                    ->label(__('admin.collection_rules.filters.operator'))
                    ->options([
                        'equals' => __('admin.collection_rules.operators.equals'),
                        'not_equals' => __('admin.collection_rules.operators.not_equals'),
                        'contains' => __('admin.collection_rules.operators.contains'),
                        'not_contains' => __('admin.collection_rules.operators.not_contains'),
                        'starts_with' => __('admin.collection_rules.operators.starts_with'),
                        'ends_with' => __('admin.collection_rules.operators.ends_with'),
                        'greater_than' => __('admin.collection_rules.operators.greater_than'),
                        'less_than' => __('admin.collection_rules.operators.less_than'),
                        'greater_than_or_equal' => __('admin.collection_rules.operators.greater_than_or_equal'),
                        'less_than_or_equal' => __('admin.collection_rules.operators.less_than_or_equal'),
                    ]),
                DateFilter::make('created_at')
                    ->label(__('admin.collection_rules.filters.created_at')),
                Filter::make('recent')
                    ->label(__('admin.collection_rules.filters.recent'))
                    ->query(fn(Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                TableBulkAction::make('reorder')
                    ->label(__('admin.collection_rules.actions.reorder'))
                    ->icon('heroicon-o-arrows-up-down')
                    ->color('info')
                    ->action(function (CollectionRule $record, array $data): void {
                        $record->update(['position' => $data['position'] ?? 0]);
                        FilamentNotification::make()
                            ->title(__('admin.collection_rules.reordered_successfully'))
                            ->success()
                            ->send();
                    })
                    ->form([
                        TextInput::make('position')
                            ->label(__('admin.collection_rules.form.fields.position'))
                            ->numeric()
                            ->required(),
                    ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableBulkAction::make('reorder_bulk')
                        ->label(__('admin.collection_rules.actions.reorder_bulk'))
                        ->icon('heroicon-o-arrows-up-down')
                        ->color('info')
                        ->action(function (EloquentCollection $records, array $data): void {
                            $records->each(function (CollectionRule $record, int $index) use ($data): void {
                                $record->update(['position' => ($data['start_position'] ?? 0) + $index]);
                            });
                            FilamentNotification::make()
                                ->title(__('admin.collection_rules.bulk_reordered_successfully'))
                                ->success()
                                ->send();
                        })
                        ->form([
                            TextInput::make('start_position')
                                ->label(__('admin.collection_rules.form.fields.start_position'))
                                ->numeric()
                                ->required()
                                ->default(0),
                        ]),
                ]),
            ])
            ->defaultSort('position', 'asc');
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
            'index' => Pages\ListCollectionRules::route('/'),
            'create' => Pages\CreateCollectionRule::route('/create'),
            'view' => Pages\ViewCollectionRule::route('/{record}'),
            'edit' => Pages\EditCollectionRule::route('/{record}/edit'),
        ];
    }
}
