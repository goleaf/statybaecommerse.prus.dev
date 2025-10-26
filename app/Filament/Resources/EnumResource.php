<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\EnumResource\Pages;
use App\Models\EnumValue;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Components\Tabs as SchemaTabs;
use Filament\Schemas\Components\Tabs\Tab as SchemaTab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

final class EnumResource extends Resource
{
    protected static ?string $model = EnumValue::class;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-squares-2x2';

    /** @var string|BackedEnum|null Anchor the resource to the System navigation area. */
    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::System->value;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        // Use the centralized enum label to avoid duplicated translations.
        $group = self::$navigationGroup;

        return $group instanceof NavigationGroup ? $group->label() : $group;
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin.enums.title');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('admin.enums.plural');
    }

    public static function getModelLabel(): string
    {
        return trans('admin.enums.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaTabs::make('enum_resource_tabs')
                ->tabs([
                    SchemaTab::make(trans('admin.enums.form.tabs.basic_information'))
                        ->schema([
                            SchemaSection::make(trans('admin.enums.form.sections.basic_information'))
                                ->schema([
                                    SchemaGrid::make(2)
                                        ->schema([
                                            Select::make('type')
                                                ->label(trans('admin.enums.form.fields.type'))
                                                ->options(self::getTypeOptions())
                                                ->searchable()
                                                ->required()
                                                ->live(),
                                            TextInput::make('key')
                                                ->label(trans('admin.enums.form.fields.key'))
                                                ->required()
                                                ->maxLength(255)
                                                ->unique(ignoreRecord: true),
                                        ])
                                        ->columnSpanFull(),
                                    SchemaGrid::make(2)
                                        ->schema([
                                            TextInput::make('value')
                                                ->label(trans('admin.enums.form.fields.value'))
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('name')
                                                ->label(trans('admin.enums.form.fields.name'))
                                                ->maxLength(255),
                                        ])
                                        ->columnSpanFull(),
                                    Textarea::make('description')
                                        ->label(trans('admin.enums.form.fields.description'))
                                        ->rows(3)
                                        ->maxLength(1000)
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    SchemaTab::make(trans('admin.enums.form.tabs.additional_settings'))
                        ->schema([
                            SchemaSection::make(trans('admin.enums.form.sections.additional_settings'))
                                ->schema([
                                    SchemaGrid::make(3)
                                        ->schema([
                                            TextInput::make('sort_order')
                                                ->label(trans('admin.enums.form.fields.sort_order'))
                                                ->numeric()
                                                ->default(0),
                                            Toggle::make('is_active')
                                                ->label(trans('admin.enums.form.fields.is_active'))
                                                ->default(true),
                                            Toggle::make('is_default')
                                                ->label(trans('admin.enums.form.fields.is_default'))
                                                ->default(false),
                                        ]),
                                    KeyValue::make('metadata')
                                        ->label(trans('admin.enums.form.fields.metadata'))
                                        ->keyLabel(trans('admin.enums.form.fields.metadata_key'))
                                        ->valueLabel(trans('admin.enums.form.fields.metadata_value'))
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    SchemaTab::make(trans('admin.enums.form.tabs.preview'))
                        ->schema([
                            SchemaSection::make(trans('admin.enums.form.sections.preview'))
                                ->schema([
                                    Placeholder::make('usage_count')
                                        ->label(trans('admin.enums.form.fields.usage_count'))
                                        ->content(fn (?EnumValue $record): string => (string) ($record?->usage_count ?? 0)),
                                    Placeholder::make('enum_preview')
                                        ->label(trans('admin.enums.form.fields.enum_preview'))
                                        ->content(fn (?EnumValue $record): string => $record?->formatted_value ?? '-'),
                                ])
                                ->columns(2),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label(trans('admin.enums.form.fields.type'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'navigation_group' => 'primary',
                        'order_status'     => 'success',
                        'payment_status'   => 'warning',
                        'shipping_status'  => 'info',
                        'user_role'        => 'danger',
                        'product_status'   => 'secondary',
                        default            => 'gray',
                    }),
                TextColumn::make('key')
                    ->label(trans('admin.enums.form.fields.key'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('value')
                    ->label(trans('admin.enums.form.fields.value'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('name')
                    ->label(trans('admin.enums.form.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('description')
                    ->label(trans('admin.enums.form.fields.description'))
                    ->limit(50)
                    ->tooltip(fn (TextColumn $column): ?string => strlen((string) $column->getState()) > 50 ? (string) $column->getState() : null)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->label(trans('admin.enums.form.fields.sort_order'))
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('usage_count')
                    ->label(trans('admin.enums.form.fields.usage_count'))
                    ->numeric()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label(trans('admin.enums.form.fields.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                IconColumn::make('is_default')
                    ->label(trans('admin.enums.form.fields.is_default'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('created_at')
                    ->label(trans('admin.enums.form.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('admin.enums.form.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(trans('admin.enums.filters.type'))
                    ->options(self::getTypeOptions())
                    ->searchable(),
                SelectFilter::make('is_active')
                    ->label(trans('admin.enums.filters.is_active'))
                    ->options([
                        1 => trans('admin.enums.actions.activate'),
                        0 => trans('admin.enums.actions.deactivate'),
                    ]),
                TernaryFilter::make('is_default')
                    ->label(trans('admin.enums.filters.is_default')),
                Filter::make('recent')
                    ->label(trans('admin.enums.filters.recent'))
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                Action::make('activate')
                    ->label(trans('admin.enums.actions.activate'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (EnumValue $record): bool => ! $record->is_active)
                    ->action(function (EnumValue $record): void {
                        $record->activate();
                        Notification::make()
                            ->title(trans('admin.enums.activated_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('deactivate')
                    ->label(trans('admin.enums.actions.deactivate'))
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (EnumValue $record): bool => $record->is_active)
                    ->action(function (EnumValue $record): void {
                        $record->deactivate();
                        Notification::make()
                            ->title(trans('admin.enums.deactivated_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('set_default')
                    ->label(trans('admin.enums.actions.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (EnumValue $record): bool => ! $record->is_default)
                    ->action(function (EnumValue $record): void {
                        $record->setAsDefault();
                        Notification::make()
                            ->title(trans('admin.enums.set_default_successfully'))
                            ->success()
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate_bulk')
                        ->label(trans('admin.enums.actions.activate_bulk'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->activate();
                            Notification::make()
                                ->title(trans('admin.enums.bulk_activated_successfully'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('deactivate_bulk')
                        ->label(trans('admin.enums.actions.deactivate_bulk'))
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each->deactivate();
                            Notification::make()
                                ->title(trans('admin.enums.bulk_deactivated_successfully'))
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('type')
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'        => Pages\ListEnums::route('/'),
            'create'       => Pages\CreateEnum::route('/create'),
            'view'         => Pages\ViewEnum::route('/{record}'),
            'edit'         => Pages\EditEnum::route('/{record}/edit'),
            'values'       => Pages\ListEnumValues::route('/values'),
            'create_value' => Pages\CreateEnumValue::route('/values/create'),
            'view_value'   => Pages\ViewEnumValue::route('/values/{record}'),
            'edit_value'   => Pages\EditEnumValue::route('/values/{record}/edit'),
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

    public static function getNavigationBadgeColor(): string|array|null
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
        return sprintf('%s::%s', $record->type, $record->key);
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            trans('admin.enums.form.fields.value')       => $record->value,
            trans('admin.enums.form.fields.name')        => $record->name,
            trans('admin.enums.form.fields.description') => $record->description,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['type', 'key', 'value', 'name', 'description'];
    }

    /**
     * @return array<string, string>
     */
    private static function getTypeOptions(): array
    {
        $options = trans('admin.enums.types');

        if (is_array($options) && $options !== []) {
            return $options;
        }

        return EnumValue::getTypes();
    }
}
