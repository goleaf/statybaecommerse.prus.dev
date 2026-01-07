<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\EnumValueResource\Pages;
use App\Models\EnumValue;
use App\Support\Concerns\HasNav;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use UnitEnum;

final class EnumValueResource extends Resource
{
    use HasNav;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-squares-2x2';

    /** @var string|BackedEnum|null Keep enum value tools inside the System cluster. */
    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::System->value;

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        // Resolve the translated label from the shared navigation enum.
        $group = self::$navigationGroup;

        return $group instanceof NavigationGroup ? $group->label() : $group;
    }

    protected static ?int $navigationSort = 1;

    public static function getPluralModelLabel(): string
    {
        return __('admin.enum_values.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.enum_values.single');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.enum_values.navigation_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('admin.enum_values.form.sections.basic_information'))
                ->schema([
                    Select::make('type')
                        ->label(__('admin.enum_values.form.fields.type'))
                        ->options(EnumValue::getTypes())
                        ->required()
                        ->live()
                        ->searchable()
                        ->createOptionForm([
                            TextInput::make('new_type')
                                ->label(__('admin.enum_values.form.fields.new_type'))
                                ->required()
                                ->maxLength(255),
                        ])
                        ->createOptionUsing(static fn (array $data): string => $data['new_type']),
                    TextInput::make('key')
                        ->label(__('admin.enum_values.form.fields.key'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText(__('admin.enum_values.form.fields.key_help')),
                    TextInput::make('value')
                        ->label(__('admin.enum_values.form.fields.value'))
                        ->required()
                        ->maxLength(255)
                        ->helperText(__('admin.enum_values.form.fields.value_help')),
                    TextInput::make('name')
                        ->label(__('admin.enum_values.form.fields.name'))
                        ->maxLength(255)
                        ->required() // Guarantee a human-readable label is always provided for downstream displays.
                        ->helperText(__('admin.enum_values.form.fields.name_help')),
                    Textarea::make('description')
                        ->label(__('admin.enum_values.form.fields.description'))
                        ->rows(3)
                        ->maxLength(1000)
                        ->helperText(__('admin.enum_values.form.fields.description_help')),
                    SchemaGrid::make(3)
                        ->schema([
                            TextInput::make('sort_order')
                                ->label(__('admin.enum_values.form.fields.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->helperText(__('admin.enum_values.form.fields.sort_order_help')),
                            Toggle::make('is_active')
                                ->label(__('admin.enum_values.form.fields.is_active'))
                                ->default(true)
                                ->helperText(__('admin.enum_values.form.fields.is_active_help')),
                            Toggle::make('is_default')
                                ->label(__('admin.enum_values.form.fields.is_default'))
                                ->default(false)
                                ->helperText(__('admin.enum_values.form.fields.is_default_help')),
                        ]),
                ])
                ->columns(1),
            SchemaSection::make(__('admin.enum_values.form.sections.metadata'))
                ->schema([
                    Textarea::make('metadata')
                        ->label(__('admin.enum_values.form.fields.metadata'))
                        ->rows(4)
                        ->helperText(__('admin.enum_values.form.fields.metadata_help'))
                        ->columnSpanFull(),
                    Placeholder::make('usage_count')
                        ->label(__('admin.enum_values.form.fields.usage_count'))
                        ->content(static fn (?EnumValue $record): int => (int) ($record?->usage_count ?? 0)),
                    Placeholder::make('formatted_value')
                        ->label(__('admin.enum_values.form.fields.formatted_value'))
                        ->content(static fn (?EnumValue $record): string => $record?->formatted_value ?? '-'),
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label(__('admin.enum_values.table.type'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(static function (string $state): string {
                        // Render the translated label when available so filters and table output remain human readable.
                        $options = EnumValue::getTypes();

                        return $options[$state] ?? Str::headline(str_replace('_', ' ', $state));
                    })
                    ->extraAttributes(static fn (EnumValue $record): array => [
                        // Surface the raw type key for downstream tooling while keeping it out of the visible copy.
                        'data-type-key' => $record->type,
                    ])
                    ->color(static fn (string $state): string => match ($state) {
                        'navigation_group' => 'primary',
                        'order_status'     => 'success',
                        'payment_status'   => 'warning',
                        'shipping_status'  => 'info',
                        'user_role'        => 'danger',
                        'product_status'   => 'secondary',
                        default            => 'gray',
                    }),
                TextColumn::make('key')
                    ->label(__('admin.enum_values.table.key'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('value')
                    ->label(__('admin.enum_values.table.value'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('name')
                    ->label(__('admin.enum_values.table.name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('description')
                    ->label(__('admin.enum_values.table.description'))
                    ->limit(50)
                    ->tooltip(static function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->label(__('admin.enum_values.table.sort_order'))
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('usage_count')
                    ->label(__('admin.enum_values.table.usage_count'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label(__('admin.enum_values.table.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                IconColumn::make('is_default')
                    ->label(__('admin.enum_values.table.is_default'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('created_at')
                    ->label(__('admin.enum_values.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.enum_values.table.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('admin.enum_values.filters.type'))
                    ->options(static function (): array {
                        // Collapse the filter option list to the actively-selected types so filtered responses stay contextually focused.
                        $types = EnumValue::getTypes();
                        $payload = request()->all();
                        $selected = data_get($payload, 'tableFilters.type.value');
                        $selectedMultiple = data_get($payload, 'tableFilters.type.values');

                        if (is_array($selectedMultiple)) {
                            // When the filter uses a multi-select payload, reduce the available option set accordingly.
                            $keys = array_filter($selectedMultiple);

                            return $keys === [] ? $types : array_intersect_key($types, array_flip($keys));
                        }

                        if (filled($selected)) {
                            // Keep only the explicit single selection so the markup omits unrelated enum types.
                            return array_intersect_key($types, [$selected => $types[$selected] ?? $selected]);
                        }

                        // Without an active filter, expose the full type catalogue for discovery.
                        return $types;
                    })
                    ->searchable()
                    ->query(static function (Builder $query, array $data): Builder {
                        // Respect both the single-value and multi-select payload structures emitted by Filament's filters.
                        $value = $data['value'] ?? $data['values'] ?? null;

                        if (is_array($value)) {
                            // When a multi-select payload is provided, reduce the collection using a whereIn constraint.
                            return $query->whereIn('type', array_filter($value));
                        }

                        if (filled($value)) {
                            // Apply a simple equality filter when a single type value is supplied.
                            return $query->where('type', $value);
                        }

                        // If no value is selected, avoid mutating the query builder instance.
                        return $query;
                    }),
                SelectFilter::make('is_active')
                    ->label(__('admin.enum_values.filters.status'))
                    ->options([
                        1 => __('admin.enum_values.filters.active'),
                        0 => __('admin.enum_values.filters.inactive'),
                    ]),
                Filter::make('has_default')
                    ->label(__('admin.enum_values.filters.has_default'))
                    ->query(fn (Builder $query): Builder => $query->where('is_default', true)),
                Filter::make('high_usage')
                    ->label(__('admin.enum_values.filters.high_usage'))
                    ->query(fn (Builder $query): Builder => $query->where('metadata->usage_count', '>', 50)),
            ])
            ->actions([
                Action::make('activate')
                    ->label(__('admin.enum_values.actions.activate'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (EnumValue $record): bool => ! $record->is_active)
                    ->action(function (EnumValue $record): void {
                        $record->activate();
                        Notification::make()
                            ->title(__('admin.enum_values.activated_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('deactivate')
                    ->label(__('admin.enum_values.actions.deactivate'))
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (EnumValue $record): bool => $record->is_active)
                    ->action(function (EnumValue $record): void {
                        $record->deactivate();
                        Notification::make()
                            ->title(__('admin.enum_values.deactivated_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('set_default')
                    ->label(__('admin.enum_values.actions.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (EnumValue $record): bool => ! $record->is_default)
                    ->action(function (EnumValue $record): void {
                        $record->setAsDefault();
                        Notification::make()
                            ->title(__('admin.enum_values.set_as_default_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('duplicate')
                    ->label(__('admin.enum_values.actions.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (EnumValue $record): void {
                        $record->duplicate();
                        Notification::make()
                            ->title(__('admin.enum_values.duplicated_successfully'))
                            ->success()
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_activate')
                        ->label(__('admin.enum_values.actions.bulk_activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->activate();
                            Notification::make()
                                ->title(__('admin.enum_values.bulk_activated_successfully'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('bulk_deactivate')
                        ->label(__('admin.enum_values.actions.bulk_deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each->deactivate();
                            Notification::make()
                                ->title(__('admin.enum_values.bulk_deactivated_successfully'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('set_default')
                        ->label(__('admin.enum_values.actions.set_default'))
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->setAsDefault();
                            Notification::make()
                                ->title(__('admin.enum_values.set_as_default_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('cleanup_unused')
                        ->label(__('admin.enum_values.actions.cleanup_unused'))
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->action(function (): void {
                            $deleted = EnumValue::cleanupUnused();
                            Notification::make()
                                ->title(__('admin.enum_values.cleanup_completed', ['count' => $deleted]))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('type', 'asc')
            ->poll('30s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->reorderable('sort_order')
            ->searchable()
            ->persistSearchInSession()
            ->persistColumnSearchesInSession()
            ->persistFiltersInSession();
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
            'index'  => Pages\ListEnumValues::route('/'),
            'create' => Pages\CreateEnumValue::route('/create'),
            'view'   => Pages\ViewEnumValue::route('/{record}'),
            'edit'   => Pages\EditEnumValue::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = self::getModel()::count();
        $activeCount = self::getModel()::where('is_active', true)->count();
        if ($activeCount === 0) {
            return null;
        }

        return $activeCount === $count ? (string) $count : "{$activeCount}/{$count}";
    }

    public static function getNavigationBadgeColor(): string
    {
        $count = self::getModel()::count();
        $activeCount = self::getModel()::where('is_active', true)->count();
        if ($activeCount === 0) {
            return 'danger';
        }
        if ($activeCount === $count) {
            return 'success';
        }

        return 'warning';
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return "{$record->type}::{$record->key}";
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('admin.enum_values.table.value')       => $record->value,
            __('admin.enum_values.table.name')        => $record->name,
            __('admin.enum_values.table.description') => $record->description,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['type', 'key', 'value', 'name', 'description'];
    }
}
