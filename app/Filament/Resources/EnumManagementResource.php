<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use BackedEnum;
use UnitEnum;
use App\Filament\Resources\EnumManagementResource\Pages;
use App\Models\EnumValue;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use BackedEnum;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;
use UnitEnum;

final class EnumManagementResource extends Resource
{
    protected static ?string $model = EnumValue::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    /** @var string|\BackedEnum|null Pin enum tools to the shared System navigation section. */
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::System;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        // Share the navigation label via enum for localization consistency.
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

    public static function form(Schema $form): Schema
    {
        // Configure the Filament resource form schema using the v4 Schema API.
        return $schema->schema([
            Tabs::make('enum_management_tabs')
                ->tabs([
                    Tab::make(trans('admin.enums.form.tabs.basic_information'))
                        ->schema([
                            Section::make(trans('admin.enums.form.sections.basic_information'))
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('type')
                                                ->label(trans('admin.enums.form.fields.type'))
                                                ->options(self::getTypeOptions())
                                                ->required()
                                                ->searchable()
                                                ->live(),
                                            TextInput::make('key')
                                                ->label(trans('admin.enums.form.fields.key'))
                                                ->required()
                                                ->maxLength(255)
                                                ->unique(ignoreRecord: true),
                                        ])
                                        ->columnSpanFull(),
                                    Grid::make(2)
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
                    Tab::make(trans('admin.enums.form.tabs.additional_settings'))
                        ->schema([
                            Section::make(trans('admin.enums.form.sections.additional_settings'))
                                ->schema([
                                    Grid::make(3)
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
                    Tab::make(trans('admin.enums.form.tabs.preview'))
                        ->schema([
                            Section::make(trans('admin.enums.form.sections.preview'))
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
        // Configure the Filament table definition for the resource.
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
                    ->sortable()
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
                    ->label(trans('admin.enum_values.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('admin.enum_values.table.updated_at'))
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
            'index'       => Pages\ListEnumManagement::route('/'),
            'create'      => Pages\CreateEnumManagement::route('/create'),
            'view'        => Pages\ViewEnumManagement::route('/{record}'),
            'edit'        => Pages\EditEnumManagement::route('/{record}/edit'),
            'enums'       => Pages\ListEnums::route('/enums'),
            'create_enum' => Pages\CreateEnum::route('/enums/create'),
            'view_enum'   => Pages\ViewEnum::route('/enums/{record}'),
            'edit_enum'   => Pages\EditEnum::route('/enums/{record}/edit'),
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