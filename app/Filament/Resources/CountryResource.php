<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Filament\Resources\CountryResource\RelationManagers\AddressesRelationManager;
use App\Filament\Resources\CountryResource\RelationManagers\CitiesRelationManager;
use App\Filament\Resources\CountryResource\RelationManagers\CustomersRelationManager;
use App\Filament\Resources\CountryResource\RelationManagers\UsersRelationManager;
use App\Models\Country;
use App\Models\Scopes\ActiveScope;
use App\Support\Concerns\HasNav;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction as TableBulkAction;
use Filament\Actions\BulkActionGroup as TableBulkActionGroup;
use Filament\Actions\DeleteAction as TableDeleteAction;
use Filament\Actions\DeleteBulkAction as TableDeleteBulkAction;
use Filament\Actions\EditAction as TableEditAction;
use Filament\Actions\ViewAction as TableViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Throwable;

final class CountryResource extends Resource
{
    use HasNav;

    protected static ?string $model = Country::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('countries.navigation.countries');
    }

    public static function getModelLabel(): string
    {
        return __('countries.models.country');
    }

    public static function getPluralModelLabel(): string
    {
        return __('countries.models.countries');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make(__('countries.sections.basic_info'))
                    ->schema([
                        SchemaGrid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('countries.fields.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                                TextInput::make('name_official')
                                    ->label(__('countries.fields.name_official'))
                                    ->maxLength(255)
                                    ->columnSpan(1),
                            ]),
                        SchemaGrid::make(4)
                            ->schema([
                                TextInput::make('cca2')
                                    ->label(__('countries.fields.cca2'))
                                    ->required()
                                    ->maxLength(2)
                                    ->unique(ignoreRecord: true)
                                    ->helperText(__('countries.tooltips.cca2')),
                                TextInput::make('cca3')
                                    ->label(__('countries.fields.cca3'))
                                    ->maxLength(3)
                                    ->helperText(__('countries.tooltips.cca3')),
                                TextInput::make('ccn3')
                                    ->label(__('countries.fields.ccn3'))
                                    ->maxLength(3)
                                    ->helperText(__('countries.tooltips.ccn3')),
                                TextInput::make('iso_code')
                                    ->label(__('countries.fields.iso_code'))
                                    ->maxLength(10),
                            ]),
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('region')
                                    ->label(__('countries.fields.region'))
                                    ->maxLength(100),
                                TextInput::make('subregion')
                                    ->label(__('countries.fields.subregion'))
                                    ->maxLength(100),
                            ]),
                        Textarea::make('description')
                            ->label(__('countries.fields.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                SchemaSection::make(__('countries.sections.location_info'))
                    ->schema([
                        SchemaGrid::make(3)
                            ->schema([
                                TextInput::make('latitude')
                                    ->label(__('countries.fields.latitude'))
                                    ->numeric()
                                    ->step(0.000001)
                                    ->helperText(__('countries.validation.latitude_numeric')),
                                TextInput::make('longitude')
                                    ->label(__('countries.fields.longitude'))
                                    ->numeric()
                                    ->step(0.000001)
                                    ->helperText(__('countries.validation.longitude_numeric')),
                                TextInput::make('timezone')
                                    ->label(__('countries.fields.timezone'))
                                    ->maxLength(50),
                            ]),
                        SchemaGrid::make(2)
                            ->schema([
                                KeyValue::make('timezones')
                                    ->label(__('countries.fields.timezones'))
                                    ->keyLabel('Timezone')
                                    ->valueLabel('Offset')
                                    ->columnSpan(1),
                                KeyValue::make('languages')
                                    ->label(__('countries.fields.languages'))
                                    ->keyLabel('Language')
                                    ->valueLabel('Code')
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columns(2),
                SchemaSection::make(__('countries.sections.economic_info'))
                    ->schema([
                        SchemaGrid::make(3)
                            ->schema([
                                TextInput::make('currency_code')
                                    ->label(__('countries.fields.currency_code'))
                                    ->maxLength(3)
                                    ->helperText(__('countries.validation.currency_code_invalid')),
                                TextInput::make('currency_symbol')
                                    ->label(__('countries.fields.currency_symbol'))
                                    ->maxLength(5),
                                TextInput::make('phone_code')
                                    ->label(__('countries.fields.phone_code'))
                                    ->maxLength(10)
                                    ->helperText(__('countries.validation.phone_code_invalid')),
                            ]),
                        SchemaGrid::make(3)
                            ->schema([
                                TextInput::make('phone_calling_code')
                                    ->label(__('countries.fields.phone_calling_code'))
                                    ->maxLength(10),
                                TextInput::make('vat_rate')
                                    ->label(__('countries.fields.vat_rate'))
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->helperText(__('countries.validation.vat_rate_numeric')),
                                Hidden::make('currencies')
                                    ->default([]),
                            ]),
                        SchemaGrid::make(2)
                            ->schema([
                                Toggle::make('is_eu_member')
                                    ->label(__('countries.fields.is_eu_member'))
                                    ->helperText(__('countries.tooltips.eu_member')),
                                Toggle::make('requires_vat')
                                    ->label(__('countries.fields.requires_vat'))
                                    ->helperText(__('countries.tooltips.requires_vat')),
                            ]),
                    ])
                    ->columns(2),
                SchemaSection::make(__('countries.sections.metadata'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('flag')
                                    ->label(__('countries.fields.flag'))
                                    ->maxLength(255),
                                TextInput::make('svg_flag')
                                    ->label(__('countries.fields.svg_flag'))
                                    ->maxLength(255),
                            ]),
                        SchemaGrid::make(3)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('countries.fields.is_active'))
                                    ->default(true),
                                Toggle::make('is_enabled')
                                    ->label(__('countries.fields.is_enabled'))
                                    ->default(true),
                                TextInput::make('sort_order')
                                    ->label(__('countries.fields.sort_order'))
                                    ->numeric()
                                    ->default(0),
                            ]),
                        KeyValue::make('metadata')
                            ->label(__('countries.fields.metadata'))
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->helperText(__('countries.tooltips.metadata'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        // The infolist must operate on the provided $schema instance to render
        // details on the view page; returning an undefined variable caused the
        // Filament view to crash during runtime, so we now build on $schema.
        return $schema
            ->schema([
                InfolistSection::make(__('countries.sections.basic_info'))
                    ->schema([
                        InfolistGrid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('countries.fields.name'))
                                    ->weight('bold'),
                                TextEntry::make('name_official')
                                    ->label(__('countries.fields.name_official'))
                                    ->placeholder('—'),
                            ]),
                        InfolistGrid::make(3)
                            ->schema([
                                TextEntry::make('cca2')
                                    ->label(__('countries.fields.cca2')),
                                TextEntry::make('cca3')
                                    ->label(__('countries.fields.cca3')),
                                TextEntry::make('iso_code')
                                    ->label(__('countries.fields.iso_code')),
                            ]),
                    ])
                    ->columns(1),
                InfolistSection::make(__('countries.sections.location_info'))
                    ->schema([
                        InfolistGrid::make(2)
                            ->schema([
                                TextEntry::make('region')
                                    ->label(__('countries.fields.region')),
                                TextEntry::make('subregion')
                                    ->label(__('countries.fields.subregion')),
                            ]),
                        InfolistGrid::make(2)
                            ->schema([
                                TextEntry::make('timezone')
                                    ->label(__('countries.fields.timezone')),
                                TextEntry::make('phone_calling_code')
                                    ->label(__('countries.fields.phone_calling_code'))
                                    ->formatStateUsing(static fn (?string $state): string => filled($state) ? '+' . $state : '—'),
                            ]),
                    ])
                    ->columns(1),
                InfolistSection::make(__('countries.sections.economic_info'))
                    ->schema([
                        InfolistGrid::make(3)
                            ->schema([
                                TextEntry::make('currency_code')
                                    ->label(__('countries.fields.currency_code')),
                                TextEntry::make('currency_symbol')
                                    ->label(__('countries.fields.currency_symbol'))
                                    ->placeholder('—'),
                                TextEntry::make('vat_rate')
                                    ->label(__('countries.fields.vat_rate'))
                                    ->formatStateUsing(static function ($state): string {
                                        // Display a friendly VAT label while gracefully handling null data.
                                        return $state !== null ? number_format((float) $state, 2) . '%' : '—';
                                    }),
                            ]),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('countries.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('cca2')
                    ->label(__('countries.fields.cca2'))
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('region')
                    ->label(__('countries.fields.region'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('subregion')
                    ->label(__('countries.fields.subregion'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('currency_code')
                    ->label(__('countries.fields.currency_code'))
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('phone_calling_code')
                    ->label(__('countries.fields.phone_calling_code'))
                    ->prefix('+')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_eu_member')
                    ->label(__('countries.fields.is_eu_member'))
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('requires_vat')
                    ->label(__('countries.fields.requires_vat'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('is_active')
                    ->label(__('countries.fields.is_active'))
                    ->getStateUsing(static fn (Country $record): string => $record->is_active ? __('countries.statuses.active') : __('countries.statuses.inactive'))
                    ->colors([
                        'success' => static fn (string $state): bool => $state === __('countries.statuses.active'),
                        'danger'  => static fn (string $state): bool => $state === __('countries.statuses.inactive'),
                    ])
                    ->toggleable(),
                TextColumn::make('cities_count')
                    ->label('Cities')
                    ->counts('cities')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('addresses_count')
                    ->label('Addresses')
                    ->counts('addresses')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('region')
                    ->label(__('countries.filters.region'))
                    ->options(
                        fn (): array => Country::query()
                            ->orderBy('region')
                            ->pluck('region', 'region')
                            ->filter(static fn (?string $region): bool => filled($region))
                            ->all()
                    )
                    ->searchable(),
                SelectFilter::make('subregion')
                    ->label(__('countries.filters.subregion'))
                    ->options(
                        fn (): array => Country::query()
                            ->orderBy('subregion')
                            ->pluck('subregion', 'subregion')
                            ->filter(static fn (?string $subregion): bool => filled($subregion))
                            ->all()
                    )
                    ->searchable(),
                TernaryFilter::make('is_eu_member')
                    ->label(__('countries.filters.eu_member'))
                    ->boolean(),
                TernaryFilter::make('requires_vat')
                    ->label(__('countries.filters.requires_vat'))
                    ->boolean(),
                TernaryFilter::make('is_active')
                    ->label(__('countries.filters.is_active'))
                    ->boolean(),
                SelectFilter::make('currency_code')
                    ->label(__('countries.filters.currency_code'))
                    ->options(
                        fn (): array => Country::query()
                            ->orderBy('currency_code')
                            ->pluck('currency_code', 'currency_code')
                            ->filter(static fn (?string $currencyCode): bool => filled($currencyCode))
                            ->all()
                    )
                    ->searchable(),
                Filter::make('created_at')
                    ->form([
                        SupportFlatpickr::makeDate('created_from')
                            ->label('Created from'),
                        SupportFlatpickr::makeDate('created_until')
                            ->label('Created until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $createdFrom = $data['created_from'] ?? null;
                        $createdUntil = $data['created_until'] ?? null;

                        return $query
                            ->when(
                                filled($createdFrom),
                                fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $createdFrom),
                            )
                            ->when(
                                filled($createdUntil),
                                fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $createdUntil),
                            );
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    TableViewAction::make(),
                    TableEditAction::make(),
                    TableDeleteAction::make(),
                    Action::make('activate')
                        ->label(__('countries.actions.activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (?Country $record): void {
                            if (! $record instanceof Country) {
                                return;
                            }

                            $record->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('countries.notifications.activated'))
                                ->success()
                                ->send();
                        })
                        ->visible(static fn (?Country $record): bool => ($record?->is_active ?? false) === false),
                    Action::make('deactivate')
                        ->label(__('countries.actions.deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (?Country $record): void {
                            if (! $record instanceof Country) {
                                return;
                            }

                            $record->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('countries.notifications.deactivated'))
                                ->success()
                                ->send();
                        })
                        ->visible(static fn (?Country $record): bool => ($record?->is_active ?? false) === true),
                ]),
            ])
            ->bulkActions([
                TableBulkActionGroup::make([
                    TableDeleteBulkAction::make(),
                    TableBulkAction::make('activate')
                        ->label(__('countries.actions.activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (iterable $records): void {
                            collect($records)->each(static function (Country $record): void {
                                if ($record instanceof Country) {
                                    $record->update(['is_active' => true]);
                                }
                            });
                            Notification::make()
                                ->title(__('countries.notifications.bulk_activated'))
                                ->success()
                                ->send();
                        }),
                    TableBulkAction::make('deactivate')
                        ->label(__('countries.actions.deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (iterable $records): void {
                            collect($records)->each(static function (Country $record): void {
                                if ($record instanceof Country) {
                                    $record->update(['is_active' => false]);
                                }
                            });
                            Notification::make()
                                ->title(__('countries.notifications.bulk_deactivated'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->withoutGlobalScopes([ActiveScope::class]))
            ->defaultSort('name')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    /**
     * Handle getRelations functionality with proper error handling.
     */
    public static function getRelations(): array
    {
        return [
            AddressesRelationManager::class,
            CitiesRelationManager::class,
            CustomersRelationManager::class,
            UsersRelationManager::class,
        ];
    }

    /**
     * Handle getPages functionality with proper error handling.
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'view'   => Pages\ViewCountry::route('/{record}'),
            'edit'   => Pages\EditCountry::route('/{record}/edit'),
        ];
    }

    /**
     * Handle getGlobalSearchResultDetails functionality with proper error handling.
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        if (! $record instanceof Country) {
            return [];
        }

        return [
            'Code'      => $record->cca2,
            'Region'    => $record->region,
            'Currency'  => $record->currency_code,
            'EU Member' => $record->is_eu_member ? 'Yes' : 'No',
        ];
    }

    /**
     * Get the global search result actions.
     */
    public static function getGlobalSearchResultActions(Model $record): array
    {
        if (! $record instanceof Country) {
            return [];
        }

        $actions = [];

        try {
            $actions[] = Action::make('view')
                ->label(__('countries.actions.view'))
                ->icon('heroicon-o-eye')
                ->url(self::getUrl('view', ['record' => $record->getKey()]));
        } catch (Throwable) {
            // Route might not exist, skip this action
        }

        try {
            $actions[] = Action::make('edit')
                ->label(__('countries.actions.edit'))
                ->icon('heroicon-o-pencil')
                ->url(self::getUrl('edit', ['record' => $record->getKey()]));
        } catch (Throwable) {
            // Route might not exist, skip this action
        }

        return $actions;
    }
}
